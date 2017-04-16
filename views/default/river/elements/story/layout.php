<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggRiverItem) {
	return;
}

$vars['time'] = elgg_view('river/elements/story/time', $vars);
$vars['menu'] = elgg_view('river/elements/story/menu', $vars);
$vars['image'] = elgg_view('river/elements/story/image', $vars);
$vars['summary'] = elgg_view('river/elements/story/summary', $vars);
$vars['message'] = elgg_view('river/elements/story/message', $vars);
$vars['attachments'] = elgg_view('river/elements/story/attachments', $vars);
$vars['responses'] = elgg_view('river/elements/story/responses', $vars);

$header = elgg_view('river/elements/story/header', $vars);
$body = elgg_view('river/elements/story/body', $vars);
$footer = elgg_view('river/elements/story/footer', $vars);

echo elgg_view_module('river', null, $body, [
	'header' => $header,
	'footer' => $footer,
	'class' => 'elgg-river-item elgg-river-story',
]);