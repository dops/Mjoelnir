-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 30. Jun 2014 um 10:54
-- Server Version: 5.5.37
-- PHP-Version: 5.4.4-14+deb7u11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `mjoelnir`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `login_hash` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `time_insert` int(10) unsigned NOT NULL,
  `time_update` int(10) unsigned DEFAULT NULL,
  `insert_user_id` int(10) unsigned NOT NULL,
  `update_user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `user`
--

INSERT INTO `user` (`user_id`, `first_name`, `last_name`, `email`, `password`, `login_hash`, `active`, `time_insert`, `time_update`, `insert_user_id`, `update_user_id`) VALUES
(1, 'Michael', 'Streb', 'michael.streb@bs-trust.com', 'af25cd2c674bc079d11bd4701ae78938', NULL, 1, 1404118391, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_role`
--

CREATE TABLE IF NOT EXISTS `user_role` (
  `user_role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `time_insert` int(10) unsigned NOT NULL,
  `time_update` int(10) unsigned DEFAULT NULL,
  `insert_user_id` int(10) unsigned NOT NULL,
  `update_user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `user_role`
--

INSERT INTO `user_role` (`user_role_id`, `name`, `time_insert`, `time_update`, `insert_user_id`, `update_user_id`) VALUES
(1, 'Admin', 1404118408, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_role_permission`
--

CREATE TABLE IF NOT EXISTS `user_role_permission` (
  `user_role_permission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_role_id` int(10) unsigned NOT NULL,
  `application` varchar(50) NOT NULL,
  `controller` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `allow` tinyint(1) NOT NULL,
  `time_insert` int(10) unsigned NOT NULL,
  `time_update` int(10) unsigned DEFAULT NULL,
  `insert_user_id` int(10) unsigned NOT NULL,
  `update_user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_role_permission_id`),
  UNIQUE KEY `permission` (`application`,`controller`,`action`),
  KEY `user_role_id` (`user_role_id`),
  KEY `application` (`application`),
  KEY `controller` (`controller`),
  KEY `action` (`action`),
  KEY `allow` (`allow`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_user_role`
--

CREATE TABLE IF NOT EXISTS `user_user_role` (
  `user_user_role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `user_role_id` int(10) unsigned NOT NULL,
  `time_insert` int(10) unsigned NOT NULL,
  `time_update` int(10) unsigned DEFAULT NULL,
  `insert_user_id` int(10) unsigned NOT NULL,
  `update_user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_user_role_id`),
  UNIQUE KEY `user_role` (`user_id`,`user_role_id`),
  KEY `user_id` (`user_id`),
  KEY `user_role_id` (`user_role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `user_user_role`
--

INSERT INTO `user_user_role` (`user_user_role_id`, `user_id`, `user_role_id`, `time_insert`, `time_update`, `insert_user_id`, `update_user_id`) VALUES
(1, 1, 1, 1404118423, NULL, 0, NULL);

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `user_role_permission`
--
ALTER TABLE `user_role_permission`
  ADD CONSTRAINT `user_role_permission_ibfk_1` FOREIGN KEY (`user_role_id`) REFERENCES `user_role` (`user_role_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `user_user_role`
--
ALTER TABLE `user_user_role`
  ADD CONSTRAINT `user_user_role_ibfk_2` FOREIGN KEY (`user_role_id`) REFERENCES `user_role` (`user_role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_user_role_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

