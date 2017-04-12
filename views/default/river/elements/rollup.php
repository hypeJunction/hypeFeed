<?php

$params = [
	'summary' => elgg_view('river/elements/rollup/summary', $vars),
	'message' => elgg_view('river/elements/rollup/original', $vars),
	'responses' => ' ',
	'attachments' => '',
];

echo elgg_view('river/elements/body', array_merge($vars, $params));

