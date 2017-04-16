<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$story = elgg_extract('story', $vars);
if (!isset($story)) {
	if ($item->story_guid && $item->story_guid != $item->object_guid) {
		$object = get_entity($item->story_guid);
	} else {
		$object = hypeJunction\Interactions\InteractionsService::getRiverObject($item);
	}

	while ($object instanceof ElggComment) {
		$object = $object->getContainerEntity();
	}

	$story = $object;

	if ($story->river_id && $story->river_id != $item->id) {
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
			'object_guids' => [(int) $object->guid],
			'limit' => 1,
			'order_by' => 'rv.posted ASC',
			'wheres' => [
				"rv.id != $item->id",
			],
		]);
	}

	if ($river) {
		$item = array_shift($river);
		echo elgg_view($item->getView(), [
			'item' => $item,
			'rollup' => true,
		]);
		return;
	}
}

echo elgg_view('river/elements/story/layout', [
	'entity' => $story,
	'item' => $item,
]);
