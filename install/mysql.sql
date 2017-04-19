CREATE TABLE IF NOT EXISTS `prefix_feeds` (
  `owner_guid` bigint(20) unsigned NOT NULL,
  `story_guid` bigint(20) unsigned NOT NULL,
  `id` bigint(20) unsigned NOT NULL,
  `posted` int(11) NOT NULL,
  KEY `owner_guid` (`owner_guid`),
  KEY `story_guid` (`story_guid`),
  KEY `id` (`id`),
  KEY `posted` (`posted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;