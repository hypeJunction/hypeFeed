<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$responses = elgg_extract('responses', $vars);

if (!isset($responses)) {
	$object = $item->getObjectEntity();
	$responses = elgg_view_comments($object);
}

if (!$responses) {
	return;
}

?>
<div class="elgg-river-responses clearfix">
	<?= $responses ?>
</div>