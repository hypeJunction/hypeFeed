<?php

$item = elgg_extract('item', $vars);

$related_items = false;
if ($item instanceof \hypeJunction\Feed\FeedItem && $item->action_type != 'create') {
	$related_items = $item->getRelatedItems();
}

if (!empty($related_items)) {
	$vars['related_items'] = $related_items;
	$body = elgg_view('river/elements/rollup', $vars);
} else {
	$body = elgg_view('river/elements/body', $vars);
}

echo elgg_format_element('div', [
	'class' => 'elgg-river-item',
		], $body);
