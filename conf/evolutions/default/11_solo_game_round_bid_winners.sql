# --- !Ups

CREATE TABLE `solo_game_round_bid_winners` (
  `game_round_id` int(10) unsigned NOT NULL,
  `player_position` int(11) NOT NULL,
  `tricks` int(11) DEFAULT NULL,
  PRIMARY KEY (`game_round_id`,`player_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;


# --- !Downs

DROP TABLE IF EXISTS `solo_game_round_bid_winners`;
