<?php

$story = elgg_extract('story', $vars);
if ($story) {
	echo $story;
	return;
}

$message = elgg_extract('message', $vars);
$attachments = elgg_extract('attachments', $vars);

$body = $message . $attachments;

if ($body) {
	echo elgg_format_element('div', [
		'class' => 'elgg-river-body clearfix',
			], $body);
}
