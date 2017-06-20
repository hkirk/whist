# --- !Ups

CREATE TABLE `groups_locations` (
  `group_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  KEY `location_id` (`location_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci COMMENT='Group and location relations';

# --- !Downs

DROP TABLE IF EXISTS `groups_locations`;
