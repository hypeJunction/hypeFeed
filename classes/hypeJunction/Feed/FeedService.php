<?php

namespace hypeJunction\Feed;

use ElggData;
use ElggEntity;

/**
 * @access private
 */
class FeedService {

	/**
	 * @var self
	 */
	static $_instance;

	/**
	 * @var FeedTable
	 */
	private $table;

	/**
	 * Constructor 
	 * @param FeedTable $table DB table
	 */
	public function __construct(FeedTable $table) {
		$this->table = $table;
	}

	/**
	 * Returns a singleton
	 * @return self
	 */
	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self(new FeedTable());
		}
		return self::$_instance;
	}

	/**
	 * Returns DB table
	 * @return FeedTable
	 */
	public function getTable() {
		return $this->table;
	}

		/**
	 * View activity feed
	 *
	 * @param array $options Options
	 * @return string
	 *
	 * @note this is a modified version of elgg_list_river()
	 */
	public function view(array $options = []) {

		elgg_register_rss_link();

		$defaults = array(
			'offset' => (int) max(get_input('offset', 0), 0),
			'limit' => (int) max(get_input('limit', max(20, elgg_get_config('default_limit'))), 0),
			'pagination' => true,
			'list_class' => 'elgg-list-river',
			'no_results' => '',
		);

		$options = array_merge($defaults, $options);

		if (!$options["limit"] && !$options["offset"]) {
			// no need for pagination if listing is unlimited
			$options["pagination"] = false;
		}

		$options['count'] = true;
		$count = $this->getTable()->getAll($options);

		if ($count > 0) {
			$options['count'] = false;
			$items = $this->getTable()->getAll($options);
		} else {
			$items = array();
		}

		$options['count'] = $count;
		$options['items'] = $items;

		return elgg_view('page/components/list', $options);
	}

	/**
	 * Sync newly created river item
	 * 
	 * @param string        $event "created"
	 * @param string        $type  "river"
	 * @param ElggRiverItem $item  River item
	 * @return void
	 */
	public static function addRiverItem($event, $type, $item) {		
		$svc = self::getInstance();
		$svc->getTable()->insert($item);
	}

	/**
	 * Sync deleted river item
	 *
	 * @param string        $event "delete:after"
	 * @param string        $type  "river"
	 * @param ElggRiverItem $river River item
	 * @return void
	 */
	public static function removeRiverItem($event, $type, $river) {
		$svc = self::getInstance();
		$svc->getTable()->deleteByRiverId($item->id);
	}

	/**
	 * Remove rows from feed table when entity is deleted
	 * 
	 * @param string $event  "delete"
	 * @param string $type   "all"
	 * @param mixed  $object Deleted object
	 * @return void
	 */
	public static function entityDeleteHandler($event, $type, $object) {

		$svc = self::getInstance();
		if ($object instanceof ElggEntity) {
			$svc->getTable()->deleteByEntityGUID($object->guid);
		} else if ($object instanceof \ElggAnnotation) {
			$svc->getTable()->deleteByAnnotationID($object->id);
		}
	}

	/**
	 * Update access levels
	 *
	 * @param string $event  "update"
	 * @param string $type   "all"
	 * @param mixed  $object Updated object
	 * @return void
	 */
	public static function entityUpdateHandler($event, $type, $object) {
		$svc = self::getInstance();
		if ($object instanceof ElggEntity) {
			$attributes = $object->getOriginalAttributes();
			if (array_key_exists('access_id', $attributes) || array_key_exists('owner_guid', $attributes)) {
				$svc->getTable()->updateAccess($object);
			}
		} else if ($object instanceof ElggData) {
			$svc->getTable()->updateAccess($object);
		}
	}
	
}
