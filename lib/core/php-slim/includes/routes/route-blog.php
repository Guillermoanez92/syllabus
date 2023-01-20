<?php
/**
 * Article Routing
 */
$GLOBAL->app->get("/articles/[{category}/[{post}/]]", function($request, $response, $args) use ($app){

	global $CONTENT;

	/* --- Check if post --- */
	if(isset($args["post"])){

		$doc = $CONTENT->local->getContent($args["post"], "articles");

		/* --- Check for 404 --- */
		if(!isset($doc) || $doc === null || $doc->data->category->slug !== $args["category"]) {
			not_found_include();
		} else {
			/* --- Render --- */
			render($app, "type--articles", array("document" => $doc, "namespace" => "article"));
		}

		return $response;
	}

	/* --- Check if category --- */
	if(isset($args["category"])){

		$doc = $CONTENT->local->getContent($args["category"], "categories");

		/* --- Check for 404 --- */
		if (!isset($doc) || $doc === null){
			not_found_include();
		} else {
			/* --- Render --- */
			render($app, "type--categories", array("document" => $doc, "namespace" => "articles"));
		}

		return $response;
	}

	$doc = $CONTENT->local->getContent("articles");

	/* --- Check for 404 --- */
	if(!isset($doc) || $doc === null || $doc->uid !== "articles"){
		not_found_include();
	} else {
		/* --- Render --- */
		render($app, "single--articles", array("document" => $doc, "namespace" => "articles", "type" => "single"));
	}

	return $response;
});
?>