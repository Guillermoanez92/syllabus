<?php
/**
 * Collections Router
 */
$GLOBAL->app->get("/collections/[{slug}/]", function($request, $response, $args) use ($app){

	global $STOREFRONT;
	global $CONTENT;

	/* --- Return single product page, or parent --- */
	if(isset($args["slug"])){

		$type = "type";
		$document = getContent($args["slug"]) !== null ? $CONTENT->local->getContent($args["slug"], "collections") : $CONTENT->local->getContent("collections");  // FLAG -- getContent SOLVE
		$object = getCollection($args["slug"]);

		if(!$object){
			not_found_include();
			return $response;
		}

		$title = (isset($object->metafields->global->description_tag)) ? $object->metafields->global->description_tag : ucwords($object->title);
		$desc = (isset($object->metafields->global->title_tag)) ? $object->metafields->global->title_tag : truncateString(html_entity_decode(strip_tags($object->body_html)), 300);

	/* --- Return collections listing page --- */
	} else {

		$type = "single";
		$document = $CONTENT->local->getContent("collections"); // FLAG -- getContent SOLVE
		$object = (isset($STOREFRONT->collections)) ? $STOREFRONT->collections : new stdClass(); // FLAG -- RETHINK THIS

		$title = "Collections";
		$desc = "All Collections";
	}

	/* --- Render --- */
	render($app, $type . "--collections", array("document" => $document, "collection" => $object, "namespace" => "collections", "title" => $title, "description" => $desc));

	return $response;
});
?>