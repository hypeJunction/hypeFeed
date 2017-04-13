<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$menu = elgg_view_menu('river', array(
	'item' => $item,
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

echo $menu;