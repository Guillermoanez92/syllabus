<?php

/**
 * Storefront API definition
 */
class StorefrontAPI implements StorefrontEndpoints {

	/**
	 * Address Create
	 */
	public function addressCreate($object){

		/* --- Enough similarities to piggyback addressUpdate --- */
		$object->type = "address-create";

		return self::addressUpdate($object);
	}

	/**
	 * Address Delete
	 */
	public function addressDelete($object){

		global $STOREFRONT;

		/* --- Setup our return --- */
		$return = new stdClass();
		$return->error = "";

		/* --- Return if missing customer token --- */
		if(empty($object->token)){

			$return->error = "Customer token is required";

			return json_encode($return);
		}

		/* --- Return if missing address id --- */
		if(empty($object->address->id)){

			$return->error = "Address ID is required";

			return json_encode($return);
		}

		$token = $object->token;
		$id = $object->address->id;

		/* --- Setup our query --- */
		$query = "mutation{ customerAddressDelete(customerAccessToken: \"$token\", id: \"$id\"){ deletedCustomerAddressId customerUserErrors{ field message } } }";

		/* --- Make our request --- */
		$request = shopifyRequest($query);

		/* --- Alright let's run some error messaging --- */
		if(!empty($request->data->customerAddressDelete->customerUserErrors)){

			/* --- This'll be Shopify errors --- */
			$return->error = $request->data->customerAddressDelete->customerUserErrors;

			return json_encode($return);
		}

		/* --- Hey! Success, let's update the customer cookie / data --- */
		try {

			$STOREFRONT->functions->getCustomerData(FILE_CACHE . "customers/", $object->token, true);

		} catch(\Exception $e){

			$return->error = $e->getMessage();
		}

		/* --- Return our request --- */
		$return->context = "address-delete";
		$return->request = $request;

		return json_encode($return);
	}

	/**
	 * Address Update
	 */
	public function addressUpdate($object){

		global $STOREFRONT;

		/* --- Setup our return --- */
		$return = new stdClass();
		$return->error = "";

		/* --- Only really used to sanitize a check later --- */
		if(empty($object->type))
			$object->type = "address-update";

		/* --- Return if missing customer token --- */
		if(empty($object->token)){

			$return->error = "Customer token is required";

			return json_encode($return);
		}

		/* --- Setup our address object, we use "_" to normalize on the GraphQL end --- */
		$address = new stdClass();

		$address->_address1_ = (!empty($object->address->address1)) ? $object->address->address1 : "";
		$address->_address2_ = (!empty($object->address->address2)) ? $object->address->address2 : "";
		$address->_city_ = (!empty($object->address->city)) ? $object->address->city : "";
		$address->_company_ = (!empty($object->address->company)) ? $object->address->company : "";
		$address->_country_ = (!empty($object->address->country)) ? $object->address->country : "";
		$address->_firstName_ = (!empty($object->address->firstName)) ? $object->address->firstName : "";
		$address->_lastName_ = (!empty($object->address->lastName)) ? $object->address->lastName : "";
		$address->_phone_ = (!empty($object->address->phone)) ? $object->address->phone : "";
		$address->_province_ = (!empty($object->address->province)) ? $object->address->province : "";
		$address->_zip_ = (!empty($object->address->zip)) ? $object->address->zip : "";

		/* --- Stringify the object --- */
		$address = shopifyQueryFix(json_encode($address));

		$token = $object->token;

		/* --- Create --- */
		$type = "customerAddressCreate";
		$query = "mutation{ $type(customerAccessToken: \"$token\", address: $address){ customerAddress{ id } customerUserErrors{ field message } } }";

		/* --- Update --- */
		if($object->type === "address-update"){

			$addressId = $input->address->id;
			$type = "customerAddressUpdate";
			$query = "mutation{ $type(customerAccessToken: \"$token\", id: \"$addressId\", address: $address){ customerAddress{ id } customerUserErrors{ field message } } }";
		}

		/* --- Make our request --- */
		$request = shopifyRequest($query);

		/* --- Alright let's run some error messaging --- */
		if(!empty($request->data->{$type}->customerUserErrors)){

			/* --- This'll be Shopify errors --- */
			$return->error = $request->data->{$type}->customerUserErrors;

			return json_encode($return);
		}

		/* --- Update default address --- */
		if(!empty($object->address->default) && $object->address->default == "on"){

			$addressId = $request->data->{$call}->customerAddress->id;
			$query = "mutation{ customerDefaultAddressUpdate(customerAccessToken: \"$token\", addressId: \"$addressId\"){ customer{ id } customerUserErrors{ field message } } }";

			$setDefault = shopifyRequest($query);

			if(!empty($setDefault->data->customerDefaultAddressUpdate->customerUserErrors)){

				$return->error = $setDefault->data->customerDefaultAddressUpdate->customerUserErrors;
			}
		}

		/* --- Update customer cookie / data if edit --- */
		if(empty($return->error)){

			try {

				$STOREFRONT->functions->getCustomerData(FILE_CACHE . "customers/", $object->token, true);

			} catch(\Exception $e){

				$return->error = $e->getMessage();
			}
		}

		/* --- Return our request --- */
		$return->context = ($object->type === "address-update") ? "address-update" : "address-create";
		$return->request = $request;

		return json_encode($return);
	}

	/**
	 * Cart Create
	 */
	public function cartCreate($object){
		
		/* --- Enough similarities to piggyback cartUpdate --- */
		$object->type = "cart-create";

		return self::cartUpdate($object);
	}

	/**
	 * Cart Update
	 */
	public function cartUpdate($object){

		/* --- Setup our return --- */
		$return = new stdClass();
		$return->error = "";

		/* --- GraphQL query fix --- */
		$cart = str_replace('"value"', "value", str_replace('"key"', "key", str_replace('"customAttributes"', "customAttributes", str_replace('"quantity"', "quantity", str_replace('"variantId"', "variantId", json_encode($object->cart))))));

		/* --- Set our keys --- */
		$type = "checkoutCreate(input: { lineItems: $cart })";
		$key = "checkoutCreate";
		$err = "checkoutUserErrors";

		/* --- Update existing cart --- */
		if(!empty($object->checkout)){
			$checkoutId = $object->checkout->id;
			$type = "checkoutLineItemsReplace( lineItems: $cart, checkoutId: \"$checkoutId\" )";
			$key = "checkoutLineItemsReplace";
			$err = "userErrors";
		}

		/* --- Setup our query --- */
		$query = "mutation{ $type{ checkout{ id webUrl lineItems(first: 50){ edges{ node{ id customAttributes{ key value } title quantity variant{ id priceV2{ amount currencyCode } compareAtPriceV2{ amount currencyCode } sku title image(maxWidth: 300){ altText src } product{ id handle productType vendor } } } } } } $err{ field message } } }";

		/* --- Make our request --- */
		$request = shopifyRequest($query);

		/* --- Alright let's run some error messaging, don't log the customer out in this case --- */
		if(!empty($request->errors)){

			/* --- This'll be GraphQL errors --- */
			$return->error = $request->errors;

			return json_encode($return);
		}

		/* --- Alright let's run some error messaging --- */
		if(!empty($request->data->{$type}->{$key})){

			/* --- This'll be Shopify errors --- */
			$return->error = $request->data->{$type}->{$key};

			return json_encode($return);
		}

		/* --- Link Checkout upon create --- */
		if(empty($object->checkout) && !empty($_COOKIE["cat"])){

			$customerToken = $_COOKIE["cat"];
			$checkoutId = $request->checkout->id;

			$bindCheckout = "mutation{ checkoutCustomerAssociateV2(customerAccessToken: \"$customerToken\", checkoutId: \"$checkoutId\"){ checkout{ id webUrl lineItems(first: 50){ edges{ node{ id title quantity variant{ id price title image(maxWidth: 300){ altText src } product{ id images(first: 1, maxWidth: 300){ edges{ node{ altText src } } } vendor } } } } } } } }";

			$bindCheckoutRequet = shopifyRequest($bindCheckout);
		}

		/* --- Write our carts to cache --- */
		if(isset($request->data->{$key}->checkout) && isset($request->data->{$key}->checkout->id)){

			$filename = FILE_CACHE . "carts/" . $request->data->{$key}->checkout->id;
			
			file_put_contents($filename, json_encode($request->data->{$key}->checkout));
			chmod($filename, 0664);

			/* --- Set our cookies cookie --- */
			setcookie("cart", $request->data->{$key}->checkout->id, time() + (86400 * 30), "/");
		}

		/* --- Return our request --- */
		$return->context = (!empty($object->checkout)) ? "cart-update" : "cart-create";
		$return->request = $request->data->{$key};

		return json_encode($return);
	}

	/**
	 * Customer Create
	 */
	public function customerCreate($object){

		/* --- Enough similarities to piggyback customerUpdate --- */
		$object->token = "customer-create";

		return self::customerUpdate($object);
	}

	/**
	 * Customer Update
	 */
	public function customerUpdate($object){

		global $STOREFRONT;

		/* --- Setup our return --- */
		$return = new stdClass();
		$return->error = "";

		/* --- Return if missing customer token --- */
		if(empty($object->token)){

			$return->error = "Customer token is required";

			return json_encode($return);
		}

		/* --- If the user updates their password, we need a flag to log them out --- */
		$return->logout = false;

		/* --- Setup our customer object, we use "_" to normalize on the GraphQL end --- */
		$customer = new stdClass();

		/* --- Email --- */
		if(!empty($object->customer->email))
			$customer->_email_ = $object->customer->email;

		/* --- First Name --- */
		if(!empty($object->customer->firstName))
			$customer->_firstName_ = $object->customer->firstName;

		/* --- Last Name --- */
		if(!empty($object->customer->lastName))
			$customer->_lastName_ = $object->customer->lastName;

		/* --- Phone --- */
		if(!empty($object->customer->phone)){

			try {

				/* --- Check for valid US Phone --- */
				$USPhoneNum = $phoneUtil->parse($object->customer->phone, "US");

				if($phoneUtil->isValidNumber($USPhoneNum))
					$customer->_phone_ = $phoneUtil->format($USPhoneNum, \libphonenumber\PhoneNumberFormat::E164);

			} catch(\Exception $e){

				/* --- Return the invalid phone number --- */
				$return->error = "Phone number " . $object->customer->phone . " is invalid";

				return json_encode($return);
			}
		}

		/* --- Password --- */
		if(!empty($object->customer->password) && !empty($object->customer->confirmPassword)){

			/* --- Return if passwords do not match --- */
			if($object->customer->password !== $object->customer->confirmPassword){

				$return->error = "Passwords do not match";

				return json_encode($return);
			}

			$customer->_password_ = $object->customer->password;

			/* --- Set logout flag to true because password was changed --- */
			$return->logout = true;
		}

		/* --- Accepts Marketing --- */
		$customer->_acceptsMarketing_ = (!empty($object->customer->accepts_marketing) && $object->customer->accepts_marketing == "on") ? true : false;

		/* --- Stringify the object --- */
		$customer = shopifyQueryFix(json_encode($customer));

		/* --- Setup our Queries --- */
		$type = "customerUpdate";
		$query = "mutation{ $type(customerAccessToken: \"$object->token\", customer: $customer){ customer{ id } customerAccessToken{ accessToken expiresAt } customerUserErrors{ field message } } }";

		/* --- We'll actually handle create here too --- */
		if($object->token === "customer-create"){
			$type = "customerCreate";
			$query = "mutation{ $type(input: $customer){ customer{ id } customerUserErrors{ field message } } }";
			$return->logout = false;
		}

		/* --- Make our request --- */
		$request = shopifyRequest($query);

		/* --- Alright let's run some error messaging, don't log the customer out in this case --- */
		if(!empty($request->errors)){

			/* --- This'll be GraphQL errors --- */
			$return->error = $request->errors;
			$return->logout = false;

			return json_encode($return);
		}

		if(!empty($request->data->{$type}->customerUserErrors)){

			/* --- This'll be Shopify errors --- */
			$return->error = $request->data->{$type}->customerUserErrors;
			$return->logout = false;

			return json_encode($return);
		}

		/* --- Let's link the checkout if it exists --- */
		if($object->token === "customer-create" && !empty($object->chk)){

			$customerId = $request->data->customerCreate->customer->id;
			$checkoutToken = $object->chk;

			$bindChk = "mutation{ checkoutCustomerAssociateV2(customerAccessToken: \"$customerId\", checkoutId: \"$checkoutToken\"){ checkout{ id webUrl lineItems(first: 50){ edges{ node{ id title quantity variant{ id price title image(maxWidth: 300){ altText src } product{ id images(first: 1, maxWidth: 300){ edges{ node{ altText src } } } vendor } } } } } } } }";

			$return->checkout = shopifyRequest($bindChk);
		}

		/* --- Update customer cookie / data if edit --- */
		if($return->logout === false && $object->token !== "customer-create"){

			try {

				$STOREFRONT->functions->getCustomerData(FILE_CACHE . "customers/", $object->token, true);

			} catch(\Exception $e){

				$return->error = $e->getMessage();
			}
		}

		/* --- Return our request --- */
		$return->context = ($object->token === "customer-create") ? "customer-create" : "customer-update";
		$return->request = $request;

		return json_encode($return);
	}

	/**
	 * Password Recover
	 */
	public function passwordRecover($object){

		/* --- Setup our return --- */
		$return = new stdClass();
		$return->error = "";

		/* --- Return if missing customer email --- */
		if(empty($object->customer->email)){

			$return->error = "Email is required";

			return json_encode($return);
		}

		try {
			
			/* --- Setup our query --- */
			$email = $object->customer->email;
			$query = "mutation{ customerRecover(email: \"$email\"){ customerUserErrors{ field message } } }";

			/* --- Make our request --- */
			$request = shopifyRequest($query);

			/* --- Alright let's run some error messaging, don't log the customer out in this case --- */
			if(!empty($request->errors)){

				/* --- This'll be GraphQL errors --- */
				$return->error = $request->errors;

				return json_encode($return);
			}

			if(!empty($request->data->customerRecover->customerUserErrors)){

				/* --- This'll be Shopify errors --- */
				$return->error = $request->data->customerRecover->customerUserErrors;

				return json_encode($return);
			}

			/* --- Return our request --- */
			$return->context = "password-recover";
			$return->request = $request;

		} catch(Exception $e){

			/* --- Return the error --- */
			$return->error = $e->getMessage();
		}

		return json_encode($return);
	}

	/**
	 * Password Reset
	 */
	public function passwordReset($object){

		/* --- Setup our return --- */
		$return = new stdClass();
		$return->error = "";

		/* --- Return if missing customer token --- */
		if(empty($object->token)){

			$return->error = "Customer token is required";

			return json_encode($return);
		}

		/* --- Return if missing customer id --- */
		if(empty($object->id)){

			$return->error = "Customer ID is required";

			return json_encode($return);
		}

		/* --- Form query url --- */
		$url = "https://" . STOREFRONT_DOMAIN . "/account/reset/" . $object->id . "/" . $object->token;

		/* --- Setup our query --- */
		$password = $object->customer->password;
		$query = "mutation{ customerResetByUrl(resetUrl: \"$url\", password: \"$password\"){ customer{ id } customerAccessToken{ accessToken expiresAt } customerUserErrors{ field message } } }";

		try {
			/* --- Make our request --- */
			$request = shopifyRequest($query);

			/* --- Alright let's run some error messaging, don't log the customer out in this case --- */
			if(!empty($request->errors)){

				/* --- This'll be GraphQL errors --- */
				$return->error = $request->errors;

				return json_encode($return);
			}

			if(!empty($request->data->customerResetByUrl->customerUserErrors)){

				/* --- This'll be Shopify errors --- */
				$return->error = $request->data->customerResetByUrl->customerUserErrors;

				return json_encode($return);
			}

			/* --- Return our request --- */
			$return->context = "password-reset";
			$return->request = $request;

		} catch(Exception $e){

			/* --- Return the error --- */
			$return->error = $e->getMessage();
		}

		return json_encode($return);
	}

	/**
	 * Load Product By Slug
	 *	- Note: For some reason the fallback caching solution will
	 *	  pull in an additional field; "admin_graphql_api_id".
	 *	  This is minor and isn't used anywhere as we have the 
	 *	  product ID's already stored and can build that url.
	 */
	public function getProduct($handle){

		global $STOREFRONT;

		if(empty($handle) || $handle === "") return null;

		/* --- Check against input --- */
		$slug = (!empty($handle->handle)) ? $handle->handle : $handle;

		/* --- Make Sure Products Cache Exists --- */
		$cache = FILE_CACHE . "storefront/products/";

		if(!file_exists($cache)){
			mkdir($cache, 775);
			chmod($cache, 0775);
		}

		$filename = base64_encode($slug);

		/* --- Check for cached file --- */
		if(!file_exists($cache . $filename)){

			$data = null;
			$products = null;

			if(file_exists(FILE_CACHE . "storefront/products.json")){
				$products = json_decode(file_get_contents(FILE_CACHE . "storefront/products.json"));
			}

			$pid = null;

			/* --- Look up product ID num --- */
			if($products){

				foreach($products as $key => $product){

					if($product == $slug){
						$pid = (int)$key;
					}
				}
			}

			/* --- Get product data --- */
			if($pid){
				$data = arrayToObject($STOREFRONT->client->Product($pid)->get());
			} else {
				$params = array(
					"handle" => $slug,
					"limit" => 250,
					"published_status" => "published"
				);
				$data = $STOREFRONT->client->Product->get($params);

				// MIGHT NEED TO CONVERT TO LOOP FOR EXACT MATCH
				if($data && isset($data[0])){
					$data = arrayToObject($data[0]);
					$pid = $data->id;
				}
			}

			/* --- Cool, passed the first test, now let's check our sales channel --- */
			$sales_channel = $STOREFRONT->client->ProductListing()->productIds();

			/* --- Bounce this product --- */
			if(!in_array($pid, $sales_channel["product_ids"])){
				return;
			}

			/* --- Make sure data was returned --- */
			if($data){

				if(!isset($data->published_at) || is_null($data->published_at) || !isset($data->status) || $data->status !== "active"){
					return;
				}

				$metafields = $STOREFRONT->client->Product($pid)->Metafield()->get(array("limit" => "250"));
				$data->metafields = $STOREFRONT->functions->reKeyMetafields($metafields);

				/* --- Get Variant Metafields & add to JSON --- */
				foreach($data->variants as $key => $variant){
					$vid = $variant->id;
					$metafields = $STOREFRONT->client->Product($pid)->Variant($vid)->Metafield()->get(array("limit" => "250"));
					$data->variants[$key]->metafields = $STOREFRONT->functions->reKeyMetafields($metafields);
				}
			}

			$document = $data;

			if(defined("ENABLE_CACHE") && ENABLE_CACHE === true && $document && isset($document->id)){

				/* --- Write cache file --- */
				file_put_contents($cache . $filename, json_encode($document));
				chmod($cache . $filename, 0664);
			}

		} else {
			$document = json_decode(file_get_contents($cache . $filename));
		}

		return $document;
	}

	/** 
	 * Load Shopify Collection By Slug
	 *	- Note: For some reason the fallback caching solution will
	 *	  pull in an additional field; "admin_graphql_api_id".
	 *	  This is minor and isn't used anywhere as we have the 
	 *	  collection ID's already stored and can build that url.
	 */
	public function getCollection($slug){

		global $STOREFRONT;

		/* --- Make Sure collections Cache Exists --- */
		$cache = FILE_CACHE . "storefront/collections/";

		if(!file_exists($cache)){
			mkdir($cache, 775);
			chmod($cache, 0775);
		}

		$filename = base64_encode($slug);

		/* --- Check for cached file --- */
		if(!file_exists($cache . $filename)){

			$data = null;
			$collections = null;

			if(file_exists(FILE_CACHE . "storefront/collections.json")){
				$collections = json_decode(file_get_contents(FILE_CACHE . "storefront/collections.json"));
			}

			$pid = null;

			/* --- Look up collection ID num --- */
			if($collections){

				foreach($collections as $key => $collection){

					if($collection == $slug){
						$pid = (int)$key;
					}
				}
			}

			/* --- Get collection data --- */
			if($pid){
				$data = arrayToObject($STOREFRONT->client->Collection($pid)->get());
			} else {
				$params = array(
					"handle" => $slug,
					"limit" => 250,
					"published_status" => "published"
				);

				try {
					$data = $STOREFRONT->client->SmartCollection->get($params);
				} catch(Exception $e){

					try {
						$data = $STOREFRONT->client->CustomCollection->get($params);
					} catch(Exception $e){
						$data = null;
					}
				}

				// MIGHT NEED TO CONVERT TO LOOP FOR EXACT MATCH
				if($data && isset($data[0])){
					$data = arrayToObject($data[0]);
					$pid = $data->id;
				}
			}

			/* --- Make sure data was returned --- */
			if($data){

				if(isset($data->rules)){
					$metafields = $STOREFRONT->client->SmartCollection($pid)->Metafield()->get(array("limit" => "250"));
				} else {
					$metafields = $STOREFRONT->client->CustomCollection($pid)->Metafield()->get(array("limit" => "250"));
				}

				$data->metafields = $STOREFRONT->functions->reKeyMetafields($metafields);
			}

			$document = $data;

			if(defined("ENABLE_CACHE") && ENABLE_CACHE === true && $document && isset($document->id)){
				
				/* --- Write cache file --- */
				file_put_contents($cache . $filename, json_encode($document));
				chmod($cache . $filename, 0664);
			}

		} else {
			$document = json_decode(file_get_contents($cache . $filename));
		}

		return $document;
	}

	/** 
	 * Get Yotpo Reviews
	 */
	public function getReviews($object){

		/* --- Returned object --- */
		$reviews = new stdClass();

		/* --- Stored for later sort --- */
		$reviewsArray = array();

		/* --- Store ID --- */
		$productId = $object->product_id;
		$productRef = json_decode(file_get_contents(FILE_CACHE . "storefront/products.json"));
		$productInfo = self::getProduct($productRef->{$productId}->handle);
		$page = (!empty($object->page)) ? (int)$object->page : 1;

		if(empty($productInfo)) return "{}";

		/* --- Cache --- */
		$folder = FILE_CACHE . "reviews/pid_" . $productId . "/";

		if(!file_exists($folder)){
			mkdir($folder, 775);
			chmod($folder, 0775);
		}

		/* --- Get Expire timer for this product --- */
		$expires = 0;
		$hours = 4;

		/* --- Check if we have reviews --- */
		if(file_exists($folder . "baseline.json")){

			/* --- Store expires so we can check for new reviews later --- */
			$expires = filemtime($folder . "baseline.json");
		}

		/* --- Expires timer is up, let's check for new stuff --- */
		if(time() - $expires > $hours * 3600){

			/* --- Remove ID --- */
			unset($object->product_id);

			$continue = false;
			$results = yotpoRequest("https://api.yotpo.com/v1/widget/" . YOTPO_API_KEY . "/products/" . $productId . "/reviews.json", 0, $object);

			if(!empty($results->response->reviews)){

				$page = (int)$results->response->pagination->page;
				$per_page = (int)$results->response->pagination->per_page;
				$total = (int)$results->response->pagination->total;

				$reviews->baseline = (!empty($results->response->bottomline)) ? $results->response->bottomline : new stdClass();
				$reviewsArray = array_merge($reviewsArray, $results->response->reviews);

				while(($page * $per_page) < $total){

					++$page;

					$object->page = $page;

					$additional = yotpoRequest("https://api.yotpo.com/v1/widget/" . YOTPO_API_KEY . "/products/" . $productId . "/reviews.json", 0, $object);

					if(!empty($additional->response->reviews)){
						$reviewsArray = array_merge($reviewsArray, $additional->response->reviews);
					}
				}
			}

			if(!empty($reviews->baseline)){
				/* --- Quick, let's cache refresh --- */
				file_put_contents($folder . "baseline.json", json_encode($reviews->baseline), LOCK_EX);
				chmod($folder . "baseline.json", 0664);
			}

			/* --- And the rest --- */
			foreach($reviewsArray as $rev){

				/* --- Add in our product information --- */
				$rev->product_id = $productId;
				$rev->product_image = shopifyReturnImage($productInfo->image->src, "300x");
				$rev->product_name = $productInfo->title;
				$rev->product_url = "/products/" . slugify($productInfo->product_type) . "/" . $productInfo->handle . "/";
				$rev->product_color = (!empty($productInfo->metafields->custom_fields->accent_color)) ? "#" . $productInfo->metafields->custom_fields->accent_color : "#000000";

				file_put_contents($folder . "rev_" . $rev->id . ".json", json_encode($rev), LOCK_EX);
				chmod($folder . "rev_" . $rev->id . ".json", 0664);
			}

		/* --- Pull from cache --- */
		} else {

			/* --- Let's get our products review --- */
			$dir = new FilesystemIterator($folder);

			foreach($dir as $fileinfo){

				/* --- Store baseline in own object --- */
				if($fileinfo->getFilename() === "baseline.json"){

					$reviews->baseline = json_decode(file_get_contents($fileinfo->getPath() . "/" . $fileinfo->getFilename()));

					continue;
				}

				/* --- Skip any junk files --- */
				if($fileinfo->getExtension() !== "json") continue;

				$review = json_decode(file_get_contents($fileinfo->getPath() . "/" . $fileinfo->getFilename()));

				array_push($reviewsArray, $review);
			}
		}

		/* --- We can sort here --- */
		$reviews->reviews = $reviewsArray;

		return json_encode($reviews);
	}

	/** 
	 * Post Yotpo Reviews
	 */
	public function postReview($object){

		// TBD
	}

	/** 
	 * Get Yotpo Reviews
	 */
	public function getFeaturedReview($object){

		/* --- Cache --- */
		$dirs = scandir(FILE_CACHE . "reviews/");
		$json = null;

		/* --- Let's scan for file --- */
		foreach($dirs as $dir){

			/* --- Skip --- */
			if($dir === "." || $dir === "..") continue;
			if(file_exists(FILE_CACHE . "reviews/" . $dir . "/rev_" . $object->review_id . ".json")){

				$json = file_get_contents(FILE_CACHE . "reviews/" . $dir . "/rev_" . $object->review_id . ".json");

				break;
			}
		}

		/* --- Return encoded --- */
		if(!is_null($json)) return $json;

		/* --- If not cached, let's hit the API --- */
		$results = yotpoRequest("https://api.yotpo.com/reviews/" . $object->review_id, 0);

		/* --- Add in our product information --- */
		if(!empty($results->response) && !empty($results->response->review)){

			$product_yotpo = $results->response->review->products[0]->Product;
			$product_handle_array = explode("/products/", $product_yotpo->product_url);

			$product = self::getProduct(trim($product_handle_array[1]));
			
			/* --- Bounce --- */
			if(empty($product)) return null;

			$folder = FILE_CACHE . "reviews/pid_" . $product->id . "/";

			if(!file_exists($folder)){
				mkdir($folder, 775);
				chmod($folder, 0775);
			}

			/* --- Backup get product stuff --- */
			$results->response->review->product_id = $product->id;
			$results->response->review->product_image = shopifyReturnImage($product->image->src, "300x");
			$results->response->review->product_name = $product->title;
			$results->response->review->product_url = "/products/" . slugify($product->product_type) . "/" . $product->handle . "/";
			$results->response->review->product_color = (!empty($product->metafields->custom_fields->accent_color)) ? "#" . $product->metafields->custom_fields->accent_color : "#000000";

			file_put_contents($folder . "rev_" . $object->review_id . ".json", json_encode($results->response->review), LOCK_EX);
			chmod($folder . "rev_" . $object->review_id . ".json", 0664);

			return json_encode($results->response->review);
		}

		/* --- So something happens --- */
		return null;
	}

	/** 
	 * Upvote Review
	 */
	public function upvoteReview($object){

		$file_path = FILE_CACHE . "reviews/pid_" . $object->product_id . "/rev_" . $object->review_id . ".json";

		/* --- Get our file --- */
		if(file_exists($file_path)) $file = json_decode(file_get_contents($file_path));

		/* --- Send our results --- */
		$results = yotpoRequest("https://api.yotpo.com/reviews/" . $object->review_id . "/vote/up", 1);

		if(!empty($file)){

			/* --- Increase votes --- */
			$file->votes_up = $file->votes_up + 1;

			/* --- And recache --- */
			file_put_contents($file_path, json_encode($file), LOCK_EX);
			chmod($file_path, 0664);
		}

		return json_encode($results);
	}
}
?>