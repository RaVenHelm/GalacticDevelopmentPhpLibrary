<?php 
	namespace GalacticDevelopment\Databases;
	
	class MysqlDatabaseHandler {
		
		private $pdo;
		
		public function __construct($mysqlSettingsFile = '/../../mysql_settings.ini') 
		{
			if (!$settings = parse_ini_file($mysqlSettingsFile, true)) { throw new exception('Unable to open: ' . $mysqlSettingsFile); }
			
			$mysqlSettings = $settings['MySQL'];
			
			$dns = 'mysql:host=' . $mysqlSettings['host'] . ';dbname=' . $mysqlSettings['db_name'];
			
			$this->pdo = new \PDO($dns, $mysqlSettings['username'], $mysqlSettings['password']);
			$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
	}
?>