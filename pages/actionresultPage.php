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
					echo "Account successfully created!";
					break;
				case 3:
					echo "Successfully signed up to tournament!";
					break;
				case 4:
					echo "You have been removed from that tournament!";
					break;
				case 5:
					echo "Content saved!";
					break;
				case 6;
					echo "You are already logged in!";
					break;
				case 7:
					echo "You are already registered!";
			}
			echo " Redirecting to index page...";
			$parent->displayFooter();
			
		}
	
	
	}
	
?>