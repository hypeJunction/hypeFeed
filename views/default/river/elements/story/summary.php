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

	$keys = [
		"river:story:$type:$subtype",
		"river:story:$type:default",
		"river:story:$type",
		"river:story:$item->action_type",
		"river:story:default",
	];

	foreach ($keys as $key) {
		if (elgg_language_key_exists($key)) {
			$summary = elgg_echo($key, array($subject_link));
			break;
		}
	}
}

if (!$summary) {
	return;
}
?>
<div class="elgg-river-summary clearfix">
	<?= $summary ?>
</div>
