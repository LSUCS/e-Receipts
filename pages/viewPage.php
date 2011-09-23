<?php

	class view {
		
		function run($parent) {
			
			$parent->requireLogin();
			$parent->title = "View Receipts";
			$parent->displayHeader();
			$parent->displayFooter();
			
		}
		
	}

?>
