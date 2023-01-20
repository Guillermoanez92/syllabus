<?php
class StorefrontGlobals {

	public function setLocale(){

		global $STOREFRONT;

		/**
		* Setup our Locale - for things like money_format()
		*/
		if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){
			$lang = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
		} else {
			$lang = "en-US";
		}

		setlocale(LC_ALL, $lang);

		$STOREFRONT->language = $lang;
		$STOREFRONT->format = new NumberFormatter($lang, NumberFormatter::CURRENCY);
		$STOREFRONT->format->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 0);
	}

	public function checkCache(){

		/**
		* Make sure cache folders exists
		*  - Make sure at least cache folder is www-data user/group
		*/
		if(!file_exists(FILE_CACHE . "carts/")){
			mkdir(FILE_CACHE . "carts/", 775);
			chmod(FILE_CACHE . "carts/", 0775);
		}

		if(!file_exists(FILE_CACHE . "customers/")){
			mkdir(FILE_CACHE . "customers/", 775);
			chmod(FILE_CACHE . "customers/", 0775);
		}

		if(!file_exists(FILE_CACHE . "storefront/")){
			mkdir(FILE_CACHE . "storefront/", 775);
			chmod(FILE_CACHE . "storefront/", 0775);
		}

		if(!file_exists(FILE_CACHE . "subscription/")){
			mkdir(FILE_CACHE . "subscription/", 775);
			chmod(FILE_CACHE . "subscription/", 0775);
		}
	}
}
?>