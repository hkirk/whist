# --- !Ups

CREATE TABLE `groups_players` (
  `group_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  KEY `group_id` (`group_id`),
  KEY `player_id` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

# --- !Downs

DROP TABLE IF EXISTS `groups_players`;
