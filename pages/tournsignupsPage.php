<?php

	class tournsignups {
		
		private $parent;
		
		function run($parent) {
			
			$this->parent = $parent;
			$db = $parent->db;
			$parent->title = "Tournament Signups";
			$parent->requireLogin();
			
			$type = "default";
			if (isset($_GET["type"]))
				$type = $_GET["type"];
			
			//Initiate arrays of signup info
			$games   = array();
			$teams   = array();
			$signups = array();
			
			//Read games into array of objects
			$res = $db->query("SELECT * FROM `games`");
			while ($game = mysql_fetch_object($res))
				$games[$game->id] = $game;
			//Read teams into array of objects
			$res = $db->query("SELECT * FROM `teams`");
			while ($team = mysql_fetch_object($res))
				$teams[$team->id] = $team;
			//Read signups into array of objects
			$res = $db->query("SELECT * FROM `signups`");
			while ($signup = mysql_fetch_object($res))
				$signups[$signup->id] = $signup;
				
			
			
			//Loop through games, working out whether the user can join them
			foreach ($games as $key => $game) {
				$games[$key]->canJoin = true;
				
				foreach ($signups as $signup)
					if ($signup->gameid == $game->id && $signup->userid == $parent->user->userid)
						$games[$key]->canJoin = false;
						
				foreach ($teams as $key => $team) {
					if ($team->gameid == $game->id) {
						$teams[$key]->canJoin = true;
						$res = $db->query("SELECT * FROM `signups` WHERE teamid='%s'", $team->id);
						if (mysql_num_rows($res) >= $game->teamsize) {
							$teams[$key]->canJoin = false;
						}
						$teams[$key]->memberCount = mysql_num_rows($res);
					}
				}
				
			}
			
			//Are we trying to add a signup?
			if ($type == "add") {
				
				//Sort out initial user input issues (people trying to screw around with fake inputs)
				if (!$_GET["gameid"] || !isset($games[$_GET["gameid"]]))
					return $this->err(1);
				$game = $games[$_GET["gameid"]];
				if (!$game->canJoin)
					return $this->err(2);
				if ($game->open == 0)
					return $this->err(3);
				if (!isset($_POST["teamtype"]) && $game->teamsize > 0)
					return $this->err(8);
				
				//If it is a non-team game or the person is going random
				if (!isset($_POST["teamtype"]) || $_POST["teamtype"] == "solo") {
					echo "hi";
					$sql = "INSERT INTO `signups` (userid, gameid)
							VALUES ('%s', '%s')";
					$db->query($sql, $parent->user->userid, $game->id);
				}
				//Joining a team
				elseif ($_POST["teamtype"] == "jointeam") {
					if (!isset($_POST["teamlist"]) || $_POST["teamlist"] == null)
						return $this->err(9);
					if (!isset($teams[$_POST["teamlist"]]))
						return $this->err(5);
					$team = $teams[$_POST["teamlist"]];
					if (!$team->canJoin)
						return $this->err(5);
					if (strlen($team->password) > 0) {
						if (!isset($_POST["teampassword"]) || $_POST["teampassword"] == null || $_POST["teampassword"] != $team->password)
							return $this->err(6);
					}
					$sql = "INSERT INTO `signups` (userid, gameid, teamid)
							VALUES ('%s', '%s', '%s')";
					$db->query($sql, $parent->user->userid, $game->id, $team->id);
				}
				//Creating a team
				elseif ($_POST["teamtype"] == "newteam") {
					
					//Error check
					if (!isset($_POST["teamname"]) || $_POST["teamname"] == null)
						return $this->err(7);
					$res = mysql_query("SELECT * FROM `teams` WHERE gameid='%s' AND UPPER(teamname)=UPPER('%s')");
					if (mysql_num_rows($res) > 0)
						return $this->err(4);
					if (!isset($_POST["newpassword"]))
						return $this->err(8);
						
					//Create team
					if ($_POST["newpassword"] == null) {
						$sql = "INSERT INTO `teams` (gameid, teamname)
								VALUES ('%s', '%s')";
						$db->query($sql, $game->id, $_POST["teamname"]);
					}
					else {
						$sql = "INSERT INTO `teams` (gameid, teamname, password)
								VALUES ('%s', '%s', '%s')";
						$db->query($sql, $game->id, $_POST["teamname"], $_POST["newpassword"]);
					}
					
					//Get teamid
					$sql = "SELECT * FROM `teams` WHERE teamname='%s'";
					$res = $db->query($sql, $_POST["teamname"]);
					$team = mysql_fetch_object($res);
					
					//Add player to team
					$sql = "INSERT INTO `signups` (userid, gameid, teamid)
							VALUES ('%s', '%s', '%s')";
					$db->query($sql, $parent->user->userid, $game->id, $team->id);
				}
				elseif ($_POST["teamtype"] != null)
					return $this->err(8);
					
				$parent->actionResult(3);
				
			}
			
			elseif ($type == "del") {
				if (!isset($_GET["gameid"]) || $_GET["gameid"] == null)
					return $this->err(8);
				$sql = "DELETE FROM `signups` WHERE userid='%s' AND gameid='%s'";
				$db->query($sql, $parent->user->userid, $_GET["gameid"]);
				$parent->actionResult(4);
			}
			
			//Nope, we are just displaying the sign-up info
			else {
				$parent->displayHeader();
				
				//Errors
				if (isset($_GET["err"])) {
					switch ($_GET["err"]) {
						case 1:
							$msg = "Invalid game id supplied";
							break;
						case 2:
							$msg = "You have already joined that game!";
							break;
						case 3:
							$msg = "Signups for this game have closed!";
							break;
						case 4:
							$msg = "A team with that name already exists!";
							break;
						case 5:
							$msg = "That team is full!";
							break;
						case 6:
							$msg = "Incorrect password for that team!";
							break;
						case 7:
							$msg = "Please fill in a teamname when creating a team!";
							break;
						case 8:
							$msg = "You broke it. Congratulations, I hope you are happy with yourself.";
							break;
						case 9:
							$msg = "You haven't selected a team to join!";
							break;
					}
					echo '<div class="errorBox">' . $msg . '</div>';
				}
				
				echo "Note: Please do not sign up to tournaments unless you actually intend to play them";
				
				//Loop through games array outputting relevant info
				foreach ($games as $key => $game) {
					?>
					<div class="signupGame">
						<h1><?php echo $game->game; ?></h1>
						<h2><b>Type:</b> <?php echo $game->type; ?></h2>
						<h2><b>Time:</b> <?php echo $game->time; ?></h2>
						<?php if ($game->teamsize > 0) echo '<h2><b>Teamsize:</b> ' . $game->teamsize . '</h2>'; ?>
						<h2 style="margin-left: 2px"><b>Info: </b><?php echo $game->info . "</h2>";
						if ($game->canJoin && $game->open == 1) {
							?>
							<div class="signupGameForm">
								<form action="index.php?page=tournsignups&type=add&gameid=<?php echo $game->id; ?>" method="post">
								<?php
								if ($game->teamsize > 0) { ?>
									<input type="radio" name="teamtype" value="newteam" /> Create new team<br />
									&nbsp;&nbsp;&nbsp;Team name: <input type="text" name="teamname" /><br />
									&nbsp;&nbsp;&nbsp;Password: <input type="password" name="newpassword" style="margin-bottom: 10px" /> (Leave blank for public team)<br />
									<input type="radio" name="teamtype" value="jointeam" CHECKED /> Join existing team<br />
									&nbsp;&nbsp;&nbsp;<select name="teamlist" style="margin-bottom: 10px; min-width: 140px;">
									<?php
										foreach ($teams as $team) {
											if ($team->gameid == $game->id)
												echo '<option value="' . $team->id . '">' . $team->teamname . ' ('.$team->memberCount.'/'.$game->teamsize.')</option>';
										}
									?>
									</select><br />
									&nbsp;&nbsp;&nbsp;Password: <input type="password" name="teampassword" /> (Leave blank if no password set)<br />
									<input type="radio" name="teamtype" value="solo" /> Go solo and be put in a random team<br />
								<?php } ?>
									<input type="submit" value="Sign up" />
								</form>
							</div> <?php
						} elseif ($game->open == 0)
							echo "Signups for this game have now closed";
						else
							echo 'You have already signed up to play this game. Click <a href="index.php?page=tournsignups&type=del&gameid='.$game->id.'">here</a> to resign from this game.';
					echo "</div>";
				}
				$parent->displayFooter();
			}
		}
		
		function err($num) {
			header("Location:index.php?page=tournsignups&err=" . $num);
		}
	}
	
?>