<?php
require_once dirname(__DIR__) . "/functions.php";

/**
 * Subscription API definition
 */
class SubscriptionAPI implements SubscriptionEndpoints {

	/**
	 * Get Subscription
	 */
	public function subscriptionGet($object){

		$filename = "subscription_" . $object->productId . ".json";
		$file = FILE_CACHE . "subscription/" . $filename;

		/* --- Return or recache file --- */
		if(file_exists($file) && (time() - filemtime($file)) <= 24 * 3600){

			return file_get_contents($file);
		}

		$data = rechargeRequest("/products?shopify_product_ids=" . $object->productId, 0);

		if(!empty($data)){

			/* --- Don't need the array --- */
			$data = (!empty($data->products)) ? $data->products[0] : new stdClass();

			file_put_contents($file, json_encode($data));
			chmod($file, 0664);
		}

		return json_encode($data);
	}

	/**
	 * Create Subscription
	 */
	public function subscriptionCreate($object, $post = 1, $custom = null){

		/* --- Let's check for an existing reCart --- */
		$endpoint = (!empty($object->subscription_checkout)) ? "/checkouts/" . $object->subscription_checkout : "/checkouts";

		/* --- Setup our query --- */
		$query = new stdClass();
		$query->checkout = new stdClass();
		$query->checkout->line_items = array();

		/* --- Check for Shopify checkout association --- */
		if(!empty($_COOKIE["cart"])){

			try {
				$file = json_decode(file_get_contents(FILE_CACHE . "carts/" . $_COOKIE["cart"]));

				$checkoutId = base64_decode($file->id);
				$checkoutId = explode("gid://shopify/Checkout/", $checkoutId);
				$checkoutId = explode("?key=", $checkoutId[1]);

				$query->checkout->external_checkout_id = $checkoutId[0];
				$query->checkout->external_checkout_source = "shopify";

			} catch(\Exception $e){

			}
		}

		/* --- Loop through cart object if we've got multiple products --- */
		if(!empty($object->cart)){

			foreach($object->cart as $key => $line_item){

				/* --- Grab ID from Shopify graphQL ID --- */
				$variantId = explode("/ProductVariant/", base64_decode($line_item->variantId));

				$sub_item = new stdClass();
				$sub_item->quantity = (int)$line_item->quantity;
				$sub_item->variant_id = (int)$variantId[1];
				$sub_item->properties = new stdClass();

				/* --- In the cart loop, we're looking at customAttributes --- */
				if(empty($line_item->customAttributes)){

					/* --- Add our line item --- */
					array_push($query->checkout->line_items, $sub_item);

					continue;
				}

				/* --- Check against our recharge attributes --- */
				foreach($line_item->customAttributes as $attribute){

					switch($attribute->key){

						case "_order_interval_unit":

							$unit = rtrim(strtolower($attribute->value), "s");

							$sub_item->properties->_order_interval_unit = $attribute->value;

							/* --- Break if null --- */
							if($attribute->value === "null") break;

							$sub_item->order_interval_unit = $unit;

							break;

						case "_order_interval_frequency":

							$sub_item->properties->_order_interval_frequency = (string)$attribute->value;

							/* --- Break if null --- */
							if($attribute->value === "null") break;

							$sub_item->charge_interval_frequency = (int)$attribute->value;
							$sub_item->order_interval_frequency = (int)$attribute->value;

							break;

						default:

							break;
					}
				}
				
				/* --- Add our line item --- */
				array_push($query->checkout->line_items, $sub_item);
			}
		}

		/* --- Backup will be to setup the query based on a singular product --- */
		if(empty($query->checkout->line_items) && !empty($object->id)){

			$variantId = explode("/ProductVariant/", base64_decode($object->id));

			$query->checkout->line_items[0]->quantity = (int)$object->quantity;
			$query->checkout->line_items[0]->variant_id = (int)$variantId[1];
			$query->checkout->line_items[0]->properties = new stdClass();

			/* --- Properties --- */
			$query->checkout->line_items[0]->properties->order_interval_unit = $object->properties->_order_interval_unit;
			$query->checkout->line_items[0]->properties->order_interval_frequency = (string)$object->properties->_order_interval_frequency;

			if($object->properties->order_interval_unit !== "null"){
				
				$query->checkout->line_items[0]->order_interval_unit = rtrim(strtolower($object->properties->_order_interval_unit), "s");
			}

			if($object->properties->order_interval_frequency !== "null"){

				$query->checkout->line_items[0]->charge_interval_frequency = (int)$object->properties->_order_interval_frequency;
				$query->checkout->line_items[0]->order_interval_frequency = (int)$object->properties->_order_interval_frequency;
			}
		}

		/* --- Query --- */
		$data = rechargeRequest($endpoint, $post, $custom, json_encode($query));

		if(!empty($data->checkout)){

			/* --- Write our carts to cache --- */
			$filename = FILE_CACHE . "carts/" . $data->checkout->token;
			
			file_put_contents($filename, json_encode($data->checkout));
			chmod($filename, 0664);

			/* --- Set our cookies cookie --- */
			setcookie("subcart", $data->checkout->token, time() + (86400 * 30), "/");
		}

		return json_encode($data);
	}

	/**
	 * Update Subscription
	 */
	public function subscriptionUpdate($object){

		/* --- Simply piggyback the Create call --- */
		$data = self::subscriptionCreate($object, 1, "PUT");

		return $data;
	}

	/**
	 * Discount Subscription
	 */
	public function subscriptionDiscount($object){

	}
}
?>