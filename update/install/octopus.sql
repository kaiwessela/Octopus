-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 21. Mrz 2022 um 21:05
-- Server-Version: 10.7.3-MariaDB
-- PHP-Version: 8.1.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Datenbank: `octopus`
--
CREATE DATABASE IF NOT EXISTS `octopus` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `octopus`;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `columns`
--

CREATE TABLE `columns` (
  `id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longid` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `events`
--

CREATE TABLE `events` (
  `id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longid` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organisation` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` datetime NOT NULL,
  `location` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancelled` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `groups`
--

CREATE TABLE `groups` (
  `id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longid` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `media`
--

CREATE TABLE `media` (
  `id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longid` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` set('application','audio','image','video') COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extension` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copyright` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alternative` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variants` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variants`)),
  `file` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `motions`
--

CREATE TABLE `motions` (
  `id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longid` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timestamp` datetime NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `votes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`votes`)),
  `document_id` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `pages`
--

CREATE TABLE `pages` (
  `id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longid` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `persongrouprelations`
--

CREATE TABLE `persongrouprelations` (
  `id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` int(11) DEFAULT NULL,
  `role` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `persons`
--

CREATE TABLE `persons` (
  `id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longid` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_id` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `postcolumnrelations`
--

CREATE TABLE `postcolumnrelations` (
  `id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `column_id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `posts`
--

CREATE TABLE `posts` (
  `id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longid` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `overline` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `headline` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subline` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `teaser` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` datetime NOT NULL,
  `image_id` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `columns`
--
ALTER TABLE `columns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `longid` (`longid`);

--
-- Indizes für die Tabelle `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `longid` (`longid`);

--
-- Indizes für die Tabelle `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `longid` (`longid`);

--
-- Indizes für die Tabelle `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `longid` (`longid`);

--
-- Indizes für die Tabelle `motions`
--
ALTER TABLE `motions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `longid` (`longid`),
  ADD KEY `document_id` (`document_id`);

--
-- Indizes für die Tabelle `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `longid` (`longid`);

--
-- Indizes für die Tabelle `persongrouprelations`
--
ALTER TABLE `persongrouprelations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `person_id` (`person_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indizes für die Tabelle `persons`
--
ALTER TABLE `persons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `longid` (`longid`),
  ADD KEY `image_id` (`image_id`);

--
-- Indizes für die Tabelle `postcolumnrelations`
--
ALTER TABLE `postcolumnrelations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `column_id` (`column_id`);

--
-- Indizes für die Tabelle `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `longid` (`longid`),
  ADD KEY `image_id` (`image_id`);

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `motions`
--
ALTER TABLE `motions`
  ADD CONSTRAINT `motions.document` FOREIGN KEY (`document_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints der Tabelle `persongrouprelations`
--
ALTER TABLE `persongrouprelations`
  ADD CONSTRAINT `persongrouprelations.group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `persongrouprelations.person` FOREIGN KEY (`person_id`) REFERENCES `persons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `persons`
--
ALTER TABLE `persons`
  ADD CONSTRAINT `persons.image` FOREIGN KEY (`image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints der Tabelle `postcolumnrelations`
--
ALTER TABLE `postcolumnrelations`
  ADD CONSTRAINT `postcolumnrelations.column` FOREIGN KEY (`column_id`) REFERENCES `columns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `postcolumnrelations.post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts.image` FOREIGN KEY (`image_id`) REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;
