<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

if (!preg_match('/^[a-z_]+\.(ogg|m4a)$/', $_GET['sprite'], $match)) {
	header('Status: 404 Not Found');
	die("Cannot download that filename");
}

$fp = fopen('assets/audio/sprites/' . $_GET['sprite'], 'rb');

if (!$fp) {
	header('Status: 404 Not Found');
	die("Cannot open file");
}

stream_filter_append($fp, 'convert.base64-encode');

header('Content-type: text/plain');
echo 'data:audio/' . $match[1] . ';base64,';
fpassthru($fp);