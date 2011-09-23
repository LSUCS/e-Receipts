<?php

	class logout {
		
		function run($parent) {
			
			if ($parent->loggedIn)
				unset($_SESSION["username"]);
			$parent->actionResult(1);
			
		}
	
	
	}
	
?>