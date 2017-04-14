<?php
$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$summary = elgg_extract('summary', $vars);

if (!isset($summary)) {

	$object = $item->getObjectEntity();
	$related_items = elgg_extract('related_items', $vars);
	/* @var $related_items FeedItem[] */

	$users = [
		$item->subject_guid,
	];

	if (!empty($related_items)) {
		foreach ($related_items as $related_item) {
			if ($item->action_type == $related_item->action_type) {
				$users[] = $related_item->subject_guid;
			}
		}
	}

	$subject = $item->getSubjectEntity();
	$subject_link = elgg_view('output/url', array(
		'href' => $subject->getURL(),
		'text' => $subject->getDisplayName(),
		'class' => 'elgg-river-subject',
		'is_trusted' => true,
	));

	$users = array_unique($users);
	$user_count = count($users);
	if ($user_count > 1) {
		$subject_link .= elgg_echo('feed:subject:others', [$user_count - 1]);
	}

	$story = $object;
	while ($story instanceof ElggComment) {
		$story = $story->getContainerEntity();
	}

	$story_link = elgg_view('output/url', array(
		'href' => $story->getURL(),
		'text' => elgg_get_excerpt($story->getDisplayName(), 100),
		'class' => 'elgg-river-object',
		'is_trusted' => true,
	));

	$action = $item->action_type;
	if ($object instanceof ElggComment) {
		$action = 'comment';
	}
	
	$type = $story->getType();
	$subtype = $story->getSubtype() ?: 'default';

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

	$container = $story->getContainerEntity();
} else {
	$container = $item->getObjectEntity()->getContainerEntity();
}

if ($container instanceof ElggGroup && $container->guid != elgg_get_page_owner_guid()) {
	$group_link = elgg_view('output/url', [
		'href' => $container->getURL(),
		'text' => elgg_get_excerpt($container->getDisplayName(), 100),
	]);
	$summary .= ' ' . elgg_echo('river:ingroup', array($group_link));
}

if (!$summary) {
	return;
}
?>
<div class="elgg-river-summary clearfix">
	<?= $summary ?>
</div>
