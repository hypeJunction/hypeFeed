<?php

$entity = elgg_extract('entity', $vars);
if (!elgg_instanceof($entity)) {
	return;
}

$responses = elgg_extract('responses', $vars);

if (!isset($responses)) {
	$responses = elgg_view_comments($entity);
}

if (!$responses) {
	return;
}

?>
<div class="elgg-river-responses clearfix">
	<?= $responses ?>
</div>