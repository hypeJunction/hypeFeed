<?php

namespace hypeJunction\Feed;

class Menus {

	/**
	 * Setup menu
	 *
	 * @param string $hook   "register"
	 * @param string $type   "menu:river"
	 * @param array  $menu   Menu
	 * @param array  $params Hook parameters
	 * @return array
	 */
	public static function setupRiverMenu($hook, $type, $menu, $params) {

		$item = elgg_extract('item', $params);
		$related_items = $item->related_items;
		if (count($related_items) < 2) {
			return;
		}

		$menu[] = \ElggMenuItem::factory([
			'name' => 'related',
			'href' => current_page_url(),
			'link_class' => 'elgg-river-show-related',
			'text' => elgg_format_element('span', [
				'class' => 'elgg-river-badge',
			], count($related_items)),
			'title' => elgg_echo('feed:rollup:desc', [count($related_items)]),
			'priority' => 100,
			'deps' => ['river/elements/related'],
		]);

		return $menu;
	}
}
