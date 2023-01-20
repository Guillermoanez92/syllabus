<?php
require_once dirname(dirname(dirname(__DIR__))) . "/vendor/autoload.php";
require_once __DIR__ . "/functions.php";

use Elasticsearch\ClientBuilder;

/**
 * Storefront Core definition
 */
class Database implements DatabaseCore {

	/* --- Establish our connection --- */
	static private $hosts = array(
		SITE_SEARCH_URL,
		"https://" . SITE_SEARCH_URL
	);

	static public function Init(){

		try {
			return ClientBuilder::create()->setHosts(self::$hosts)->build();
		} catch(\Exception $e){

			error_log($e->getMessage());

			return $e->getMessage();
		}
	}

	/* --- Check for our database tables --- */
	public function CheckDatabases($array = array()){

		global $DATABASE;

		$DATABASE->tables = new stdClass();

		foreach($array as $table){

			$args = array(
				"index" => SITE_SEARCH_KEY . "_" . $table
			);

			$DATABASE->tables->{$table} = new stdClass();
			$DATABASE->tables->{$table}->alive = $DATABASE->client->indices()->exists($args);

			/* --- Create our Index if it doesn't exist --- */
			if($DATABASE->tables->{$table}->alive === false){

				include_once __DIR__ . "/tables/" . $table . "/settings.php";
				include_once __DIR__ . "/tables/" . $table . "/mappings.php";

				$setup = array(
					"index" => SITE_SEARCH_KEY . "_" . $table,
					"body" => array(
						"settings" => $settings
					)
				);

				if(!empty($mappings)){
					$setup["body"]["mappings"] = $mappings;
				}

				/* --- Index the Document --- */
				$DATABASE->tables->{$table}->index = $DATABASE->client->indices()->create($setup);
			}
		}
	}

	/* --- Index bulk documents --- */
	public function IndexBulk($object){

		global $DATABASE;

		try {

			return $DATABASE->client->bulk($object);

		} catch(\Exception $e){

			error_log($e->getMessage());

			return $e->getMessage();
		}
	}

	/* --- Index single document --- */
	public function IndexDocument($object){

		global $DATABASE;

		try {

			return $DATABASE->client->index($object);

		} catch(\Exception $e){

			error_log($e->getMessage());

			return $e->getMessage();
		}
	}

	/* --- Delete single document --- */
	public function DeleteDocument($object){
		
		global $DATABASE;

		try {

			return $DATABASE->client->delete($object);

		} catch(\Exception $e){

			error_log($e->getMessage());

			return $e->getMessage();
		}
	}
}
?>