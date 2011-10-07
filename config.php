<?php

	class Config {
		
		public $database;
		public $general;
		public $admin;
		public $receipt;
		
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
			$this->admin["defaultPass"]   = "adminpass";
			
			/**
			 * Receipt control
			 */
			$this->receipt["fromEmail"]   = "LSU SocFed Receipts <committee@lsucs.org.uk>";
			$this->receipt["receiptTitle"]  = "Receipt for your payment to %SOCIETY%"; #%SOCIETY% is replaced with the society name
			
			
		}
		
	}

?>