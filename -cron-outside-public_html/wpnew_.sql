--
-- Database: `WPNEW_`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `whatpulse_aapdata`
--

CREATE TABLE `whatpulse_aapdata` (
  `id` int(15) NOT NULL,
  `datum` date NOT NULL DEFAULT '1000-01-01',
  `Username` varchar(40) NOT NULL,
  `Team` varchar(20) NOT NULL,
  `UserID` int(8) NOT NULL,
  `Pulses` int(10) NOT NULL,
  `Keys1` int(15) NOT NULL,
  `Clicks` int(15) NOT NULL,
  `Scrolls` int(15) NOT NULL,
  `DistanceInMiles` float(18,3) NOT NULL,
  `DownloadMB` varchar(15) NOT NULL,
  `UploadMB` varchar(15) NOT NULL,
  `Download` varchar(15) NOT NULL,
  `Upload` varchar(15) NOT NULL,
  `UptimeSeconds` int(18) NOT NULL,
  `UptimeShort` varchar(30) NOT NULL,
  `UptimeLong` varchar(100) NOT NULL,
  `Rank_Keys` int(10) NOT NULL,
  `Rank_Clicks` int(10) NOT NULL,
  `Rank_Download` int(10) NOT NULL,
  `Rank_Upload` int(10) NOT NULL,
  `Rank_Uptime` int(10) NOT NULL,
  `Rank_Scrolls` int(10) NOT NULL,
  `Rank_Distance` int(10) NOT NULL,
  `LastPulse` datetime NOT NULL DEFAULT '1000-01-01 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;