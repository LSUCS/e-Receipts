<?php
	//Start up sessions, check if logged in
	session_start();
	$main = new Main();
	
	class Main {
		
		//Setup some variables
		public $db;
		public $titleSuffix = "LAN30 - A local area network for local people";
		public $title = "Home";
		public $page;
		public $pages = array("actionresult", "index", "login", "logout", "register", "tournsignups", "tournaments");
		public $user;
		public $refresh = false;
		public $loggedIn = false;
		
		function __construct() {
			
			$this->db   = new Db();
			$this->page = (isset($_GET["page"]) ? $_GET["page"] : "index");
			
			if (isset($_SESSION["username"])) {
				$this->loadUser($_SESSION["username"]);
			}
			//Include requested page
			if (in_array($this->page, $this->pages)) {
				include("pages/".$this->page."Page.php");
				$child = new $this->page;
				$child->run($this);
			}
			
		}
		
		//Setup user from username
		function loadUser($username) {
			$this->user = new User($username, $this->db);
			$this->loggedIn = true;
		}
		
		//Display global page header
		function displayHeader() {
			$compiledTitle = $this->title . " | " . $this->titleSuffix;
			if ($this->loggedIn)
				$userStatus = "Welcome, " . $this->user->username . '. Click here to <a href="index.php?page=logout">logout</a>';
			else
				$userStatus = 'Click here to <a href="index.php?page=login">login</a> or <a href="index.php?page=register">register</a>';
			?>
			<html> 
				<head>
                	<?php if ($this->refresh) echo '<meta http-equiv="refresh" content="3;url=index.php?page=index">'; ?>
					<title><?php echo $compiledTitle; ?></title>
                    <link rel="shortcut icon" href="images/favicon.ico" />
					<link href="css/styles.css" rel="stylesheet" type="text/css" />
                    <link rel="stylesheet" type="text/css" href="css/custom-theme/jquery-ui-1.8.13.custom.css" />
					<link rel="stylesheet" type="text/css" href="css/uniform.default.css" />
                    <script type="text/javascript" src="jquery-1.1.3.1.min.js"></script>
                    <script type="text/javascript" src="js/jquery-ui-1.8.13.custom.min.js"></script>
        			<script type="text/javascript" src="js/scripts.js"></script>
        			<script type="text/javascript" src="js/jquery.uniform.min.js"></script>
				</head>
				<body>
                	<div class="navBarBack">
                        <div class="navBar">
                            <ul>
                                <li><a href="index.php?page=index">Home</a></li>
                                <li><a href="http://lsucs.org.uk/forum/">Forum</a></li>
                                <li><a href="index.php?page=tournaments">Tournaments</a></li>
                                <li><a href="index.php?page=tournsignups">Tournament Signups</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="mainContainer">
                        <div class="logoBar">
                            lan<b>30</b>
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
			if ($this->loggedIn && $this->user->username == "admin")
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
		
		public $userid;
		public $username;
		public $name;
		public $seat;
		public $steamid;
		
		function __construct($username, $db) {
			$info = $db->getUser($username);
			$this->userid   = $info->id;
			$this->username = $info->username;
			$this->steamid  = $info->steamid;
			$this->seat     = $info->seat;
			$this->name     = $info->name;
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
		
		function checkLoginDetails($username, $password) {
			$sql = "SELECT * FROM `users` WHERE UPPER(username)=UPPER('%s') AND password='%s'";
			$result = mysql_query(sprintf($sql, $this->clean($username), sha1($this->clean($password))));
			if (mysql_num_rows($result) == 0)
				return false;
			return true;
		}
		
		function addUser($username, $password, $seat, $steam, $name) {
			$sql = "INSERT INTO `users` (username, password, steamid, seat, name)
					VALUES ('%s', '%s', '%s', '%s', '%s')";
			$sql = sprintf($sql, $this->clean($username), sha1($password), $this->clean($steam), $this->clean($seat), $this->clean($name));
			mysql_query($sql);
		}
		
		function getUser($username) {
			if (is_numeric($username))
				$sql = "SELECT * FROM `users` WHERE id=%s";
			else
				$sql = "SELECT * FROM `users` WHERE UPPER(username)=UPPER('%s')";
			$result = mysql_query(sprintf($sql, $this->clean($username)));
			return mysql_fetch_object($result);
		}
		
		function userExists($username) {
			if ($this->getUser($username) != false)
				return true;
			return false;
		}
		
		private function clean($string) {
			return mysql_real_escape_string(trim($string));
		}
		
		private function createTables() {
			$sql = "CREATE TABLE IF NOT EXISTS `users` (
					id int NOT NULL AUTO_INCREMENT, 
					username varchar(50),
					password varchar(150),
					steamid varchar(50),
					seat varchar(3),
					name varchar(150),
					PRIMARY KEY(id)
					)";
			mysql_query($sql);
			$sql = "INSERT INTO `users` (username, password)
					VALUES ('admin', '%s')";
			if (!$this->userExists("admin")) $this->query($sql, sha1("82NAL"));
			$sql = "CREATE TABLE IF NOT EXISTS `games` (
					id int NOT NULL AUTO_INCREMENT, 
					game varchar(50),
					type varchar(150),
					teamsize int(10),
					time varchar(100),
					info varchar(300),
					open tinyint(1) DEFAULT '1',
					PRIMARY KEY(id)
					)";
			mysql_query($sql);
			$sql = "CREATE TABLE IF NOT EXISTS `signups` (
					id int NOT NULL AUTO_INCREMENT, 
					userid int(10),
					gameid int(10),
					teamid int(10),
					PRIMARY KEY(id)
					)";
			mysql_query($sql);
			$sql = "CREATE TABLE IF NOT EXISTS `teams` (
					id int NOT NULL AUTO_INCREMENT,
					gameid int(10),
					teamname varchar(50),
					password varchar(50),
					PRIMARY KEY(id)
					)";
			mysql_query($sql);
		}
		
	}
?>