<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$object = $item->getObjectEntity();
$subject = $item->getSubjectEntity();
$icon = elgg_view_entity_icon($subject, 'tiny');

$subject_link = elgg_view('output/url', array(
	'href' => $subject->getURL(),
	'text' => $subject->getDisplayName(),
	'class' => 'elgg-river-subject',
	'is_trusted' => true,
		));


$key = hypeJunction\Feed\Languages::getSummaryKey($item);
$summary = elgg_echo($key, [$subject_link, elgg_echo('feed:object:this')]);

$menu = elgg_view('river/elements/menu', $vars);
$time = elgg_view('river/elements/time', $vars);

echo elgg_view_image_block($icon, $menu . $summary . $time, [
	'class' => 'elgg-subtext',
]);
