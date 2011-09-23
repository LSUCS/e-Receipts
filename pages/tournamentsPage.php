<?php

	class tournaments {
				
		function run($parent) {
			
			$db = $parent->db;
			
			//Initiate arrays of signup info
			$games   = array();
			$teams   = array();
			$signups = array();
			
			//Read games into array of objects
			$res = $db->query("SELECT * FROM `games`");
			while ($game = mysql_fetch_object($res))
				$games[$game->id] = $game;
			
			$parent->displayHeader();
			
			foreach ($games as $game) {
				?>
                <div class="tournamentGame">
                    <h1><?php echo $game->game; ?></h1>
                    <h2><b>Type:</b> <?php echo $game->type; ?></h2>
                    <h2><b>Time:</b> <?php echo $game->time; ?></h2>
                    <?php if ($game->teamsize > 0) echo '<h2><b>Teamsize:</b> ' . $game->teamsize . '</h2>'; ?>
                    <h2 style="margin-left: 2px"><b>Info: </b><?php echo $game->info . "</h2>";
				
				//Team game
				if ($game->teamsize > 0) {
					
					//Loop through all teams and list their info
					$teams = $db->query("SELECT * FROM `teams` WHERE gameid='%s'", $game->id);
					while ($team = mysql_fetch_object($teams)) {
						echo '<table class="teamTable">';
						echo '<tr><td colspan=4 class="headTd">'.$team->teamname.'</td></tr>';
						$members = $db->query("SELECT * FROM `signups` WHERE teamid='%s'", $team->id);
						$i = 1;
						while ($member = mysql_fetch_object($members)) {
							$user = $db->getUser($member->userid);
							echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', $i, $user->name, $user->steamid, $user->seat);
							$i++;
						}
						echo '</table>';
					}
					//List the randomers team if it exists
					$members = $db->query("SELECT * FROM `signups` WHERE teamid IS NULL AND gameid='%s'", $game->id);
					if (mysql_num_rows($members) > 0) {
						echo '<table class="teamTable">';
						echo '<tr><td colspan=4 class="headTd">Randomers</td></tr>';
						while ($member = mysql_fetch_object($members)) {
							$user = $db->getUser($member->userid);
							echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', $user->id, $user->name, $user->steamid, $user->seat);
						}
						echo '</table>';
					}
				}
				//Solo game
				else {
					$members = $db->query("SELECT * FROM `signups` WHERE gameid='%s'", $game->id);
					if (mysql_num_rows($members) > 0) {
						echo '<table class="teamTable">';
						echo '<tr><td colspan=4 class="headTd">'.$game->game.'</td></tr>';
						$i = 1;
						while ($member = mysql_fetch_object($members)) {
							$user = $db->getUser($member->userid);
							echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', $i, $user->name, $user->steamid, $user->seat);
							$i++;
						}
						echo '</table>';
					}
				}
				echo "</div>";
				
			}			
			
			$parent->displayFooter();
			
		}	
	}
	
?>