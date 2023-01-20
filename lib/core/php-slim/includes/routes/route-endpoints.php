<?php
/**
 * Core Endpoint
 */
$GLOBAL->app->post("/v1/cache/get/", function($request, $response) use ($app){

	try {

		$data = json_decode($request->getBody());

		if(!file_exists(FILE_CACHE . $data->url)) throw new Exception("Cache URL is required.", 1);

		$return = file_get_contents(FILE_CACHE . $data->url);

	} catch(\Exception $e){

		$return = "{}";
	}

	$response->getBody()->write($return);

	return $response->withHeader("Content-Type", "application/json")->withStatus(200);
});

/**
 * Content related Endpoints
 */
if(isset($BUILDINFO->lib->content_management)){

	$endpointContentArray = array(
		"/v1/app/document/" => "getDocument",
		"/v1/app/documents/" => "getDocuments"
	);

	/* --- Loop through our endpoints --- */
	foreach($endpointContentArray as $url => $endpoint){

		$GLOBAL->app->post($url, function($request, $response) use ($CONTENT, $BUILDINFO, $endpoint){

			$return = call_user_func_array(array($CONTENT->api, $endpoint), array(json_decode($request->getBody())));

			$data = ($BUILDINFO->lib->framework === "react") ? json_encode($return) : $return;

			$response->getBody()->write($data);

			return $response->withHeader("Content-Type", "application/json")->withStatus(200);
		});
	}
}

/**
 * Storefront related Endpoints
 */
if(!empty($BUILDINFO->lib->ecommerce)){

	/* --- Endpoint Associate Array Reference -- "url" => "function" --- */
	$endpointStorefrontArray = array(
		"/v1/app/customer/create/"	=> "customerCreate",
		"/v1/app/customer/update/"	=> "customerUpdate",
		"/v1/app/address/create/"	=> "addressCreate",
		"/v1/app/address/update/"	=> "addressUpdate",
		"/v1/app/address/delete/"	=> "addressDelete",
		"/v1/app/password/recover/"	=> "passwordRecover",
		"/v1/app/password/reset/"	=> "passwordReset",
		"/v1/app/cart/add/"			=> "cartCreate",
		"/v1/app/cart/update/"		=> "cartUpdate",
		"/v1/app/product/"			=> "getProduct",
		"/v1/app/products/"			=> "getCollection",
		/* --- Reviews --- */
		"/v1/app/reviews/get/"		=> "getReviews",
		"/v1/app/reviews/featured/"	=> "getFeaturedReview",
		"/v1/app/reviews/post/"		=> "postReview",
		"/v1/app/reviews/upvote/"	=> "upvoteReview"
	);

	/* --- Loop through our endpoints --- */
	foreach($endpointStorefrontArray as $url => $endpoint){

		$GLOBAL->app->post($url, function($request, $response) use ($STOREFRONT, $BUILDINFO, $endpoint){

			$return = call_user_func_array(array($STOREFRONT->api, $endpoint), array(json_decode($request->getBody())));

			$data = ($BUILDINFO->lib->framework === "react") ? json_encode($return) : $return;

			$response->getBody()->write($data);

			return $response->withHeader("Content-Type", "application/json")->withStatus(200);
		});
	}
}

/**
 * Subscription related Endpoints
 */
if(!empty($BUILDINFO->lib->ecommerce->subscription)){

	/* --- Endpoint Associate Array Reference -- "url" => "function" --- */
	$endpointSubscriptionArray = array(
		"/v1/app/subscription/get/"			=> "subscriptionGet",
		"/v1/app/subscription/create/"		=> "subscriptionCreate",
		"/v1/app/subscription/update/"		=> "subscriptionUpdate",
		"/v1/app/subscription/discount/"	=> "subscriptionDiscount"
	);

	/* --- Loop through our endpoints --- */
	foreach($endpointSubscriptionArray as $url => $endpoint){

		$GLOBAL->app->post($url, function($request, $response) use ($STOREFRONT, $BUILDINFO, $endpoint){

			$return = call_user_func_array(array($STOREFRONT->subscription, $endpoint), array(json_decode($request->getBody())));

			$data = ($BUILDINFO->lib->framework === "react") ? json_encode($return) : $return;

			$response->getBody()->write($data);

			return $response->withHeader("Content-Type", "application/json")->withStatus(200);
		});
	}
}
?>