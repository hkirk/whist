# --- !Ups

CREATE TABLE `locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` tinytext COLLATE utf8_danish_ci NOT NULL,
  `current` bit(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `current` (`current`),
  KEY `name` (`name`(3))
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci;

# --- !Downs

DROP TABLE IF EXISTS `locations`;
