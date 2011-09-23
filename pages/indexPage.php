<?php

	class index {
		
		function run($parent) {
			
			$parent->displayHeader();
				
			?>
                
                <div class="homePage">
                    
								
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
			return $string;
		}
	
	
	}
	
?>