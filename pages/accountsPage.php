<?php

	class accounts {
		
		function run($parent) {
			
			$parent->requireLogin();
			$parent->title = "Account Management";
			$parent->displayHeader();
			$parent->displayFooter();
			
		}
		
	}

?>
