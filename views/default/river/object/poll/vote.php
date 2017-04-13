<?php
/**
 * Poll river view
 */

$object = $vars['item']->getObjectEntity();

echo elgg_view('river/elements/layout', array(
	'item' => $vars['item'],
	'attachments' => elgg_view('poll/body', [
		'entity' => $object,
	]),
));
