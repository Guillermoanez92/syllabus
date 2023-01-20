<?php
require_once dirname(dirname(__DIR__)) . "/vendor/autoload.php";

use Elasticsearch\ClientBuilder;

/**
 * Shopify Connection
 */
class ElasticsearchClient{

	static private $hosts = array(
		SITE_SEARCH_URL,
		"https://" . SITE_SEARCH_URL
	);

	static public function Init(){
		return ClientBuilder::create()->setHosts(self::$hosts)->build();
	}
}

function esGetUser($client, $id){

	$params = array(
		"index" => SITE_SEARCH_KEY . "_shop",
		"type" => "shop",
		"id" => $id
	);

	try{
		return $client->get($params);
	} catch(Exception $e){
		return null;
	}
}

function esCreateUser($client, $object, $id){

	$params = array(
		"index" => SITE_SEARCH_KEY . "_shop",
		"type" => "shop",
		"id" => $id,
		"body" => $object
	);

	return $client->index($params);
}

function esUpdateUser($client, $object, $id){

	$params = array(
		"index" => SITE_SEARCH_KEY . "_shop",
		"type" => "shop",
		"id" => $id,
		"body" => array(
			"doc" => $object,
			"upsert" => $object
		)
	);

	return $client->update($params);
}
?>