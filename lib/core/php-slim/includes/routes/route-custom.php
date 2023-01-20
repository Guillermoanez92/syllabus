<?php
/**
 * Cart
 */
$GLOBAL->app->get("/test/", function($request, $response, $args) use ($app){

	$doc = null;

	/* --- Render Page --- */
	render($app, "single--articles", array("document" => $doc, "namespace" => "test"));

	return $response;
});
?>