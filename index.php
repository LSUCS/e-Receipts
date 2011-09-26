<?php
	//Start up sessions, check if logged in
	session_start();
	$main = new Main();
	
	class Main {
		
		//Setup some variables
		public $db;
		public $titleSuffix = "LSUCS e-Receipt System";
		public $title = "Issue Receipt";
		public $page;
		public $get;
		public $pages = array("actionresult", "login", "logout", "make", "view", "refund", "product", "accounts");
		public $user;
		public $refresh = false;
		public $loggedIn = false;
		public $defaultPage = "make";
		
		function __construct() {
			
			$this->db   = new Db();
			$this->page = (isset($_GET["page"]) ? $_GET["page"] : $this->defaultPage);
			
			//Load user if logged in
			if (isset($_SESSION["email"])) {
				$this->loadUser($_SESSION["email"]);
			}
			
			//See if there is a 'get' to do
			if (isset($_GET["get"])) $this->get = strtolower($_GET["get"]);
			else unset($this->get);
			
			//Include requested page
			if (!in_array($this->page, $this->pages)) {
				$this->page = $this->defaultPage;
			}
			include("pages/".$this->page."Page.php");
			$child = new $this->page;
			$child->run($this);
			
		}
		
		//Setup user from username
		function loadUser($username) {
			$this->user = new User($username, $this->db);
			$this->loggedIn = true;
		}
		
		//Display global page header
		function displayHeader() {
			
			//Format require information for template
			$compiledTitle = $this->title . " | " . $this->titleSuffix;
			$userStatus = "";
			if ($this->loggedIn)
				$userStatus = "Welcome, " . $this->user->email . '. Click here to <a href="index.php?page=logout">logout</a>';
				
			?>
			<html> 
				<head>
                	<?php if ($this->refresh) echo '<meta http-equiv="refresh" content="3;url=index.php">'; ?>
					<title><?php echo $compiledTitle; ?></title>
                    <link rel="shortcut icon" href="images/favicon.ico" />
					<link href="css/styles.css" rel="stylesheet" type="text/css" />
                    <link rel="stylesheet" type="text/css" href="css/custom-theme/jquery-ui-1.8.16.custom.css" />
					<link rel="stylesheet" type="text/css" href="css/uniform.default.css" />
					<link rel="stylesheet" type="text/css" href="css/datatables.css" />
                    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
                    <script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
        			<script type="text/javascript" src="js/scripts.js"></script>
        			<script type="text/javascript" src="js/jquery.uniform.min.js"></script>
        			<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
        			<script type="text/javascript" src="js/jquery.jeditable.min.js"></script>
					<?php 
						if (file_exists("css/pages/" . $this->page . ".css")) echo '<link rel="stylesheet" type="text/css" href="css/pages/' . $this->page . '.css" />';
						if (file_exists("js/pages/" . $this->page . ".js")) echo '<script type="text/javascript" src="js/pages/' . $this->page . '.js"></script>';
					?>
				</head>
				<body>
                	<div class="navBarBack">
                	<?php if ($this->loggedIn) { ?>
	                	<ul>
	                    	<li><a href="index.php">Issue Receipt</a></li>
	                        <li><a href="index.php?page=view">View Receipts</a></li>
	                        <li><a href="index.php?page=product">Product Management</a></li>
	                        <?php if ($this->isAdmin()) echo '<li><a href="index.php?page=accounts">Account Management</a></li>'; ?>
	                    </ul>
                	<?php } ?>
                    </div>
                    <div class="mainContainer">
                        <div class="logoBar">
                            <b>e</b>-Receipts
                        </div>
                        <div class="titleBar">
                        	<?php echo $this->title; ?>
                            <b><?php echo $userStatus; ?></b>
                        </div>
                        <div class="contentContainer">
			<?php
		}
		
		//Display global footer
		function displayFooter() {
			?>
            			</div>
					</div>
				</body>
			</html>
			<?php
		}
		
		//Go to action result page
		function actionResult($id) {
			header("location:index.php?page=actionresult&type=".$id);
		}
	
		//Returns true if user is admin
		function isAdmin() {
			if ($this->loggedIn && $this->user->email == "admin")
				return true;
			return false;
		}
		
		//Check if user is logged in, if they aren't, redirect to the login page
		function requireLogin() {
			if (!$this->loggedIn) {
				header("location:index.php?page=login&loggedin=true");
			}
		}
		
	}
	
	//User class
	class User {
		
		public $email;
		public $active;
		public $student_id;
		public $society_id;
		
		function __construct($email, $db) {
			$info = $db->getUser($email);
			$this->email = $info->email;
			$this->active = $info->active;
			$this->student_id = $info->student_id;
			$this->society_id = $info->society_id;
		}
		
	}
	
	//Database class
	class Db {
		
		private $host = "localhost";
		private $username = "root";
		private $password = "";
		private $dbname = "lan";
		
		function __construct() {
			mysql_connect($this->host, $this->username, $this->password) or die("Unable to connect to SQL Database!");
			mysql_select_db($this->dbname) or die("Unable to select database!");
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
			$sql = "INSERT INTO `users` (email, password, student_id, society_id)
					VALUES ('%s', '%s')";
			$this->query($sql, $email, sha1($password), $student_id, $society_id);
		}
		
		
		/**
		 * Retrieves a user from the database
		 */
		function getUser($email) {
			if (is_numeric($email))
				$sql = "SELECT * FROM `users` WHERE user_id=%s";
			else
				$sql = "SELECT * FROM `users` WHERE UPPER(email)=UPPER('%s')";
			$result = $this->query($sql, $email);
			return mysql_fetch_object($result);
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
		 	$sql = "SELECT products.product_id, products.product_name, products.price, products.available, societies.society_name FROM `products`, `societies` WHERE products.society_id = societies.society_id";
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
		 * Creates default tables if they don't exist
		 */
		private function createTables() {

			//User table
			$sql = "CREATE TABLE IF NOT EXISTS `users` (
					user_id int NOT NULL AUTO_INCREMENT,
					student_id varchar(30),
					email varchar(100),
					password varchar(50),
					society_id int DEFAULT 0,
					active BOOL DEFAULT 1,
					PRIMARY KEY(user_id)
					)";
			$this->query($sql);
			$sql = "INSERT INTO `users` (email, password)
					VALUES ('admin', '%s')";
			if (!$this->userExists("admin")) $this->query($sql, sha1("adminpass"));
			
			//Society table
			$sql = "CREATE TABLE IF NOT EXISTS `societies` (
					society_id int NOT NULL AUTO_INCREMENT,
					email varchar(100),
					society_name varchar(50),
					PRIMARY KEY(society_id)
					)";
			$this->query($sql);
			$sql = "INSERT IGNORE INTO `societies` (email, society_name)
					VALUES ('committee@lsucs.org.uk', 'LSUCS')";
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
			$sql = "INSERT IGNORE INTO `societies` (email, name, society_id)
					VALUES ('committee@lsucs.org.uk', 'LSUCS', 1)";
			$this->query($sql);
			
			//Receipts table
			$sql = "CREATE TABLE IF NOT EXISTS `receipts` (
					receipt_id int NOT NULL AUTO_INCREMENT,
					user_id int,
					email varchar(100),
					name varchar(100),
					comments text,
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