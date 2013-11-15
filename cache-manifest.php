<?php

$is_webkit = strstr($_SERVER['HTTP_USER_AGENT'], 'AppleWebKit/') !== false;

$files = array_merge(
	glob('assets/audio/sprites/*.json'),
	glob('assets/characters/*.svg'),
	glob('assets/objects/*.svg'),
	glob('assets/*.svg'),
	array(
		'game.css',
		'configuration.php',
		'index.html',
		'lib/sequence.js')
);

$files = array_merge($files, $is_webkit
	? glob('assets/audio/sprites/*.m4a')
	: glob('assets/audio/sprites/*.ogg'));

$latest_change = 0;
$cache_size = 0;
foreach ($files as $file)
{
	$latest_change = max(filemtime($file), $latest_change);
	$cache_size += filesize($file);
}

$latest_change = max(filemtime('configuration.csv'), $latest_change);

header('Content-type: text/cache-manifest');
header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $latest_change) . ' GMT'); 
?>
CACHE MANIFEST
# Version: <?=$latest_change?>

# Total size: <?=number_format($cache_size / (1024 * 1024), 2)?>MB

<?=implode("\n", $files)?>
