# --- !Ups

CREATE TABLE `game_players` (
  `game_id` int(10) unsigned NOT NULL,
  `player_position` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `total_points` int(11) NOT NULL,
  PRIMARY KEY (`game_id`,`player_position`),
  UNIQUE KEY `game_id_player_id` (`game_id`,`player_id`),
  KEY `player_id` (`player_id`),
  CONSTRAINT `game_players_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`),
  CONSTRAINT `game_players_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

# --- !Downs

DROP TABLE IF EXISTS `game_players`;
