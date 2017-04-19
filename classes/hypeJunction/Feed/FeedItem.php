<?php

namespace hypeJunction\Feed;

use ElggComment;
use ElggFile;
use ElggRiverItem;
use InvalidParameterException;
use stdClass;

class FeedItem extends ElggRiverItem {

	protected $related_ids;

	public function __construct($object) {

		if (!$object instanceof stdClass) {
			throw new InvalidParameterException("Invalid input to \ElggRiverItem constructor");
		}

		if (isset($object->related_ids)) {
			$object->related_ids = array_unique(explode(',', $object->related_ids));
		} else {
			$object->related_ids = [];
		}

		// the casting is to support typed serialization like json
		$int_types = [
			'id',
			'story_guid',
			'owner_guid',
			'subject_guid',
			'object_guid',
			'target_guid',
			'annotation_id',
			'access_guid',
			'access_owner_guid',
			'access_id',
			'posted',
		];

		foreach ($object as $key => $value) {
			if (in_array($key, $int_types)) {
				$this->$key = (int) $value;
			} else {
				$this->$key = $value;
			}
		}
	}

	/**
	 * Returns related items in the roll
	 * @return FeedItem[]
	 */
	public function getRelatedItems() {
		$svc = FeedService::getInstance();
		return $svc->getTable()->getByIds($this->related_ids);
	}

	/**
	 * Map action type to what it should be
	 *
	 * @param ElggRiverItem $item River item
	 * @return string
	 */
	public static function getActionType(ElggRiverItem $item) {
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

		return $action;
	}

	/**
	 * Get object type key
	 * 
	 * @param ElggRiverItem $item River item
	 * @return string
	 */
	public static function getObjectTypeKey(\ElggRiverItem $item) {

		$object = $item->getObjectEntity();

		while ($object instanceof ElggComment) {
			$object = $object->getContainerEntity();
		}

		if (!$object) {
			return '';
		}
		
		$type = $object->getType();
		$subtype = $object->getSubtype() ?: 'default';

		$keys = [
			"$type:$subtype",
			"$type:default",
		];

		foreach ($keys as $key) {
			if (elgg_language_key_exists($key)) {
				break;
			}
		}

		return $key;
	}

	/**
	 * Get summary language key
	 *
	 * @param ElggRiverItem $item        River item
	 * @param bool          $action_only Ignore object type
	 * @return string
	 */
	public static function getSummaryKey(ElggRiverItem $item, $action_only = false) {

		$object = $item->getObjectEntity();

		$action = self::getActionType($item);

		$type = $object->getType();
		$subtype = $object->getSubtype() ?: 'default';

		if ($action_only) {
			$keys = [
				"river:$action:default",
				'river:default',
			];
		} else {
			$keys = [
				"river:$action:$type:$subtype",
				"river:$action:$type:default",
				"river:$action:$type",
				"river:$action:default",
				'river:default',
			];
		}

		foreach ($keys as $key) {
			if (elgg_language_key_exists($key)) {
				break;
			}
		}

		return elgg_trigger_plugin_hook('key:summary', 'river', ['item' => $item], $key);
	}

}
