<?php

$files = array_merge(
	glob('assets/audio/sprites/*.json'),
	glob('assets/audio/sprites/*.m4a'),
	glob('assets/audio/sprites/*.ogg'),
	glob('assets/characters/*.svg'),
	glob('assets/objects/*.png'),
	glob('assets/objects/*.svg'),
	glob('assets/*.svg'),
	array(
		'game.css',
		'configuration.php',
		'index.html',
		'lib/sequence.js',
		'lib/howler/howler.min.js')
);

$latest_change = 0;
$cache_size = 0;
foreach ($files as $file)
{
	$latest_change = max(filemtime($file), $latest_change);
	$cache_size += filesize($file);
}

header('Content-type: text/cache-manifest');
?>
CACHE MANIFEST
# Version: <?=$latest_change?>

# Total size: <?=number_format($cache_size / (1024 * 1024), 2)?>MB

NETWORK:
post-measurements.php

CACHE:
<?=implode("\n", $files)?>
