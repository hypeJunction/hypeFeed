<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$story = elgg_extract('story', $vars);
if (!isset($story)) {
	$object = hypeJunction\Interactions\InteractionsService::getRiverObject($item);
	$story = $object;

	while ($story instanceof ElggComment) {
		$story = $story->getContainerEntity();
	}

	if (!$story instanceof ElggEntity) {
		$story = $object;
	}

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
			'object_guids' => [(int) $story->guid],
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
