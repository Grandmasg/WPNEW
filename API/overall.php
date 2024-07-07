<?php
error_reporting(1);
/**
 * Returns the list of Overall stats.
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

$dag = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset, date("Y")));
$dag1 = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset - 1, date("Y")));

$query="
SELECT UserID, Username, ifnull(Keys1,0) AS Keys1
FROM whatpulse_aapdata
WHERE datum = '$dag1' AND Username LIKE '%$search%'
ORDER BY Keys1 DESC, Clicks DESC
";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
$i = 1;
$overall = array();
if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $totalflushgister[$row['UserID']] = $i;
        $i++;
    }
}

$query="
SELECT s1.UserID, s1.Username, s1.Username as UsernameFull,
ifnull(s1.Keys1,0) AS Keys1, 
ifnull(s1.Clicks,0) AS Clicks,
ifnull(s1.Scrolls,0) AS Scrolls,
ifnull(s1.DistanceInMiles,0) AS DistanceInMiles,
ifnull(s1.DownloadMB,0) AS DownloadMB,
ifnull(s1.UploadMB,0) AS UploadMB,
s1.UptimeSeconds AS UptimeSeconds,
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
s1.Pulses AS Pulses,
s1.LastPulse,
s1.Team
FROM whatpulse_aapdata AS s1, whatpulse_aapdata AS s2
WHERE s1.UserID = s2.UserID and s1.datum = '$dag' AND s2.datum = '$dag1' AND s1.Username LIKE '%$search%'
ORDER BY Keys1 DESC, Clicks DESC
";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
$i = 1;
$overall = array();
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
            $letter_position1 = strpos($row['Username'], "[", 0);
            $letter_position2 = strpos($row['Username'], "]", 0);
            if (strpos($row['Username'], "[", 0) >= 1) {
                $row['Username'] = substr($row['Username'], $letter_position1);
                $letter_position1 = strpos($row['Username'], "[", 0);
                $letter_position2 = strpos($row['Username'], "]", 0);
            }
            $naam = substr($row['Username'], $letter_position1, $letter_position2+1);
        if ($letter_position1 !== false && $letter_position2 !== false ) {
            $row['Username'] = str_replace($naam, "", $row['Username']);
            $row['Team'] = $naam;
            $row['Username'] = trim($row['Username']);
        } else {
            $row['Username'] = trim($row['Username']);
        }
        $row['today'] = $i;
        $row['yesterday'] = $totalflushgister[$row['UserID']];
        $overall[] = $row;
        $i++;
    }
}

function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_string ($d)) {
        return utf8_encode($d);
    }
    return $d;
}

$overall = utf8ize($overall);

// JSON-encode the response
$json_response = json_encode(['data'=>$overall], JSON_NUMERIC_CHECK);

// # Return the response
echo $json_response;
?>