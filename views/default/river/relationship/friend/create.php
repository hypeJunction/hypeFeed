<?php
/**
 * Create friend river view
 */
$item = $vars['item'];
/* @var ElggRiverItem $item */

$subject = $item->getSubjectEntity();
$object = $item->getObjectEntity();

$subject_icon = elgg_view_entity_icon($subject, 'small');
$object_icon = elgg_view_entity_icon($object, 'small');

$vars['attachments'] = $subject_icon . elgg_view_icon('arrow-right') . $object_icon;
echo elgg_view('river/elements/layout', $vars);
