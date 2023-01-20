<?php
/**
 * Preview Handler
 */
$GLOBAL->app->get("/preview/", function($request, $response) use ($app){

	global $CONTENT;

	/* --- Grab Preview Token --- */
	$url = $CONTENT->local->previewHandler($request);

	return $response->withStatus(302)->withHeader("Location", $url);
});
?>