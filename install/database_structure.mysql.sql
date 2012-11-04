-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 04, 2012 at 05:45 PM
-- Server version: 5.0.51
-- PHP Version: 5.3.3-7+squeeze9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `whist_calc`
--

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE IF NOT EXISTS `games` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `location` tinytext collate utf8_danish_ci NOT NULL,
  `description` text collate utf8_danish_ci NOT NULL,
  `attachments` set('sans','strongs','tips','halves') collate utf8_danish_ci NOT NULL,
  `point_rules` set('reallybad','tips','solotricks') collate utf8_danish_ci NOT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime default NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `started_at` (`started_at`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci AUTO_INCREMENT=102 ;

-- --------------------------------------------------------

--
-- Table structure for table `game_players`
--

CREATE TABLE IF NOT EXISTS `game_players` (
  `game_id` int(10) unsigned NOT NULL,
  `player_position` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `total_points` int(11) NOT NULL,
  PRIMARY KEY  (`game_id`,`player_position`),
  UNIQUE KEY `game_id_player_id` (`game_id`,`player_id`),
  KEY `player_id` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `game_rounds`
--

CREATE TABLE IF NOT EXISTS `game_rounds` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `game_id` int(10) unsigned NOT NULL,
  `round` int(10) unsigned NOT NULL,
  `bid_type` enum('normal','solo') collate utf8_danish_ci NOT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime default NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `game_id` (`game_id`,`round`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci AUTO_INCREMENT=115 ;

-- --------------------------------------------------------

--
-- Table structure for table `game_round_players`
--

CREATE TABLE IF NOT EXISTS `game_round_players` (
  `game_round_id` int(10) unsigned NOT NULL,
  `player_position` int(10) unsigned NOT NULL,
  `points` int(11) NOT NULL,
  PRIMARY KEY  (`game_round_id`,`player_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` tinytext collate utf8_danish_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `groups_players`
--

CREATE TABLE IF NOT EXISTS `groups_players` (
  `group_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `normal_game_rounds`
--

CREATE TABLE IF NOT EXISTS `normal_game_rounds` (
  `game_round_id` int(10) unsigned NOT NULL,
  `bid_winner_position` int(10) unsigned NOT NULL,
  `bid_winner_mate_position` int(10) unsigned default NULL,
  `bid_tricks` int(10) unsigned NOT NULL,
  `bid_attachment` enum('none','sans','tips','strongs','goods','halves') collate utf8_danish_ci NOT NULL,
  `tricks` int(10) unsigned default NULL,
  `tips` int(10) unsigned default NULL,
  PRIMARY KEY  (`game_round_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `nickname` tinytext collate utf8_danish_ci NOT NULL,
  `fullname` tinytext collate utf8_danish_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `solo_game_rounds`
--

CREATE TABLE IF NOT EXISTS `solo_game_rounds` (
  `game_round_id` int(10) unsigned NOT NULL,
  `solo_type` enum('solo','cleansolo','table','cleantable') collate utf8_danish_ci NOT NULL,
  PRIMARY KEY  (`game_round_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `solo_game_round_bid_winners`
--

CREATE TABLE IF NOT EXISTS `solo_game_round_bid_winners` (
  `game_round_id` int(10) unsigned NOT NULL,
  `player_position` int(11) NOT NULL,
  `tricks` int(11) default NULL,
  PRIMARY KEY  (`game_round_id`,`player_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `game_players`
--
ALTER TABLE `game_players`
  ADD CONSTRAINT `game_players_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`),
  ADD CONSTRAINT `game_players_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`);
