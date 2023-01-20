<?php
/**
 * Storefront Indices definition
 */
class ContentIndex implements ContentIndices {

	public function indexContent($data){

		global $CONTENT;

		/**
		 * Make sure shopify products cache folders exist
		 */
		$cache = FILE_CACHE . "content/";

		if(!file_exists($cache)){
			mkdir($cache, 775);
			chmod($cache, 0775);
		}

		$contentReference = array();
		$sitemap_json = array();

		/* --- Flush cache, Prismic is a bulk updater --- */
		$di = new RecursiveDirectoryIterator($cache, FilesystemIterator::SKIP_DOTS);
		$ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);

		foreach($ri as $file){
		    $file->isDir() ? rmdir($file) : unlink($file);
		}

		/* --- Set Options: https://prismic.io/docs/php/query-the-api/query-options-reference --- */
		$options = array(
			"pageSize" => 100,
			"page" => 1
		);

		$api = $CONTENT->framework->get_api();
		$response = $api->query("", $options);

		/* --- Loop through Results --- */
		foreach($response->results as $content){

			if(empty($content->uid)) continue;

			$filename = base64_encode($content->uid);
			$sitemap_object = new stdClass();

			if(!isset($contentReference[$content->type]) || !is_array($contentReference[$content->type])){
				$contentReference[$content->type] = array();
				$sitemap_json[$content->type] = array();
			}

			if(!in_array($filename, $contentReference[$content->type])){

				array_push($contentReference[$content->type], $filename);

				$sitemap_object->lastmod = $content->last_publication_date;
				$sitemap_object->loc = $content->uid;

				array_push($sitemap_json[$content->type], $sitemap_object);
			}

			/* --- Index --- */
			try{

				file_put_contents($cache . $filename, json_encode($content), LOCK_EX);
				chmod($cache . $filename, 0664);

			} catch(Exception $e){
				error_log($e->getMessage());
			}
		}

		/* --- Loop through pages --- */
		while(!is_null($response->next_page)){

			$options["page"]++;
			$response = $api->query("", $options);

			/* --- Log any errors that don't have results --- */
			if(empty($response->results)){

				error_log("CONTENT WEBHOOK ERROR ----- " . json_encode($response) . PHP_EOL);

				continue;
			}

			/* --- Write to cache --- */
			foreach($response->results as $content){

				if(empty($content->uid)) continue;

				$filename = base64_encode($content->uid);
				$sitemap_object = new stdClass();

				if(!isset($contentReference[$content->type]) || !is_array($contentReference[$content->type])){
					$contentReference[$content->type] = array();
					$sitemap_json[$content->type] = array();
				}

				if(!in_array($filename, $contentReference[$content->type])){

					array_push($contentReference[$content->type], $filename);

					$sitemap_object->lastmod = $content->last_publication_date;
					$sitemap_object->loc = $content->uid;

					array_push($sitemap_json[$content->type], $sitemap_object);
				}

				/* --- Index --- */
				try{

					file_put_contents($cache . $filename, json_encode($content), LOCK_EX);
					chmod($cache . $filename, 0664);

				} catch(Exception $e){
					error_log($e->getMessage());
				}
			}
		}

		/* --- Write Reference --- */
		file_put_contents(FILE_CACHE . "documents.json", json_encode($contentReference));
		chmod(FILE_CACHE . "documents.json", 0664);

		/* --- Store Sitemap Raw --- */
		file_put_contents(FILE_CACHE . "sitemap.json", json_encode($sitemap_json));
		chmod(FILE_CACHE . "sitemap.json", 0664);

		/* --- Fire Parallel cURL --- */
		$blankObj = new stdClass();
		execRequest(PROTOCOL . HOST . "/server/sitemap.php", json_encode($blankObj));
	}
}
?>
