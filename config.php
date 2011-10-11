<?php

	class Config {
		
		public $database;
		public $general;
		public $admin;
		public $receipt;
		public $email;
		
		function __construct() {
			
			/**
			 * Database settings
			 */
			$this->database["host"] = "localhost";
			$this->database["user"] = "root";
			$this->database["pass"] = "";
			$this->database["db"]   = "lan";
			
			/**
			 * General settings
			 */
			$this->general["defaultPage"]  = "make";
			$this->general["defaultTitle"] = "Issue Receipt";
			$this->general["titleSuffix"]  = "LSUCS e-Receipt System";
			
			/**
			 * Admin control
			 */
			$this->admin["adminAccounts"] = array( "admin@lsucs.org.uk", "admin@socfed.co.uk" );
			$this->admin["defaultAdmin"]  = "admin@lsucs.org.uk";
			$this->admin["defaultPass"]   = "nopassforyou";
			
			/**
			 * Receipt control
			 */
			$this->receipt["receiptTitle"]  = "Receipt for your payment to %SOCIETY%"; #%SOCIETY% is replaced with the society name
			
			/**
			 * Email control
			 */
			$this->email["user"] = "receipts@lsucs.org.uk";
			$this->email["pass"] = 'nopassforyou';
			$this->email["host"] = "ssl://smtp.gmail.com";
			$this->email["port"] = "465";
			
		}
		
	}

?>