<?php

	class make {
		
		function run($parent) {
			
			$parent->requireLogin();
			$parent->displayHeader();
			$parent->displayFooter();
			
		}
		
	}

?>
