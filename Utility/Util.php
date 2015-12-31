<?php 

	namespace GalacticDevelopment\Utility;
	/**
	* Util: Utility class with a bunch of helper methods
	*/
	class Util
	{
        /**
         * http://php.net/manual/en/dir.constants.php
         */
		public static function file_build_path() {
			$segments = func_get_args();
    		return join(DIRECTORY_SEPARATOR, $segments);
		}
	}
?>