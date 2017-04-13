<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$story = elgg_extract('story', $vars);
if (!isset($story)) {
	if ($item->story_guid && $item->story_guid != $item->object_guid) {
		$story = get_entity($item->story_guid);
	} else {
		$object = $item->getObjectEntity();
		while ($object instanceof ElggComment) {
			$object = $object->getContainerEntity();
		}
		$story = $object;
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
		if ($river) {
			$item = array_shift($river);
			echo elgg_view($item->getView(), [
				'item' => $item,
			]);
			return;
		}
	}
}

echo elgg_view('river/elements/story/layout', [
	'entity' => $story,
	'item' => $item,
]);
