<?php
/**
 * Short summary of the action that occurred
 *
 * @vars['item'] ElggRiverItem
 */

use hypeJunction\Feed\FeedItem;

$item = elgg_extract('item', $vars);
/* @var $item FeedItem */

$related_items = elgg_extract('related_items', $vars);
/* @var $related_items FeedItem[] */

$users = [
	$item->subject_guid,
];

foreach ($related_items as $related_item) {
	if ($item->action_type == $related_item->action_type) {
		$users[] = $related_item->subject_guid;
	}
}

$subject = $item->getSubjectEntity();
$subject_link = elgg_view('output/url', array(
	'href' => $subject->getURL(),
	'text' => $subject->name,
	'class' => 'elgg-river-subject',
	'is_trusted' => true,
));

$users = array_unique($users);
$user_count = count($users);
if ($user_count > 1) {
	$subject_link .= elgg_echo('feed:subject:others', [$user_count - 1]);
}

if ($item->story_guid && $item->story_guid != $item->object_guid) {
	$story = get_entity($item->story_guid);
} else {
	$story = $item->getObjectEntity();
}

$story_link = elgg_view('output/url', array(
	'href' => $story->getURL(),
	'text' => elgg_get_excerpt($story->getDisplayName(), 100),
	'class' => 'elgg-river-object',
	'is_trusted' => true,
));

$action = $item->action_type;
$type = $item->type;
$subtype = $item->subtype ? $item->subtype : 'default';

// check summary translation keys.
// will use the $type:$subtype if that's defined, otherwise just uses $type:default
$key = "river:$action:$type:$subtype";
$summary = elgg_echo($key, array($subject_link, $story_link));

if ($summary == $key) {
	$key = "river:$action:$type:default";
	$summary = elgg_echo($key, array($subject_link, $story_link));
}

echo $summary;
