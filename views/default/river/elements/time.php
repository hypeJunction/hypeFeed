<?php
$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$time = elgg_extract('time', $vars);
if (isset($time)) {
	echo $time;
	return;
}

$timestamp = elgg_extract('timestamp', $vars);
if (!isset($timtestamp)) {
	$timestamp = elgg_view_friendly_time($item->getTimePosted());
}
?>
<div class="elgg-river-subtitle">
	<span class="elgg-river-timestamp">
		<?= $timestamp ?>
	</span>
</div>