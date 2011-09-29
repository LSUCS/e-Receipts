<?php

	class actionresult {
		
		function run($parent) {
			
			$parent->refresh = true;
			$parent->title = "Action";
			$parent->displayHeader();
			switch ($_GET["type"]) {
				case 0:
					echo "Successfully logged in!";
					break;
				case 1:
					echo "Successfully logged out!";
					break;
				case 2:
					echo "User account created!";
					break;
				case 3:
					echo "Receipt sent!";
					break;
				case 4:
					echo "Refund sent!";
					break;
				case 5:
					echo "Product added!";
					break;
				case 6;
					echo "You are already logged in!";
					break;
				case 7:
					echo "Product deleted!";
					break;
				case 8:
					echo "User added account deleted!";
					break;
				case 9:
					echo "You do not have access to that page!";
					break;
			}
			echo " Redirecting to index page...";
			$parent->displayFooter();
			
		}
	
	
	}
	
?>