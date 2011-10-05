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
					if (!isset($_POST["user_id"]) || (!is_numeric($_POST["user_id"]) && strlen($_POST["user_id"]) > 0) || !isset($_POST["email"]) || !isset($_POST["student_id"]) || !isset($_POST["password"]) || !isset($_POST["active"]) || !isset($_POST["society_id"]) || !is_numeric($_POST["society_id"])) {
						$json["error"] = "Stop trying to break me!";
						echo json_encode($json);
						return;
					}
					
					$user_id = $_POST["user_id"];
					$email = $_POST["email"];
					$student_id = $_POST["student_id"];
					$password = $_POST["password"];
					$active = str_replace(array("yes", "no"), array(1, 0), strtolower($_POST["active"]));
					$society_id = $_POST["society_id"];
					$json = array();
					
					//Error checking
					if (preg_match('/^[a-z]\d{6}$/i', $student_id) == 0) return $this->error($json, "Invalid student ID!");
					if ($active != "1" && $active != "0") return $this->error($json, "Invalid value for active!");
					if (preg_match('/^(?:(?:(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff]))(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff])|\.(?=[^\.])){1,62}(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff])|[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]{1,2})|"(?:[^"]|(?<=\x5c)"){1,62}")@(?:(?!.{64})(?:[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.?|[a-zA-Z0-9]\.?)+\.(?:xn--[a-zA-Z0-9]+|[a-zA-Z]{2,6})|\[(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])(?:\.(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])){3}\])$/ ', $email) == 0) return $this->error($json, "Invalid email format!");
					 
					//Check if society actually exists
					$society = $parent->db->getSociety($society_id);
					if (!$society) return $this->error($json, "Invalid society!");
					
					//Sort out password
					if ($password == "Click to change password") $password = null;
					if (strlen($password) < 6 && $password != null) return $this->error($json, "Password must be longer than 6 characters!");
					
					//New data
					if (strlen($user_id) == 0) {
						$parent->db->addUser($email, $password, $student_id, $society_id);
						echo json_encode($json);
						return;
					}
					
					//Update data
					$parent->db->updateUser($user_id, $email, $password, $student_id, $society_id);
						
					//Sort out data and echo out the JSON
					$json["data"] = array($user_id, $email, $student_id, "Click to change password", str_replace(array(1, 0), array("Yes", "No"), $active), $society->society_name);
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
