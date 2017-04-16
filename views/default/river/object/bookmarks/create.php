<?php

if (elgg_view_exists('output/card')) {
	$vars['attachments'] = elgg_view('output/card', [
		'href' => $object->address,
	]);
} else {
	$vars['attachments'] = elgg_view('output/url', [
		'href' => $object->address,
	]);
}
echo elgg_view('river/elements/layout', $vars);
