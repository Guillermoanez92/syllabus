<?php
/**
 * General Page Type & Single Handler route fallback
 */
$GLOBAL->app->get("/{page}/[{slug}/]", function($request, $response, $args) use ($app){

	global $CONTENT;

	/* --- Check if Child --- */
	if(isset($args["slug"])){

		try {

			/* --- Get Content --- */
			$doc = $CONTENT->local->getContent($args["slug"], $args["page"]);

			/* --- Throw Exception --- */
			if(!isset($doc) || $doc === null || $args["page"] == "page") throw new Exception("Page does not exist", 1);

			/* --- Render Page --- */
			render($app, "type--" . $args["page"], array("document" => $doc, "namespace" => $args["page"]));

		} catch(\Exception $e){

			/* --- Return 404 --- */
			not_found_include();
		}

		return $response;
	}

	/* --- Otherwise check page --- */
	try {

		/* --- Get Content --- */
		$doc = $CONTENT->local->getContent($args["page"], "page");

		/* --- Throw Exception --- */
		if(!isset($doc) || $doc === null || $doc->type !== "page") throw new Exception("Error Processing Request", 1);

		/* --- Render Page --- */
		render($app, "page--" . $args["page"], array("document" => $doc, "namespace" => "page", "type" => "page"));

	} catch(\Exception $e){

		/* --- Get Content --- */
		$doc = $CONTENT->local->getContent($args["page"], "single_".$args["page"]);

		/* --- Check for Page --- */
		if(!isset($doc) || $doc === null || $doc->uid !== $args["page"] || $doc->uid == "home"){

			/* --- Return 404 --- */
			not_found_include();

			return $response;
		}

		/* --- Render --- */
		render($app, "single--" . $args["page"], array("document" => $doc, "namespace" => $args["page"], "type" => "single"));
	}

	return $response;
});
?>
