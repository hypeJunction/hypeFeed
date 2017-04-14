<?php

namespace hypeJunction\Feed;

use ElggAnnotation;
use ElggBatch;
use ElggComment;
use ElggData;
use ElggEntity;
use ElggObject;
use ElggRiverItem;
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
		$this->row_callback = [$this, 'rowToFeedItem'];
	}

	/**
	 * Convert DB row to an instance of FeedItem
	 * 
	 * @param stdClass $row DB row
	 * @return FeedItem
	 */
	public function rowToFeedItem(stdClass $row) {
		return new FeedItem($row);
	}

	/**
	 * Get feed item by its ID
	 * 
	 * @param int $id ID
	 * @return FeedItem|false
	 */
	public function get($id) {
		$query = "
			SELECT * FROM {$this->table}
			WHERE id = :id
		";

		$params = [
			':id' => $id,
		];

		return get_data_row($query, $this->row_callback, $params) ?: false;
	}

	/**
	 * Get river items
	 *
	 * @param array $options Options
	 * @return \ElggRiverItem[]|false
	 *
	 * @note this is a modified version of elgg_get_river()
	 */
	public function getAll(array $options = []) {

		$defaults = array(
			'ids' => ELGG_ENTITIES_ANY_VALUE,
			'subject_guids' => ELGG_ENTITIES_ANY_VALUE,
			'object_guids' => ELGG_ENTITIES_ANY_VALUE,
			'target_guids' => ELGG_ENTITIES_ANY_VALUE,
			'annotation_ids' => ELGG_ENTITIES_ANY_VALUE,
			'action_types' => ELGG_ENTITIES_ANY_VALUE,
			'types' => ELGG_ENTITIES_ANY_VALUE,
			'subtypes' => ELGG_ENTITIES_ANY_VALUE,
			'type_subtype_pairs' => ELGG_ENTITIES_ANY_VALUE,
			'posted_time_lower' => ELGG_ENTITIES_ANY_VALUE,
			'posted_time_upper' => ELGG_ENTITIES_ANY_VALUE,
			'limit' => 20,
			'offset' => 0,
			'count' => false,
			'distinct' => true,
			'batch' => false,
			'batch_inc_offset' => true,
			'batch_size' => 25,
			'order_by' => 'rv.posted desc',
			'group_by' => ELGG_ENTITIES_ANY_VALUE,
			'wheres' => array(),
			'joins' => array(),
			'selects' => array(),
		);

		$options = array_merge($defaults, $options);

		$dbprefix = elgg_get_config('dbprefix');

		$type = elgg_extract('type', $options);
		unset($options['type']);

		$subtypes = (array) elgg_extract('subtype', $options, []);
		unset($options['subtype']);

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

		$options['wheres'][] = _elgg_get_access_where_sql([
			'table_alias' => 'rv',
			'owner_guid_column' => 'access_owner_guid',
			'guid_column' => 'access_guid',
		]);

		$relationship_guid = (int) elgg_extract('relationship_guid', $options);
		$relationship = elgg_extract('relationship', $options);
		$inverse_relationship = elgg_extract('inverse_relationship', $options);

		if ($relationship_guid) {
			if (!$inverse_relationship) {
				$rel_where = "guid_one = rv.owner_guid AND guid_two = $relationship_guid";
			} else {
				$rel_where = "guid_two = rv.owner_guid AND guid_one = $relationship_guid";
			}

			if ($relationship) {
				$relationship = sanitize_string($relationship);
				$rel_where .= " AND relationship = '$relationship'";
			}

			$options['wheres'][] = "
				EXISTS (SELECT 1
					FROM {$dbprefix}entity_relationships
					WHERE $rel_where)
				";

			$options['distinct'] = true;
		} else if ($owner) {
			$options['wheres'][] = "rv.owner_guid = $owner->guid";
		}

		$options['order_by'] = (array) $options['order_by'];

		if (empty($options['ids'])) {
			$interval = elgg_get_plugin_setting('aggregation_interval', 'hypeFeed', 'day');
			switch ($interval) {
				case 'hour' :
					$group_by_interval = "TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(rv.posted), NOW())";
					break;
				case 'three_hours' :
					$group_by_interval = "TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(rv.posted), NOW()) DIV 3";
					break;
				case 'six_hours' :
					$group_by_interval = "TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(rv.posted), NOW()) DIV 6";
					break;
				case 'twelve_hours' :
					$group_by_interval = "TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(rv.posted), NOW()) DIV 12";
					break;
				case 'day' :
				default :
					$group_by_interval = "TIMESTAMPDIFF(DAY, FROM_UNIXTIME(rv.posted), NOW())";
					break;
				case 'week' :
					$group_by_interval = "TIMESTAMPDIFF(WEEK, FROM_UNIXTIME(rv.posted), NOW())";
					break;
				case 'month' :
					$group_by_interval = "TIMESTAMPDIFF(MONTH, FROM_UNIXTIME(rv.posted), NOW())";
					break;
			}
			
			$options['group_by'] = "rv.story_guid, group_interval";
			$options['selects'][] = "GROUP_CONCAT(rv.id) AS related_ids, $group_by_interval AS group_interval";
		}

		if ($options['batch'] && !$options['count']) {
			$batch_size = $options['batch_size'];
			$batch_inc_offset = $options['batch_inc_offset'];

			// clean batch keys from $options.
			unset($options['batch'], $options['batch_size'], $options['batch_inc_offset']);

			return new ElggBatch('elgg_get_river', $options, null, $batch_size, $batch_inc_offset);
		}

		$singulars = array('id', 'subject_guid', 'object_guid', 'target_guid', 'annotation_id', 'action_type', 'type', 'subtype');
		$options = _elgg_normalize_plural_options_array($options, $singulars);

		$options['wheres'][] = _elgg_get_guid_based_where_sql('rv.id', $options['ids']);
		$options['wheres'][] = _elgg_get_guid_based_where_sql('rv.subject_guid', $options['subject_guids']);
		$options['wheres'][] = _elgg_get_guid_based_where_sql('rv.object_guid', $options['object_guids']);
		$options['wheres'][] = _elgg_get_guid_based_where_sql('rv.target_guid', $options['target_guids']);
		$options['wheres'][] = _elgg_get_guid_based_where_sql('rv.annotation_id', $options['annotation_ids']);
		$options['wheres'][] = _elgg_river_get_action_where_sql($options['action_types']);
		$options['wheres'][] = _elgg_get_river_type_subtype_where_sql('rv', $options['types'], $options['subtypes'], $options['type_subtype_pairs']);

		if ($options['posted_time_lower'] && is_int($options['posted_time_lower'])) {
			$options['wheres'][] = "rv.posted >= {$options['posted_time_lower']}";
		}

		if ($options['posted_time_upper'] && is_int($options['posted_time_upper'])) {
			$options['wheres'][] = "rv.posted <= {$options['posted_time_upper']}";
		}

		if (!access_get_show_hidden_status()) {
			$options['wheres'][] = "rv.enabled = 'yes'";
		}

		$options['wheres'] = array_unique(array_filter($options['wheres']));
		$options['joins'] = array_unique(array_filter($options['joins']));

		// evalutate selects
		if ($options['selects']) {
			$selects = '';
			foreach ($options['selects'] as $select) {
				$selects .= ", $select";
			}
		} else {
			$selects = '';
		}

		$distinct = $options['distinct'] ? "DISTINCT" : "";

		$query = "SELECT $distinct rv.*$selects FROM (SELECT * FROM {$dbprefix}feeds ORDER BY posted DESC) rv ";

		if ($options['joins']) {
			$query .= implode(' ', $options['joins']);
		}

		if ($options['wheres']) {
			$query .= ' WHERE ' . implode(' AND ', $options['wheres']);
		}

		$options['group_by'] = sanitise_string($options['group_by']);
		if ($options['group_by']) {
			$query .= " GROUP BY {$options['group_by']}";
		}

		if (!$options['count']) {
			$options['order_by'] = sanitise_string(implode(', ', $options['order_by']));
			$query .= " ORDER BY {$options['order_by']}";

			if ($options['limit']) {
				$limit = sanitise_int($options['limit']);
				$offset = sanitise_int($options['offset'], false);
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
			$object = \hypeJunction\Interactions\InteractionsService::getRiverObject($item);
		}

		$story = $object;
		while ($story instanceof ElggComment) {
			$story = $story->getContainerEntity();
		}

		$access_id = $story->access_id;
		$access_guid = $story->guid;
		$access_owner_guid = $story->owner_guid;

		$container = $object->getContainerEntity();
		while ($container instanceof ElggEntity) {
			$owner_guids[] = $container->guid;
			$owner_guids[] = $container->owner_guid;
			$container = $container->getContainerEntity();
		}

		if ($object instanceof ElggObject) {
			$topic = $object->getSubtype();
		} else {
			$topic = $object->getType();
		}

		$owner_guids = array_unique(array_filter($owner_guids));

		$query = "
			INSERT INTO {$this->table}
			SET owner_guid = :owner_guid,
				story_guid = :story_guid,
				subject_guid = :subject_guid,
				object_guid = :object_guid,
				target_guid = :target_guid,
				id = :id,
				access_guid = :access_guid,
				access_owner_guid = :access_owner_guid,
				access_id = :access_id,
				topic = :topic,
				type = :type,
				subtype = :subtype,
				action_type = :action_type,
				view = :view,
				annotation_id = :annotation_id,
				posted = :posted,
				enabled = :enabled
		";

		foreach ($owner_guids as $owner_guid) {
			$params = [
				':owner_guid' => (int) $owner_guid,
				':story_guid' => $story ? (int) $story->guid : 0,
				':object_guid' => (int) $item->object_guid,
				':subject_guid' => (int) $item->subject_guid,
				':target_guid' => (int) $item->target_guid,
				':action_type' => (string) $item->action_type,
				':annotation_id' => (int) $item->annotation_id,
				':posted' => (int) $item->posted,
				':enabled' => $item->enabled == 'yes' ? 'yes' : 'no',
				':id' => (int) $item->id,
				':access_guid' => (int) $access_guid,
				':access_owner_guid' => (int) $access_owner_guid,
				':access_id' => (int) $access_id,
				':topic' => (string) $topic,
				':type' => (string) $item->type,
				':subtype' => (string) $item->subtype,
				':view' => (string) $item->view,
				':annotation_id' => (int) $item->annotation_id,
			];

			insert_data($query, $params);
		}

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
	 * Update database row
	 *
	 * @param ElggData $object Object
	 * @return bool
	 */
	public function updateAccess(ElggData $object) {

		if ($object instanceof ElggEntity) {
			$params = [
				':access_guid' => (int) $object->guid,
				':access_owner_guid' => (int) $object->owner_guid,
				':access_id' => (int) $object->access_id,
			];
			$query = "
				UPDATE {$this->table}
				SET access_owner_guid = :access_owner_guid,
					access_id = :access_id
				WHERE access_guid = :access_guid
			";
			return update_data($query, $params);
		} else if ($object instanceof ElggAnnotation) {
			$params = [
				':annotation_id' => (int) $object->id,
				':access_guid' => (int) $object->entity_guid,
				':access_owner_guid' => (int) $object->owner_guid,
				':access_id' => (int) $object->access_id,
			];
			$query = "
				UPDATE {$this->table}
				SET access_guid = :access_guid,
				    access_owner_guid = :access_owner_guid,
					access_id = :access_id
				WHERE annotation_id = :annotation_id
			";
			return update_data($query, $params);
		} else {
			return true;
		}
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
				story_guid,
				object_guid,
				subject_guid,
				target_guid
			)
		";

		$params = [
			':guid' => (int) $guid,
		];

		return delete_data($query, $params);
	}

	/**
	 * Delete rows by annotation id
	 *
	 * @param int $annotation_id Annotation id
	 * @return bool
	 */
	public function deleteByAnnotationID($annotation_id) {

		$query = "
			DELETE FROM {$this->table}
			WHERE annotation_id = :annotation_id
		";

		$params = [
			':annotation_id' => (int) $annotation_id,
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
