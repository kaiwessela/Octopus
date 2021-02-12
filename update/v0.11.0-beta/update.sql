--
-- Tabellenstruktur für Tabelle `proposals`
--
CREATE TABLE `proposals` (
  `proposal_id` varchar(8) NOT NULL,
  `proposal_longid` varchar(60) NOT NULL,
  `proposal_title` varchar(80) NOT NULL,
  `proposal_description` text DEFAULT NULL,
  `proposal_timestamp` datetime NOT NULL,
  `proposal_status` varchar(20) NOT NULL,
  `proposal_votes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`proposal_votes`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indizes für die Tabelle `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`proposal_id`),
  ADD UNIQUE KEY `proposal_longid` (`proposal_longid`);
COMMIT;
