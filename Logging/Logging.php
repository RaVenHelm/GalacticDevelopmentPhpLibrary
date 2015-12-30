<?php 
	namespace GalacticDevelopment\Logging;

	/**
	* 
	*/
	class Logging
	{
		
		private $org_name;

		private $log_dir;

		private $file_type;

		function __construct($org, $dir, $type = '.json')
		{
			$this->org_name = $org;
			$this->log_dir = $dir;
			$this->file_type = $type;

			$this->create_log_dir();
		}

		private function create_log_dir()
		{
			if (!file_exists($this->log_dir)) {
				mkdir($this->log_dir);
			}
		}

		public function log_general($message, $date = null)
		{
			$to_write = new \stdClass();
			//echo $this->org_name;
			$file_path = $this->log_dir . '\\' . $this->org_name . '.log' . $this->file_type;
			$bytes_written = 0;

			$fh = fopen($file_path, 'a');

			if (!isset($date)) {
				$date = new \DateTime();
			}

			$to_write->date = $date;
			$to_write->level = 'general';
			$to_write->msg = $message;

			if ($this->file_type === '.json') {
				$bytes_written = fwrite($fh, json_encode($to_write, JSON_PRETTY_PRINT));
			} else {
				$bytes_written = fwrite($fh, $to_write);
			}
			fclose($fh);

			return $bytes_written;
		}

		public function log_error($message, $date = null)
		{
			$to_write = new \stdClass();
			$file_path = $this->log_dir . '\\' . $this->org_name . '.log.error' . $this->file_type;
			$bytes_written = 0;
			$fh = fopen($file_path, 'a');

			if (!isset($date)) {
				$date = new \DateTime();
			}

			$to_write->date = $date;
			$to_write->level = 'error';
			$to_write->msg = $message;

			if ($this->file_type === '.json') {
				$bytes_written = fwrite($fh, json_encode($to_write, JSON_PRETTY_PRINT));
			} else {
				$bytes_written = fwrite($fh, $to_write);
			}
			fclose($fh);

			return $bytes_written;
		}
	}
?>