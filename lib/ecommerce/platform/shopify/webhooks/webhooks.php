<?php
/**
 * Storefront Webhooks definition
 */
class StorefrontWebhook implements StorefrontWebhooks {

	/* --- Product Create --- */
	public function productCreate($data, $header){

		/* --- Uses same index as Update --- */
		return self::productUpdate($data, $header);
	}

	/* --- Product Update --- */
	public function productUpdate($data, $header){

		try {

			/* --- Verify the webhook --- */
			if(verify_webhook($data, $header[0])){

				/* --- Let's correct our content entry so it doesn't screw up our exec --- */
				if(strpos($data, "'") > -1){
					$data = str_replace("'", "’", $data);
				}

				/* --- Ship our data to parallel request --- */
				execRequest(PROTOCOL . HOST . "/v1/index/product/", $data);

				return true;
			}

		} catch(\Exception $e){

			return false;
		}
	}

	/* --- Product Delete --- */
	public function productDelete($data, $header){

		global $DATABASE;
		global $STOREFRONT;

		/* --- Verify the webhook --- */
		if(verify_webhook($data, $header[0])){

			/* --- Remove Product From Reference Sheet --- */
			$handle = $STOREFRONT->functions->updateProductReference($data->id, null, null, null, true);

			/* --- Event: Product Delete --- */
			$filename = base64_encode($handle);

			try{
				/* --- Delete Cache File --- */
				unlink(FILE_CACHE . "storefront/products/" . $filename);
			} catch(Exception $e){
				error_log($e->getMessage() . PHP_EOL);
			}

			/* --- Delete the Document if ES is enabled --- */
			if(isset($DATABASE->client)){

				try{

					/* --- Set our ES params --- */
					$params = array(
						"index" => SITE_SEARCH_KEY . "_shop",
						"type" => "shop",
						"id" => $data->id
					);

					/* --- Delete Document --- */
					$DATABASE->app->deleteDocument($params);

				} catch(Exception $e){
					error_log($e->getMessage() . PHP_EOL);
				}
			}

			return true;
		}
	}

	/* --- Collection Create --- */
	public function collectionCreate($data, $header){

		/* --- Uses same index as Update --- */
		return self::collectionUpdate($data, $header);
	}

	/* --- Collection Update --- */
	public function collectionUpdate($data, $header){

		try {

			/* --- Verify the webhook --- */
			if(verify_webhook($data, $header[0])){

				/* --- Let's correct our content entry so it doesn't screw up our exec --- */
				if(strpos($data, "'") > -1){
					$data = str_replace("'", "’", $data);
				}

				/* --- Ship our data to parallel request --- */
				execRequest(PROTOCOL . HOST . "/v1/index/collection/", $data);

				return true;
			}

		} catch(\Exception $e){

			return false;
		}
	}

	/* --- Collection Delete --- */
	public function collectionDelete($data, $header){

		global $DATABASE;
		global $STOREFRONT;

		/* --- Verify the webhook --- */
		if(verify_webhook($data, $header[0])){

			/* --- Remove Product From Reference Sheet --- */
			$handle = $STOREFRONT->functions->updateCollectionReference($data->id, null, null, true);

			/* --- Event: Product Delete --- */
			$filename = base64_encode($handle);

			try{
				/* --- Delete Cache File --- */
				unlink(FILE_CACHE . "storefront/collections/" . $filename);
			} catch(Exception $e){
				error_log($e->getMessage() . PHP_EOL);
			}

			/* --- Delete the Document if ES is enabled --- */
			if(isset($DATABASE->client)){

				try{

					/* --- Set our ES params --- */
					$params = array(
						"index" => SITE_SEARCH_KEY . "_shop",
						"type" => "shop",
						"id" => $data->id
					);

					/* --- Delete Document --- */
					$DATABASE->app->deleteDocument($params);

				} catch(Exception $e){
					error_log($e->getMessage() . PHP_EOL);
				}
			}

			return true;
		}
	}
}
?>