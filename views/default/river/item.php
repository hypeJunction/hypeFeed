<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$subject = $item->getSubjectEntity();
$object = $item->getObjectEntity();

if (!$subject || !$object) {
	return;
}

if ($item instanceof \hypeJunction\Feed\FeedItem) {
	$related_items = $item->getRelatedItems();
	if (!empty($related_items)) {
		$item = array_shift($related_items);
		$item->related_items = $related_items;
		echo elgg_view($item->getView(), [
			'item' => $item,
		]);
		return;
	}
}

echo elgg_view($item->getView(), $vars);
