<?php
$entity = elgg_extract('entity', $vars);
if (!elgg_instanceof($entity)) {
	return;
}

$summary = elgg_extract('summary', $vars);

if (!isset($summary)) {
	if ($entity instanceof ElggUser) {
		$subject = $entity;
	} else {
		$subject = $entity->getOwnerEntity();
	}
	if (!$subject) {
		return;
	}
	$subject_link = elgg_view('output/url', array(
		'href' => $subject->getURL(),
		'text' => $subject->getDisplayName(),
		'class' => 'elgg-river-subject',
		'is_trusted' => true,
	));

	$type = $entity->getType();
	$subtype = $entity->getSubtype() ?: 'default';

	$item = elgg_extract('item', $vars);

	if ($item instanceof ElggRiverItem) {
		$action = hypeJunction\Feed\FeedItem::getActionType($item);
	}

	if (elgg_language_key_exists("river:story:$action")) {
		$summary = elgg_echo("river:story:$action", array($subject_link));
	} else {
		if (elgg_language_key_exists("$type:$subtype")) {
			$type_str = elgg_echo("$type:$subtype");
		} else {
			$type_str = elgg_echo("$type:default");
		}
		
		$summary = elgg_echo('river:story:byline', [ucfirst($type_str), $subject_link]);
	}
}

if (!$summary) {
	return;
}
?>
<div class="elgg-river-summary clearfix">
	<?= $summary ?>
</div>
