<?php

$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'group');

$group = get_entity($guid);

elgg_set_page_owner_guid($group->guid);

elgg_group_gatekeeper();

elgg_push_breadcrumb($group->getDisplayName(), $group->getURL());
elgg_push_breadcrumb(elgg_echo('groups:activity'));

$title = elgg_echo('groups:activity');

$content = elgg_view('listing/activity/group', [
	'options' => [
		'type' => get_input('type'),
		'subtype' => get_input('subtype'),
		'owner' => $group,
	],
]);

$layout = elgg_view_layout('content', [
	'title' => $title,
	'content' => $content,
	'filter' => false,
	'sidebar' => elgg_view('core/river/sidebar'),
	'class' => 'elgg-river-layout',
]);

echo elgg_view_page($title, $layout);
