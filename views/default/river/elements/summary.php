<?php
$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$rollup = elgg_extract('rollup', $vars);
if ($rollup) {
	$summary = elgg_view('river/elements/story/summary', $vars + [
		'entity' => $item->getObjectEntity(),
	]);
} else {
	$summary = elgg_extract('summary', $vars);
}

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
		$subject_link .= ' ' . elgg_echo('feed:and') . ' ' . elgg_view('output/url', [
					'href' => '#',
					'text' => elgg_echo('feed:subject:others', [$user_count - 1]),
					'class' => 'elgg-river-show-related',
		]);
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

	$key = hypeJunction\Feed\Languages::getSummaryKey($item);
	$summary = elgg_echo($key, array($subject_link, $story_link));

	$container = $story->getContainerEntity();
} else {
	$container = $item->getObjectEntity()->getContainerEntity();
}

if ($container instanceof ElggGroup && $container->guid != elgg_get_page_owner_guid() && !$rollup) {
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
