<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

if (elgg_extract('rollup', $vars)) {
	$menu = elgg_view('river/elements/story/menu', [
		'entity' => $item->getObjectEntity(),
	]);
} else {
	$menu = elgg_view_menu('river', $vars + [
		'sort_by' => 'priority',
		'class' => 'elgg-menu-hz',
	]);
}

echo $menu;
