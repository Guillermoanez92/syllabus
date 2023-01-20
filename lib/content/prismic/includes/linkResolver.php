<?php
use Prismic\LinkResolver;

/**
 * The link resolver is the code building URLs for pages corresponding to
 * a Prismic document.
 *
 * If you want to change the URLs of your site, you need to update this class
 * as well as the routes in app.php.
 */
class PrismicLinkResolver extends LinkResolver{

	public function resolve($link) :? string{

		/* --- External Links --- */
		if(!empty($link->link_type) && $link->link_type === "Web"){

			if(!empty($link->target) && $link->target === "_blank"){
				return $link->url . '" target="_blank" rel="noopener';

			} else {

				/* --- Make these links relative --- */
				$sanitizedLink = str_replace("https://staging.domain.com", "", $link->url);
				$sanitizedLink = str_replace("https://www.domain.com", "", $sanitizedLink);
				$sanitizedLink = str_replace("https://domain.com", "", $sanitizedLink);

				return $sanitizedLink;
			}
		}

		/* --- Broken Links --- */
		if(property_exists($link, "isBroken") && $link->isBroken === true){
			return "/404/";
		}

		/* --- Home Routing --- */
		if(!empty($link->link_type) && $link->type === "home"){
			return "/";

		/* --- Product Categories --- */
		} elseif(isset($link->link_type) && $link->type === "product_categories"){
			return "/products/" . $link->uid . "/";

		/* --- Page Routing --- */
		} elseif($link->type !== "page" && $link->type !== "home" && strpos($link->type, "single_") === false){
			return "/" . $link->type . "/" . $link->uid . "/";

		/* --- Single Routing --- */
		} else {
			return "/" . $link->uid . "/";
		}
	}
}
?>
