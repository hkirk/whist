# --- !Ups

CREATE TABLE `game_rounds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(10) unsigned NOT NULL,
  `round` int(10) unsigned NOT NULL,
  `dealer_position` tinyint(3) unsigned NOT NULL,
  `bid_type` enum('normal','solo') COLLATE utf8_danish_ci NOT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `game_id_round` (`game_id`,`round`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;


# --- !Downs

DROP TABLE IF EXISTS `game_rounds`;