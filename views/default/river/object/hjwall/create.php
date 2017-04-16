<?php

use hypeJunction\Wall\Post;

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$object = $item->getObjectEntity();
if (!$object instanceof Post) {
	return;
}

$vars['summary'] = $object->formatSummary();
$vars['message'] = $object->formatMessage();
$vars['attachments'] = $object->formatAttachments();

echo elgg_view('river/elements/layout', $vars);

