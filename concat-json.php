#!/usr/bin/php
<?php

// Source: http://www.daveperrett.com/articles/2008/03/11/format-json-with-php/
function pretty_json($json)
{
	$result      = '';
	$pos         = 0;
	$strLen      = strlen($json);
	$indentStr   = '  ';
	$newLine     = "\n";
	$prevChar    = '';
	$outOfQuotes = true;

	for ($i=0; $i<=$strLen; $i++) {

		// Grab the next character in the string.
		$char = substr($json, $i, 1);

		// Are we inside a quoted string?
		if ($char == '"' && $prevChar != '\\') {
			$outOfQuotes = !$outOfQuotes;
		
		// If this character is the end of an element, 
		// output a new line and indent the next line.
		} else if(($char == '}' || $char == ']') && $outOfQuotes) {
			$result .= $newLine;
			$pos --;
			for ($j=0; $j<$pos; $j++) {
				$result .= $indentStr;
			}
		}
		
		// Add the character to the result string.
		$result .= $char;

		// If the last character was the beginning of an element, 
		// output a new line and indent the next line.
		if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
			$result .= $newLine;
			if ($char == '{' || $char == '[') {
				$pos ++;
			}
			
			for ($j = 0; $j < $pos; $j++) {
				$result .= $indentStr;
			}
		}
		
		$prevChar = $char;
	}

	return $result;
}

$files = array();

for ($i = 1; $i < $argc; ++$i)
	$files[$argv[$i]] = json_decode(file_get_contents($argv[$i]));

$json = json_encode($files);
printf("window.json_files = %s\n", pretty_json($json));
