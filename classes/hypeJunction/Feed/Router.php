<?php

namespace hypeJunction\Feed;

class Router {

	/**
	 * Page handler for activity
	 *
	 * @param array $segments URL segments
	 * @return \Elgg\Http\ResponseBuilder
	 * @access private
	 */
	public static function handleActivityPage($segments) {

		$page = array_shift($segments);

		switch ($page) {
			default :
			case 'all' :
				echo elgg_view_resource('activity/all');
				return true;

			case 'owner' :
			case 'mine' :
				$username = array_shift($segments);
				echo elgg_view_resource('activity/owner', [
					'username' => $username,
				]);
				return true;

			case 'friends' :
				$username = array_shift($segments);
				echo elgg_view_resource('activity/friends', [
					'username' => $username,
				]);
				return true;

			case 'network' :
				$username = array_shift($segments);
				echo elgg_view_resource('activity/network', [
					'username' => $username,
				]);
				return true;

			case 'group' :
				$guid = array_shift($segments);
				echo elgg_view_resource('activity/group', [
					'guid' => $guid,
				]);
				return true;
		}

		return false;
	}

}
