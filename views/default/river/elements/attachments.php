<?php
$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$attachments = elgg_extract('attachments', $vars);

if (!isset($attachments) && !elgg_in_context('widgets')) {
	$object = $item->getObjectEntity();
	if ($object instanceof ElggFile) {
		if ($object->getSimpleType() == 'image') {
			$attachments = elgg_view_entity_icon($object, 'large');
		}
	} else {
		$attachments = elgg_view('output/attachments', [
			'entity' => $object,
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