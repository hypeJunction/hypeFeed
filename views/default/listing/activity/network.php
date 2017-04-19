<?php

use hypeJunction\Feed\FeedService;

$options = (array) elgg_extract('options', $vars, []);

$owner = elgg_extract('owner', $options);
$options['owner'] = false;

$owner_guid = (int) $owner->guid;

$dbprefix = elgg_get_config('dbprefix');
$options['relationship'] = ['friend', 'member'];
$options['relationship_guid'] = $owner_guid;
$options['inverse_relationship'] = false;

$options['no_results'] = elgg_echo('river:none');
$options['pagination'] = true;
$options['pagination_type'] = 'infinite';

$content = elgg_view('core/river/filter');

$svc = FeedService::getInstance();
$content .= $svc->view($options);

?>
<div class="elgg-activity">
	<?= $content ?>
</div>
