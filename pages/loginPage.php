<?php 

	class login {
		
		function run($parent) {
			
			$parent->title = "Login";
			$parent->displayHeader();
			
			if ($parent->loggedIn)
				$parent->actionResult(6);
			
			if (isset($_POST["email"])) $email = $_POST["email"];
			if (isset($_POST["password"])) $password = $_POST["password"];
			if (isset($email) && isset($password)) {
				if ($parent->db->checkLoginDetails($email, $password)) {
					$parent->loadUser($_POST["email"]);
					$_SESSION['email'] = $email;
					$parent->actionResult(0);
				}
				else {
					?>
                        <div class="errorBox">
                            Invalid email or password entered!
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
                        Email: <input type="text" name="email" /><br />
                        Password: <input type="password" name="password" /><br />
                        <input type="submit" value="Login" />
                    </form>
                </div>
            <?php
			
			$parent->displayFooter();
			
		}
		
	}

?>