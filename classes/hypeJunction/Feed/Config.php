<?php

namespace hypeJunction\Feed;

use ElggMenuItem;
use ElggUser;

class Config {

	/**
	 * Get activity page tab configuration
	 *
	 * @param bool $active Only return active tabs
	 * @return ElggMenuItem[]
	 */
	public static function getTabs($active = false) {

		$tabs = [];
		if (elgg_is_logged_in()) {
			$defaults = [
				'all' => 1,
				'mine' => 2,
				'friends' => 3,
				'network' => 0,
			];
		} else {
			$defaults = [
				'all' => 1,
			];
		}

		foreach ($defaults as $tab => $default) {
			$position = elgg_get_plugin_setting("tab:$tab", 'hypeFeed', $default);
			if ($active && !$position) {
				continue;
			}

			$tabs[$tab] = self::getTab($tab, $position);
		}

		uasort($tabs, function(ElggMenuItem $elem1, ElggMenuItem $elem2) {
			if (!$elem2->getPriority()) {
				// push 0 priority to the end
				return -1;
			}
			if ($elem1->getPriority() == $elem2->getPriority()) {
				return 0;
			}
			if ($elem1->getPriority() < $elem2->getPriority()) {
				return -1;
			}
			return 1;
		});

		return $tabs;
	}

	/**
	 * Returns a tab as a menu item
	 * 
	 * @param array    $tab      Tab
	 * @param int      $position Tab position/priority
	 * @param ElggUser $target   Target user (default: logged in user)
	 * @return ElggMenuItem
	 */
	public static function getTab($tab = 'all', $position = 1, $target = null) {

		if (!isset($target)) {
			$target = elgg_get_logged_in_user_entity();
		}

		$label = elgg_echo($tab);
		$href = "activity/$tab";

		if ($target) {
			switch ($tab) {
				case 'owner' :
					$label = elgg_echo('mine');
					$href = "activity/owner/$target->username";
					break;

				case 'network' :
					$label = elgg_echo('activity:network');
					$href = "activity/network/$target->username";
					break;

				case 'friends' :
					$href = "activity/friends/$target->username";
					break;
			}
		}

		return ElggMenuItem::factory([
					'name' => $tab,
					'text' => $label,
					'href' => $href,
					'priority' => $position,
		]);
	}

}
