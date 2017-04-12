<?php

if (get_input('upgrade_completed')) {
	$factory = new ElggUpgrade();
	$upgrade = $factory->getUpgradeFromPath('admin/upgrades/feed/populate');
	if ($upgrade instanceof ElggUpgrade) {
		$upgrade->setCompleted();
	}
	return true;
}

$svc = hypeJunction\Feed\FeedService::getInstance();

$original_time = microtime(true);
$time_limit = 4;

$success_count = 0;
$error_count = 0;

$response = [];

while (microtime(true) - $original_time < $time_limit) {

	$dbprefix = elgg_get_config('dbprefix');
	$items = elgg_get_river([
		'wheres' => [
			"NOT EXISTS (SELECT 1 FROM {$dbprefix}feeds
				WHERE id = rv.id)",
		],
		'batch' => true,
		'limit' => 5,
	]);

	foreach ($items as $item) {
		if ($svc->getTable()->insert($item)) {
			$success_count++;
		} else {
			$error_count++;
		}
	}
	
}

if (elgg_is_xhr()) {
	$response['numSuccess'] = $success_count;
	$response['numErrors'] = $error_count;
	echo json_encode($response);
}
