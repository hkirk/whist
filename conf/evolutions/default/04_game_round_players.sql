# --- !Ups

CREATE TABLE `game_round_players` (
  `game_round_id` int(10) unsigned NOT NULL,
  `player_position` int(10) unsigned NOT NULL,
  `bye` tinyint(1) NOT NULL COMMENT 'Boolean (0/1)',
  `points` int(11) DEFAULT NULL,
  PRIMARY KEY (`game_round_id`,`player_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;


# -- !Downs

DROP TABLE IF EXISTS `game_round_players`;
