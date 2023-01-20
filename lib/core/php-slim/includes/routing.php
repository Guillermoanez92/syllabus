<?php
/**
 * Routing
 *	- These serve in the order they're set and
 *	  the order matters
 */

/* --- Index Routes --- */
include __DIR__ . "/routes/route-index.php";

/* --- Webhook Routes --- */
include __DIR__ . "/routes/route-webhooks.php";

/* --- API Endpoint Routes --- */
include __DIR__ . "/routes/route-endpoints.php";

/* --- Check if Content is enabled --- */
if(isset($BUILDINFO->lib->content_management)){
	/* --- /preview/ --- */
	include __DIR__ . "/routes/route-preview.php";
}

/* --- / --- */
include __DIR__ . "/routes/route-home.php";

/* --- Check if Search is enabled --- */
if(defined("SITE_SEARCH") && SITE_SEARCH === true){

	/* --- /search/ --- */
	include __DIR__ . "/routes/route-search.php";
}

/* --- Check if Storefront is enabled --- */
if(!empty($BUILDINFO->lib->ecommerce)){

	/* --- /cart/ --- */
	include __DIR__ . "/routes/route-cart.php";

	/* --- /account/ --- */
	include __DIR__ . "/routes/route-account.php";

	/* --- /products/ --- */
	include __DIR__ . "/routes/route-products.php";

	/* --- /collections/ --- */
	include __DIR__ . "/routes/route-collections.php";
}

/* --- Does this project support a blog? --- */
if(defined("BLOG_ENABLED") && BLOG_ENABLED === true){

	/* --- /category/post/ --- */
	include __DIR__ . "/routes/route-blog.php";
}

/* --- Custom Routes --- */
include __DIR__ . "/routes/route-custom.php";

/* --- Catch All Routes --- */
include __DIR__ . "/routes/route-default.php";
?>