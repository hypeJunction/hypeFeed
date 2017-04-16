<?php

$related_items = elgg_extract('related_items', $vars);
if (empty($related_items)) {
	return;
}

$list = [];
foreach ($related_items as $item) {
	$list_item = elgg_view('river/elements/related_item', ['item' => $item]);
	$list[] = elgg_format_element('li', [], $list_item);
}

echo elgg_format_element('ul', [
	'class' => 'elgg-river-related-items',
], implode('', $list));

elgg_require_js('river/elements/related');