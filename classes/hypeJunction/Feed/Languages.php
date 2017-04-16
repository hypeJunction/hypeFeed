<?php

namespace hypeJunction\Feed;

class Languages {

	/**
	 * Get summary language key
	 *
	 * @param \ElggRiverItem $item River item
	 * @return string
	 */
	public static function getSummaryKey(\ElggRiverItem $item) {

		$object = $item->getObjectEntity();
		
		$action = $item->action_type;
		if ($object instanceof ElggComment && $action == 'create') {
			$action = $object->getSubtype();
		} else if ($object instanceof ElggFile && $action == 'create') {
			$action = 'upload';
		}

		switch ($item->getView()) {
			case 'river/user/default/profileiconupdate' :
				$action = 'profileiconupdate';
				break;
			case 'river/user/default/profileupdate' :
				$action = 'profileupdate';
				break;
			case 'river/relationship/member/create' :
				$action = 'join';
				break;
			case 'river/relationship/friend/create' :
				$action = 'friend';
				break;
		}

		$type = $object->getType();
		$subtype = $object->getSubtype() ?: 'default';

		$keys = [
			"river:$action:$type:$subtype",
			"river:$action:$type:default",
			"river:$action:$type",
			"river:$action:default",
			'river:default',
		];

		foreach ($keys as $key) {
			if (elgg_language_key_exists($key)) {
				break;
			}
		}

		return elgg_trigger_plugin_hook('key:summary', 'river', ['item' => $item], $key);
	}

}
