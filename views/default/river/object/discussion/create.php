<?php

$item = elgg_extract('item', $vars);

$vars['responses'] = elgg_view('river/elements/discussion_replies', [
	'topic' => $item->getObjectEntity(),
]);

echo elgg_view('river/elements/layout', $vars);
