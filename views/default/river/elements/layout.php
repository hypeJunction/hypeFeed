<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

elgg_require_js('river/readmore');

$related_items = elgg_extract('related_items', $vars, $item->related_items);

$object = $item->getObjectEntity();

if ($object instanceof ElggComment || $item->annotation_id) {
	$story_vars = $vars;
	$story_vars['attachments'] = $vars['attachments'];
	$vars['attachments'] = false;
	$story_vars['responses'] = $vars['responses'];
	$vars['responses'] = false;
	$vars['story'] = elgg_view('river/elements/story', $vars);
}

$class = elgg_extract_class($vars, 'elgg-river-item');
$vars['related_items'] = $related_items;

if ($related_items || elgg_extract('story', $vars)) {
	$class[] = 'elgg-river-rollup';
}

$vars = elgg_trigger_plugin_hook('elements', 'river', $vars, $vars);

$vars['time'] = elgg_view('river/elements/time', $vars);
$vars['menu'] = elgg_view('river/elements/menu', $vars);
$vars['image'] = elgg_view('river/elements/image', $vars);
$vars['summary'] = elgg_view('river/elements/summary', $vars);
$vars['attachments'] = elgg_view('river/elements/attachments', $vars);
$vars['message'] = elgg_view('river/elements/message', $vars);
$vars['responses'] = elgg_view('river/elements/responses', $vars);

$header = elgg_view('river/elements/header', $vars);
$related = elgg_view('river/elements/related', $vars);
$body = elgg_view('river/elements/body', $vars);
$footer = elgg_view('river/elements/footer', $vars);

echo elgg_format_element('div', [
	'class' => $class,
], $header . $related . $body . $footer);
