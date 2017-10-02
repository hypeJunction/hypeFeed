<?php

$tabs = \hypeJunction\Feed\Config::getTabs(true);

$filter_context = elgg_extract('selected', $vars);

$page_owner = elgg_get_page_owner_entity();
$viewer = elgg_get_logged_in_user_entity();

foreach ($tabs as &$tab) {
	if ($filter_context == $tab->getName()) {
		$tab->setSelected(true);
	}
}

echo elgg_view_menu('filter:activity', [
	'sort_by' => 'priority',
	'items' => $tabs,
	'class' => 'elgg-menu-hz elgg-menu-filter',
]);
