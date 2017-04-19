<?php

/**
 * hypeFeed
 *
 * Improved activity stream experience
 * 
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2017, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

use hypeJunction\Feed\FeedService;
use hypeJunction\Feed\Menus;
use hypeJunction\Feed\Router;

elgg_register_event_handler('init', 'system', function() {

	elgg_register_page_handler('activity', [Router::class, 'handleActivityPage']);

	elgg_register_action('upgrade/feed/populate', __DIR__ . '/actions/upgrade/feed/populate.php', 'admin');

	elgg_register_event_handler('created', 'river', [FeedService::class, 'addRiverItem'], 999);

	elgg_register_event_handler('delete:after', 'river', [FeedService::class, 'removeRollup'], 999);
	elgg_register_event_handler('update', 'all', [FeedService::class, 'entityUpdateHandler'], 999);
	elgg_register_event_handler('delete', 'all', [FeedService::class, 'entityDeleteHandler'], 999);

	elgg_register_plugin_hook_handler('register', 'menu:river', [Menus::class, 'setupRiverMenu'], 999);

	elgg_extend_view('elgg.css', 'feed.css');
});

elgg_register_event_handler('upgrade', 'system', function() {
	if (!elgg_is_admin_logged_in()) {
		return;
	}
	require __DIR__ . '/lib/upgrades.php';
}, 1);