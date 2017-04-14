<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$responses = elgg_extract('responses', $vars);
if (is_string($responses)) {
	$responses = trim($responses);
}

if (!$responses && $responses !== false) {
	$object = hypeJunction\Interactions\InteractionsService::getRiverObject($item);
	if ($object instanceof ElggObject) {
		$responses = elgg_view_comments($object);
	}
}

if (!$responses) {
	return;
}

?>
<div class="elgg-river-responses clearfix">
	<?= $responses ?>
</div>