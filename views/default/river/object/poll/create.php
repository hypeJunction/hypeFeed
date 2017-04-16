<?php

$item = elgg_extract('item', $vars);

$object = $item->getObjectEntity();

$vars['attachments'] = elgg_view('poll/body', [
	'entity' => $object,
		]);

echo elgg_view('river/elements/layout', $vars);
