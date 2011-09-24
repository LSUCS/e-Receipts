<?php

	class make {
		
		function run($parent) {
			
			$parent->requireLogin();
			$parent->displayHeader();
			
			?>
			
			<div class="makeForm">
				<form action="index.php?page=make" method="post">
					<b>*</b> Email: <input class="emailBox" type="text" name="email" /><br />
					<b>*</b> Name: <input class="nameBox" type="text" name="name" /><br />
					Student ID: <input class="studentidBox" type="text" name="studentid" /><br />
					<select size="3" class="selectedProducts" type="text" name="selProducts">
						<option>LAN30</option>
						<option>Stash - Hoodie</option>
					</select><button class="removeButton" onclick=""><span>Remove</span></button><br />
					<select class="selectProduct">
						<option>HELLO DAVE</option>
						<option>LAN30</option>
						<option>Blitz Night</option>
						<option>Stash - Hat</option>
						<option>Stash - Hoodie</option>
					</select><button class="addButton" onclick=""><span>Add</span></button>
				</form>
			</div>
			
			<?php
			$parent->displayFooter();
			
		}
		
	}

?>
