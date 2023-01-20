<?php
/**
 * Account Router
 */
$GLOBAL->app->map(["GET", "POST"], "/account/[{slug}/[{key}/]]", function($request, $response, $args) use ($app){

	global $STOREFRONT;
	global $CONTENT;

	$slug = isset($args["slug"]) ? $args["slug"] : null;
	$key = isset($args["key"]) ? $args["key"] : null;
	$doc = $CONTENT->local->getContent("account");

	switch($slug){ // FLAG -- Check templates, merge down

		case "login":
		case "reset":
		case "recover":
		case "register":
		case "activate":

			/* --- Check if user is logged in - move them to /account/ --- */
			if(isset($STOREFRONT->customer->data) && $STOREFRONT->customer->data != null) {

				/* --- Handle Login from Checkout --- */
				if(isset($_GET) && isset($_GET["checkout_url"])) {

					/* --- Blow out customer cookie if it exists, basically Shopify wasn't connected --- */
					$STOREFRONT->functions->Logout();

					render($app, "accounts/" . $slug, array("document" => $doc, "checkoutUrl" => $_GET["checkout_url"], "customerError" => $error, "namespace" => "account"));

				} else {

					/* --- They don't need to login again --- */
					return $response->withHeader("Location", "/account/");
				}

			} else {

				$error = "";

				/* --- Check for errors --- */
				if(isset($STOREFRONT->customer->error)) {
					$error = $STOREFRONT->customer->error;
				}

				/* --- Render --- */
				render($app, "accounts/" . $slug, array("document" => $doc, "customerError" => $error, "namespace" => "account"));
			}

			break;

		case "addresses":
		case "orders":

			$orders = "";
			$error  = "";

			/* --- Check for orders --- */
			if (isset($key) && $key !== "" && $slug === "orders") {
				$orders = returnOrderObject($STOREFRONT->customer->data->orders->edges, $args["key"]);
			} else {
				$orders = $STOREFRONT->customer->data->orders->edges;
			}

			/* --- Check for errors --- */
			if (isset($STOREFRONT->customer->error)) {
				$error = $STOREFRONT->customer->error;
			}

			/* --- Render --- */
			render($app, "accounts/" . $slug, array("document" => $doc, "customer" => $STOREFRONT->customer->data, "orders" => $orders, "customerError" => $error, "namespace" => "account"));

			break;

		default:

			/* --- Check if user is logged in --- */
			if (isset($STOREFRONT->customer->data) && $STOREFRONT->customer->data != null) {

				$orders = "";
				$error  = "";

				/* --- Check for orders --- */
				if(isset($STOREFRONT->customer->data->orders->edges)){
					$orders = $STOREFRONT->customer->data->orders->edges;
				}

				/* --- Check for errors --- */
				if(isset($STOREFRONT->customer->error)){
					$error = $STOREFRONT->customer->error;
				}

				/* --- Render --- */
				render($app, "accounts/account", array("document" => $doc, "customer" => $STOREFRONT->customer->data, "orders" => $orders, "customerError" => $error, "namespace" => "account"));

			} else {

				return $response->withHeader("Location", "/account/login/");
			}

			break;
	}

	return $response;
});
?>