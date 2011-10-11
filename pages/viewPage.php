<?php

	class view {
		
		function run($parent) {
			
			//If we are retrieving JSON data
			if (isset($parent->get)) {
				
				//If not logged in, GTFO
				if (!$parent->loggedIn) return;
				
				//Return JSON of all receipts
				if ($parent->get == "all") {
					
					$receipts = $parent->db->getReceipts($parent->isAdmin()? null : $parent->user->society_id);
					if ($receipts == null) return;
					
					//Format products
					foreach ($receipts as $key => $receipt) {
						$products = array();
						$productArray = explode(",", $receipt[4]);
						$price = 0;
						foreach ($productArray as $pid) {
							$product = $parent->db->getProduct($pid, null);
							$price += $product->price;
							$products[] = $product->product_name;
						}
						$receipts[$key][6] = "&pound;" . number_format($price, 2);
						$receipts[$key][4] = implode(", ", $products);
					}
					
					echo json_encode($receipts);
					
				}
				
				return;
				
			}
			
			$parent->requireLogin();
			
			//Normal display mode
			$parent->title = "View Receipts";
			$parent->displayHeader();
			
			?>
			
			<div class="receipts">
				<table class="receiptsTable"></table>
			</div>
			
			<?php
			$parent->displayFooter();
			
		}
		
	}

?>
