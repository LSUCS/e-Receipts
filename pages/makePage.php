<?php

	class make {
		
		function run($parent) {
			
			//If we are retrieving JSON data
			if (isset($parent->get)) {
				
				//If not logged in, GTFO
				if (!$parent->loggedIn) return;
				
				if ($parent->get == "emails") {
					$parent->db->getReceipts($parent->user->society_id);
				}
				
				//Set data
				if ($parent->get == "set") {
					
					$json = array();
					if ($parent->isAdmin()) return $parent->errorJSON($json, "Admin accounts may not issue receipts!");
					
					//If required POST isn't present
					if (!isset($_POST["email"]) || !isset($_POST["name"]) || !isset($_POST["products"]) || !isset($_POST["student_id"]) || !isset($_POST["comments"])) return $parent->errorJSON($json, "Stop trying to break me!");
					
					$emailAddr      = $_POST["email"];
					$name       = $_POST["name"];
					$products   = $_POST["products"];
					$student_id = $_POST["student_id"];
					$comments   = $_POST["comments"] == "Comments" ? null : $_POST["comments"];
					$society    = $parent->db->getSociety($parent->user->society_id);
					
					//Error checking
					if (strlen($name) < 1 || strlen($name) > 100) return $parent->errorJSON($json, "Invalid name!");
					if (strlen($student_id) > 0 && preg_match('/^[a-z]\d{6}$/i', $student_id) == 0) return $parent->errorJSON($json, "Invalid student ID!");
					if (preg_match('/^(?:(?:(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff]))(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff])|\.(?=[^\.])){1,62}(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff])|[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]{1,2})|"(?:[^"]|(?<=\x5c)"){1,62}")@(?:(?!.{64})(?:[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.?|[a-zA-Z0-9]\.?)+\.(?:xn--[a-zA-Z0-9]+|[a-zA-Z]{2,6})|\[(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])(?:\.(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])){3}\])$/ ', $emailAddr) == 0) return $parent->errorJSON($json, "Invalid email format!");
					if (strlen($comments) > 200) return $parent->errorJSON($json, "Comments may not be over 200 characters!");
					
					//Check products
					if (strlen($products) == 0) return $parent->errorJSON($json, "You must enter at least 1 product!");
					$productArray = explode(",", $products);
					foreach ($productArray as $key => $product) {
						if (strlen($product) == 0 || !is_numeric($product) || !$parent->db->getProduct($product, null)) return $parent->errorJSON($json, "Invalid product ID!");
						$productArray[$key] = $parent->db->getProduct($product, null);
					}
					
					//Add to database
					if (!$parent->db->addReceipt($parent->user->user_id, $emailAddr, $name, $student_id, $products, $comments, $parent->user->society_id)) {
						return $parent->errorJSON($json, "Unable to store receipt!" . mysql_error());
					}
					
					//Send email
					$email = new Email($parent->config);
					$email->addHeader("Bcc", $society->email);
					$email->setTo($emailAddr);
					$email->setSubject(str_replace('%SOCIETY%', $society->society_name, $parent->config->receipt["receiptTitle"]));
					$email->setMessage("Hello dave");
					if (!$email->send()) return $parent->errorJSON($json, "Unable to send email receipt!");
					
					echo json_encode($json);
					
				}
				
				return;
				
			}
			
			$parent->requireLogin();
			$parent->displayHeader();
			
			?>
			
			<div class="makeForm">
				<form action="index.php?page=make" method="post">
					<b>*</b> Email: <input class="emailBox" type="text" name="email" /><br />
					<b>*</b> Name: <input class="nameBox" type="text" name="name" /><br />
					Student ID: <input class="studentidBox" type="text" name="studentid" /><br />
					<select size="3" class="selectedProducts" type="text" name="selProducts">
					</select>
					<div>
						<input type="button" class="removeButton" value="Remove" />
						<input type="button" class="addButton" value="Add">
					</div>
					<div class="selectProductDiv">
						<select class="selectProduct">
						<?php
							
							$products = $parent->db->getProducts($parent->isAdmin() ? null : $parent->user->society_id);
							foreach ($products as $product) {
								echo '<option value="' . $product[0] . '">' . $product[1] . '</option>';
							}
							
						?>
						</select>
					</div>
					<textarea class="receiptComments">Comments</textarea>
					<input type="button" class="submitButton" value="Submit" />
				</form>
			</div>
			
			<?php
			$parent->displayFooter();
			
		}
		
	}

?>
