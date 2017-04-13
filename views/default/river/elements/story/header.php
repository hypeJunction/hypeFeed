<?php

$menu = elgg_extract('menu', $vars);
$summary = elgg_extract('summary', $vars);
$time = elgg_extract('time', $vars);

$header = $menu . $summary . $time;

$image = elgg_extract('image', $vars);
echo elgg_view_image_block($image, $header, [
	'class' => 'elgg-river-header clearfix',
]);