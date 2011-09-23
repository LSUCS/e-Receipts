<?php

	class refund {
		
		function run($parent) {
			
			$parent->requireLogin();
			$parent->title = "Issue Refund";
			$parent->displayHeader();
			$parent->displayFooter();
			
		}
		
	}

?>
