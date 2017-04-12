<?php

$options = (array) elgg_extract('options', $vars, []);

$owner = elgg_extract('owner', $options);
$options['owner'] = false;

$options['relationship_guid'] = (int) $owner->guid;
$options['relationship'] = 'friend';

$site_guid = (int) elgg_get_site_entity()->guid;
$options['wheres'][] = "rv.owner_guid != $site_guid";

$options['no_results'] = elgg_echo('river:none');
$options['pagination'] = true;
$options['pagination_type'] = 'infinite';

$content = elgg_view('core/river/filter');

$svc = hypeJunction\Feed\FeedService::getInstance();
$content .= $svc->view($options);

?>
<div class="elgg-activity">
	<?= $content ?>
</div>
