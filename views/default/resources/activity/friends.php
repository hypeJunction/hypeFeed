<?php

$tabs = hypeJunction\Feed\Config::getTabs(true);
if (!array_key_exists('friends', $tabs)) {
	$next = array_shift($tabs);
	if ($next) {
		forward($next->getHref());
	}
	forward('', '404');
}

$username = elgg_extract('username', $vars);
if ($username) {
	$user = get_user_by_username($username);
} else {
	$user = elgg_get_logged_in_user_entity();
}

if (!$user || !$user->canEdit()) {
	forward('', '404');
}

elgg_set_page_owner_guid($user->guid);

elgg_push_breadcrumb($user->getDisplayName(), $user->getURL());
elgg_push_breadcrumb(elgg_echo('friends'), "friends/$user->username");
elgg_push_breadcrumb(elgg_echo('activity'));

$title = elgg_echo('activity');

$content = elgg_view('listing/activity/friends', [
	'options' => [
		'type' => get_input('type'),
		'subtype' => get_input('subtype'),
		'owner' => $user,
	],
		]);

$filter = '';
if ($user->guid == elgg_get_logged_in_user_guid()) {
	$filter = elgg_view('activity/filter', [
		'selected' => 'friends',
	]);
}

$layout = elgg_view_layout('content', [
	'title' => $title,
	'content' => $content,
	'filter' => $filter,
	'sidebar' => elgg_view('core/river/sidebar'),
	'class' => 'elgg-river-layout',
		]);

echo elgg_view_page($title, $layout);
