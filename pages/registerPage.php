<?php

	class register {
		
		function run($parent) {
			
			if ($parent->loggedIn)
				$parent->actionResult(6);
			
			$parent->title = "Register";
			$parent->displayHeader();
			
			if (isset($_GET["add"])) {
				
				//Validate inputs
				$fields = array("username", "password", "seat", "steam");
				$valid = true;
				if (!isset($_POST["name"]) || $_POST["name"] == null)
					$valid = false;
				if (isset($_POST["name"]) && preg_match("/^[\s\w]*$/", $_POST["name"]) == 0)
					$valid = false;
				if (!isset($_POST["seat"]) || $_POST["seat"] == null)
					$valid = false;
				if (isset($_POST["seat"]) && preg_match("/^[a-fA-F]1?[1-9]$/", $_POST["seat"]) == 0)
					$valid = false;
				foreach ($fields as $field) {
					if (!isset($_POST[$field]) || !ctype_alnum($_POST[$field]) || $_POST[$field] == null)
						$valid = false;
				}
				
				if ($valid && !$parent->db->userExists($_POST["username"])) {
					$parent->db->adduser($_POST["username"], $_POST["password"], $_POST["seat"], $_POST["steam"], $_POST["name"]);
					$parent->loadUser($_POST["username"]);
					$_SESSION['username'] = $_POST["username"];
					$parent->actionResult(2);
				}
				elseif (!$valid) {
					?>
                        <div class="errorBox">
                            Invalid info entered! Please ensure all info is alpha-numeric and all fields are filled out! Spaces are only allowed in your name
                        </div>
                    <?php
				}
				else {
					?>
                        <div class="errorBox">
                            Username already registered!
                        </div>
                    <?php
				}
				
			}
			
			?>
			
				<div class="loginForm">
                    <form action="index.php?page=register&add=true" method="post">
                        Username: <input type="text" name="username" /><br />
                        Password: <input type="password" name="password" /><br />
                        Real name: <input type="text" name="name" /><br />
                        Seat number: <input type="text" name="seat" /><br />
                        Game/Steam name: <input type="text" name="steam" /><br />
                        <input type="submit" value="Register" />
                    </form>
                    Already registered? Click <a href="index.php?page=login">here</a> to login
                </div>
            
            <?php
			$parent->displayFooter();
			
		}
	
	
	}
	
?>