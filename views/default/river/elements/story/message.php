<?php

$entity = elgg_extract('entity', $vars);
if (!elgg_instanceof($entity)) {
	return;
}
$message = elgg_extract('message', $vars);

if (!isset($message)) {
	$message = elgg_view('output/longtext', [
		'class' => 'elgg-river-object-description',
		'value' => $entity->description,
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