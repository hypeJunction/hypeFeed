<?php
$entity = elgg_extract('entity', $vars);
if (!elgg_instanceof($entity)) {
	return;
}

$attachments = elgg_extract('attachments', $vars);

if (!isset($attachments)) {
	if ($entity instanceof ElggFile) {
		if ($entity->getSimpleType() == 'image') {
			$attachments = elgg_view_entity_icon($entity, 'large');
		}
	} else {
		$attachments = elgg_view('output/attachments', [
			'entity' => $entity,
		]);
	}
}

if (!$attachments) {
	return;
}
?>
<div class="elgg-river-attachments clearfix">
	<?= $attachments ?>
</div>