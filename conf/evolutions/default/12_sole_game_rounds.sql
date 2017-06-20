# --- !Ups

CREATE TABLE `solo_game_rounds` (
  `game_round_id` int(10) unsigned NOT NULL,
  `solo_type` enum('solo','cleansolo','table','cleantable') COLLATE utf8_danish_ci NOT NULL,
  PRIMARY KEY (`game_round_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

# --- !Downs

DROP TABLE IF EXISTS `solo_game_rounds`;
