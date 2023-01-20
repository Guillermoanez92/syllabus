<?php
/**
 *	eCommerce core defaults
 */
interface StorefrontCore {

	function Logout();
	function checkCheckout();
	function setClient();
	function getShopInfo();
	function getCustomer();
	function getCustomerData($cache, $token, $flush);
	function normalizeCustomer($object);
}

/**
 *	eCommerce indices defaults
 */
interface StorefrontIndices {

	function indexProduct($data);
	function indexCollection($data);
}

/**
 *	eCommerce webhook defaults
 */
interface StorefrontWebhooks {

	function productCreate($data, $header);
	function productUpdate($data, $header);
	function productDelete($data, $header);
	function collectionCreate($data, $header);
	function collectionUpdate($data, $header);
	function collectionDelete($data, $header);
}

/**
 *	eCommerce endpoint defaults
 */
interface StorefrontEndpoints {

	function addressCreate($object);
	function addressDelete($object);
	function addressUpdate($object);
	function cartCreate($object);
	function cartUpdate($object);
	function customerCreate($object);
	function customerUpdate($object);
	function passwordRecover($object);
	function passwordReset($object);
	function getProduct($slug);
	function getCollection($slug);
}

/**
 *	Subscription endpoint defaults
 */
interface SubscriptionEndpoints {

	function subscriptionGet($data);
	function subscriptionCreate($data);
	function subscriptionUpdate($data);
	function subscriptionDiscount($data);
}
?>