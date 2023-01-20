<?php
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/includes/linkResolver.php";
require_once __DIR__ . "/includes/prismicHelper.php";

/**
 * Storefront Core definition
 */
class Content implements ContentCore {

	/**
	 * Start our content connection
	 */
	public function initFramework(){

		global $GLOBAL;
		global $CONTENT;

		$CONTENT->framework = new PrismicHelper($GLOBAL->app);
	}

	/**
	 * Start our content connection
	 */
	public function previewHandler($request){

		global $CONTENT;

		$token = $request->getParam("token");
		$url = $CONTENT->framework->get_api()->previewSession($token, $CONTENT->framework->linkResolver, "/");

		/* --- Store Token In Cookie --- */
		setcookie(Prismic\PREVIEW_COOKIE, $token, time() + 1800, "/", null, false, false);

		return $url;
	}

	public function getContent($uid, $type = null){

		global $CONTENT;

		/* --- Get Preview Cookie --- */
		$previewRef = getPreviewRef();

		/* --- Make Sure Cache Exists --- */
		$cache = FILE_CACHE . "content/";
		$filename = base64_encode($uid);

		/* --- Load through Preview --- */
		if($previewRef){

			/* --- Connect our API --- */
			$api = $CONTENT->framework->get_api();
			$ref = $previewRef ? $previewRef : $api->master()->getRef();
			$options = array(
				"ref" => $ref
			);

			if($type){
				$document = $api->getByUID($type, $uid, $options);
			} else {

				/* --- Naming conventions --- */
				if($uid !== "site_settings" && $uid !== "home"){
					$uid = "single_" . str_replace("-", "_", $uid);
				}

				$document = $api->getSingle($uid);
			}

			return $document;
		}

		if(file_exists(FILE_CACHE . "documents.json")){
			$reference = json_decode(file_get_contents(FILE_CACHE . "documents.json"));
		}

		/* --- No Reference? No Problem --- */
		if(!isset($reference)){
			$contentReference = new stdClass();
		} else {
			$contentReference = $reference;
		}

		/* --- Check for cached file --- */
		$fileExists = file_exists($cache . $filename);
		$fileTime = ($fileExists) ? filemtime($cache . $filename) : null;
		
		/* --- Length of time to backup cache --- */
		$hours = 24;

		/* --- Check for cached file or expired file--- */
		if(!$fileExists || $fileExists && time() - $fileTime > $hours * 3600){

			/* --- Connect our API --- */
			$api = $CONTENT->framework->get_api();

			if($type){
				$document = $api->getByUID($type, $uid);
			} else {

				try {

					/* --- Naming conventions --- */
					if($uid !== "site_settings" && $uid !== "home"){
						$uid = "single_" . str_replace("-", "_", $uid);
					}

					$document = $api->getSingle($uid);
				} catch(\Exception $e){
					throw new Exception("getByUID(\$uid, \$type); is either missing \$type, or this document doesn't exist.");
				}
			}

			if(defined("ENABLE_CACHE") && ENABLE_CACHE === true && isset($document->uid)){

				$uid = $document->uid;
				$filename = base64_encode($document->uid);

				/* --- Write cache file --- */
				file_put_contents($cache . $filename, json_encode($document));
				chmod($cache . $filename, 0664);

				/* --- Check for reference object --- */
				if(!isset($contentReference->{$document->type}) || !is_array($contentReference->{$document->type})){
					$contentReference->{$document->type} = array();
				}

				/* --- Make sure item isn't there --- */
				if(!in_array($filename, $contentReference->{$document->type})){
					array_push($contentReference->{$document->type}, $filename);

					/* --- Write Reference --- */
					file_put_contents(FILE_CACHE . "documents.json", json_encode($contentReference));
					chmod(FILE_CACHE . "documents.json", 0664);
				}
			}

		} else {
			$document = json_decode(file_get_contents($cache . $filename));
		}

		return $document;
	}
}

?>