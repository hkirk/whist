-- MySQL dump 10.11
--
-- Host: localhost    Database: whist_calc
-- ------------------------------------------------------
-- Server version	5.0.51a-24+lenny5

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `game_players`
--

DROP TABLE IF EXISTS `game_players`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `game_players` (
  `game_id` int(10) unsigned NOT NULL,
  `player_position` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `total_points` int(11) NOT NULL,
  PRIMARY KEY  (`game_id`,`player_position`),
  UNIQUE KEY `game_id_player_id` (`game_id`,`player_id`),
  KEY `player_id` (`player_id`),
  CONSTRAINT `game_players_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`),
  CONSTRAINT `game_players_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `game_round_players`
--

DROP TABLE IF EXISTS `game_round_players`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `game_round_players` (
  `game_round_id` int(10) unsigned NOT NULL,
  `player_position` int(10) unsigned NOT NULL,
  `points` int(11) NOT NULL,
  PRIMARY KEY  (`game_round_id`,`player_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `game_rounds`
--

DROP TABLE IF EXISTS `game_rounds`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `game_rounds` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `game_id` int(10) unsigned NOT NULL,
  `round` int(10) unsigned NOT NULL,
  `bid_type` enum('normal','solo') collate utf8_danish_ci NOT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime default NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `game_id` (`game_id`,`round`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `games`
--

DROP TABLE IF EXISTS `games`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `games` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `location_id` int(10) unsigned NOT NULL,
  `description` text collate utf8_danish_ci NOT NULL,
  `attachments` set('sans','strongs','tips','halves') collate utf8_danish_ci NOT NULL,
  `point_rules` set('reallybad','tips','solotricks') collate utf8_danish_ci NOT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime default NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `started_at` (`started_at`),
  KEY `location_id` (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` tinytext collate utf8_danish_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `groups_locations`
--

DROP TABLE IF EXISTS `groups_locations`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `groups_locations` (
  `group_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  KEY `location_id` (`location_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci COMMENT='Group and location relations';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `groups_players`
--

DROP TABLE IF EXISTS `groups_players`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `groups_players` (
  `group_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  KEY `group_id` (`group_id`),
  KEY `player_id` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `locations` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` tinytext collate utf8_danish_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `normal_game_rounds`
--

DROP TABLE IF EXISTS `normal_game_rounds`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `normal_game_rounds` (
  `game_round_id` int(10) unsigned NOT NULL,
  `bid_winner_position` int(10) unsigned NOT NULL,
  `bid_winner_mate_position` int(10) unsigned default NULL,
  `bid_tricks` int(10) unsigned NOT NULL,
  `bid_attachment` enum('none','sans','tips','strongs','goods','halves') collate utf8_danish_ci NOT NULL,
  `tricks` int(10) unsigned default NULL,
  `tips` int(10) unsigned default NULL,
  PRIMARY KEY  (`game_round_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `players` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `nickname` tinytext collate utf8_danish_ci NOT NULL,
  `fullname` tinytext collate utf8_danish_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `solo_game_round_bid_winners`
--

DROP TABLE IF EXISTS `solo_game_round_bid_winners`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `solo_game_round_bid_winners` (
  `game_round_id` int(10) unsigned NOT NULL,
  `player_position` int(11) NOT NULL,
  `tricks` int(11) default NULL,
  PRIMARY KEY  (`game_round_id`,`player_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `solo_game_rounds`
--

DROP TABLE IF EXISTS `solo_game_rounds`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `solo_game_rounds` (
  `game_round_id` int(10) unsigned NOT NULL,
  `solo_type` enum('solo','cleansolo','table','cleantable') collate utf8_danish_ci NOT NULL,
  PRIMARY KEY  (`game_round_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-02-19 19:25:29
