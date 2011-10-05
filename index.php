<?php

	//Includes
	include("db.php");
	include("config.php");
	
	//Start up sessions and initiate main class
	session_start();
	$main = new Main();
	
	class Main {
		
		//Setup some variables
		public $config;
		public $db;
		public $page;
		public $get;
		public $pages = array("actionresult", "login", "logout", "make", "view", "refund", "product", "accounts", "societies");
		public $title;
		public $navbar = array();
		public $user;
		public $refresh = false;
		public $loggedIn = false;
		
		function __construct() {
			
			$this->config = new Config();
			$this->db     = new Db($this);
			$this->page   = (isset($_GET["page"]) ? $_GET["page"] : $this->config->general["defaultPage"]);
			$this->title  = $this->config->general["defaultTitle"];
			
			//Load user if logged in
			if (isset($_SESSION["email"])) {
				$this->loadUser($_SESSION["email"]);
				$this->requireLogin();
			}
			
			//See if there is a 'get' to do
			if (isset($_GET["get"])) $this->get = strtolower($_GET["get"]);
			else unset($this->get);
			
			//Add nav elements	
			$this->addNavElement("index.php", "Issue Receipt", false, true);
			$this->addNavElement("index.php?page=view", "View Receipts", false, true);
			$this->addNavElement("index.php?page=product", "Product Management", false, true);
			$this->addNavElement("index.php?page=societies", "Society Management", true, true);
			$this->addNavElement("index.php?page=accounts", "Account Management", true, true);
			
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
			$this->user = $this->db->getUser($username);
			if ($this->user != false)
				$this->loggedIn = true;
		}
		
		//Display global page header
		function displayHeader() {
			
			//Format require information for template
			$compiledTitle = $this->title . " | " . $this->config->general["titleSuffix"];
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
	                	<ul>
                		<?php
                		
                			//Echo navbar
                			foreach ($this->navbar as $element) {
                				if ((($element["login"] && $this->loggedIn) || !$element["login"]) && (($element["admin"] && $this->isAdmin()) || !$element["admin"]))
                					echo '<li><a href="' . $element["url"] . '">' . $element["title"] . '</a></li>';
                			}
	                        
	                    ?>
	                    </ul>
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
			if ($this->loggedIn && in_array($this->user->email, $this->config->admin["adminAccounts"]))
				return true;
			return false;
		}
		
		//Check if user is logged in, if they aren't, redirect to the login page
		function requireLogin() {
			if (!$this->loggedIn) {
				unset($_SESSION["email"]);
				header("location:index.php?page=login&loggedin=true");
			}
		}
		
		//Adds an element to the NavBar
		function addNavElement($url, $title, $requireAdmin, $requireLogin) {
			$this->navbar[] = array("url" => $url, "title" => $title, "admin" => $requireAdmin, "login" => $requireLogin);
		}
		
	}
	
?>