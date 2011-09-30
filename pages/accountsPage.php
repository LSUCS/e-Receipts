<?php

	class accounts {
		
		function run($parent) {
			
			//If we are retrieving JSON data
			if (isset($parent->get)) {
				
				//If not logged in or isn't admin, GTFO
				if (!$parent->loggedIn || !$parent->isAdmin()) return;
				
				//If it is a number we want to return the specified account
				if (is_numeric($parent->get)) {
					
					$user = $parent->db->getUser($parent->get);
					if ($user == null) return;
					echo json_encode($user);
					
				}
				
				//If it is a simple list
				else if ($parent->get == "socs") {
				
					$socs = $parent->db->getSocieties();
					if ($socs == null) return;
					foreach ($socs as $soc)	$json[$soc[0]] = $soc[1];
					echo json_encode($json);
					
				}
				
				//If not, return them all
				else if ($parent->get == "all") {
				
					$users = $parent->db->getUsers();
					if ($users == null) return;
					echo json_encode($users);
					
				}
				
				//Set data
				else if ($parent->get == "set") {
					
					//If required POST isn't present
					if (!isset($_POST["society_id"]) || (!is_numeric($_POST["society_id"]) && strlen($_POST["society_id"]) > 0) || !isset($_POST["name"]) || !isset($_POST["email"])) {
						$json["error"] = "Stop trying to break me!";
						echo json_encode($json);
						return;
					}
					
					$society_id = $_POST["society_id"];
					$name = $_POST["name"];
					$email = $_POST["email"];
					$json = array();
					
					//Error checking
					if (strlen($name) < 1 || strlen($name) > 100) return $this->error($json, "Invalid society name!");
					if (preg_match('/^(?:(?:(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff]))(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff])|\.(?=[^\.])){1,62}(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff])|[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]{1,2})|"(?:[^"]|(?<=\x5c)"){1,62}")@(?:(?!.{64})(?:[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.?|[a-zA-Z0-9]\.?)+\.(?:xn--[a-zA-Z0-9]+|[a-zA-Z]{2,6})|\[(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])(?:\.(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])){3}\])$/ ', $email) == 0) return $this->error($json, "Invalid email format!");
					  
					//New data
					if (strlen($society_id) == 0) {
						$parent->db->updateSociety(null, $name, $email);
						echo json_encode($json);
						return;
					}
					
					//Check if society actually exists
					$society = $parent->db->getSociety($society_id);
					if (!$society) return $this->error($json, "Invalid society ID!");
					
					//Update data
					$parent->db->updateSociety($society_id, $name, $email);
						
					//Sort out data and echo out the JSON
					$json["data"] = array($society_id, $name, $email);
					echo json_encode($json);
					
				}
				
				return;
				
			}
			
			$parent->requireLogin();
		
			//If not admin, GTFO
			if (!$parent->isAdmin()) $parent->actionResult(9);
			
			//Normal display mode
			$parent->title = "Account Management";
			$parent->displayHeader();
			
			?>
			
			<div class="accounts">
			</div>
			<input class="accountEmail" type="text" name="email" value="Email" />
			<input class="accountStudentId" type="text" name="studentId" value="Student ID" />
			<input class="accountPassword" type="text" name="password" value="Password" />
			<input class="accountActive" type="text" name="active" value="Active" />
			<span class="societySpan"><select class="accountSociety" name="society">
				<?php foreach ($parent->db->getSocieties() as $society) echo "<option value='".$society[0]."'>".$society[1]."</option>"; ?>
			</select></span>
			<button class="addAccountButton">Add Account</button>
			
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
