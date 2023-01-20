<?php
/**
 * Home Route
 */
$GLOBAL->app->get("/", function($request, $response, $args) use ($app){

	global $BUILDINFO;
	global $CONTENT;

	/* --- Get Content --- */
	$doc = $CONTENT->local->getContent("home", "home");
	
	/* --- Render Page --- */
	render($app, "home", array("document" => $doc, "namespace" => "home"));

	return $response;
});
?>