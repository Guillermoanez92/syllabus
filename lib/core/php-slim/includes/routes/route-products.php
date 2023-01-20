<?php
/**
 * Products Router
 */
$GLOBAL->app->get("/tienda/[{slug}/]", function($request, $response, $args) use ($STOREFRONT, $CONTENT, $app){

	/* --- Return single product page, or parent --- */
	if(isset($args["slug"])){

		$type = "type";
		$document = null;

		/* --- Does your project have individual product pages managed at the CONTENT level? --- *
		$document = $CONTENT->local->getContent($args["slug"]) !== null ? $CONTENT->local->getContent($args["slug"], "products") : $CONTENT->local->getContent("products");
		/* --- END IF --- */

		/* --- Does your project only have a "global" products managed CONTENT template? --- *
		$document = $CONTENT->local->getContent("products");
		/* --- END IF --- */

		$object = $STOREFRONT->api->getProduct($args["slug"]);

		if(!$object){
			not_found_include();
			return $response;
		}
		$document = $CONTENT->local->getContent($args["slug"], "product");
		$title = (isset($object->metafields->global->description_tag)) ? $object->metafields->global->description_tag : ucwords($object->title);
		$desc = (isset($object->metafields->global->title_tag)) ? $object->metafields->global->title_tag : truncateString(html_entity_decode(strip_tags($object->body_html)), 300);

	/* --- Return product listing page --- */
	} else {

		$type = "single";
		$document = $CONTENT->local->getContent("products"); // FLAG -- getContent SOLVE
		$object = (isset($STOREFRONT->products)) ? $STOREFRONT->products : new stdClass(); // FLAG -- RETHINK THIS

		$title = "Products";
		$desc = "All Products";
	}

	/* --- Render Page --- */
	render($app, $type . "--products", array("document" => $document, "product" => $object, "namespace" => "products", "title" => $title, "description" => $desc));

	return $response;
});
?>
