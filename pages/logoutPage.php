<?php

	class logout {
		
		function run($parent) {
			
			if ($parent->loggedIn)
				unset($_SESSION["email"]);
			$parent->actionResult(1);
			
		}
	
	
	}
	
?>