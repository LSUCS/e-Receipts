<?php

	class Template {
		
		private $content;
		private $keys;
		
		function __construct($template) {
			$this->content = file_get_contents("templates/".$template."/".$template.".html");
		}
		
		function addKey($key, $value) {
			$this->keys[$key] = $value;
		}
		
		function format() {
			return str_replace(array_keys($this->keys), array_values($this->keys), $this->content);
		}
		
	}
	
?>