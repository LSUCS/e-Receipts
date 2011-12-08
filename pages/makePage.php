<?php

	class make {
		
		function run($parent) {
			
			//If we are retrieving JSON data
			if (isset($parent->get)) {
				
				//Set data
				if ($parent->get == "set") {
					

					$json = array();
					if ($parent->isAdmin()) return $parent->errorJSON($json, "Admin accounts may not issue receipts!");
					
					//If required POST isn't present
					if (!isset($_POST["email"]) || !isset($_POST["name"]) || !isset($_POST["products"]) || !isset($_POST["student_id"]) || !isset($_POST["comments"])) return $parent->errorJSON($json, "Stop trying to break me!");
					
					$emailAddr  = $_POST["email"];
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
                                        if (strtolower($emailAddr) == $parent->user->email) return $parent->errorJSON($json, "You cannot issue yourself a receipt!");
					
					//Check products
					if (strlen($products) == 0) return $parent->errorJSON($json, "You must enter at least 1 product!");
					$productArray = explode(",", $products);
					foreach ($productArray as $key => $product) {
                                                $prod = $parent->db->getProduct($product, null);
						if (strlen($product) == 0 || !is_numeric($product) || !$prod || $prod->active == "No") return $parent->errorJSON($json, "Invalid product ID!");
						$productArray[$key] = $prod;
					}
					
					//Format product table for email
					$prodTable = "<table style='border-color: black;'><tr style='background-color: #333;'><td style='padding: 5px' width='200px'>Product</td><td style='padding: 5px' width='100px'>Price</td></tr>";
					$total = 0;
					foreach ($productArray as $key => $product) {
						$prodTable .= "<tr style='background-color: #C8C8C8; color: black;'><td style='padding: 2px;' width='200px'>" . $product->product_name . "</td><td style='padding: 2px;' width='100px'>&pound;" . $product->price . "</td></tr>";
						$total += $product->price;
					}
					$prodTable .="</table>";
					$total = "&pound;" . number_format($total, 2);
					
					//Set up email template
					$template = new Template("receipt");
                    $template->addKey("%TITLE%", str_replace('%SOCIETY%', $society->society_name, $parent->config->receipt["receiptTitle"]));
                    $template->addKey("%SOCIETY%", $society->society_name);
                    $template->addKey("%SOCEMAIL%", $society->email);
                    $template->addKey("%SOCFEDEMAIL%", $parent->config->receipt["socfedEmail"]);
                    $template->addKey("%CUSTNAME%", $name);
                    $template->addKey("%CUSTEMAIL%", $emailAddr);
                    $template->addKey("%CUSTID%", $student_id == null?"N/A":$student_id);
                    $template->addKey("%ORDERDATE%", date('l jS \of F Y h:i:s A'));

                    $template->addKey("%PRODTABLE%", $prodTable);
                    $template->addKey("%ORDERTOTAL%", $total);
                    $template->addKey("%ORDERCOMMENTS%", $comments == null?"None":$comments);
                    $template->addKey("%ISSUER%", $parent->user->email);

					//Send email
                    /*$email = new Email($parent->config);
                    $email->addRecipient($emailAddr);
                    $email->addRecipient($society->email);
                    $email->addHeader("Subject", str_replace('%SOCIETY%', $society->society_name, $parent->config->receipt["receiptTitle"]));
                    $email->setMessage($template->format());
                    if (!$email->send($parent)) return $parent->errorJSON($json, "Unable to send email receipt!");*/

					//HACKLOLOLOLOLOLOLOOOOO
					//set POST variables
                    $url = 'http://oliwali.co.uk/receipts/send.php';
                    $fields = array(
                                'pass' => 'davepasshello',
                                'recip' => $emailAddr,
                                'socemail' => $society->email,
                                'subject' => urlencode(str_replace('%SOCIETY%', $society->society_name, $parent->config->receipt["receiptTitle"])),
                                'message' => urlencode($template->format())
                                
                            );
                    //url-ify the data for the POST
                    foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
                    rtrim($fields_string,'&');
                    //open connection
                    $ch = curl_init();

                    //set the url, number of POST vars, POST data
                    curl_setopt($ch,CURLOPT_URL,$url);
                    curl_setopt($ch,CURLOPT_POST,count($fields));
                    curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    //execute post
                    $result = curl_exec($ch);

                    //close connection
                    curl_close($ch);
		
                    if ($result != 1) return $parent->errorJSON($json, "Unable to send email receipt!");
                    

					//Add to database
					if (!$parent->db->addReceipt($parent->user->user_id, $emailAddr, $name, $student_id, $products, $comments, $parent->user->society_id)) {
						return $parent->errorJSON($json, "Unable to store receipt!" . mysql_error());
					}
					
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
                                                                if ($product[3] == "No") continue; 
								echo '<option value="' . $product[0] . '">' . $product[1] . '</option>';
							}
							
						?>
						</select>
					</div>
					<textarea class="receiptComments">Comments</textarea>
					<input type="button" class="submitButton" value="Submit" />
				</form>
			</div>
			
			<div class="overlay">
				Please wait...
			</div>
			
			<?php
			$parent->displayFooter();
			
		}
		
	}

?>
