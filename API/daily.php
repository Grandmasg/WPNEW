<?php
error_reporting(1);
/**
 * Returns the list of Daily stats.
 */
require 'connect.php';
  
if (isset($_GET['Team']) && !empty($_GET['Team'])) {
	if ($_GET['Team'] == "-") {$search = "";} else {$search = "[".$_GET['Team']."]";}
} else {
	$search = "";
}

if (isset($_GET['Offset']) && !empty($_GET["Offset"])) {
	$Offset = $_GET['Offset'];
} else {
	$Offset = 0;
}

if (isset($_GET['get']) && !empty($_GET["get"])) {
	$Get = $_GET['get'];
}

$dag = date("Ymd", mktime(0, 0, 0, date("m"), date("d") + $Offset, date("Y")));
$dag1 = date("Ymd", mktime(0, 0, 0, date("m"), date("d") + $Offset - 1, date("Y")));
$dag2 = date("Ymd", mktime(0, 0, 0, date("m"), date("d") + $Offset - 2, date("Y")));

$query = "
SELECT s1.UserID, ifnull(s1.Keys1,0) - ifnull(s2.Keys1,0) AS StatsKeys, ifnull(s1.Clicks,0) - ifnull(s2.Clicks,0) AS StatsClicks
FROM whatpulse_aapdata AS s1, whatpulse_aapdata AS s2
WHERE s1.UserID = s2.userID and s1.datum = '$dag1'  AND s2.datum = '$dag2' AND s1.Username LIKE '%$search%'
ORDER  BY StatsKeys DESC, StatsClicks DESC
";

$result = $mysqli->query($query) or die($mysqli->error . __LINE__);
$i = 1;
if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		if ($row['StatsKeys'] > 0 && $row['StatsClicks'] > 0) {
			$dailyflushgister[$row['UserID']] = $i;
		}
	$i++;
	}
}

$query = "
SELECT s1.UserID, s1.Username, s1.Username as UsernameFull,
ifnull(s1.Keys1,0) - ifnull(s2.Keys1,0) AS StatsKeys, 
ifnull(s1.Clicks,0) - ifnull(s2.Clicks,0) AS StatsClicks,
ifnull(s1.Scrolls,0) - ifnull(s2.Scrolls,0) AS StatsScrolls,
ifnull(s1.DistanceInMiles,0) - ifnull(s2.DistanceInMiles,0) AS StatsDistance,
ifnull(s1.DownloadMB,0) - ifnull(s2.DownloadMB,0) AS StatsDownloadMB,
ifnull(s1.UploadMB,0) - ifnull(s2.UploadMB,0) AS StatsUploadMB,
s1.UptimeSeconds - s2.UptimeSeconds AS StatsUptimeSeconds,
s1.Rank_Keys AS RankKeysToday, s2.Rank_Keys AS RankKeysYesterday,
s1.Rank_Keys - s2.Rank_Keys AS StatsRankKeys,
s1.Rank_Clicks AS RankClicksToday, s2.Rank_Clicks AS RankClicksYesterday,
s1.Rank_Clicks - s2.Rank_Clicks AS StatsRankClicks,
s1.Rank_Scrolls AS RankScrollsToday, s2.Rank_Scrolls AS RankScrollsYesterday,
s1.Rank_Scrolls - s2.Rank_Scrolls AS StatsRankScrolls,
s1.Rank_Distance AS RankDistanceToday, s2.Rank_Distance AS RankDistanceYesterday,
s1.Rank_Distance - s2.Rank_Distance AS StatsRankDistance,
s1.Rank_Download AS RankDownloadToday, s2.Rank_Download AS RankDownloadYesterday,
s1.Rank_Download - s2.Rank_Download AS StatsRankDownload,
s1.Rank_Upload AS RankUploadToday, s2.Rank_Upload AS RankUploadYesterday,
s1.Rank_Upload - s2.Rank_Upload AS StatsRankUpload,
s1.Rank_Uptime AS RankUptimeToday, s2.Rank_Uptime AS RankUptimeYesterday,
s1.Rank_Uptime - s2.Rank_Uptime AS StatsRankUptime,
s1.Pulses AS PulsesToday, s2.Pulses AS PulsesYesterday,
s1.Pulses - s2.Pulses AS StatsPulses,
s1.LastPulse,
s1.Team
FROM whatpulse_aapdata AS s1, whatpulse_aapdata AS s2
WHERE s1.UserID = s2.UserID and s1.datum = '$dag' AND s2.datum = '$dag1' AND s1.Username LIKE '%$search%'
ORDER BY StatsKeys DESC, StatsClicks DESC
";

$result = $mysqli->query($query) or die($mysqli->error . __LINE__);
$i = 1;
$daily = array();
if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		if ($row['StatsKeys'] > 0 || $row['StatsClicks'] > 0) {
			$letter_position1 = strpos($row['Username'], "[", 0);
			$letter_position2 = strpos($row['Username'], "]", 0);
			if (strpos($row['Username'], "[", 0) >= 1 && strpos($row['Username'], "]", 0) >= 1 ) {
					$row['Username'] = substr($row['Username'], $letter_position1);
					$letter_position1 = strpos($row['Username'], "[", 0);
					$letter_position2 = strpos($row['Username'], "]", 0);
			}

			$naam = substr($row['Username'], $letter_position1, $letter_position2 + 1);
			if ($letter_position1 !== false && $letter_position2 !== false) {
				if ($letter_position1 > 0) {
					$naam = substr($row['Username'], $letter_position1);
				}
				$row['Username'] = str_replace($naam, "", $row['Username']);
				$row['Team'] = $naam;
				$row['Username'] = trim($row['Username']);
			} else {
				$row['Username'] = trim($row['Username']);
			}
			$row['datum'] = $dag;
			$row['today'] = $i;
			if ($dailyflushgister[$row['UserID']] == '') {
				$row['yesterday'] = null;
			} else {
				$row['yesterday'] = $dailyflushgister[$row['UserID']];
			}
			$daily[] = $row;
			
			$statscolumns = implode(', ',array_keys($row));
			$escaped_values = array_values($row);
			$statsvalues  = "\"".implode("\", \"", $escaped_values)."\"";
		}
	$i++;
	}
}

$dag = date("Ymd", mktime(0, 0, 0, date("m"), date("d") + $Offset, date("Y")));
$dag1 = date("Ymd", mktime(0, 0, 0, date("m"), date("d") + $Offset - 21, date("Y")));

$query = "
SELECT `datum`, sum(`Keys1`) as Keys1, sum(`Clicks`) as Clicks FROM `whatpulse_aapdata` WHERE Username LIKE '%$search%' AND `datum` BETWEEN '$dag1' AND '$dag' group by `datum` ORDER by `datum` DESC
";

$result = $mysqli->query($query) or die($mysqli->error . __LINE__);

$dailyG = array();
if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
			$dailyG[] = $row;
	$i++;
	}
}

// JSON-encode the response
$json_response = json_encode(['data'=>$daily,'dataG'=>$dailyG], JSON_NUMERIC_CHECK);
// # Return the response
echo $json_response;
	
?>