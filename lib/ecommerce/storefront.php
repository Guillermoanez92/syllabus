<?php
global $BUILDINFO;
global $STOREFRONT;

$STOREFRONT = new stdClass();

/**
 *    eCommerce includes
 */
require_once __DIR__ . "/_interfaces.php";
require_once __DIR__ . "/_globals.php";
require_once __DIR__ . "/platform/" . $BUILDINFO->lib->ecommerce->platform . "/init.php";

/**
 *    eCommerce global functions
 */
$STOREFRONT->settings = new StorefrontGlobals();
$STOREFRONT->settings->checkCache();
$STOREFRONT->settings->setLocale();

/**
 *    eCommerce local functions
 */
$STOREFRONT->functions = new Storefront();

/**
 *    eCommerce connection setup
 */
$STOREFRONT->client = $STOREFRONT->functions->setClient();

/**
 *    eCommerce indices setup
 */
require_once __DIR__ . "/platform/" . $BUILDINFO->lib->ecommerce->platform . "/index/indices.php";

$STOREFRONT->indices = new StorefrontIndex();

/**
 *    eCommerce webhooks setup
 */
require_once __DIR__ . "/platform/" . $BUILDINFO->lib->ecommerce->platform . "/webhooks/webhooks.php";

$STOREFRONT->webhooks = new StorefrontWebhook();

/**
 *    eCommerce endpoints setup
 */
require_once __DIR__ . "/platform/" . $BUILDINFO->lib->ecommerce->platform . "/endpoints/endpoints.php";

$STOREFRONT->api = new StorefrontAPI();

/**
 *    Subscription endpoints setup
 */
if(!empty($BUILDINFO->lib->ecommerce->subscription)){

	require_once __DIR__ . "/subscription/" . $BUILDINFO->lib->ecommerce->subscription . "/endpoints/endpoints.php";

	$STOREFRONT->subscription = new SubscriptionAPI();
}

/**
 *    eCommerce startup function checks
 */
if(isset($_GET["logout"]) && $_GET["logout"] == true){
    $STOREFRONT->functions->Logout();
}

$STOREFRONT->functions->checkCheckout();
$STOREFRONT->functions->getShopInfo();
$STOREFRONT->functions->getCustomer();

/* --------------- *
echo "<br><pre>";
print_r($STOREFRONT);
echo "</pre>";
/* --------------- */
?>