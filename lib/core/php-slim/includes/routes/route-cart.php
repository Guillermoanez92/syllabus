<?php
/**
 * Cart
 */
$GLOBAL->app->get("/cart/", function($request, $response, $args) use ($app){

	/* --- Get Content --- */
	$doc = null;
	$crt = [];
	$chk = null;

	/* --- Get Cart Cookie --- */
	if(isset($_COOKIE["cart"])){

		try{
			$json = normalizeCart(json_decode(file_get_contents(FILE_CACHE . "carts/" . $_COOKIE["cart"])));

			$chk = $json->webUrl;
			$crt = $json->lineItems;

		} catch(\Exception $e) {
			$crt = "Cart Error";
		}
	}

	/* --- Render Page --- */
	render($app, "cart", array("document" => $doc, "checkout" => $chk, "cart" => $crt, "namespace" => "cart"));

	return $response;
});
?>