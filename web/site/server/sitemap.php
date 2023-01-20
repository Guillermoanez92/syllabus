<?php
ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require dirname(dirname(__DIR__)) . "/config.php";

/**
 * Make sure we're running in UTC
 */
date_default_timezone_set("UTC");

/**
 * Grab POST
 */
if($json = json_decode(file_get_contents(FILE_CACHE . "sitemap.json"), true)){
	$data = $json;
}

$siteRoot = dirname(__DIR__);
$domain = SITE_DOMAIN;
$limit = 2000; // Sitemap Content Limit

if($data){

	/* --- Check for Products to add to data --- */
	try {
		
		$data["products"] = array();
		$products = file_exists(FILE_CACHE . "storefront/products.json") ? json_decode(file_get_contents(FILE_CACHE . "storefront/products.json")) : array();

		foreach($products as $key => $product){

			if(empty($product->type)) continue;
			
			$data["products"][$key]["loc"] = slugify($product->type) . "/" . $product->handle;
	 		$data["products"][$key]["lastmod"] = $product->updated_at;
		}

	} catch(\Exception $e){
		
	}

	/* --- Check for Collections to add to data --- */
	try {

		$data["collections"] = array();
		$collections = file_exists(FILE_CACHE . "storefront/collections.json") ? json_decode(file_get_contents(FILE_CACHE . "storefront/collections.json")) : array();

		foreach($collections as $key => $product){
			$data["collections"][$key]["loc"] = $product->handle;
			$data["collections"][$key]["lastmod"] = $product->updated_at;
		}

	} catch(\Exception $e){

	}

	/* --- Setup XML Index --- */
	$xml = '<?xml version="1.0" encoding="UTF-8"?>';
	$xml.= '<sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

	foreach($data as $key => $type){

		// file_put_contents(FILE_CACHE . "/shopify/raw-array-" . $key . ".json", json_encode($type));

		switch($key){

			case "categories":
			case "navigation":
			case "not_found":
			case "site_settings":
			case "single_not_found":
			case "single_redirects":
			case "tags":

				/* --- PASS --- */
				break;

			default:

				$url = $domain . "/sitemap-" . $key;

				/* --- Get Pages --- */
				$count = ceil(count($type) / $limit);

				for($i = 1; $i <= $count; ++$i){

					$mod = getLastDate($type, $i - 1, $limit);

					/* --- Append Page --- */
					$append = ($i > 1 ? "-" . $i : "");

					$xml.= '<sitemap>';
						$xml.= '<loc>' . $url . $append .'.xml</loc>';
						$xml.= '<lastmod>' . substr_replace($mod, ":", -2, 0) . '</lastmod>';
					$xml.= '</sitemap>';
				}

				break;
		}
	}

	$xml .= '</sitemapindex>';

	/* --- WRITE Index --- */
	file_put_contents($siteRoot . "/sitemap.xml", $xml);
	chmod($siteRoot . "/sitemap.xml", 0664);

	/* --- Start our Full Loop --- */
	foreach($data as $key => $type){

		switch($key){

			case "categories":
			case "navigation":
			case "not_found":
			case "site_settings":
			case "single_not_found":
			case "single_redirects":
			case "tags":

				/* --- PASS --- */
				break;

			default:

				/* --- Start XML File markup --- */
				$xml = '<?xml version="1.0" encoding="UTF-8"?>';
				$xml.= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

				$filename = "/sitemap-" . $key . ".xml";

				$x = 0;		// Count
				$p = 2;

				foreach($type as $index => $post){

					$xml.= '<url>';
						$xml.= '<loc>' . $domain . buildUrl($post["loc"], $key) . '</loc>';
						if($key === "products" || $key === "collections"){
							$xml.= '<lastmod>' . $post["lastmod"] . '</lastmod>';
						} else {
							$xml.= '<lastmod>' . substr_replace($post["lastmod"], ":", -2, 0) . '</lastmod>';
						}
						$xml.= '<changefreq>' . getFrequency($key) . '</changefreq>';
						$xml.= '<priority>' . getPriority($key) . '</priority>';
					$xml.= '</url>';

					++$x;

					/* --- Paginate XML Sheet --- */
					if($x >= $limit){

						/* --- End Last XML --- */
						$xml.= '</urlset>';

						/* --- Write Last XML --- */
						file_put_contents($siteRoot . $filename, $xml);
						chmod($siteRoot . $filename, 0664);

						/* --- Start New XML --- */
						$xml = '<?xml version="1.0" encoding="UTF-8"?>';
						$xml.= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

						$filename = "/sitemap-" . $key . "-" . $p . ".xml";

						/* --- Reset Vars / Move Page --- */
						$x = 0;

						++$p;
					}
				}

				$xml.= '</urlset>';

				/* --- Write this XML --- */
				file_put_contents($siteRoot . $filename, $xml);
				chmod($siteRoot . $filename, 0664);

				break;
		}
	}
} else {
	error_log(json_encode($data) . PHP_EOL);
	error_log(" ----- NO SITEMAP DATA ----- " . PHP_EOL);
}

function getLastDate($array, $page, $limit){

	$start = $page * $limit;

	if(!array_key_exists($start, $array)){
		$newDate = new DateTime();
		$lastMod = $newDate->format("c");
	} else {
		$lastMod = $array[$start]["lastmod"];
	}

	$max = count($array);

	if($max > $start + $limit){
		$max = $start + $limit;
	}

	/* --- Loop through batch --- */
	for($i = $start; $i < $max; ++$i){

		if(!array_key_exists($i, $array)){
			continue;
		}

		if($array[$i]["lastmod"] > $lastMod){
			$lastMod = $array[$i]["lastmod"];
		}
	}

	return $lastMod;
}


function buildUrl($handle, $type){

	$url = $handle;

	switch($type){

		case "home":

			$url = "/";

			break;

		case "articles":
		case "authors":
		case "collections":
		case "products":

			$url = "/" . $type . "/" . $handle . "/";

			break;

		case "product_categories":

			$url = "/products/" . $handle . "/";

			break;

		default:

			$url = "/" . $handle . "/";

			break;
	}

	return $url;
}

function getFrequency($type){

	$freq = "monthly";

	switch($type){

		case "articles":
		case "home":
		case "single_reviews":

			$freq = "daily";

			break;

		case "categories":
		case "product_categories":
		case "page":
		case "tags":

			$freq = "weekly";

			break;

		default:

			break;
	}

	return $freq;
}

function getPriority($type){

	$priority = "0.2";

	switch($type){

		case "home":

			$priority = "1.0";

			break;

		case "products":
		case "collections";
		case "product_categories":

			$priority = "0.8";

			break;

		case "page":
		case "single_reviews":

			$priority = "0.6";

			break;

		case "authors":
		case "categories":
		case "tags":

			$priority = "0.4";

			break;

		default:

			break;
	}

	return $priority;
}
?>