<?php

$files = array_merge(
	glob('assets/audio/conversations/*.wav'),
	glob('assets/audio/cues/*.wav'),
	array('assets/audio/wispering.m4a'),
	glob('assets/characters/*.svg'),
	glob('assets/objects/*.png'),
	array('assets/basket.svg', 'assets/meadow.svg')
);

$latest_change = 0;
foreach ($files as $file)
	$latest_change = max(filemtime($file), $latest_change);

header('Content-type: text/cache-manifest');
?>
CACHE MANIFEST
# Version: <?=$latest_change?>

NETWORK:
index.html
configuration.js
game.css
lib/sequence.js

CACHE:
<?=implode("\n", $files)?>
