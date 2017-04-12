<?php

$dbprefix = elgg_get_config('dbprefix');
$count = elgg_get_river([
	'count' => true,
	'wheres' => [
		"NOT EXISTS (SELECT 1 FROM {$dbprefix}feeds
				WHERE id = rv.id)",
	],
		]);

echo elgg_view('output/longtext', [
	'value' => elgg_echo('admin:upgrades:feed:populate:description'),
]);

echo elgg_view('admin/upgrades/view', [
	'count' => $count,
	'action' => 'action/upgrade/feed/populate',
]);
