<?php

$item = elgg_extract('item', $vars);
$subject = $item->getSubjectEntity();

$vars['attachments'] = elgg_view_entity_icon($subject, 'large', [
	'use_hover' => false,
	'use_link' => false,
		]);

$vars['message'] = false;

echo elgg_view('river/elements/layout', $vars);
