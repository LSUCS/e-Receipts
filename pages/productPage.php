<?php

	class product {
		
		function run($parent) {
			
			$parent->requireLogin();
			$parent->title = "Product Management";
			$parent->displayHeader();
			$parent->displayFooter();
			
		}
		
	}

?>
