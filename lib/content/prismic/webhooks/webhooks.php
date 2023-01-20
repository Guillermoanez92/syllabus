<?php
/**
 * Content Webhooks definition
 */
class ContentWebhook implements ContentWebhooks {

	public function contentUpdate($data){

		/**
		 * Webhook Handler
		 */
		if(!empty($data) && !empty($data->secret) && $data->secret == CONTENT_WEBHOOK_SECRET){

			/* --- Fire Parallel cURL --- */
			$blankObj = new stdClass();

			execRequest(PROTOCOL . HOST . "/v1/index/content/", json_encode($blankObj));

			return true;
		}

		return false;
	}

	public function contentDelete($data){

		var_dump("CONTENT");
	}
}
?>