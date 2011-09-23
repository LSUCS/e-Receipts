<?php 

	class login {
		
		function run($parent) {
			
			$parent->title = "Login";
			$parent->displayHeader();
			
			if ($parent->loggedIn)
				$parent->actionResult(6);
			
			if (isset($_POST["username"])) $username = $_POST["username"];
			if (isset($_POST["password"])) $password = $_POST["password"];
			if (isset($username) && isset($password)) {
				if ($parent->db->checkLoginDetails($username, $password)) {
					$parent->loadUser($_POST["username"]);
					$_SESSION['username'] = $username;
					$parent->actionResult(0);
				}
				else {
					?>
                        <div class="errorBox">
                            Invalid username or password entered!
                        </div>
                    <?php
				}
			}
			
			if (isset($_GET["loggedin"])) {
				?>
                    <div class="errorBox">
                        You must be logged in to do that!
                    </div>
                <?php
			}
			
			?>
            
                <div class="loginForm">
                    <form action="index.php?page=login" method="post">
                        Username: <input type="text" name="username" /><br />
                        Password: <input type="password" name="password" /><br />
                        <input type="submit" value="Login" />
                    </form>
                </div>
                Not registered yet? Click <a href="index.php?page=register">here</a> to create an account
            <?php
			
			$parent->displayFooter();
			
		}
		
	}

?>