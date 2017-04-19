<?php

use hypeJunction\Feed\FeedService;

$options = (array) elgg_extract('options', $vars, []);

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
