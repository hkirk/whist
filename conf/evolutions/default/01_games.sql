# --- !Ups

CREATE TABLE `games` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location_id` int(10) unsigned NOT NULL,
  `description` text COLLATE utf8_danish_ci NOT NULL,
  `attachments` set('sans','strongs','tips','halves') COLLATE utf8_danish_ci NOT NULL,
  `point_rules` set('reallybad','tips','solotricks') COLLATE utf8_danish_ci NOT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `started_at` (`started_at`),
  KEY `location_id` (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

# --- !Downs

DROP TABLE IF EXISTS `games`;
