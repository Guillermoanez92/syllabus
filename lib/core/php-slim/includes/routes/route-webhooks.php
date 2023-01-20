<?php
/**
 * Content related Endpoints
 */
if(isset($BUILDINFO->lib->content_management)){

	/* --- Webhook Associate Array Reference -- "url" => "function" --- */
	$webhookContentArray = array(
		"/v1/webhook/content/update/"	=> "contentUpdate",
		"/v1/webhook/content/delete/"	=> "contentDelete"
	);

	/* --- Loop through our webhooks --- */
	foreach($webhookContentArray as $url => $webhook){

		$GLOBAL->app->post($url, function($request, $response) use ($CONTENT, $webhook){

			$return = call_user_func_array(array($CONTENT->webhooks, $webhook), array(json_decode($request->getBody())));

			return $response->withStatus(200);
		});
	}
}

/**
 * Storefront related Endpoints
 */
if(!empty($BUILDINFO->lib->ecommerce)){

	/* --- Webhook Associate Array Reference -- "url" => "function" --- */
	$webhookStorefrontArray = array(
		"/v1/webhook/product/create/"		=> "productCreate",
		"/v1/webhook/product/update/"		=> "productUpdate",
		"/v1/webhook/product/delete/"		=> "productDelete",
		"/v1/webhook/collection/create/"	=> "collectionCreate",
		"/v1/webhook/collection/update/"	=> "collectionUpdate",
		"/v1/webhook/collection/delete/"	=> "collectionDelete"
	);

	/* --- Loop through our webhooks --- */
	foreach($webhookStorefrontArray as $url => $webhook){

		$GLOBAL->app->post($url, function($request, $response) use ($STOREFRONT, $webhook){

			$hmac = (!empty($request->getHeader("HTTP_X_SHOPIFY_HMAC_SHA256"))) ? $request->getHeader("HTTP_X_SHOPIFY_HMAC_SHA256") : "";
			$return = call_user_func_array(array($STOREFRONT->webhooks, $webhook), array($request->getBody(), $hmac));

			return $response->withStatus(200);
		});
	}
}
?>