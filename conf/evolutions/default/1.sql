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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

CREATE TABLE `players` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` tinytext COLLATE utf8_danish_ci NOT NULL,
  `fullname` tinytext COLLATE utf8_danish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

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

CREATE TABLE `game_round_players` (
  `game_round_id` int(10) unsigned NOT NULL,
  `player_position` int(10) unsigned NOT NULL,
  `bye` tinyint(1) NOT NULL COMMENT 'Boolean (0/1)',
  `points` int(11) DEFAULT NULL,
  PRIMARY KEY (`game_round_id`,`player_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=312 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` tinytext COLLATE utf8_danish_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

CREATE TABLE `groups_locations` (
  `group_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  KEY `location_id` (`location_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci COMMENT='Group and location relations';

CREATE TABLE `groups_players` (
  `group_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  KEY `group_id` (`group_id`),
  KEY `player_id` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

CREATE TABLE `locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` tinytext COLLATE utf8_danish_ci NOT NULL,
  `current` bit(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `current` (`current`),
  KEY `name` (`name`(3))
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

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

CREATE TABLE `solo_game_round_bid_winners` (
  `game_round_id` int(10) unsigned NOT NULL,
  `player_position` int(11) NOT NULL,
  `tricks` int(11) DEFAULT NULL,
  PRIMARY KEY (`game_round_id`,`player_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

CREATE TABLE `solo_game_rounds` (
  `game_round_id` int(10) unsigned NOT NULL,
  `solo_type` enum('solo','cleansolo','table','cleantable') COLLATE utf8_danish_ci NOT NULL,
  PRIMARY KEY (`game_round_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

# --- !Downs

DROP TABLE IF EXISTS `game_players`;
DROP TABLE IF EXISTS `solo_game_rounds`;
DROP TABLE IF EXISTS `solo_game_round_bid_winners`;
DROP TABLE IF EXISTS `players`;
DROP TABLE IF EXISTS `normal_game_rounds`;
DROP TABLE IF EXISTS `locations`;
DROP TABLE IF EXISTS `groups_players`;
DROP TABLE IF EXISTS `groups`;
DROP TABLE IF EXISTS `groups_locations`;
DROP TABLE IF EXISTS `games`;
DROP TABLE IF EXISTS `game_round_players`;
DROP TABLE IF EXISTS `game_rounds`;
