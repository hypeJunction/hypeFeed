<?php

/**
 * Post comment river view
 */
$item = $vars['item'];
/* @var ElggRiverItem $item */

$comment = $item->getObjectEntity();
$subject = $item->getSubjectEntity();
$target = $item->getTargetEntity();
if (!$target) {
	return;
}

$subject_link = elgg_view('output/url', array(
	'href' => $subject->getURL(),
	'text' => $subject->name,
	'class' => 'elgg-river-subject',
	'is_trusted' => true,
		));

$target_link = elgg_view('output/url', array(
	'href' => $comment->getURL(),
	'text' => $target->getDisplayName(),
	'class' => 'elgg-river-target',
	'is_trusted' => true,
		));

$type = $target->getType();
$subtype = $target->getSubtype() ? $target->getSubtype() : 'default';
$key = "river:comment:$type:$subtype";
if (!elgg_language_key_exists($key)) {
	$key = "river:comment:$type:default";
}
$summary = elgg_echo($key, array($subject_link, $target_link));

$rollup = elgg_view('river/elements/layout', array(
	'item' => $vars['item'],
	'summary' => $summary,
	'message' => elgg_view('river/elements/rollup/original', $vars),
	'responses' => false,
		));

echo elgg_format_element('div', [
	'class' => 'elgg-river-nest',
		], $rollup);
