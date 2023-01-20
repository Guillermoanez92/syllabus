<?php
global $GLOBAL;
global $BUILDINFO;
global $ASSETPATH;

require_once dirname(__DIR__) . "/vendor/autoload.php";
require_once dirname(__DIR__) . "/config.php";

/* --- Let's check our configs --- */
$GLOBAL = new stdClass();
$BUILDINFO = json_decode(file_get_contents(__DIR__ . "/build.json"));
$ASSETPATH = (!in_array($_SERVER["REMOTE_ADDR"], array("127.0.0.1", "::1")) && isset($BUILDINFO->cdnUrl)) ? "https://storage.googleapis.com/" . $BUILDINFO->cdnUrl : "/assets/code/";

/* --- Fire up our Core --- */
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

/* --- Initialize Slim App --- */
$app = AppFactory::create();

$GLOBAL->app = $app;

/* --- Load up our Data --- */
if(isset($BUILDINFO->lib->data)) require_once dirname(__DIR__) . "/lib/data/data.php";

/* --- Load up our eCommerce --- */
if(isset($BUILDINFO->lib->ecommerce)) require_once dirname(__DIR__) . "/lib/ecommerce/storefront.php";

/* --- Load up our CMS --- */
if(isset($BUILDINFO->lib->content_management)) require_once dirname(__DIR__) . "/lib/content/content.php";

/* --- Run our App --- */
require_once dirname(__DIR__) . "/lib/core/" . $BUILDINFO->lib->core . "/functions.php";
require_once dirname(__DIR__) . "/lib/core/" . $BUILDINFO->lib->core . "/app.php";
?>
