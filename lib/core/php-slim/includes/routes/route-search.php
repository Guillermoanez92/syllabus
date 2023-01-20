<?php
/**
 * Search Route
 */
$GLOBAL->app->get("/search/", function($request, $response, $args) use ($app){

	global $CONTENT;

	/* --- Get Content --- */
	$doc = $CONTENT->local->getContent("search");

	/* --- Render Page --- */
	render($app, "search", array("document" => $doc, "namespace" => "search", "title" => "Search"));

	return $response;
});
?>