<?php

	class Email {
		
		private $to = "";
		private $subject = "";
		private $message = "";
		private $headers = array();
		
		function __construct($config) {
			
			require_once "Mail.php";
			
			$this->addHeader("From", $config->receipt["fromEmail"]);
			$this->addHeader("MIME-Version",  "1.0");
			$this->addHeader("Content-type", "text/html; charset=iso-8859-1");
			
		}
		
		/**
		 * Sets the address to send to
		 */
		function setTo($to) {
			$this->to = $to;
		}
		
		/**
		 * Sets the subject
		 */
		function setSubject($subject) {
			$this->subject = $subject;
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
          		array ('host' => $config->email["host"],
            		   'port' => $config->email["port"],
                       'auth' => true,
                       'username' => $config->email["user"],
                       'password' => $config->email["pass"]));

        	$mail = $smtp->send($this->to, $this->headers, $this->message);

	        if (PEAR::isError($mail)) return false;
			return true;
			
		}
		
	}

?>