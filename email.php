<?php

	class Email {
		
		private $to = "";
		private $subject = "";
		private $message = "";
		private $headers = array();
		
		function __construct($config) {
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
			$headers = "";
			foreach ($this->headers as $key => $val) $headers .= $key . ": " . $val . "\r\n";
			return @mail($this->to, $this->subject, $this->message, $headers);
		}
		
	}

?>