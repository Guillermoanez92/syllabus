<?php
/**
 * Storefront Indices definition
 */
class StorefrontIndex implements StorefrontIndices {

	public function indexProduct($data){

		global $STOREFRONT;

		if(!empty($data)){

			/**
			 * Make sure shopify products cache folders exist
			 */
			$cache = FILE_CACHE . "storefront/products/";

			if(!file_exists($cache)){
				mkdir($cache, 775);
				chmod($cache, 0775);
			}

			/* --- Decode Data --- */
			$json = $data;
			$id = $json->id;
			$handle = $json->handle;

			/* --- Do Not Cache unpublished products --- */
			if(isset($json->published_at) && $json->published_at === null){
				return;
			}

			/* --- Cool, passed the first test, now let's check our sales channel --- */
			$sales_channel = $STOREFRONT->client->ProductListing()->productIds();

			/* --- Bounce this product --- */
			if(!in_array($id, $sales_channel["product_ids"])){
				return;
			}

			if(isset($json->updated_at) && $json->updated_at !== ""){
				$updated = $json->updated_at;
			} else {
				$updated = date("Y-m-dTH:i:sP", time());
			}

			/* --- Update Reference Sheet --- */
			$check = $STOREFRONT->functions->updateProductReference($id, $handle, $updated, $json->vendor);

			/* --- Get Product Metafields & add to JSON --- */
			$metafields = $STOREFRONT->client->Product($id)->Metafield()->get(array("limit" => "250"));
			$json->metafields = $STOREFRONT->functions->reKeyMetafields($metafields);

			/* --- Get Variant Metafields & add to JSON --- */
			foreach($json->variants as $key => $variant){
				
				$vid = $variant->id;

				/* --- Normalize Price --- */
				$json->variants[$key]->price = (float)$json->variants[$key]->price;

				/* --- Re Key our Metafields --- */
				$metafields = $STOREFRONT->client->Product($id)->Variant($vid)->Metafield()->get(array("limit" => "250"));
				$json->variants[$key]->metafields = $STOREFRONT->functions->reKeyMetafields($metafields);
			}

			/* --- Event: Product Update --- */
			$filename = base64_encode($handle);

			try {
				file_put_contents($cache . $filename, json_encode($json), LOCK_EX);
				chmod($cache . $filename, 0664);
			} catch(Exception $e){
				error_log($e->getMessage() . PHP_EOL);
			}
		}
	}

	public function indexCollection($data){

		global $STOREFRONT;

		if(!empty($data)){

			/**
			 * Make sure shopify products cache folders exist
			 */
			$cache = FILE_CACHE . "storefront/collections/";

			if(!file_exists($cache)){
				mkdir($cache, 775);
				chmod($cache, 0775);
			}

			/* --- Decode Data --- */
			$json = $data;
			$id = $json->id;
			$handle = $json->handle;

			/* --- Event: Collection Update --- */
			$filename = base64_encode($handle);

			/* --- Do Not Cache unpublished products --- */
			if(isset($json->published_at) && $json->published_at === null){
				return;
			}

			if(isset($json->updated_at) && $json->updated_at !== ""){
				$updated = $json->updated_at;
			} else {
				$updated = date("Y-m-dTH:i:sP", time());
			}

			/* --- Update Reference Sheet --- */
			$check = $STOREFRONT->functions->updateCollectionReference($id, $handle, $updated);

			/* --- Get Collection Metafields & add to JSON --- */
			if(isset($json->rules)){
				$metafields = $STOREFRONT->client->SmartCollection($id)->Metafield()->get(array("limit" => "250"));
			} else {
				$metafields = $STOREFRONT->client->CustomCollection($id)->Metafield()->get(array("limit" => "250"));
			}

			/* --- Cool, passed the first test, now let's check our sales channel --- */
			$sales_channel = $STOREFRONT->client->ProductListing()->productIds();

			/* --- Store our metafields --- */
			$json->metafields = $STOREFRONT->functions->reKeyMetafields($metafields);

			/* --- Setup a products array --- */
			$products = array();

			/* --- Get our products in default collection order --- */
			$query = "{ collection(id: \"gid://shopify/Collection/$id\"){ id products(first: 250){ edges{ cursor node{ handle id } } pageInfo{ hasNextPage } } } }";
			$queryResults = shopifyAdminRequest($query);

			/* --- Normalize the array --- */
			foreach($queryResults->data->collection->products->edges as $key => $product){

				$obj = new stdClass();
				$obj->handle = $product->node->handle;
				$obj->id = (int)str_replace("gid://shopify/Product/", "", $product->node->id);

				/* --- Bounce this product --- */
				if(!in_array($obj->id, $sales_channel["product_ids"])){
					continue;
				}

				array_push($products, $obj);
			}

			/* --- Store pagination items --- */
			$hasNextPage = $queryResults->data->collection->products->pageInfo->hasNextPage;
			$cursor = $queryResults->data->collection->products->edges[count($queryResults->data->collection->products->edges) - 1]->cursor;

			/* --- Pagination loop --- */
			while($hasNextPage === true){

				$query = "{ collection(id: \"gid://shopify/Collection/$id\"){ id products(first: 250, after: \"$cursor\"){ edges{ cursor node{ handle id } } pageInfo{ hasNextPage } } } }";
				$queryResults = shopifyAdminRequest($query);

				/* --- Normalize the array --- */
				foreach($queryResults->data->collection->products->edges as $key => $product){

					$obj = new stdClass();
					$obj->handle = $product->node->handle;
					$obj->id = (int)str_replace("gid://shopify/Product/", "", $product->node->id);

					/* --- Bounce this product --- */
					if(!in_array($obj->id, $sales_channel["product_ids"])){
						continue;
					}

					array_push($products, $obj);
				}

				/* --- Store pagination items --- */
				$hasNextPage = $queryResults->data->collection->products->pageInfo->hasNextPage;
				$cursor = $queryResults->data->collection->products->edges[count($queryResults->data->collection->products->edges) - 1]->cursor;
			}

			/* --- Store products --- */
			$json->products = $products;

			/* --- Write our stuff --- */
			try{
				file_put_contents($cache . $filename, json_encode($json), LOCK_EX);
				chmod($cache . $filename, 0664);
			} catch(Exception $e){
				error_log($e->getMessage() . PHP_EOL);
			}
		}
	}
}
?>