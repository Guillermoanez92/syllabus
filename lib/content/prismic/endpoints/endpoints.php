<?php
/**
 * Content API definition
 */
class ContentAPI implements ContentEndpoints {

	/**
	 * Get Single Document
	 */
	public function getDocument($object){

		global $CONTENT;

		/* --- Need UID --- */
		if(empty($object->uid)) throw new Exception("Content UID is required.", 1);
		
		$type = (!empty($object->type)) ? $object->type : null;

		$document = $CONTENT->local->getContent($object->uid, $type);

		/* --- Normalize --- */
		if(is_null($document)) $document = "{}";

		return json_encode($document);
	}

	/**
	 * Get Multiple Documents
	 */
	public function getDocuments($object){

		global $CONTENT;

		$type = (!empty($object->type)) ? $object->type : null;
		$sort = (!empty($object->sort)) ? $object->sort : null;
		$direction = (!empty($object->direction)) ? $object->direction : "desc";

		$documents = returnAllContentAsArray(FILE_CACHE . "/content/", $type, $sort, $direction);

		return json_encode($documents);
	}
}
?>