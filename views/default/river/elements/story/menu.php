<?php

$entity = elgg_extract('entity', $vars);
if (!elgg_instanceof($entity)) {
	return;
}

$handlers = [
	'blog' => 'blog',
	'bookmarks' => 'bookmarks',
	'file' => 'file',
	'discussion' => 'discussion',
	'discussion_reply' => 'discussion',
	'hjwall' => 'wall',
	'page' => 'pages',
	'page_top' => 'pages',
	'poll' => 'polls',
	'thewire' => 'thewire',
];

echo elgg_view_menu('entity', array(
	'entity' => $entity,
	'handler' => elgg_extract($entity->getSubtype(), $handlers),
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));
