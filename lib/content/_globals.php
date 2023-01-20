<?php
class ContentGlobals {

	public function checkCache(){

		/**
		 * Make sure cache folders exists
		 *  - Make sure at least cache folder is www-data user/group
		 */
		if(!file_exists(FILE_CACHE . "content/")){
			mkdir(FILE_CACHE . "content/", 775);
			chmod(FILE_CACHE . "content/", 0775);
		}
	}
}
?>