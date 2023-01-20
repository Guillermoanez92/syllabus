<?php
/**
 *	content management core defaults
 */
interface ContentCore {

	function initFramework();
	function previewHandler($request);
	function getContent($uid, $type);
}

/**
 *	content management indices defaults
 */
interface ContentIndices {

	function indexContent($data);
}

/**
 *	content webhook defaults
 */
interface ContentWebhooks {

	function contentUpdate($object);
	function contentDelete($object);
}

/**
 *	content endpoint defaults
 */
interface ContentEndpoints {

	function getDocument($object);
	function getDocuments($object);
}
?>