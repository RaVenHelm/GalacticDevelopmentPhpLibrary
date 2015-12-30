<?php 

	namespace GalacticDevelopment\Sessions;
	
	class RegisterVisitor {
		
		/**
		 *
		 * @var \SQLite3 $db: A sqlite3 database for storing visitor data
		 */
		private $db;
		
		/**
		 *
		 * @var unsigned int $visitorID: The user id to be stored in the cookie
		 */
		private $visitorID;
		
		/**
		 *
		 * @var string $ip: string that represents the end user's IPv4 Address
		 */
		private $ip;
		
		/**
		 *
		 * @var string $salt: salt for mcrypt functions
		 */
		private static $salt = "17th5r10ut915j0s286ab723tyu89t45";
		
		/**
		 *
		 * @var string $cookie: name for the user cookie
		 */
		private $cookie = "visitor";

		/**
		 * @var int $cipherType: encryption/decryption cipher type
		 */
		private static $cipherType = MCRYPT_RIJNDAEL_256;

		/**
		 * @var int $cipherMode: encryption/decryption cipher mode 
		 */
		private static $cipherMode = MCRYPT_MODE_CBC;
		
		/**
		 *
		 * Constructor for RegisterVisitor class
		 * @param \SQLite3 $db: SQLite3 Database object
		 *
		 */
		public function __construct($db) {
			$this->db = $db;
			$this->db->exec('CREATE TABLE IF NOT EXISTS visitor (vid TEXT, ip TEXT, httpUserAgent TEXT, dateVisited INTEGER, PRIMARY KEY(vid, ip))');
			$this->visitorID = $this->generateToken();
			$this->ip = $this->get_ip();
		}
		
		/**
		 *
		 *
		 * Registers visitors to local SQLite3 database
		 * @param $_SERVER variable $headerData: header variables sent by server
		 */
		public function registerVisitor($headerData) {
			if(!$this->isVisitorRegistered($this->parseCookie(), $this->ip)) {
				$sql = 'INSERT INTO visitor (vid, ip, httpUserAgent, dateVisited) VALUES (:vid, :ip, :ua, :date)';
				$sth = $this->db->prepare($sql);
				
				$id = $this->visitorID;
				$ip = $this->ip;
				
				$userAgent = $headerData['HTTP_USER_AGENT'];

				$date =  new \DateTime();
				$sth->bindValue(':vid', $id, SQLITE3_TEXT);
				$sth->bindValue(':ip', $ip, SQLITE3_TEXT);
				$sth->bindValue(':ua', $userAgent, SQLITE3_TEXT);
				$sth->bindValue(':date', $date->getTimestamp(), SQLITE3_INTEGER);
				
				$sth->execute();
				$sth->close();
				
				$this->createCookie();
			}
		}
		
		/**
		 *
		 *
		 * @return: bool whether the user has been registered in the database
		 */
		public function isVisitorRegistered($vid, $ip) {
			$sql = 'SELECT * FROM visitor WHERE vid=:vid AND ip=:ip';
			$sth = $this->db->prepare($sql);
			$sth->bindValue(':vid', $vid, SQLITE3_TEXT);
			$sth->bindValue(':ip', $ip, SQLITE3_TEXT);
			
			$result = $sth->execute();
			if(!$result->fetchArray()) {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * @param: none
		 * @return: string that represents the visitors' id
		 */
		public function getVisitorId() {
			return $this->parseCookie();
		}

		/**
		 * Get the end user's IPv4 Address method from Chris Weigman:
		 *  https://gist.github.com/ChrisWiegman/5df4c564d12f2739d1c7#file-get_ip-php
		 *  https://www.chriswiegman.com/2014/05/getting-correct-ip-address-php/
		 *
		 * @param: void
		 * @return: string that represents the end user's IPv4 Address
		 */
		protected function get_ip() {
			//Just get the headers if we can or else use the SERVER global
			if (function_exists('apache_request_headers')) {
				$headers = apache_request_headers();
			} else {
				$headers = $_SERVER;
				
			}

			//Get the forwarded IP if it exists
			if (array_key_exists('X-Forwarded-For',$headers) && filter_var($headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
				$the_ip = $headers['X-Forwarded-For'];
			} elseif ( array_key_exists('HTTP_X_FORWARDED_FOR', $headers) && filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
				$the_ip = $headers['HTTP_X_FORWARDED_FOR'];
			} else {
                if($_SERVER['REMOTE_ADDR'] == "::1") {
                    $the_ip = '127.0.0.1';
                } else {
                    $the_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
                    //$the_ip = $_SERVER['REMOTE_ADDR'];
                }
			}
			
			return $the_ip;
		}
		
		/**
		 * Creates a cookie that will store the visitor id to check for previous visits
		 *
		 * @return void
		 */
		protected function createCookie()
		{
			$expiry = time() + (24 * 60 * 60);
			setcookie($this->cookie, $this->encryptAndEncode($this->visitorID), $expiry);
		}
		
		/**
		 * Retrieves the stored value of the 
		 *
		 * @return int|bool The visitor id from the cookie
		 */
		protected function parseCookie()
		{
			if(isset($_COOKIE['visitor'])) {
				$cookieData = $_COOKIE[$this->cookie];
				$vid = $this->decryptAndDecode($cookieData);
				return $vid;
			} else {
				return false;
			}
		}
		
		/**
		 * Generates a random 32-character string for the single-use token
		 *
		 * @return string 32-character hexadecimal string
		 */
		protected function generateToken()
		{
			return bin2hex(openssl_random_pseudo_bytes(16));
		}
		
		/**
		 * Encrypts and encodes the visitor id
		 *
		 * @return string Encrypted and base64 encoded iv and visitor id 
		 */
		public function encryptAndEncode($data)
		{
			return base64_encode($this->encryptData($data));
		}
		
		/**
		 * Decrypts and decodes the cookie data
		 *
		 * @return string the visitor's id
		 */
		public function decryptAndDecode($encryptedData)
		{
			return $this->decryptData(base64_decode($encryptedData));
		}
		
		/**
		 * Encrypts the visitor id
		 *
		 * @return string Encrypted iv and visitor id
		 */
		public function encryptData($data)
		{
			$salt = RegisterVisitor::$salt;
			
			$ivSize = mcrypt_get_iv_size(RegisterVisitor::$cipherType, RegisterVisitor::$cipherMode);
			$iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
			
			$encryptedString = mcrypt_encrypt(RegisterVisitor::$cipherType, $salt, $data, RegisterVisitor::$cipherMode, $iv);
			
			return $iv . $encryptedString;
		}
		
		/**
		 * Decrypts the cookie data
		 *
		 * @return string visitor id
		 */
		public function decryptData($data)
		{
			$salt = RegisterVisitor::$salt;
					
			$ivSize = mcrypt_get_iv_size(RegisterVisitor::$cipherType, RegisterVisitor::$cipherMode);
			$iv = substr($data, 0, $ivSize);
			$encryptedString = substr($data, $ivSize);
			
			$vid = mcrypt_decrypt(RegisterVisitor::$cipherType, $salt, $encryptedString, RegisterVisitor::$cipherMode, $iv);
			
			return $vid;
		}

        /**
         * @param $stmt
         * @param $result
         */
        private function cleanup($stmt, $result)
        {
            $stmt->close();
            $result->finalize();
        }
    }
?>