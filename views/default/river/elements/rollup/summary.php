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
}
if (!$story) {
	$story = $item->getObjectEntity();
}

$story_link = elgg_view('output/url', array(
	'href' => $story->getURL(),
	'text' => elgg_get_excerpt($story->getDisplayName(), 100),
	'class' => 'elgg-river-object',
	'is_trusted' => true,
));

$action = $item->action_type;
$type = $story->getType();
$subtype = $story->getSubtype() ? : 'default';

$keys = [
	"river:$action:$type:$subtype",
	"river:$action:$type:default",
	"river:$action:$type",
	"river:$action:default",
];

foreach ($keys as $key) {
	if (elgg_language_key_exists($key)) {
		$summary = elgg_echo($key, array($subject_link, $story_link));
		break;
	}
}

if (!$summary) {
	$summary = $subject_link;
}

echo $summary;
