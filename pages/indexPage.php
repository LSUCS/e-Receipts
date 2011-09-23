<?php

	class index {
		
		function run($parent) {
			
			$parent->displayHeader();

				
			?>
                
                <div class="homePage">
                    <div class="newsFeed"><?php 
					
											
					/* RSS Parser */
						
					//Get blog info and trim it
					$news = file_get_contents("newsfeed.xml");
					$news = substr($news, strpos($news, "<item"));
					$news = str_replace(array("<![CDATA[", "]]>"), "", $news);

					
					preg_match_all("/<item>.*?<title>(.*?)<\/title>.*?<link>(.*?)<\/link>.*?<pubDate>(.*?)<\/pubDate>.*?<description>(.*?)<\/description>.*?<\/item>/ms", $news, $entries, PREG_SET_ORDER);
					
					foreach ($entries as $entry) {
						
						echo "<h1>" . $entry[1] . "</h1>";
					    echo "<p>" . $this->parseBBCode($entry[4]) . "</p>";
						echo "<hr />";
					}
					
					?></div>
                    <div class="mainInfo">
                    	<div class="infoHeader">Welcome to LSUCS LAN30!</div>
                    	Keep an eye on this site for match schedules, tournament teams and key info about the LAN. <br /><br />
                   		Sign-ups can be found at the links at the top of this page. Please ensure if you sign up to a tournament you actually intend to play in it.<br />
                        If you find out you can't attend a match, let a committee member know.
                        <h2>Mumble</h2>
                        If you have any trouble setting up mumble or connecting, please see a committee member
                        <ul>
                        	<li><b>Host:</b> lan
                            <li><b>Port:</b> derp
                        </ul>
                        <h2>DC Server</h2>
                        We use DC for sharing our open-source free legal linux distros. Recommended clients are DC++ or the standard DC client.
                        <ul>
                        	<li><b>Host:</b> lan</li>
                            <li><b>Port:</b> default</li>
                        </ul>
                        <h2>NO Torrenting!</h2>
                        Anyone caught torrenting will be asked to leave the LAN. The University does NOT allow the downloading of illegal content over their network. If they see any torrent traffic coming from our LAN they will ban us from having an internet connection at future events.
                    </div>
                </div>
                
            <?php
			
			
			$parent->displayFooter();
			
		}
		
		function parseBBCode($string) {
			$search = array(
				'/\[b\](.*?)\[\/b\]/smi',
				'/\[i\](.*?)\[\/i\]/smi',
				'/\[u\](.*?)\[\/u\]/smi',
				'/\[img\](.*?)\[\/img\]/smi',
				'/\[url\=(.*?)\](.*?)\[\/url\]/smi',
				'/\[code\](.*?)\[\/code\]/smi',
				'/\[list\](.*?)\[\/list\]/smi',
				'/\[\*\](.*?)\n/'
			);
			$replace = array(
				'<b>\\1</b>',
				'<i>\\1</i>',
				'<u>\\1</u>',
				'<img src="\\1">',
				'<a href="\\1">\\2</a>',
				'<code>\\1</code>',
				'<ul>\\1</ul>',
				'<li>\\1</li>'
			);
			foreach ($search as $key => $value) {
				$string = preg_replace($value, $replace[$key], $string);
			}
			$string = preg_replace("/\n/", "<br />", $string);
			echo htmlentities($strine);
			return $string;
		}
	
	
	}
	
?>