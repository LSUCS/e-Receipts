<?php

	//Database class
	class Db {
		
		private $config;
		
		function __construct($parent) {
			$this->config = $parent->config;
			mysql_connect($this->config->database["host"], $this->config->database["user"], $this->config->database["pass"]) or die("Unable to connect to SQL Database!");
			mysql_select_db($this->config->database["db"]) or die("Unable to select database!");
			$this->createTables();
		}
		
		/**
		 * Base MySQL query function. Cleans all parameters to prevent injection
		 */
		function query() {
			$args = func_get_args();
			$sql = array_shift($args);
			foreach ($args as $key => $value)
				$args[$key] = $this->clean($value);
			return mysql_query(vsprintf($sql, $args));
		}
		/**
		 * Stops MySQL injection
		 */
		private function clean($string) {
			return mysql_real_escape_string(trim($string));
		}
		
		
		/**
		 * Checks if a user exists
		 */
		function userExists($email) {
			if ($this->getUser($email) != false)
				return true;
			return false;
		}	
		/**
		 * Check if email and password are valid
		 */
		function checkLoginDetails($email, $password) {
			$sql = "SELECT * FROM `users` WHERE UPPER(email)=UPPER('%s') AND password='%s'";
			$result = $this->query($sql, $email, sha1($password));
			if (mysql_num_rows($result) == 0)
				return false;
			return true;
		}
		/**
		 * Add user to database
		 */
		function addUser($email, $password, $student_id, $society_id) {
			$sql = "INSERT IGNORE INTO `users` (email, password, student_id, society_id)
					VALUES ('%s', '%s', '%s', '%s')";
			$this->query($sql, $email, sha1($password), $student_id, $society_id);
		}
		/**
		 * Retrieves a user from the database
		 */
		function getUser($email) {
			if (is_numeric($email))
				$sql = "SELECT users.user_id, users.student_id, users.email, users.password, users.society_id, users.active, societies.society_name FROM `users`, `societies` WHERE societies.society_id=users.society_id AND user_id=%s";
			else
				$sql = "SELECT users.user_id, users.student_id, users.email, users.password, users.society_id, users.active, societies.society_name FROM `users`, `societies` WHERE societies.society_id=users.society_id AND UPPER(users.email)=UPPER('%s')";
			$result = $this->query($sql, $email);
			if (mysql_num_rows($result) == 0) return false;
			return mysql_fetch_object($result);
		}
		/**
		 * Returns all users from the database
		 */
		function getUsers() {
			$sql = "SELECT users.user_id, users.email, users.student_id, 'Click to change password', users.active, societies.society_name FROM `users`, `societies` WHERE societies.society_id=users.society_id";
			$result = $this->query($sql);
			if (mysql_num_rows($result) == 0) return false;
			while ($row = mysql_fetch_object($result)) $rows[] = array($row->user_id, $row->email, $row->student_id, "Click to change", str_replace(array(1, 0), array("Yes", "No"), $row->active), $row->society_name);
			return $rows;
		}
		function updateUser($user_id, $email, $password, $student_id, $society_id, $active) {
			if ($password == null) {
				$sql = "UPDATE `users` SET email='%s', student_id='%s', society_id='%s', active='%s' WHERE user_id='%s'";
				return $this->query($sql, $email, $student_id, $society_id, $active, $user_id);
			}
			else {
				$sql = "UPDATE `users` SET email='%s', password='%s', student_id='%s', society_id='%s', active='%s' WHERE user_id='%s'";
				return $this->query($sql, $email, sha1($password), $student_id, $society_id, $active, $user_id);
			}
		}
		
		
		/**
		 * Returns specified product
		 */
		function getProduct($id, $society_id) {
			$sql = "SELECT products.product_id, products.product_name, products.price, products.available, societies.society_name, societies.society_id FROM `products`, `societies` WHERE products.society_id = societies.society_id AND products.product_id='%s'";
		 	if ($society_id != null) $sql .= " AND products.society_id='%s'";
		 	$result = $this->query($sql, $id, $society_id);
		 	if (mysql_num_rows($result) == 0) return false;
		 	return mysql_fetch_object($result);
		}
		/**
		 * Returns all products
		 */
		function getProducts($society_id) {
		 	$sql = "SELECT products.product_id, products.product_name, products.price, products.available, societies.society_name, societies.society_id FROM `products`, `societies` WHERE products.society_id = societies.society_id";
		 	if ($society_id != null) $sql .= " AND products.society_id='%s'";
		 	$result = $this->query($sql, $society_id);
		 	if (mysql_num_rows($result) == 0) return false;
		 	while ($row = mysql_fetch_object($result)) $rows[] = array($row->product_id, $row->product_name, "&pound;" . $row->price, str_replace(array(1, 0), array("Yes", "No"), $row->available), $row->society_name);
		 	return $rows;
		}
		/**
		 * Update or insert product
		 */
		function updateProduct($product_id, $name, $price, $available, $society_id) {
			if ($product_id == null) {
				$sql = "INSERT IGNORE INTO `products` (product_name, price, available, society_id) VALUES('%s', '%s', '%s', '%s')";
				return $this->query($sql, $name, $price, $available, $society_id);
			} else {
				$sql = "UPDATE `products` SET product_name='%s', price='%s', available='%s', society_id='%s' WHERE product_id='%s'";
				return $this->query($sql, $name, $price, $available, $society_id, $product_id);
			}
		}
		
				
		/**
		 * Returns specified society
		 */
		function getSociety($id) {
			$sql = "SELECT * FROM `societies` WHERE society_id='%s'";
		 	$result = $this->query($sql, $id);
		 	if (mysql_num_rows($result) == 0) return false;
		 	return mysql_fetch_object($result);
		}
		/**
		 * Returns all societies
		 */
		function getSocieties() {
		 	$sql = "SELECT * FROM `societies`";
		 	$result = $this->query($sql);
		 	if (mysql_num_rows($result) == 0) return false;
		 	while ($row = mysql_fetch_object($result)) $rows[] = array($row->society_id, $row->society_name, $row->email);
		 	return $rows;
		}
		/**
		 * Update or insert product
		 */
		function updateSociety($society_id, $name, $email) {
			if ($society_id == null) {
				$sql = "INSERT IGNORE INTO `societies` (society_name, email) VALUES('%s', '%s')";
				return $this->query($sql, $name, $email);
			} else {
				$sql = "UPDATE `societies` SET society_name='%s', email='%s' WHERE society_id='%s'";
				return $this->query($sql, $name, $email, $society_id);
			}
		}
		
		
		/**
		 * Adds a receipt to the database
		 */
		function addReceipt($user_id, $email, $name, $student_id, $products, $comments, $society_id) {
			$sql = "INSERT INTO `receipts` (user_id, email, name, student_id, products, comments, society_id) VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s')";
			return $this->query($sql, $user_id, $email, $name, $student_id, $products, $comments, $society_id);
		}
		/**
		 * Returns all receipts
		 */
		function getReceipts($society_id) {
		 	$sql = "SELECT * FROM `receipts`";
		 	$result = $this->query($sql);
		 	if (mysql_num_rows($result) == 0) return false;
		 	while ($row = mysql_fetch_object($result)) $rows[] = array($row->society_id, $row->society_name, $row->email);
		 	return $rows;
		}
		
		
		/**
		 * Creates default tables if they don't exist
		 */
		private function createTables() {

			//User table
			$sql = "CREATE TABLE IF NOT EXISTS `users` (
					user_id int NOT NULL AUTO_INCREMENT,
					email varchar(100),
					student_id varchar(30),
					password varchar(50),
					society_id int DEFAULT 0,
					active BOOL DEFAULT 1,
					PRIMARY KEY(user_id)
					)";
			$this->query($sql);
			if (!$this->userExists($this->config->admin["defaultAdmin"])) $this->addUser($this->config->admin["defaultAdmin"], $this->config->admin["defaultPass"], 'A000000', 1);
			
			//Society table
			$sql = "CREATE TABLE IF NOT EXISTS `societies` (
					society_id int NOT NULL AUTO_INCREMENT,
					email varchar(100),
					society_name varchar(50),
					PRIMARY KEY(society_id)
					)";
			$this->query($sql);
			$sql = "INSERT IGNORE INTO `societies` (society_id, email, society_name)
					VALUES (1, 'committee@lsucs.org.uk', 'LSUCS')";
			$this->query($sql);
			
			//Products table
			$sql = "CREATE TABLE IF NOT EXISTS `products` (
					product_id int NOT NULL AUTO_INCREMENT,
					product_name varchar(100),
					price DECIMAL(10, 2) NOT NULL,
					society_id int DEFAULT 0,
					available BOOL DEFAULT 1,
					PRIMARY KEY(product_id)
					)";
			$this->query($sql);
			
			//Receipts table
			$sql = "CREATE TABLE IF NOT EXISTS `receipts` (
					receipt_id int NOT NULL AUTO_INCREMENT,
					user_id int,
					student_id varchar(30),
					email varchar(100),
					name varchar(100),
					comments text,
					products varchar(100),
					society_id int,
					PRIMARY KEY(receipt_id)
					)";
			$this->query($sql);
			
			//Refund table
			$sql = "CREATE TABLE IF NOT EXISTS `refunds` (
					refund_id int NOT NULL AUTO_INCREMENT,
					receipt_id int,
					refund_amount DECIMAL(10, 2) NOT NULL,
					comments text,
					PRIMARY KEY(refund_id)
					)";
			$this->query($sql);
			
		}
		
	}
	
?>
