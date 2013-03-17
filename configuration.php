<?php

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('auto_detect_line_endings', true);

function simplify_header($header)
{
	$header = preg_replace('/\(.+\)/', '', $header);

	$header = trim($header);

	$header = strtolower($header);

	$header = preg_replace('/\s+/', '_', $header);

	return $header;
}

function parse_spatial_position($spatial_position, $section)
{
	if (!preg_match('/^addressee?\s+\((left|right)\)$/i', $spatial_position, $match))
		return null;

	if ($section == 'direct_and_indirect_speech_items')
		return 'whispering-' . $match[1];
	else if ($section == 'practise_items')
		return 'speaking-' . $match[1];
	else
		return 'talking-' . $match[1];
}

function beautify_json($json)
{
	$result      = '';
	$pos         = 0;
	$strLen      = strlen($json);
	$indentStr   = '  ';
	$newLine     = "\n";
	$prevChar    = '';
	$outOfQuotes = true;

	for ($i = 0; $i <= $strLen; ++$i)
	{

		// Grab the next character in the string.
		$char = substr($json, $i, 1);

		// Are we inside a quoted string?
		if ($char == '"' && $prevChar != '\\')
			$outOfQuotes = !$outOfQuotes;
		
		// If this character is the end of an element, 
		// output a new line and indent the next line.
		else if(($char == '}' || $char == ']') && $outOfQuotes)
		{
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
		if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes)
		{
			$result .= $newLine;

			if ($char == '{' || $char == '[')
				$pos ++;
			
			for ($j = 0; $j < $pos; ++$j)
				$result .= $indentStr;
		}
		
		$prevChar = $char;
	}

	return $result;
}

$f = fopen('./configuration.csv', 'r');

$headers = fgetcsv($f);

foreach ($headers as &$header)
	$header = simplify_header($header);

$configurations = array();
$current = '';

while ($row = fgetcsv($f))
{
	$row = array_filter($row);

	// Ignore empty rows
	if (empty($row))
		continue;

	// Section
	elseif (count($row) === 1)
	{
		$name = simplify_header($row[0]);
		$configurations[$name] = array();
		$current = $name;
	}

	// Actual data
	else
	{
		$row = array_map('trim', $row);
		$configurations[$current][] = array_combine($headers, $row);
	}
}

fclose($f);

// Post-process:
// - identify the type of scene (talking|wispering-left|right)
// - normalize object (de vlag -> vlag)
foreach ($configurations as $section => &$section_configurations)
	foreach ($section_configurations as &$configuration)
	{
		$configuration['type'] = parse_spatial_position($configuration['spatial_position'], $section);
		$configuration['object'] = preg_replace('/^(de|het|een)\s+/', '', $configuration['object']);
	}

header('Content-type: application/json');
printf('window.configuration = %s;', beautify_json(json_encode($configurations)));