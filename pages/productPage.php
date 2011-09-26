<?php

	class product {
		
		function run($parent) {
			
			//If we are retrieving JSON data
			if (isset($parent->get)) {
				
				//If not logged in, GTFO
				if (!$parent->loggedIn) return;
				
				//If it is a number we want to return the specified product
				if (is_numeric($parent->get)) {
					
					$product = $parent->db->getProduct($parent->get, $parent->isAdmin()? null : $parent->user->society_id);
					if ($product == null) return;
					echo json_encode($product);
					
				}
				
				//If not, return them all
				else if ($parent->get == "all") {
					
					$products = $parent->db->getProducts($parent->isAdmin()? null : $parent->user->society_id);
					if ($products == null) return;
					echo json_encode($products);
					
				}
				
				//Set data
				else if ($parent->get == "set") {
					
					//If required POST isn't present
					if (!isset($_POST["product_id"]) || (!is_numeric($_POST["product_id"]) && strlen($_POST["product_id"]) > 0) || !isset($_POST["name"]) || !isset($_POST["price"]) || !isset($_POST["available"])) {
						$json["error"] = "Stop trying to break me!";
						echo json_encode($json);
						return;
					}
					
					$product_id = $_POST["product_id"];
					$name = $_POST["name"];
					$price = $_POST["price"];
					$available = str_replace(array("yes", "no"), array(1, 0), strtolower($_POST["available"]));
					$json = array();
					
					//Error checking
					if (strlen($name) < 1 || strlen($name) > 100) return $this->error($json, "Invalid Product name!");
					if (preg_match('/^\d{1,10}\.\d\d$/', $price) == 0) return $this->error($json, "Invalid price format!");
					if ($available != "1" && $available != "0") return $this->error($json, "Invalid value for available!");
					  
					//New data
					if (strlen($product_id) == 0) {
						if ($parent->isAdmin()) return $this->error($json, "Root account may not add new products!");
						$parent->db->updateProduct(strlen($product_id) == 0 ? null:$product_id, $name, $price, $available, $parent->user->society_id);
						echo json_encode($json);
						return;
					}
					
					//Check if product actually exists
					$product = $parent->db->getProduct($product_id, $parent->isAdmin()? null : $parent->user->society_id);
					if (!$product) return $this->error($json, "Invalid product ID!");
					
					//Update data
					$parent->db->updateProduct($product_id, $name, $price, $available, $product->society_id);
						
					//Sort out data and echo out the JSON
					$json["data"] = array($product_id, $name, "&pound;" . $price, str_replace(array(1, 0), array("Yes", "No"), $available), $product->society_name);
					echo json_encode($json);
					
				}
				
				return;
				
			}
			
			$parent->requireLogin();
			
			//Normal display mode
			$parent->title = "Product Management";
			$parent->displayHeader();
			
			?>
			
			<div class="products">
			</div>
			<input class="productName" type="text" name="name" value="Product Name" />
			<input class="productPrice" type="text" name="price" value="Price" />
			<div class="availableDiv"><select class="productAvailable" name="available" />
				<option value="1">Yes</option>
				<option value="0">No</option>
			</select></div>
			<button class="addProductButton">Add Product</button>
			
			<?php
			$parent->displayFooter();
			
		}
		
		function error($json, $message) {
			$json["error"] = $message;
			echo json_encode($json);
			return;
		}
		
	}

?>
