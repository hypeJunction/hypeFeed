<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$message = elgg_extract('message', $vars);

if (!isset($message)) {
	$object = $item->getObjectEntity();
	$message = elgg_view('output/longtext', [
		'class' => 'elgg-river-object-description',
		'value' => $object->description,
	]);
	$message = elgg_format_element('div', [
		'class' => 'elgg-read-more',
	], $message);
}

if (!$message) {
	return;
}

?>
<div class="elgg-river-message">
	<?= $message ?>
</div>