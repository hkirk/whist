# --- !Ups

CREATE TABLE `normal_game_rounds` (
  `game_round_id` int(10) unsigned NOT NULL,
  `bid_winner_position` int(10) unsigned NOT NULL,
  `bid_winner_mate_position` int(10) unsigned DEFAULT NULL,
  `bid_tricks` int(10) unsigned NOT NULL,
  `bid_attachment` enum('none','sans','tips','strongs','goods','halves') COLLATE utf8_danish_ci NOT NULL,
  `tricks` int(10) unsigned DEFAULT NULL,
  `tips` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`game_round_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

# --- !Downs

DROP TABLE IF EXISTS `normal_game_rounds`;