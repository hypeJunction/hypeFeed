<?php
/**
 * Groups latest activity
 */

$group = elgg_extract('entity', $vars);
if (!$group) {
	return true;
}

if ($group->activity_enable == 'no') {
	return true;
}

$all_link = elgg_view('output/url', array(
	'href' => "groups/activity/$group->guid",
	'text' => elgg_echo('link:view:all'),
	'is_trusted' => true,
));

elgg_push_context('widgets');

$options = [
	'limit' => 4,
	'pagination' => false,
	'no_results' => elgg_echo('groups:activity:none'),
	'owner' => $group,
];

$svc = hypeJunction\Feed\FeedService::getInstance();
$content = $svc->view($options);

elgg_pop_context();

echo elgg_view('groups/profile/module', array(
	'title' => elgg_echo('groups:activity'),
	'content' => $content,
	'all_link' => $all_link,
));
