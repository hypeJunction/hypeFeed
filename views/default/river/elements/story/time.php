<?php

$entity = elgg_extract('entity', $vars);
if (!elgg_instanceof($entity)) {
	return;
}

$time = elgg_extract('time', $vars);
if (isset($time)) {
	echo $time;
	return;
}

$timestamp = elgg_extract('timestamp', $vars);
if (!isset($timtestamp)) {
	$timestamp = elgg_view_friendly_time($entity->time_created);
}
?>
<div class="elgg-river-subtitle">
	<span class="elgg-river-timestamp">
		<?= $timestamp ?>
	</span>
</div>