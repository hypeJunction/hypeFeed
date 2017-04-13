<?php
$entity = elgg_extract('entity', $vars);
if (!elgg_instanceof($entity)) {
	return;
}

$summary = elgg_extract('summary', $vars);

if (!isset($summary)) {
	$subject = $entity->getOwnerEntity();
	$subject_link = elgg_view('output/url', array(
		'href' => $subject->getURL(),
		'text' => $subject->getDisplayName(),
		'class' => 'elgg-river-subject',
		'is_trusted' => true,
	));

	$type = $entity->getType();
	$subtype = $entity->getSubtype() ? : 'default';
	
	$keys = [
		"river:story:$type:$subtype",
		"river:story:$type:default",
		"river:story:$type",
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
