<?php

namespace hypeJunction\Feed;

use ElggBatch;
use ElggComment;
use ElggEntity;
use ElggRiverItem;
use hypeJunction\Interactions\InteractionsService;
use stdClass;

/**
 * @access private
 */
class FeedTable {

	private $table;
	private $row_callback;

	/**
	 * Constructor
	 */
	public function __construct() {
		$dpbrefix = elgg_get_config('dbprefix');
		$this->table = "{$dpbrefix}feeds";
		$this->row_callback = [$this, 'rowToRollup'];
	}

	/**
	 * Convert DB row to an instance of FeedItem
	 * 
	 * @param stdClass $row DB row
	 * @return RollUp
	 */
	public function rowToRollup(stdClass $row) {
		return new RollUp($row);
	}

	public function getRiverQuery(array $options = []) {
		$defaults = array(
			'ids' => ELGG_ENTITIES_ANY_VALUE,
			'subject_guids' => ELGG_ENTITIES_ANY_VALUE,
			'object_guids' => ELGG_ENTITIES_ANY_VALUE,
			'target_guids' => ELGG_ENTITIES_ANY_VALUE,
			'annotation_ids' => ELGG_ENTITIES_ANY_VALUE,
			'action_types' => ELGG_ENTITIES_ANY_VALUE,
			'relationship' => null,
			'relationship_guid' => null,
			'inverse_relationship' => false,
			'types' => ELGG_ENTITIES_ANY_VALUE,
			'subtypes' => ELGG_ENTITIES_ANY_VALUE,
			'type_subtype_pairs' => ELGG_ENTITIES_ANY_VALUE,
			'posted_time_lower' => ELGG_ENTITIES_ANY_VALUE,
			'posted_time_upper' => ELGG_ENTITIES_ANY_VALUE,
			'count' => false,
			'distinct' => true,
			'order_by' => 'rv.posted desc',
			'group_by' => ELGG_ENTITIES_ANY_VALUE,
			'wheres' => array(),
			'joins' => array(),
		);

		$options = array_merge($defaults, $options);

		if ($options['batch'] && !$options['count']) {
			$batch_size = $options['batch_size'];
			$batch_inc_offset = $options['batch_inc_offset'];

			// clean batch keys from $options.
			unset($options['batch'], $options['batch_size'], $options['batch_inc_offset']);

			return new ElggBatch('elgg_get_river', $options, null, $batch_size, $batch_inc_offset);
		}

		$singulars = array('id', 'subject_guid', 'object_guid', 'target_guid', 'annotation_id', 'action_type', 'type', 'subtype');
		$options = _elgg_normalize_plural_options_array($options, $singulars);

		$wheres = $options['wheres'];

		$wheres[] = _elgg_get_guid_based_where_sql('rv.id', $options['ids']);
		$wheres[] = _elgg_get_guid_based_where_sql('rv.subject_guid', $options['subject_guids']);
		$wheres[] = _elgg_get_guid_based_where_sql('rv.object_guid', $options['object_guids']);
		$wheres[] = _elgg_get_guid_based_where_sql('rv.target_guid', $options['target_guids']);
		$wheres[] = _elgg_get_guid_based_where_sql('rv.annotation_id', $options['annotation_ids']);
		$wheres[] = _elgg_river_get_action_where_sql($options['action_types']);
		$wheres[] = _elgg_get_river_type_subtype_where_sql('rv', $options['types'], $options['subtypes'], $options['type_subtype_pairs']);

		if ($options['posted_time_lower'] && is_int($options['posted_time_lower'])) {
			$wheres[] = "rv.posted >= {$options['posted_time_lower']}";
		}

		if ($options['posted_time_upper'] && is_int($options['posted_time_upper'])) {
			$wheres[] = "rv.posted <= {$options['posted_time_upper']}";
		}

		if (!access_get_show_hidden_status()) {
			$wheres[] = "rv.enabled = 'yes'";
		}

		$dbprefix = elgg_get_config('dbprefix');

		// joins
		$joins = array();
		$joins[] = "JOIN {$dbprefix}entities oe ON rv.object_guid = oe.guid";

		// LEFT JOIN is used because all river items do not necessarily have target
		$joins[] = "LEFT JOIN {$dbprefix}entities te ON rv.target_guid = te.guid";

		// add optional joins
		$joins = array_merge($joins, $options['joins']);

		// see if any functions failed
		// remove empty strings on successful functions
		foreach ($wheres as $i => $where) {
			if ($where === false) {
				return false;
			} elseif (empty($where)) {
				unset($wheres[$i]);
			}
		}

		$dbprefix = elgg_get_config('dbprefix');

		// remove identical where clauses
		$wheres = array_unique($wheres);

		$query = "SELECT rv.* FROM {$dbprefix}river rv ";

		// add joins
		foreach ($joins as $j) {
			$query .= " $j ";
		}

		// add wheres
		$query .= ' WHERE ';

		foreach ($wheres as $w) {
			$query .= " $w AND ";
		}

		// Make sure that user has access to all the entities referenced by each river item
		$object_access_where = _elgg_get_access_where_sql(array('table_alias' => 'oe'));
		$target_access_where = _elgg_get_access_where_sql(array('table_alias' => 'te'));

		// We use LEFT JOIN with entities table but the WHERE clauses are used
		// regardless if a JOIN is successfully made. The "te.guid IS NULL" is
		// needed because of this.
		$query .= "$object_access_where AND ($target_access_where OR te.guid IS NULL) ";

		$options['group_by'] = sanitise_string($options['group_by']);
		if ($options['group_by']) {
			$query .= " GROUP BY {$options['group_by']}";
		}

		$options['order_by'] = sanitise_string($options['order_by']);
		$query .= " ORDER BY {$options['order_by']}";

		return $query;
	}

	/**
	 * Get river items
	 *
	 * @param array $options Options
	 * @return ElggRiverItem[]|ElggBatch
	 */
	public function getAll(array $options = []) {

		$dbprefix = elgg_get_config('dbprefix');

		$count = (bool) elgg_extract('count', $options, false);
		$batch = elgg_extract('batch', $options, false);
		$batch_inc_offset = elgg_extract('batch_inc_offset', $options, true);
		$batch_size = elgg_extract('batch_size', $options, 25);
		unset($options['count'], $options['batch'], $options['batch_inc_offset'], $options['batch_size']);

		if ($batch && !$count) {
			return new ElggBatch([$this, 'getAll'], $options, null, $batch_size, $batch_inc_offset);
		}

		$wheres = [];
		$joins = [];
		$selects = [];
		$group_by = [];
		
		$limit = (int) elgg_extract('limit', $options, 20);
		$offset = (int) elgg_extract('offset', $options, 0);
		unset($options['limit'], $options['offset']);

		$type = elgg_extract('type', $options);
		$subtypes = (array) elgg_extract('subtype', $options, []);
		unset($options['type'], $options['subtype']);

		if (in_array($type, ['object', 'group', 'user'])) {
			$options['types'] = $type;

			$registered_subtypes = get_registered_entity_types($type);
			foreach ($subtypes as $subtype) {
				if (empty($registered_subtypes)) {
					continue;
				}
				if (!in_array($subtype, $registered_subtypes)) {
					continue;
				}
				$options['subtypes'][] = $subtype;
			}
		}

		$owner = elgg_extract('owner', $options);
		unset($options['owner']);
		if (!isset($owner)) {
			$owner = elgg_get_site_entity();
		}

		$relationship_guid = (int) elgg_extract('relationship_guid', $options);
		$relationship = elgg_extract('relationship', $options);
		$inverse_relationship = elgg_extract('inverse_relationship', $options);
		unset($options['relationship_guid'], $options['relationship'], $options['inverse_relationship']);

		if ($relationship_guid) {
			if (!$inverse_relationship) {
				$rel_where = "guid_one = feeds.owner_guid AND guid_two = $relationship_guid";
			} else {
				$rel_where = "guid_two = feeds.owner_guid AND guid_one = $relationship_guid";
			}

			if ($relationship) {
				$relationships = (array) $relationship;
				foreach ($relationships as &$relationship) {
					$relationship = "'" . sanitize_string($relationship) . "'";
				}

				$in_relationship = implode(',', $relationships);
				$rel_where .= " AND relationship IN ($in_relationship)";
			}

			$wheres[] = "EXISTS (SELECT 1 FROM {$dbprefix}entity_relationships WHERE $rel_where) AND feeds.owner_guid != 1";
		} else if ($owner) {
			$wheres[] = "feeds.owner_guid = $owner->guid";
		}

		$interval = elgg_get_plugin_setting('aggregation_interval', 'hypeFeed', 'day');
		switch ($interval) {
			case 'hour' :
				$group_by_interval = "TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(feeds.posted), NOW())";
				break;
			case 'three_hours' :
				$group_by_interval = "TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(feeds.posted), NOW()) DIV 3";
				break;
			case 'six_hours' :
				$group_by_interval = "TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(feeds.posted), NOW()) DIV 6";
				break;
			case 'twelve_hours' :
				$group_by_interval = "TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(feeds.posted), NOW()) DIV 12";
				break;
			case 'day' :
			default :
				$group_by_interval = "TIMESTAMPDIFF(DAY, FROM_UNIXTIME(feeds.posted), NOW())";
				break;
			case 'week' :
				$group_by_interval = "TIMESTAMPDIFF(WEEK, FROM_UNIXTIME(feeds.posted), NOW())";
				break;
			case 'month' :
				$group_by_interval = "TIMESTAMPDIFF(MONTH, FROM_UNIXTIME(feeds.posted), NOW())";
				break;
		}

		$selects[] = "DISTINCT river.*";
		$selects[] = "feeds.story_guid";
		$selects[] = "feeds.owner_guid";
		
		$joins[] = "JOIN {$dbprefix}feeds AS feeds ON river.id = feeds.id";

		$selects[] = 'GROUP_CONCAT(feeds.id) AS related_ids';
		$group_by[] = 'feeds.story_guid';

		$selects[] = "$group_by_interval AS group_interval";
		$group_by[] = 'group_interval';

		$river_select = $this->getRiverQuery($options);

		$wheres = implode(' AND ', $wheres);
		$joins = implode(' ', $joins);
		$selects = implode(', ', $selects);
		$group_by = implode(', ', $group_by);

		$query = "
			SELECT $selects
			FROM ($river_select) river
			$joins
			WHERE $wheres
			GROUP BY $group_by
		";


		if (!$count) {
			$query .= " ORDER BY feeds.posted DESC";
			if ($limit) {
				$query .= " LIMIT $offset, $limit";
			}
			
			$river_items = get_data($query, $this->row_callback);
			_elgg_prefetch_river_entities($river_items);

			return $river_items;
		} else {
			$count_query = "SELECT COUNT(*) as total FROM ($query) groups";
			
			$total = get_data_row($count_query);
			return (int) $total->total;
		}
	}

	/**
	 * Insert row
	 *
	 * @param ElggRiverItem $item River item
	 * @return int|false
	 */
	public function insert(ElggRiverItem $item) {

		$ia = elgg_set_ignore_access(true);

		$site = elgg_get_site_entity();

		// Add this item to all feeds
		$owner_guids = [
			$site->guid,
			$item->subject_guid,
			$item->object_guid,
			$item->target_guid,
		];

		$subject = $item->getSubjectEntity();
		$target = $item->getTargetEntity();

		$annotation = $item->getAnnotation();
		if ($annotation) {
			$owner_guids[] = $annotation->owner_guid;
			$owner_guids[] = $annotation->entity_guid;
			$object = $annotation->getEntity();
		} else {
			$object = InteractionsService::getRiverObject($item);
		}

		$story = $object;
		while ($story instanceof ElggComment) {
			$story = $story->getContainerEntity();
		}

		if (!$story instanceof ElggEntity) {
			$story = $object;
		}

		$container = $object->getContainerEntity();
		while ($container instanceof ElggEntity) {
			$owner_guids[] = $container->guid;
			$owner_guids[] = $container->owner_guid;
			$container = $container->getContainerEntity();
		}

		$owner_guids = array_unique(array_filter($owner_guids));

		$query = "
			INSERT INTO {$this->table}
			SET owner_guid = :owner_guid,
				story_guid = :story_guid,
				id = :id,
				posted = :posted
		";

		foreach ($owner_guids as $owner_guid) {
			$params = [
				':owner_guid' => (int) $owner_guid,
				':story_guid' => $story ? (int) $story->guid : 0,
				':id' => (int) $item->id,
				':posted' => (int) $item->posted,
			];

			insert_data($query, $params);
		}

		elgg_set_ignore_access($ia);

		return true;
	}

	/**
	 * Update database row
	 *
	 * @param ElggRiverItem $item River item
	 * @return bool
	 */
	public function update(ElggRiverItem $item) {
		$this->deleteByRiverId($item->id);
		return $this->insert($item);
	}

	/**
	 * Delete row
	 *
	 * @param int $id ID
	 * @return bool
	 */
	public function delete($id) {

		$query = "
			DELETE FROM {$this->table}
			WHERE id = :id
		";

		$params = [
			':id' => (int) $id,
		];

		return delete_data($query, $params);
	}

	/**
	 * Delete rows by story or owner guid
	 *
	 * @param int $guid GUID
	 * @return bool
	 */
	public function deleteByEntityGUID($guid) {

		$query = "
			DELETE FROM {$this->table}
			WHERE :guid IN (
				owner_guid,
				story_guid
			)
		";

		$params = [
			':guid' => (int) $guid,
		];

		return delete_data($query, $params);
	}

	/**
	 * Delete rows by river id
	 *
	 * @param int $id River id
	 * @return bool
	 */
	public function deleteByRiverId($id) {

		$query = "
			DELETE FROM {$this->table}
			WHERE id = :id
		";

		$params = [
			':id' => (int) $id,
		];

		return delete_data($query, $params);
	}

}
