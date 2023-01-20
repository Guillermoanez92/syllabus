<?php
global $CONTENT;

$settings = $CONTENT->local->getContent("site-settings", "site_settings");
/* --------------- *
echo "<br><pre>";
print_r($settings);
echo "</pre>";
/* --------------- */
/*
	Mini Cart
-------------------------------------------------- */
if(defined("STOREFRONT_DOMAIN") && STOREFRONT_DOMAIN != ""){
	include dirname(__DIR__) . "/modules/mini-cart.php";
}
?>

<div id="global-mask"></div>
<div id="event-mask"></div>
