<?php
require dirname(__DIR__) . "/includes/elasticsearch.php";

/* --- Defaults --- */
$number_of_results = 100;

/* --- Elasticsearch Init --- */
$client = ElasticsearchClient::Init();

if(isset($_GET["s"])){

	$params = array(
		"index" => SITE_SEARCH_KEY . "_prismic," . SITE_SEARCH_KEY . "_shop",
		"size"	=> $number_of_results,
		"body"  => array(
			"query" => array(
				"multi_match" => array(
					"query" => $_GET["s"],
					"type"	=> "phrase_prefix"
				)
			)
		)
	);

	if(isset($_GET["type"]) && $_GET["type"] !== ""){
		$params["index"] = SITE_SEARCH_KEY . "_prismic";

		if($_GET["type"] == "products" || $_GET["type"] == "collections"){
			$params["index"] = SITE_SEARCH_KEY . "_shop";
		}
	}

	// Need to address mappings
	// if(isset($_GET["sort"])){
	// 	$params["body"]["sort"] = array(
	// 		array("first_publication_date" => array("order" => "desc")),
	// 		array("published_at" => array("order" => "desc"))
	// 	);
	// }

	/* --------------- */
	echo "<br><pre>";
	print_r($params);
	echo "</pre>";
	/* --------------- */

	try{
		$results = $client->search($params);
		/* --------------- */
		echo "<br><pre>";
		print_r($results);
		echo "</pre>";
		/* --------------- */

	} catch(Exception $e){
		/* --------------- */
		echo "<br><pre>";
		print_r($e);
		echo "</pre>";
		/* --------------- */
	}
}


?>