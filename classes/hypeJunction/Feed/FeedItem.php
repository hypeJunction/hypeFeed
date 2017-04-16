<?php

namespace hypeJunction\Feed;

class FeedItem extends \ElggRiverItem {

	protected $related_ids;
	
	public function __construct($object) {
		if (!($object instanceof \stdClass)) {
			throw new \InvalidParameterException("Invalid input to \ElggRiverItem constructor");
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
	 *
	 * @param array $options Options
	 * @return ElggRiverItem[]|false
	 */
	public function getRelatedItems(array $options = []) {
		
		$options['distinct'] = true;
		$options['ids'] = $this->related_ids;
		$options['limit'] = 0;
		$options['order_by'] = "rv.posted DESC";

		return elgg_get_river($options);
	}

}
