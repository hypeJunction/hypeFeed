<?php

$responses = elgg_extract('responses', $vars);

$footer = $responses;
if ($footer) {
	echo elgg_format_element('div', [
		'class' => 'elgg-river-footer clearfix',
			], $footer);
}