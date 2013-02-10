<?php

$files = array_merge(
	glob('assets/audio/conversations/*.wav'),
	glob('assets/audio/cues/*.wav'),
	glob('assets/audio/*.wav'),
	glob('assets/characters/*.svg'),
	glob('assets/objects/*.png'),
	glob('assets/*.svg'),
	array(
		'game.css',
		'configuration.js',
		'index.html',
		'lib/sequence.js')
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
report.php

CACHE:
<?=implode("\n", $files)?>
