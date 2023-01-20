<?php
require_once __DIR__ . "/functions.php";

/**
 * Storefront Core definition
 */
class Storefront implements StorefrontCore {

	/**
	 * Check Logout
	 */
	public function Logout(){
			
		if(isset($_COOKIE["cat"])){

			/* --- Kill Cached Customer --- */
			if(file_exists(FILE_CACHE . "customers/" . md5($_COOKIE["cat"] . SALT))){

				/* --- Delete file --- */
				unlink(FILE_CACHE . "customers/" . md5($_COOKIE["cat"] . SALT));
			}

			/* --- Kill cookie --- */
			unset($_COOKIE["cat"]);
	    	setcookie("cat", null, time() - 3600, "/");
		}

		/* --- Kill multipass cookie --- */
		if(isset($_COOKIE["mltp"])){

			unset($_COOKIE["mltp"]);
	    	setcookie("mltp", null, time() - 3600, "/");
		}
	}
	
	/**
	 * Check Checkout
	 *  - Blowout old cart and checkout if the old checkout was completed
	 *	- !!! TODO: Move this check into the JS layer in order to remove render blocking aspect
	 */
	public function checkCheckout(){

		if(empty($_COOKIE["cart"]) || $_SERVER["REQUEST_METHOD"] === "POST" || (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "Highway")) return;

		$checkoutId = $_COOKIE["cart"];

		$query = "{ node(id: \"$checkoutId\"){ ... on Checkout{ completedAt } } }";
		$check = shopifyRequest($query);

		if(isset($_COOKIE["subcart"])){
			$checkSub = rechargeRequest("/checkouts/" . $_COOKIE["subcart"], 0);
		}

		if(!empty($check->data->node->completedAt) || isset($checkSub) && !empty($checkSub->checkout) && !empty($checkSub->checkout->completed_at)){

			if(!empty($_COOKIE["cart"]) && file_exists(FILE_CACHE . "carts/" . $_COOKIE["cart"])){

				/* --- Delete file --- */
				unlink(FILE_CACHE . "carts/" . $_COOKIE["cart"]);
			}

			if(!empty($_COOKIE["subcart"]) && file_exists(FILE_CACHE . "carts/" . $_COOKIE["subcart"])){

				/* --- Delete file --- */
				unlink(FILE_CACHE . "carts/" . $_COOKIE["subcart"]);
			}

			unset($_COOKIE["cart"]);
			setcookie("cart", "", time() - 3600, "/");

			unset($_COOKIE["subcart"]);
			setcookie("subcart", "", time() - 3600, "/");
		}
	}

	/**
	 * Get Storefront Client Connection
	 */
	public function setClient(){

		$config = array(
			"ShopUrl" => STOREFRONT_DOMAIN,
			"Password" => STOREFRONT_PASSWORD,
			"ApiKey" => STOREFRONT_API_KEY
		);

		/* --- Initialize Shopify SDK --- */
		PHPShopify\ShopifySDK::config($config);

		return new PHPShopify\ShopifySDK;
	}

	/**
	 * Get Storefront Information
	 */
	public function getShopInfo(){

		global $STOREFRONT;

		/* --- API requests don't need to check this --- */
		if($_SERVER["REQUEST_METHOD"] === "POST") return;

		/**
		 * Get Shop Information
		 */
		$infoCache = FILE_CACHE . "storefront/info";

		if(!file_exists($infoCache) || time() - filemtime($infoCache) > 24 * 3600){

			/* --- Cache store info --- */
			$shopDataQuery = "{ shop{ name currencyCode } }";
			$STOREFRONT->shop = shopifyAdminRequest($shopDataQuery);

			file_put_contents($infoCache, encryptHash(json_encode($STOREFRONT->shop), SALT));
			chmod($infoCache, 0664);

		} else {

			/* --- Grab cached store info --- */
			$STOREFRONT->shop = json_decode(decryptHash(file_get_contents($infoCache), SALT));
		}
	}

	/**
	 * Get customer information
	 *  - Check cookies for customerAccessToken
	 *  - If token exists grab customer information
	 *  - otherwise customer will need to log in again
	 */
	public function getCustomer(){

		global $STOREFRONT;

		/* --- Establish our customer object --- */
		$STOREFRONT->customer = new stdClass();
		$STOREFRONT->customer->token = self::getCustomerAccessToken($STOREFRONT);
		$STOREFRONT->customer->ip = $_SERVER["REMOTE_ADDR"];
		$STOREFRONT->customer->data = self::getCustomerData(FILE_CACHE . "customers/", $STOREFRONT->customer->token);

		if(defined("SHOPIFY_MULTIPASS_KEY") && SHOPIFY_MULTIPASS_KEY !== "" && $STOREFRONT->customer->token !== null && !isset($_COOKIE["mltp"])){
			self::getMultipassToken($STOREFRONT);
		}

		/**
		 * Establish Customer Elasticsearch Data
		 *	- !!! TODO: Move Elasticsearch functions over to Elasticsearch folder integration
		 */
		if(defined("SITE_SEARCH") && SITE_SEARCH === true && isset($STOREFRONT->customer->data) && isset($STOREFRONT->customer->data->id)){
			
			global $DATABASE;

			$params = array(
				"index" => SITE_SEARCH_KEY . "_shop",
				"id" => $STOREFRONT->customer->data->id
			);

			if(isset($STOREFRONT->customer->ip) && $STOREFRONT->customer->ip !== ""){
				$STOREFRONT->customer->data->last_ip = $STOREFRONT->customer->ip;
			}

			try{
				$check = esGetUser($DATABASE->client, $STOREFRONT->customer->data->id);
			} catch(Exception $e){
				error_log("ES Check Failed: " . $e->getMessage() . PHP_EOL);
			}

			if(!isset($check) || isset($check->found) && $check->found === false){
				
				/* --- Create User --- */
				$object = array(
					"user" => $STOREFRONT->customer->data,
					"wishlist" => array()
				);

				$create = esUpdateUser($DATABASE->client, $object, $STOREFRONT->customer->data->id);
				$user = esGetUser($DATABASE->client, $STOREFRONT->customer->data->id);

			} else {
				
				$user = esGetUser($DATABASE->client, $STOREFRONT->customer->data->id);

				$object = array(
					"user" => $STOREFRONT->customer->data,
					"wishlist" => $user["_source"]["wishlist"]
				);

				$update = esUpdateUser($DATABASE->client, $object, $STOREFRONT->customer->data->id);
			}
		}
	}

	/**
	 * Get customer access token
	 *  - Lookup the access token, if it exists in cookie or multipass
	 */
	private function getCustomerAccessToken($STOREFRONT){

		/* --- Return cached customer access token --- */
		if(!empty($_COOKIE["cat"])) return $_COOKIE["cat"];

		/* --- Return if no post --- */
		if(empty($_POST["customer"])) return null;

		/* --- Redirect the user to defined page after login --- */
		// !!! TODO: Make sure this is still linked up, can't find the reference
		if(isset($_POST["checkout_url"])){
			$STOREFRONT->reroute = $_POST["checkout_url"];
		}

		$customer = $_POST["customer"];

		/* --- Login case --- */
		if(isset($_POST["type"]) && $_POST["type"] == "login"){

			$type = "customerAccessTokenCreate";
			$email = json_encode($customer["email"]);
			$password = json_encode($customer["password"]);

			$query = "mutation{ $type(input: { email: $email, password: $password }){ customerAccessToken{ accessToken expiresAt } customerUserErrors{ field message } } }";

		/* --- Activate case --- */
		} else {

			$type = "customerActivate";
			$id = base64_encode("gid://shopify/Customer/" . $_POST["id"]);
			$token = $_POST["token"];
			$password = json_encode($customer["password"]);

			$query = "mutation{ $type(id: \"$id\", input: { activationToken: \"$token\", password: $password }){ customerAccessToken{ accessToken expiresAt } customerUserErrors{ field message } } }";

		}

		/* --- Request our Access Token --- */
		$accessTokenQuery = shopifyRequest($query);

		/* --- Return Null on error --- */
		if(!empty($accessTokenQuery->data->{$type}->customerUserErrors)){

			// !!! TODO: change this to array form, can support multiple errors
			$STOREFRONT->customer->error = $accessTokenQuery->data->{$type}->customerUserErrors[0]->message;

			return null;
		}

		/* --- Hey, set our token --- */
		$accessToken = $accessTokenQuery->data->{$type}->customerAccessToken->accessToken;

		/* --- Set the cookie --- */
		setcookie("cat", $accessToken, time() + (86400 * 30), "/");

		/* --- Associate checkout if it exists --- */
		if(!empty($_COOKIE["chk"])){

			$checkoutObject = json_decode($_COOKIE["chk"]);
			$checkoutToken = $checkoutObject->id;
			$query = "mutation{ checkoutCustomerAssociateV2(customerAccessToken: \"$accessToken\", checkoutId: \"$checkoutToken\"){ checkout{ id webUrl lineItems(first: 50){ edges{ node{ id title quantity variant{ id price title image(maxWidth: 200){ altText src } product{ id images(first: 1, maxWidth: 200){ edges{ node{ altText src } } } vendor } } } } } } } }";

			shopifyRequest($query);
		}

		/* --- Return our token --- */
		return $accessToken;
	}

	/**
	 * Get customer data
	 *  - will encrypt and store for 30 days so customer can stay logged in
	 */
	public function getCustomerData($cache, $token, $flush = false){

		global $STOREFRONT;

		/* --- Mask the Token a little bit --- */
		$filename = md5($token . SALT);

		/* --- Check for Cached file --- */
		if(file_exists($cache . $filename) && $flush === false){

			/* --- Return raw customer data, decrypted --- */
			return json_decode(decryptHash(file_get_contents($cache . $filename), SALT));
		}

		/* --- Refresh Customer if Flush is set --- */
		if($token && $token != null){

			$query = "{ customer(customerAccessToken: \"$token\"){ acceptsMarketing createdAt updatedAt displayName email firstName id lastIncompleteCheckout{ id } lastName phone addresses(first: 250){ edges{ cursor node{ address1 address2 city company country countryCodeV2 firstName lastName formatted formattedArea id latitude longitude name phone province provinceCode zip } } } defaultAddress{ address1 address2 city company country countryCodeV2 firstName lastName formatted formattedArea id latitude longitude name phone province provinceCode zip } orders(first: 250){ pageInfo { hasNextPage hasPreviousPage } edges{ cursor node{ currencyCode customerLocale customerUrl email id name orderNumber phone processedAt statusUrl subtotalPriceV2 { amount } totalPriceV2 { amount } totalRefundedV2 { amount } totalShippingPriceV2 { amount } totalTaxV2 { amount } shippingAddress{ address1 address2 city company country countryCodeV2 firstName lastName formatted formattedArea id latitude longitude name phone province provinceCode zip } shippingDiscountAllocations{ allocatedAmount{ amount } discountApplication { allocationMethod targetSelection targetType value } } lineItems(first: 250){ edges{ cursor node{ quantity title variant{ availableForSale compareAtPriceV2 { amount } image{ altText src } priceV2 { amount } product { handle id } id sku title selectedOptions{ name value } } customAttributes{ key value } discountAllocations{ allocatedAmount{ amount } discountApplication{ allocationMethod targetSelection targetType value } } } } } discountApplications(first: 250){ edges{ cursor node{ allocationMethod targetSelection targetType value } } } } } } } }";

			$customer = shopifyRequest($query);
			/* --------------- *
			echo "<pre><br>";
			print_r($customer);
			echo "</pre>";
			/* --------------- */

			try{

				/* --- Get customer Metafields --- */
				$customerId = base64_decode($customer->data->customer->id);
				$customerId = explode("/Customer/", $customerId);
				$customerId = $customerId[1];

				$metafields = $STOREFRONT->client->Customer($customerId)->Metafield()->get();
				$customer->data->customer->metafields = self::reKeyMetafields($metafields);

			} catch(Exception $e){
				error_log("CUSTOMER METAFIELDS ERROR: " . $e->getMessage() . PHP_EOL);
			}

			/* --- Normalize this object --- */
			$normalizedCustomer = self::normalizeCustomer($customer->data->customer);

			/* --- Store encrypted Customer data --- */
			file_put_contents($cache . $filename, encryptHash(json_encode($normalizedCustomer), SALT));
			chmod($cache . $filename, 0664);

			return $normalizedCustomer;
			
		} else {

			return false;
		}
	}

	/**
	 * updateProductReference
	 */
	public function updateProductReference($id, $handle, $updated_at, $type, $remove = false){

		$update = false;
		$cachefile = FILE_CACHE . "storefront/products.json";
		$reference = (file_exists($cachefile)) ? json_decode(file_get_contents($cachefile)) : new stdClass();
		$ref = (string)$id;

		if(!is_object($reference) || $ref === null || $ref === ""){
			return null;
		}

		try{
			/* --- Remove if key exists --- */
			if($remove === true && isset($reference->{$ref})){
				$handle = $reference->{$ref}->handle;
				unset($reference->{$ref});
			}

			if($remove === false){

				/* --- Clear, then reset --- */
				if(isset($reference->{$ref})){
					unset($reference->{$ref});
				}

				$reference->{$ref} = new stdClass();
				$reference->{$ref}->handle = $handle;
				$reference->{$ref}->type = $type;
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
	 * updateCollectionReference
	 */
	public function updateCollectionReference($id, $handle, $updated_at, $remove = false){

		$update = false;
		$cachefile = FILE_CACHE . "storefront/collections.json";
		$reference = (file_exists($cachefile)) ? json_decode(file_get_contents($cachefile)) : new stdClass();
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
	 * Normalize the customer object
	 */
	public function normalizeCustomer($customer){

		/* --------------- *
		echo "<pre><br>";
		print_r($customer);
		echo "</pre>";
		/* --------------- */
		$normalizedCustomer = new stdClass();

		/* --- Base Object --- */
		$normalizedCustomer->id = $customer->id;
		$normalizedCustomer->created_at = $customer->createdAt;
		$normalizedCustomer->updated_at = $customer->updatedAt;
		$normalizedCustomer->first_name = $customer->firstName;
		$normalizedCustomer->last_name = $customer->lastName;
		$normalizedCustomer->display_name = $customer->displayName;
		$normalizedCustomer->email = $customer->email;
		$normalizedCustomer->phone = $customer->phone;
		$normalizedCustomer->accepts_marketing = $customer->acceptsMarketing;
		$normalizedCustomer->last_incomplete_checkout = $customer->lastIncompleteCheckout;

		/* --- Grab out default address --- */
		$normalizedCustomer->default_address = self::normalizeAddress($customer->defaultAddress);
		$normalizedCustomer->addresses = array();

		/* --- Grab our other addresses --- */
		foreach($customer->addresses->edges as $key => $address){

			/* --- Skip default address --- */
			if($address->node->id === $customer->defaultAddress->id) continue;

			/* --- Push address --- */
			array_push($normalizedCustomer->addresses, self::normalizeAddress($address->node));
		}
		
		/* --- Order object --- */
		$normalizedCustomer->orders = self::normalizeOrders($customer->orders->edges);

		/* --- Metafields passthrough --- */
		$normalizedCustomer->metafields = $customer->metafields;

		/* --- Return customer --- */
		return $normalizedCustomer;
	}

	/**
	 * reKeyMetafields
	 */
	public function reKeyMetafields($array){

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
	 * Normalize an address object
	 */
	private function normalizeAddress($address){

		// !!! TODO: build this out

		return $address;
	}

	/**
	 * Normalize an order object
	 */
	private function normalizeOrders($orders){

		// !!! TODO: build this out

		return $orders;
	}

	/**
	 * Get our multipass token
	 */
	private function getMultipassToken($STOREFRONT){

		/* --- Reroute to current page --- */
		if(isset($_POST) && isset($_POST["reroute"])){
			$return_to = PROTOCOL . HOST . $_POST["reroute"];
		} else {
			$return_to = PROTOCOL . HOST . "/account/";
		}

		$customer_data = array(
		    "email" => $STOREFRONT->customer->data->email,
		    "return_to" => $return_to
		);

		/* --------------- *
		echo "<pre><br>";
		print_r($customer_data);
		echo "</pre>";
		/* --------------- */

		if($STOREFRONT->customer->ip !== "::1" && $STOREFRONT->customer->ip !== "127.0.0.1"){
			$customer_data["remote_ip"] = $STOREFRONT->customer->ip;
		}

		$multipass = new ShopifyMultipass(SHOPIFY_MULTIPASS_KEY);
		$token = $multipass->generate_token($customer_data);

		$url = "https://" . STOREFRONT_DOMAIN . "/account/login/multipass/" . $token;

		if(isset($token) && $token !== ""){
			setcookie("mltp", "yes", time() + (86400 * 30), "/");
			header("Location: " . $url);

			die();
		}
	}
}

/**
 * Shopify Multipass function
 */
class ShopifyMultipass {
    
    private $encryption_key;
    private $signature_key;

    public function __construct($multipass_secret) {
        // Use the Multipass secret to derive two cryptographic keys,
        // one for encryption, one for signing
        $key_material = hash("sha256", $multipass_secret, true);
        $this->encryption_key = substr($key_material, 0, 16);
        $this->signature_key = substr($key_material, 16, 16);
    }

    public function generate_token($customer_data_hash) {
        // Store the current time in ISO8601 format.
        // The token will only be valid for a small timeframe around this timestamp.
        $customer_data_hash["created_at"] = date("c");

        // Serialize the customer data to JSON and encrypt it
        $ciphertext = $this->encrypt(json_encode($customer_data_hash));

        // Create a signature (message authentication code) of the ciphertext
        // and encode everything using URL-safe Base64 (RFC 4648)
        return strtr(base64_encode($ciphertext . $this->sign($ciphertext)), '+/', '-_');
    }

    private function encrypt($plaintext) {
        // Use a random IV
        $iv = openssl_random_pseudo_bytes(16);

        // Use IV as first block of ciphertext
        return $iv . openssl_encrypt($plaintext, "AES-128-CBC", $this->encryption_key, OPENSSL_RAW_DATA, $iv);
    }

    private function sign($data) {
        return hash_hmac("sha256", $data, $this->signature_key, true);
    }
}
?>