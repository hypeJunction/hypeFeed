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

if ($item instanceof \hypeJunction\Feed\RollUp) {
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

global $_elgg_special_river_catch;
if (!isset($_elgg_special_river_catch)) {
	$_elgg_special_river_catch = false;
}
if ($_elgg_special_river_catch) {
	echo elgg_view('river/elements/layout', $vars);
	return;
}
$_elgg_special_river_catch = true;

echo elgg_view($item->getView(), $vars);

$_elgg_special_river_catch = false;
