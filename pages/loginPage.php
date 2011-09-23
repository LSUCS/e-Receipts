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
                        Email: <input class="emailBox" type="text" name="email" /><br />
                        Password: <input class="passBox" type="password" name="password" /><br />
                        <button class="loginButton">
                        	<span>Login</span>
                        </button>
                    </form>
                </div>
            <?php
			
			$parent->displayFooter();
			
		}
		
	}

?>