<?php 

	namespace GalacticDevelopment\Utility;
	/**
	* Util: Utility class with a bunch of helper methods
	*/
	class Util
	{
		public static function file_build_path() {
			$segments = func_get_args();
    		return join(DIRECTORY_SEPARATOR, $segments);
		}
	}
?>