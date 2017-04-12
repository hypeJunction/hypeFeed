<?php

elgg_push_breadcrumb(elgg_echo('activity'));

$tabs = hypeJunction\Feed\Config::getTabs(true);
if (!array_key_exists('all', $tabs)) {
	$next = array_shift($tabs);
	if ($next) {
		forward($next->getHref());
	}
	forward('', '404');
}

$title = elgg_echo('activity');

$content = elgg_view('listing/activity/all', [
	'options' => [
		'type' => get_input('type'),
		'subtype' => get_input('subtype'),
		'owner' => elgg_get_site_entity(),
	],
]);

$filter = elgg_view('activity/filter', [
	'selected' => 'all',
]);

$layout = elgg_view_layout('content', [
	'title' => $title,
	'content' => $content,
	'filter' => $filter,
	'sidebar' => elgg_view('core/river/sidebar'),
	'class' => 'elgg-river-layout',
]);

echo elgg_view_page($title, $layout);
