-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 18. Jan 2021 um 15:34
-- Server-Version: 10.5.8-MariaDB-3
-- PHP-Version: 8.0.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `blog_new`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `columns`
--

CREATE TABLE `columns` (
  `column_id` varchar(8) NOT NULL,
  `column_longid` varchar(60) NOT NULL,
  `column_name` varchar(30) NOT NULL,
  `column_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `events`
--

CREATE TABLE `events` (
  `event_id` varchar(8) NOT NULL,
  `event_longid` varchar(60) NOT NULL,
  `event_title` varchar(50) NOT NULL,
  `event_organisation` varchar(40) NOT NULL,
  `event_timestamp` datetime NOT NULL,
  `event_location` varchar(60) DEFAULT NULL,
  `event_description` text DEFAULT NULL,
  `event_cancelled` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groups`
--

CREATE TABLE `groups` (
  `group_id` varchar(8) NOT NULL,
  `group_longid` varchar(60) NOT NULL,
  `group_name` varchar(30) NOT NULL,
  `group_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `imagefiles`
--

CREATE TABLE `imagefiles` (
  `imagefile_id` varchar(8) NOT NULL,
  `imagefile_data` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `images`
--

CREATE TABLE `images` (
  `image_id` varchar(8) NOT NULL,
  `image_longid` varchar(60) NOT NULL,
  `image_extension` varchar(4) NOT NULL,
  `image_description` varchar(100) DEFAULT NULL,
  `image_copyright` varchar(100) DEFAULT NULL,
  `image_sizes` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pages`
--

CREATE TABLE `pages` (
  `page_id` varchar(8) NOT NULL,
  `page_longid` varchar(60) NOT NULL,
  `page_title` varchar(60) NOT NULL,
  `page_content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `persongrouprelations`
--

CREATE TABLE `persongrouprelations` (
  `persongrouprelation_id` varchar(8) NOT NULL,
  `persongrouprelation_person_id` varchar(8) NOT NULL,
  `persongrouprelation_group_id` varchar(8) NOT NULL,
  `persongrouprelation_number` int(11) DEFAULT NULL,
  `persongrouprelation_role` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `persons`
--

CREATE TABLE `persons` (
  `person_id` varchar(8) NOT NULL,
  `person_longid` varchar(60) NOT NULL,
  `person_name` varchar(50) NOT NULL,
  `person_image_id` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `postcolumnrelations`
--

CREATE TABLE `postcolumnrelations` (
  `postcolumnrelation_id` varchar(8) NOT NULL,
  `postcolumnrelation_post_id` varchar(8) NOT NULL,
  `postcolumnrelation_column_id` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `posts`
--

CREATE TABLE `posts` (
  `post_id` varchar(8) NOT NULL,
  `post_longid` varchar(60) NOT NULL,
  `post_overline` varchar(25) DEFAULT NULL,
  `post_headline` varchar(60) NOT NULL,
  `post_subline` varchar(40) DEFAULT NULL,
  `post_teaser` text DEFAULT NULL,
  `post_author` varchar(50) NOT NULL,
  `post_timestamp` datetime NOT NULL,
  `post_image_id` varchar(8) DEFAULT NULL,
  `post_content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `columns`
--
ALTER TABLE `columns`
  ADD PRIMARY KEY (`column_id`),
  ADD UNIQUE KEY `column_longid` (`column_longid`);

--
-- Indizes für die Tabelle `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD UNIQUE KEY `event_longid` (`event_longid`);

--
-- Indizes für die Tabelle `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`group_id`),
  ADD UNIQUE KEY `group_longid` (`group_longid`);

--
-- Indizes für die Tabelle `imagefiles`
--
ALTER TABLE `imagefiles`
  ADD UNIQUE KEY `imagefile_id` (`imagefile_id`);

--
-- Indizes für die Tabelle `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`image_id`),
  ADD UNIQUE KEY `image_longid` (`image_longid`);

--
-- Indizes für die Tabelle `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`page_id`),
  ADD UNIQUE KEY `page_longid` (`page_longid`);

--
-- Indizes für die Tabelle `persongrouprelations`
--
ALTER TABLE `persongrouprelations`
  ADD PRIMARY KEY (`persongrouprelation_id`),
  ADD KEY `persongrouprelation_person_id` (`persongrouprelation_person_id`),
  ADD KEY `persongrouprelation_group_id` (`persongrouprelation_group_id`);

--
-- Indizes für die Tabelle `persons`
--
ALTER TABLE `persons`
  ADD PRIMARY KEY (`person_id`),
  ADD UNIQUE KEY `person_longid` (`person_longid`),
  ADD KEY `person_image_id` (`person_image_id`);

--
-- Indizes für die Tabelle `postcolumnrelations`
--
ALTER TABLE `postcolumnrelations`
  ADD PRIMARY KEY (`postcolumnrelation_id`),
  ADD KEY `postcolumnrelation_post_id` (`postcolumnrelation_post_id`),
  ADD KEY `postcolumnrelation_column_id` (`postcolumnrelation_column_id`);

--
-- Indizes für die Tabelle `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD UNIQUE KEY `post_longid` (`post_longid`),
  ADD KEY `post_image_id` (`post_image_id`);

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `imagefiles`
--
ALTER TABLE `imagefiles`
  ADD CONSTRAINT `imagefiles_ibfk_1` FOREIGN KEY (`imagefile_id`) REFERENCES `images` (`image_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `persongrouprelations`
--
ALTER TABLE `persongrouprelations`
  ADD CONSTRAINT `persongrouprelations_ibfk_1` FOREIGN KEY (`persongrouprelation_person_id`) REFERENCES `persons` (`person_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `persongrouprelations_ibfk_2` FOREIGN KEY (`persongrouprelation_group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `persons`
--
ALTER TABLE `persons`
  ADD CONSTRAINT `persons_ibfk_1` FOREIGN KEY (`person_image_id`) REFERENCES `images` (`image_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints der Tabelle `postcolumnrelations`
--
ALTER TABLE `postcolumnrelations`
  ADD CONSTRAINT `postcolumnrelations_ibfk_1` FOREIGN KEY (`postcolumnrelation_post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `postcolumnrelations_ibfk_2` FOREIGN KEY (`postcolumnrelation_column_id`) REFERENCES `columns` (`column_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`post_image_id`) REFERENCES `images` (`image_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
