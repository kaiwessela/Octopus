-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 26. Aug 2020 um 01:56
-- Server-Version: 10.3.23-MariaDB-1
-- PHP-Version: 7.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `blog`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `events`
--

CREATE TABLE `events` (
  `event_id` varchar(8) NOT NULL,
  `event_longid` varchar(128) NOT NULL,
  `event_title` varchar(64) NOT NULL,
  `event_organisation` varchar(64) NOT NULL,
  `event_timestamp` bigint(20) NOT NULL,
  `event_location` varchar(128) DEFAULT NULL,
  `event_description` text DEFAULT NULL,
  `event_cancelled` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `images`
--

CREATE TABLE `images` (
  `image_id` varchar(8) NOT NULL,
  `image_longid` varchar(128) NOT NULL,
  `image_extension` varchar(4) NOT NULL,
  `image_description` varchar(256) DEFAULT NULL,
  `image_copyright` varchar(256) NOT NULL,
  `image_sizes` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `persons`
--

CREATE TABLE `persons` (
  `person_id` varchar(8) NOT NULL,
  `person_longid` varchar(128) NOT NULL,
  `person_name` varchar(64) NOT NULL,
  `person_image_id` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `posts`
--

CREATE TABLE `posts` (
  `post_id` varchar(8) NOT NULL,
  `post_longid` varchar(128) NOT NULL,
  `post_overline` varchar(64) DEFAULT NULL,
  `post_headline` varchar(256) NOT NULL,
  `post_subline` varchar(256) DEFAULT NULL,
  `post_teaser` text DEFAULT NULL,
  `post_author` varchar(128) NOT NULL,
  `post_timestamp` bigint(20) NOT NULL,
  `post_image_id` varchar(8) DEFAULT NULL,
  `post_content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD UNIQUE KEY `event_longid` (`event_longid`);

--
-- Indizes für die Tabelle `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`image_id`),
  ADD UNIQUE KEY `image_longid` (`image_longid`);

--
-- Indizes für die Tabelle `persons`
--
ALTER TABLE `persons`
  ADD PRIMARY KEY (`person_id`),
  ADD UNIQUE KEY `person_longid` (`person_longid`);

--
-- Indizes für die Tabelle `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD UNIQUE KEY `post_longid` (`post_longid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
