<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$image = elgg_extract('image', $vars);
if (isset($image)) {
	echo $image;
	return;
}

$subject = $item->getSubjectEntity();
if (!$subject) {
	return;
}

$size = elgg_extract('size', $vars);
if (!$size) {
	$size = elgg_in_context('widgets') ? 'tiny' : 'small';
}

echo elgg_view_entity_icon($subject, $size);
