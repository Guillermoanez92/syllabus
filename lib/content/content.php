<?php
global $BUILDINFO;
global $CONTENT;

$CONTENT = new stdClass();

/**
 *	Content management includes
 */
require_once __DIR__ . "/_interfaces.php";
require_once __DIR__ . "/_globals.php";
require_once __DIR__ . "/" . $BUILDINFO->lib->content_management . "/init.php";

/**
 *	Content global functions
 */
$CONTENT->settings = new ContentGlobals();
$CONTENT->settings->checkCache();

/**
 *	Content local functions
 */
$CONTENT->local = new Content();
$CONTENT->local->initFramework();

/**
 *    Content indices setup
 */
require_once __DIR__ . "/" . $BUILDINFO->lib->content_management . "/index/indices.php";

$CONTENT->indices = new ContentIndex();

/**
 *    Content webhooks setup
 */
require_once __DIR__ . "/" . $BUILDINFO->lib->content_management . "/webhooks/webhooks.php";

$CONTENT->webhooks = new ContentWebhook();

/**
 *    Content endpoints setup
 */
require_once __DIR__ . "/" . $BUILDINFO->lib->content_management . "/endpoints/endpoints.php";

$CONTENT->api = new ContentAPI();

/* --------------- *
echo "<br><pre>";
print_r($CONTENT);
echo "</pre>";
/* --------------- */
?>