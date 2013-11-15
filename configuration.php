<?php

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

function array_remove(&$array, $value)
{
	$index = array_search($value, $array);

	if ($index === null || $index === false)
		return;

	unset($array[$index]);
}

function calculate_character_positions($configuration)
{
	$characters = array('hond', 'aap', 'olifant');
	$positions = array(
		'option-1' => null,
		'option-2' => null,
		'option-3' => null
	);

	$positions['option-2'] = $configuration['speaker'];
	array_remove($characters, $configuration['speaker']);
	array_remove($characters, $configuration['addressee']);
	$spare = current($characters);

	switch ($configuration['type'])
	{
		case 'whispering-right':
		case 'talking-left':
		case 'speaking-left':
			$positions['option-1'] = $configuration['addressee'];
			$positions['option-3'] = $spare;
			break;

		case 'whispering-left':
		case 'talking-right':
		case 'speaking-right':
			$positions['option-1'] = $spare;
			$positions['option-3'] = $configuration['addressee'];
			break;
	}

	return $positions;
}

function parse_configurations($f)
{
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

	// Post-process:
	// - identify the type of scene (talking|wispering-left|right)
	// - normalize object (de vlag -> vlag)
	foreach ($configurations as $section => &$section_configurations)
	{
		foreach ($section_configurations as &$configuration)
		{
			// This field contains weird characters that json_encode cannot encode.
			// Yet another reason to hate PHP intensely.
			unset($configuration['sentence']);

			$configuration['type'] = parse_spatial_position($configuration['spatial_position'], $section);
			$configuration['object'] = preg_replace('/^(de|het|een)\s+/', '', $configuration['object']);

			$positions = calculate_character_positions($configuration);
			//$configuration['positions'] = calculate_character_positions($configuration);
			$configuration['correct_position'] = array_search($configuration['correct_recipient_of_object'], $positions);
		}
	}

	return $configurations;
}

function get_configurations()
{
	$f = fopen('./configuration.csv', 'r');

	$configurations = parse_configurations($f);

	fclose($f);

	return $configurations;
}

if (basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__))
{
	error_reporting(E_ALL);
	ini_set('display_errors', true);
	ini_set('auto_detect_line_endings', true);

	$configurations = get_configurations();

	header('Content-type: application/json');
	printf('window.configuration = %s;', beautify_json(json_encode($configurations)));
}
