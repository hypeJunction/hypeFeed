<?php

$item = $vars['item'];
/* @var ElggRiverItem $item */

if ($item->story_guid && $item->story_guid != $item->object_guid) {
	$story = get_entity($item->story_guid);
} else {
	$story = $item->getObjectEntity();
}

if ($story->river_id) {
	$river = elgg_get_river([
		'ids' => [(int) $story->river_id],
		'limit' => 1,
		'order_by' => 'rv.posted ASC',
		'wheres' => [
			"rv.id != $item->id",
		],
	]);
} else {
	$river = elgg_get_river([
		'action_type' => 'create',
		'object_guids' => [(int) $story->guid],
		'limit' => 1,
		'order_by' => 'rv.posted ASC',
		'wheres' => [
			"rv.id != $item->id",
		],
	]);
}

if (!$river) {
	return;
}

$item = array_shift($river);

echo elgg_view($item->getView(), [
	'item' => $item,
]);
