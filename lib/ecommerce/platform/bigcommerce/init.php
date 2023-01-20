<?php
require_once __DIR__ . "/functions.php";

/**
 * Storefront Core definition
 */
class Storefront implements StorefrontCore {

	/**
	 * Check Logout
	 */
	public function checkLogout(){

	}

	/**
	 * Check Checkout
	 *  - Blowout old cart and checkout if the old checkout was completed
	 */
	public function checkCheckout(){

	}

	/**
	 * Get Storefront Information
	 */
	public function getShopInfo(){

	}

	/**
	 * Get customer information
	 *  - Check cookies for customerAccessToken
	 *  - If token exists grab customer information
	 *  - otherwise customer will need to log in again
	 */
	public function getCustomer(){

	}
}