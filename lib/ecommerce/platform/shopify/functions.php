<?php
require_once dirname(dirname(dirname(dirname(__DIR__)))) . "/vendor/autoload.php";

/**
 * Verify Shopify Webhook
 */
function verify_webhook($data, $hmac_header){

	$calculated_hmac = base64_encode(hash_hmac("sha256", $data, STOREFRONT_WEBHOOK_SECRET, true));

	return hash_equals($hmac_header, $calculated_hmac);
}

/**
 * normalizeCart
 */
function normalizeCart($cart){

	/* --- since this is a Shopify cart, we'll keep the id and webUrl --- */
	$object = new stdClass();
	$object->id = $cart->id;
	$object->lineItems = array();
	$object->webUrl = $cart->webUrl;

	if(!empty($cart->lineItems->edges)){

		/* --- Loop through our line items --- */
		foreach($cart->lineItems->edges as $key => $lineItem){

			$lineItemObject = new stdClass();
			$lineItemObject->product_id = $lineItem->node->variant->product->id;
			$lineItemObject->variant_id = $lineItem->node->variant->id;
			$lineItemObject->quantity = $lineItem->node->quantity;
			$lineItemObject->note = "";
			$lineItemObject->image = $lineItem->node->variant->image->src;
			$lineItemObject->price = $lineItem->node->variant->price;
			$lineItemObject->variant_title = $lineItem->node->variant->title;
			$lineItemObject->product_title = $lineItem->node->title;
			$lineItemObject->url = "/products" . $lineItem->node->variant->product->handle . "/";
			$lineItemObject->sku = $lineItem->node->variant->sku;
			$lineItemObject->product_type = $lineItem->node->variant->product->productType;
			$lineItemObject->vendor = $lineItem->node->variant->product->vendor;
			$lineItemObject->attributes = new stdClass();

			/* --- check for custom attributes --- */
			if(!empty($lineItem->node->customAttributes)){

				/* --- Loop through our custom attributes if they exist --- */
				foreach($lineItem->node->customAttributes as $attribute){
					$lineItemObject->attributes->{$attribute->key} = $attribute->value;
				}
			}

			array_push($object->lineItems, $lineItemObject);
		}
	}

	return $object;
}

/**
 * Return all cache data as a clean object
 */
function returnAllDataAsObject($cache, $type = null){

	$obj = new stdClass();
	$dir = new FilesystemIterator($cache);

	foreach($dir as $fileinfo){
		
		$contents = json_decode(file_get_contents($fileinfo->getPath() . "/" . $fileinfo->getFilename()));

		if(is_null($contents->published_at)) continue;
		if(!is_null($type) && slugify($contents->product_type) !== $type) continue;

		$obj->{$contents->handle} = $contents;
	}

	return $obj;
}

/**
 * Return all cache data as an array, sort support
 */
function returnAllDataAsArray($cache, $type = null, $sort = null, $direction = "desc"){

	global $STOREFRONT;

	$array = array();
	$dir = new FilesystemIterator($cache);

	/* --- Cool, passed the first test, now let's check our sales channel --- */
	$sales_channel = $STOREFRONT->client->ProductListing()->productIds();

	foreach($dir as $fileinfo){
		
		$contents = json_decode(file_get_contents($fileinfo->getPath() . "/" . $fileinfo->getFilename()));

		if(!in_array($contents->id, $sales_channel["product_ids"])) continue;
		if(!is_null($type) && slugify($contents->product_type) !== $type) continue;

		array_push($array, $contents);
	}

	if(!is_null($sort)){

		$sort = new sortObject($sort, $direction);
		$array = $sort->sort($array);
	}

	return $array;
}

/**
 * Sort
 */
class sortObject{

	private $key;
	private $direction;

	public function __construct($key, $direction){
		$this->key = $key;
		$this->dir = $direction;
	}

	public function sortKeys($a, $b){

		switch ($this->key) {

			case "author":
			case "category":
			case "location":

				if ($this->dir == "asc") {
					return strcmp($a->data->{$this->key}->slug, $b->data->{$this->key}->slug);
				} else {
					return strcmp($b->data->{$this->key}->slug, $a->data->{$this->key}->slug);
				}

				break;

			case "published_date":
			case "title":

				if ($this->dir == "asc") {
					return strcmp($a->data->{$this->key}, $b->data->{$this->key});
				} else {
					return strcmp($b->data->{$this->key}, $a->data->{$this->key});
				}

				break;

			case "relevance":

				if ($this->dir == "asc") {
					return strcmp($a->relevance, $b->relevance);
				} else {
					return strcmp($b->relevance, $a->relevance);
				}

				break;

			case "first_publication_date":
			default:

				if ($this->dir == "asc") {
					return strcmp($a->{$this->key}, $b->{$this->key});
				} else {
					return strcmp($b->{$this->key}, $a->{$this->key});
				}

				break;
		}
	}

	public function sort($object){

		usort($object, array($this, "sortKeys"));
		/* --------------- *
		echo "<br><pre>";
		print_r($object);
		echo "</pre>";
		/* --------------- */

		return $object;
	}
}

/**
 * Return order object
 */
function returnOrderObject($orders, $orderId){

	$object = null;

	foreach($orders as $key => $obj){
		if(strpos($obj->node->customerUrl, $orderId) !== false){
			$object = $obj->node;
		}
	}

	return $object;
}

/**
 * Update Reference Sheet
 */
function update_product_reference($id, $handle, $updated_at, $vendor, $remove = false){

	$update = false;
	$cachefile = FILE_CACHE . "storefront/products.json";
	$reference = json_decode(file_get_contents($cachefile));
	$ref = (string)$id;

	if(!is_object($reference) || $ref === null || $ref === ""){
		return null;
	}

	try{
		/* --- Remove if key exists --- */
		if($remove === true && isset($reference->{$ref})){
			$handle = $reference->{$ref}->handle;
			unset($reference->{$ref});
		} else {
			/* --- Clear, then reset --- */
			unset($reference->{$ref});
			$reference->{$ref} = new stdClass();
			$reference->{$ref}->handle = $handle;
			$reference->{$ref}->vendor = $vendor;
			$reference->{$ref}->updated_at = $updated_at;
		}

		file_put_contents($cachefile, json_encode($reference), LOCK_EX);
		chmod($cachefile, 0664);

		return $handle;
	} catch(\Exception $e){
		error_log($e->getMessage());
		return null;
	}
}

/**
 * Shopify Request Function
 */
function shopifyRequest($query){

	$site = "https://" . STOREFRONT_DOMAIN . "/api/graphql";
	$headers = array(
		"Accept: application/json",
		"Content-type: application/graphql",
		"X-Shopify-Storefront-Access-Token: " . STOREFRONT_ACCESS_TOKEN
	);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $site);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$result = curl_exec($ch);
	$info = curl_getinfo($ch);
	$data = json_decode($result);

    if(curl_error($ch)){
    	
        $error_msg = curl_error($ch);
		/* --------------- *
		echo "<pre>";
		print_r($data);
		print_r($error_msg);
		echo "</pre><br>";
		/* --------------- */
        error_log($error_msg);
    }

	curl_close($ch);

	return $data;
}

/**
 * Shopify Admin Request Function
 */
function shopifyAdminRequest($query){

	$site = "https://" . STOREFRONT_DOMAIN . "/admin/api/graphql";
	$headers = array(
		"Accept: application/json",
		"Content-type: application/graphql",
		"X-Shopify-Access-Token: " . STOREFRONT_PASSWORD
	);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $site);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$result = curl_exec($ch);
	$info = curl_getinfo($ch);
	$data = json_decode($result);

    if(curl_error($ch)){

        $error_msg = curl_error($ch);
		/* --------------- *
		echo "<pre>";
		print_r($data);
		print_r($error_msg);
		echo "</pre><br>";
		/* --------------- */
        error_log($error_msg);
    }

	curl_close($ch);

	return $data;
}

/**
 * Shopify GraphQL string fix
 *  - Append and Prepend your object key with $char
 *	  so that we can strip the double quotes around
 *	  the key. Default replace is _ (underscore)
 */
function shopifyQueryFix($string, $char = "_"){

	$string = str_replace("\"$char", "", $string);
	$string = str_replace("$char\"", "", $string);

	return $string;
}

/**
 * Shopify Return Image Size
 */
function shopifyReturnImage($url, $size = "1000x"){

	$ext = parse_url($url);
	$array = explode(".", $ext["path"]);
	$copy = array_slice($array, 0, -1);

	$lastKey = end($array);
	$path = implode(".", $copy);

	if(isset($ext["host"])) {
		$url = $ext["scheme"] . "://" . $ext["host"] . $path . "_" . $size . "." . $lastKey;
	} else {
		$url = "";
	}

	if(isset($ext["query"])){
		$url .= "?" . $ext["query"];
	}

	return $url;
}

/**
 * Re-Key the Metafields object
 */
function rekeyMetafields($array){

	$object = new stdClass();

	foreach($array as $key => $obj){

		// Create new Key if it's not there
		if(!isset($object->{$obj["namespace"]})){
			$object->{$obj["namespace"]} = new stdClass();
		}

		if($obj["key"] == "height" || $obj["key"] == "width" || $obj["key"] == "depth"){
			$object->{$obj["namespace"]}->{$obj["key"]} = (float)$obj["value"];
		} else {
			$object->{$obj["namespace"]}->{$obj["key"]} = $obj["value"];
		}
	}

	return $object;
}

/**
 * Fix arrays
 *  - Very important as the request doesn't seem to match the storage object
 */
function arrayToObject($array){

	$keep = false;

	if (!is_array($array)) {
		return $array;
	}

	if (isset($array[0])) {
		$keep = true;
	}

	if (is_array($array) && count($array) > 0 && $keep === false) {

		$object = new stdClass();

		foreach ($array as $name => $value) {
			$object->$name = arrayToObject($value);
		}

		return $object;
	} else {

		$object = array();

		foreach ($array as $name => $value) {
			$object[$name] = arrayToObject($value);
		}

		return $object;
	}
}

/**
 * String Encription
 */
function encryptHash(string $string, string $key): string{

	if (mb_strlen($key, "8bit") !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
		throw new RangeException("Key is not the correct size (must be 32-bit string)");
	}

	$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
	$cipher = base64_encode($nonce . sodium_crypto_secretbox($string, $nonce, $key));

	sodium_memzero($string);
	sodium_memzero($key);

	return $cipher;
}

function decryptHash(string $encrypted, string $key): string{

	$decoded = base64_decode($encrypted);
	$nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, "8bit");
	$ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, "8bit");

	$plain = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

	if (!is_string($plain)) {
		throw new Exception("Invalid MAC");
	}

	sodium_memzero($ciphertext);
	sodium_memzero($key);

	return $plain;
}
?>