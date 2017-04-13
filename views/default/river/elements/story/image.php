<?php

$entity = elgg_extract('entity', $vars);
if (!elgg_instanceof($entity)) {
	return;
}

$image = elgg_extract('image', $vars);
if (isset($image)) {
	echo $image;
	return;
}

$subject = $entity->getOwnerEntity();
if (!$subject) {
	return;
}

$size = elgg_extract('size', $vars);
if (!$size) {
	$size = elgg_in_context('widgets') ? 'tiny' : 'small';
}

echo elgg_view_entity_icon($subject, $size);
