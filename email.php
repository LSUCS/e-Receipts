<?php

	class Email {
		
		private $config;

	        private $recipients;
		private $message = "";
		private $headers = array();
		
		function __construct($config) {
			
			require_once "Mail.php";
			$this->config = $config;
			$this->addHeader("From", "<" . $config->email["user"] . ">");
			$this->addHeader("MIME-Version",  "1.0");
			$this->addHeader("Content-type", "text/html; charset=iso-8859-1");
			
		}
		
		/**
		 * Sets the address to send to
		 */
		function addRecipient($rec) {
			$this->recipients[] = "<" . $rec . ">";
		}
		
		/**
		 * Sets the message
		 */
		function setMessage($message) {
			$this->message = $message;
		}
		
		/**
		 * Adds a header
		 */
		function addHeader($header, $value) {
			$this->headers[$header] = $value;
		}
		
		/**
		 * Sends the email
		 */
		function send() {
			
        	$smtp = Mail::factory('smtp',
          		array ('host' => $this->config->email["host"],
            		   'port' => $this->config->email["port"],
                       'auth' => true,
                       'username' => $this->config->email["user"],
                       'password' => $this->config->email["pass"]));

        	$mail = $smtp->send($this->recipients, $this->headers, $this->message);	
	        if (PEAR::isError($mail)) return false;
			return true;
			
		}
		
	}

?>
