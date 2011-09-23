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
		public $pages = array("actionresult", "login", "logout", "make", "view", "refund", "product", "accounts");
		public $user;
		public $refresh = false;
		public $loggedIn = false;
		
		function __construct() {
			
			$this->db   = new Db();
			$this->page = (isset($_GET["page"]) ? $_GET["page"] : "make");
			
			//Load user if logged in
			if (isset($_SESSION["email"])) {
				$this->loadUser($_SESSION["email"]);
			}
			
			//Include requested page
			if (!in_array($this->page, $this->pages)) {
				$this->page = "make";
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
                    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
                    <script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
        			<script type="text/javascript" src="js/scripts.js"></script>
        			<script type="text/javascript" src="js/jquery.uniform.min.js"></script>
				</head>
				<body>
                	<div class="navBarBack">
                	<?php if ($this->loggedIn) { ?>
	                	<ul>
	                    	<li><a href="index.php">Issue Receipt</a></li>
	                        <li><a href="index.php?page=view">View Receipts</a></li>
	                        <?php if ($this->isAdmin()) echo '<li><a href="index.php?page=product">Product Management</a></li>'; ?>
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
		
		function __construct($email, $db) {
			$info = $db->getUser($email);
			$this->email = $info->email;
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
		
		function query() {
			$args = func_get_args();
			$sql = array_shift($args);
			foreach ($args as $key => $value)
				$args[$key] = $this->clean($value);
			return mysql_query(vsprintf($sql, $args));
		}
		
		function checkLoginDetails($email, $password) {
			$sql = "SELECT * FROM `users` WHERE UPPER(email)=UPPER('%s') AND password='%s'";
			$result = $this->query($sql, $email, sha1($password));
			if (mysql_num_rows($result) == 0)
				return false;
			return true;
		}
		
		function addUser($email, $password) {
			$sql = "INSERT INTO `users` (email, password)
					VALUES ('%s', '%s')";
			$this->query($sql, $email, sha1($password));
		}
		
		function getUser($email) {
			if (is_numeric($email))
				$sql = "SELECT * FROM `users` WHERE id=%s";
			else
				$sql = "SELECT * FROM `users` WHERE UPPER(email)=UPPER('%s')";
			$result = $this->query($sql, $email);
			return mysql_fetch_object($result);
		}
		
		function userExists($email) {
			if ($this->getUser($email) != false)
				return true;
			return false;
		}
		
		private function clean($string) {
			return mysql_real_escape_string(trim($string));
		}
		
		private function createTables() {
			$sql = "CREATE TABLE IF NOT EXISTS `users` (
					id int NOT NULL AUTO_INCREMENT, 
					email varchar(100),
					password varchar(150),
					PRIMARY KEY(id)
					)";
			mysql_query($sql);
			$sql = "INSERT INTO `users` (email, password)
					VALUES ('admin', '%s')";
			if (!$this->userExists("admin")) $this->query($sql, sha1("adminpass"));
		}
		
	}
?>