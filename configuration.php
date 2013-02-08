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

	if ($section == 'direct and indirect')
		return 'wispering-' . $match[1];
	else
		return 'talking-' . $match[1];
}

$f = fopen('./configurations.csv', 'r');

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
		$configuration['object'] = preg_replace('/^de\s+/', '', $configuration['object']);
	}

header('Content-type: application/json');
printf('window.callback(%s);', json_encode($configurations));