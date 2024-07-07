<?php
error_reporting(1);
ini_set('display_errors', 1);

require 'connect.php';

if (isset($_GET['Team']) && !empty($_GET['Team'])) {
	if ($_GET['Team'] == "-") {
		$search = "";
	} else {
		if ($Team == 'wp' || $Team == 'geil') {
			$Team = '#' . $Team;
		}
		$search = "[".$_GET['Team']."]";
	}
} else {
	$Team = "";
	$search = "";
}

if (isset($_GET['Offset']) && !empty($_GET["Offset"])) {
	$Offset = $_GET['Offset'];
} else {
	$Offset = 0;
}

function addDotsKM($num) {
	if ($num == "" || $num == "0")
		{
		return '-';
	} else {
		if ($num > 1000000)
			{
			$num = $num / 1000000;
			$added = " M";
		} elseif ($num > 1000) {
			$num = $num / 1000;
			$added = " K";
		}
	$num = trim($num);
	return number_format($num, 0, '.', '.') . $added;
	}
}

function addDotssec($num, $added) {
	if ($num == "" || $num == "0") {
		return '-';
	} else {
		if ($added == 'min') {
			$num = $num / 60;
		} elseif ($added == 'uur') {
			$num = $num / (60 * 60);
		}
		if ($added) {
			$added = ' ' . $added;
		}
	$num = trim($num);
	return number_format($num, 0, '.', '.') . $added;
	}
}

function addDotsGB($num) {
	if ($num == "" || $num == "0") {
		return '-';
	} else {
		if ($num > (1024 * 1024)) {
			$num = $num / (1024 * 1024);
			$added = " TB";
		} elseif ($num > 1024) {
			$num = $num / 1024;
			$added = " GB";
		} elseif ($num < 1024) {
			$added = " MB";
		}
	$num = trim($num);
	return number_format($num, 0, '.', '.') . $added;
	}
}

function addDots($num, $added, $num1) {
	if ($num == "" || $num == "0") {
		return '-';
	} else {
		$num = trim($num);
		if (!$added == '')	{
			$added = ' ' . $added;
		}
		return number_format($num, $num1, ',', '.') . $added;
	}
}

function mi_km($num) {
	if ($num == "" || $num == "0") {
		return '-';
	} else {	
		return round(($num * 1.609344),9);
	}
}

function addColorDots($num, $added, $num1)	{
	if ($num == 0) {
		$num = " ([img=10,8,,,\"still\",,2]https://tweakers.net/g/dpc/stil.gif[/img])";
	} else {
		if ($num > 0) {
			$num = "[green] (+" . addDots($num, '', $num1) . ") " . $added . "[/]";
		}

		if ($num < 0) {
			$num = "[red] (" . addDots($num, '', $num1) . ") " . $added . "[/]";
		}
	}
	return $num;
}

function addColorDotsRank($num)	{
	if ($num == 0) {
		$num = " ([img=10,8,,,\"still\",,2]https://tweakers.net/g/dpc/stil.gif[/img])";
	} else {
		if ($num > 0) {
			$num = "[green] ([img=10,8,,,\"up\",,2]https://tweakers.net/g/dpc/up.gif[/img]" . addDots($num, '', 0) . ")[/]";
		}
		if ($num < 0) {
			$num = "[red] ([img=10,8,,,\"down\",,2]https://tweakers.net/g/dpc/down.gif[/img]" . addDots($num*-1, '', 0) . ")[/]";
		}
	}
return $num;
}

if ($Offset >= 0) {
	echo "No stats today";
} else {
	$dag = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset, date("Y")));
	$dag1 = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset - 1, date("Y")));
	$dag2 = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset - 2, date("Y")));
	$daglang = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d") + $Offset, date("Y")));

	$query = "
	SELECT s1.UserID, ifnull(s1.Keys1,0) - ifnull(s2.Keys1,0) AS DiffrenceKeys, ifnull(s1.Clicks,0) - ifnull(s2.Clicks,0) AS DiffrenceClicks
	FROM whatpulse_aapdata AS s1, whatpulse_aapdata AS s2
	WHERE s1.UserID = s2.userID and s1.datum = '$dag1'  AND s2.datum = '$dag2' AND s1.Username LIKE '%$search%'
	ORDER BY DiffrenceKeys DESC, DiffrenceClicks DESC
	";

	$result = $mysqli->query($query) or die($mysqli->error . __LINE__);
	$i = 1;
	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			if ($row['DiffrenceKeys'] > 0 && $row['DiffrenceClicks'] > 0) {
				$dailyflushgister[$row['UserID']] = $i;
			}
		$i++;
		}
	}


		$query = "
SELECT s1.UserID as UserIDT, s1.Username as UsernameT, s1.Keys1 as Keys1T, s1.Clicks as ClicksT, s1.Scrolls as ScrollsT, s1.DistanceInMiles as DistanceT, s1.DownloadMB as DownloadMBT, s1.UploadMB as UploadMBT, s1.UptimeSeconds as UptimeSecondsT,
    ifnull(s1.Keys1,0) - ifnull(s2.Keys1,0) AS diffrence_keys,
    ifnull(s1.Clicks,0) - ifnull(s2.Clicks,0) AS diffrence_clicks,
    ifnull(s1.Scrolls,0) - ifnull(s2.Scrolls,0) AS diffrence_Scrolls,
    ifnull(s1.DistanceInMiles,0) - ifnull(s2.DistanceInMiles,0) AS diffrence_Distance,
    ifnull(s1.DownloadMB,0) - ifnull(s2.DownloadMB,0) AS diffrence_DownloadMB,
    ifnull(s1.UploadMB,0) - ifnull(s2.UploadMB,0) AS diffrence_UploadMB,
    s1.UptimeSeconds - s2.UptimeSeconds AS diffrence_UptimeSeconds,
    s1.Rank_Keys AS Rank_Keys_today, s2.Rank_Keys AS Rank_Keys_yesterday,
    s2.Rank_Keys - s1.Rank_Keys AS diffrence_Rank_Keys,
    s1.Rank_Clicks AS Rank_Clicks_today, s2.Rank_Clicks AS Rank_Clicks_yesterday,
    s2.Rank_Clicks - s1.Rank_Clicks AS diffrence_Rank_Clicks,
    s1.Rank_Scrolls AS Rank_Scrolls_today, s2.Rank_Scrolls AS Rank_Scrolls_yesterday,
    s2.Rank_Scrolls - s1.Rank_Scrolls AS diffrence_Rank_Scrolls,
    s1.Rank_Distance AS Rank_Distance_today, s2.Rank_Distance AS Rank_Distance_yesterday,
    s2.Rank_Distance - s1.Rank_Distance AS diffrence_Rank_Distance,
    s1.Rank_Download AS Rank_Download_today, s2.Rank_Download AS Rank_Download_yesterday,
    s2.Rank_Download - s1.Rank_Download AS diffrence_Rank_Download,
    s1.Rank_Upload AS Rank_Upload_today, s2.Rank_Upload AS Rank_Upload_yesterday,
    s2.Rank_Upload - s1.Rank_Upload AS diffrence_Rank_Upload,
    s1.Rank_Uptime AS Rank_Uptime_today, s2.Rank_Uptime AS Rank_Uptime_yesterday,
    s2.Rank_Uptime - s1.Rank_Uptime AS diffrence_Rank_Uptime,
    s1.Pulses AS PulsesT, s2.Pulses AS PulsesY,
    s1.Pulses - s2.Pulses AS diffrence_Pulses,
    s1.LastPulse,
    s1.Team
    FROM whatpulse_aapdata AS s1, whatpulse_aapdata AS s2
    WHERE s1.UserID = s2.UserID and s1.datum = '" . $dag . "' AND s2.datum = '" . $dag1 . "' AND s1.Username LIKE '%" . $search . "%'
    ORDER BY Keys1T DESC, ClicksT DESC
";
		$result = $mysqli->query($query) or die($mysqli->error . __LINE__);
		$i = 1;
		$arr = array();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc())	{
				$letter_position1 = strpos($row['UsernameT'], "[", 0);
				$letter_position2 = strpos($row['UsernameT'], "]", 0);
				if (strpos($row['Username'], "[", 0) >= 1) {
					$row['Username'] = substr($row['UsernameT'], $letter_position1);
					$letter_position1 = strpos($row['UsernameT'], "[", 0);
					$letter_position2 = strpos($row['UsernameT'], "]", 0);
				}

				$naam = substr($row['Username'], $letter_position1, $letter_position2 + 1);
				if ($letter_position1 !== false && $letter_position2 !== false)	{
					if ($letter_position1 > 0) {
						$naam = substr($row['UsernameT'], $letter_position1);
					}
					$row['UsernameT'] = str_replace($naam, "", $row['UsernameT']);
					$row['Team'] = $naam;
					$row['UsernameT'] = trim($row['UsernameT']);
				} else {
					$row['UsernameT'] = trim($row['UsernameT']);
				}
				$row['today'] = $i;
				$row['yesterday'] = $dailyflushgister[$row['UserID']];
				$arr[] = $row;
				$i++;
			}
		}

		$query = "
SELECT Username
FROM whatpulse_aapdata
WHERE datum = '$dag' AND Username LIKE '%" . $search . "%'
";
		$result = $mysqli->query($query) or die($mysqli->error . __LINE__);
		$aantalA = 0;
		$arrayA = array();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				array_push($arrayA, array(
					"Username" => $row['Username']
				));
				$aantalA++;
			}
		}

		$query = "
SELECT Username
FROM whatpulse_aapdata
WHERE datum = '$dag1' AND Username LIKE '%" . $search . "%'
";
		$result = $mysqli->query($query) or die($mysqli->error . __LINE__);
		$aantalB = 0;
		$arrayB = array();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				array_push($arrayB, array(
					"Username" => $row['Username']
				));
				$aantalB++;
			}
		}

		foreach($arrayA as $num => $values) {
			$Usernamea[] = $values['Username'];
		}

		foreach($arrayB as $num => $values)	{
			$Usernameb[] = $values['Username'];
		}

		$resultadded = array_diff($Usernamea, $Usernameb);
		$countadded = count($resultadded);
		$counttoday = count($arrayA);

		$i = 0;
		foreach($resultadded as $x => $x_value)	{
			$added.= "[green]" . $x_value . "[/]";
			while ($i < $countadded - 1) {
				$added.= ",";
				$i++;
			}
		}

		$resultleft = array_diff($Usernameb, $Usernamea);
		$countleft = - 1 * count($resultleft);

		$i = 0;
		foreach($resultleft as $x => $x_value) {
			$left.= "[red]" . $x_value . "[/]";
			while ($i < $countadded - 1) {
				$left.= ",";
				$i++;
			}
		}

		if ($countadded == 0 && $countleft == 0) {
			$xmladdleft = addColorDots(0,'',0);
			$xmluaddleft = "";
		}
		elseif ($countadded > 0 && $countleft < 0) {
			$xmladdleft = addColorDots($countadded,'',0) . addColorDots($countleft,'',0);
			$xmluaddleft = "[tr][th][green]Added[/]/[red]Left[/][/][td]" . $added . "," . $left . "[/][/]";
		}
		elseif ($countadded > 0) {
			$xmladdleft = addColorDots($countadded,'',0);
			$xmluaddleft = "[tr][th][green]Added[/][/][td]" . $added . "[/][/]";
		}
		elseif ($countleft < 0) {
			$xmladdleft = addColorDots($countleft,'',0);
			$xmluaddleft = "[tr][th][red]Left[/][/][td]" . $left . "[/][/]";
		}

    //
    // Users added/left
    //

		$query = "
SELECT s1.Team, sum(s1.Keys1) as Keys1T, sum(s1.Clicks) as ClicksT, sum(s1.Scrolls) as ScrollsT, sum(s1.DistanceInMiles) as DistanceT, sum(s1.DownloadMB) as DownloadMBT, sum(s1.UploadMB) as UploadMBT, sum(s1.UptimeSeconds) as UptimeSecondsT,
    sum(ifnull(s1.Keys1,0) - ifnull(s2.Keys1,0)) AS diffrence_keys,
    sum(ifnull(s1.Clicks,0) - ifnull(s2.Clicks,0)) AS diffrence_clicks,
    sum(ifnull(s1.Scrolls,0) - ifnull(s2.Scrolls,0)) AS diffrence_Scrolls,
	sum(ifnull(s1.DistanceInMiles,0) - ifnull(s2.DistanceInMiles,0)) AS diffrence_Distance,
    sum(ifnull(s1.DownloadMB,0) - ifnull(s2.DownloadMB,0)) AS diffrence_DownloadMB,
    sum(ifnull(s1.UploadMB,0) - ifnull(s2.UploadMB,0)) AS diffrence_UploadMB,
    sum(s1.UptimeSeconds - s2.UptimeSeconds) AS diffrence_UptimeSeconds,
    sum(s1.Pulses) AS PulsesT, sum(s2.Pulses) AS PulsesY,
    sum(s1.Pulses - s2.Pulses) AS diffrence_Pulses
    FROM whatpulse_aapdata AS s1, whatpulse_aapdata AS s2
    WHERE s1.UserID = s2.UserID and s1.datum = '" . $dag . "' AND s2.datum = '" . $dag1 . "' AND s1.Username LIKE '%" . $search . "%'
    GROUP BY s1.Team
    ORDER BY diffrence_keys DESC, Keys1T DESC
";
		$result = $mysqli->query($query) or die($mysqli->error . __LINE__);
		$teamC = 0;
		$Tarr = array();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc())	{
				array_push($Tarr, array(
					$row
				));
				$teamC++;
			}
		}

		$teamC--;
		$teamC--;

		$aantal = 0;
		foreach($arr as $num => $values)
			{
			$aantal++;
			$Keys1T+= $values['Keys1T'];
			$ClicksT+= $values['ClicksT'];
			$ScrollsT+= $values['ScrollsT'];
			$DistanceT+= $values['DistanceT'];
			$DownloadMBT+= $values['DownloadMBT'];
			$UploadMBT+= $values['UploadMBT'];
			$UptimeSecondsT+= $values['UptimeSecondsT'];
			$PulsesT+= $values['PulsesT'];
			$diffrence_keys+= $values['diffrence_keys'];
			$diffrence_clicks+= $values['diffrence_clicks'];
			$diffrence_scrolls+= $values['diffrence_Scrolls'];
			$diffrence_distance+= $values['diffrence_Distance'];
			$diffrence_DownloadMB+= $values['diffrence_DownloadMB'];
			$diffrence_UploadMB+= $values['diffrence_UploadMB'];
			$diffrence_UptimeSeconds+= $values['diffrence_UptimeSeconds'];
			$diffrence_Pulses+= $values['diffrence_Pulses'];
			}

    //
		// XML daily
    //

		$xml = "[nosmilies][table border=1 width=800 fontsize=11]
[tr][th bgcolor=#262A34 colspan=2][IMG=800,65]https://tweakers.net/i/hz22FOee9sarIvctQILgShlpLtg=/800x/filters:strip_exif()/f/image/7hcr87E0H98NSp3Ee1mtEqDA.png?f=fotoalbum_large[/IMG][/][/]
[tr][th colspan=2 fontsize=20 align=center][b]Stats - " . $daglang . "[/][/][/]
[tr][th]Keys[/][td]" . addDots($Keys1T, '', 0) . " " . addColorDots($diffrence_keys,'',0) . "[/][/]
[tr][th]Clicks[/][td]" . addDots($ClicksT, '', 0) . " " . addColorDots($diffrence_clicks,'',0) . "[/][/]
[tr][th]Scrolls[/][td]" . addDots($ScrollsT, '', 0) . " " . addColorDots($diffrence_scrolls,'',0) . "[/][/]
[tr][th]Distance (km)[/][td]" . addDots(mi_km($DistanceT), 'km', 3) . " " . addColorDots(mi_km($diffrence_distance),'km',3) . "[/][/]
[tr][th]Download[/][td]" . addDots($DownloadMBT, 'MB', 0) . " " . addColorDots($diffrence_DownloadMB,'MB',0) . "[/][/]
[tr][th]Upload[/][td]" . addDots($UploadMBT, 'MB', 0) . " " . addColorDots($diffrence_UploadMB,'MB',0) . "[/][/]
[tr][th]Uptime[/][td]" . addDots($UptimeSecondsT, 'sec', 0) . " " . addColorDots($diffrence_UptimeSeconds,'sec',0) . "[/][/]
[tr][th]Pulses[/][td]" . addDots($PulsesT, '', 0) . " " . addColorDots($diffrence_Pulses,'',0) . "[/][/]
[tr][th]Members[/][td]" . addDots($counttoday, '', 0) . " " . $xmladdleft . "[/][/]" . $xmluaddleft . "
";

    //
		// Subteam
    //

		$xml.= "[/][table border=1 width=800 fontsize=11]
[tr][th bgcolor=#262A34 colspan=9][IMG=800,65]https://tweakers.net/i/WVJgTtDlzjbG8j6OV2oBW_cFSA4=/800x/filters:strip_exif()/f/image/bs8ZY2RBYclQdejLIgZSjAFu.png?f=fotoalbum_large[/IMG][/][/]
[tr][th]#[/][th]Team[/][th]Keys[/][th]Clicks[/][th]Scrolls[/][th]Distance (km)[/][th]Download[/][th]Upload[/][th]Uptime[/][/]
";
		for ($i = 0; $i < 13; $i++)
			{
			$j++;
			if ($Tarr[$i][0]['Team'] == '-')
				{
				$i++;
				}

			$xml.= "[tr][td][i]" . $j . "[/][/][td]" . $Tarr[$i][0]['Team'] . "[/][td][blue]" . addDotsKM($Tarr[$i][0]['Keys1T']) . "[/]" . addColorDots($Tarr[$i][0]['diffrence_keys'],'',0) . "[/][td]" . addDotsKM($Tarr[$i][0]['ClicksT']) . " " . addColorDots($Tarr[$i][0]['diffrence_clicks'],'',0) . "[/][td]" . addDotsKM($Tarr[$i][0]['ScrollsT']) . " " . addColorDots($Tarr[$i][0]['diffrence_Scrolls'],'',0) . "[/][td]" . addDotsKM($Tarr[$i][0]['DistanceT']) . " " . addColorDots($Tarr[$i][0]['diffrence_Distance'],'km',3) . "[/][td]" . addDotsGB($Tarr[$i][0]['DownloadMBT']) . " " . addColorDots($Tarr[$i][0]['diffrence_DownloadMB'],'MB',0) . "[/][td]" . addDotsGB($Tarr[$i][0]['UploadMBT']) . " " . addColorDots($Tarr[$i][0]['diffrence_UploadMB'],'MB',0) . "[/][td]" . addDotssec($Tarr[$i][0]['UptimeSecondsT'],'uur') . " " . addColorDots($Tarr[$i][0]['diffrence_UptimeSeconds'],'sec',0) . "[/][/]\n";
			}

		$xml.= "[tr][td fontsize=11 colspan=8]Totaal zijn er [b]" . $teamC . " subteams[/] geregistreerd![/][/]\n";

    //
		// Keys top 25
    //

		$xml.= "[/][table border=1 width=800 fontsize=11]
[tr][th bgcolor=#262A34 colspan=5][IMG=800,65]https://tweakers.net/i/eIA306C3UpAdfhi7Okuv7m2Y504=/800x/filters:strip_exif()/f/image/CGlgOLQwfuQN8wJAJ1EN4bZ9.png?f=fotoalbum_large[/IMG][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Keys[/][th]Clicks[/][/]
";
		for ($i = 0; $i < 25; $i++)	{
			$j = $i + 1;
			$xml.= "[tr][td][i]" . $j . "[/][/][td]" . addDots($arr[$i]['Rank_Keys_today'],'',0) . " " . addColorDotsRank($arr[$i]['diffrence_Rank_Keys']) . "[/][td]" . $arr[$i]['UsernameT'] . "[/][td][blue]" . addDots($arr[$i]['Keys1T'],'',0) . "[/] " . addColorDots($arr[$i]['diffrence_keys'],'',0) . "[/]" . "[td]" . addDots($arr[$i]['ClicksT'],'',0) . " " . addColorDots($arr[$i]['diffrence_clicks'],'',0) . "[/][/]\n";
		}

    //
		// Keys
    //

		function keySort($item1, $item2) {
			if ($item1['diffrence_keys'] == $item2['diffrence_keys'])	{
				return $item1['diffrence_clicks'] < $item2['diffrence_clicks'] ? 1 : -1;
			} else {
				return ($item1['diffrence_keys'] < $item2['diffrence_keys']) ? 1 : -1;
			}
		}

		usort($arr, 'keySort');
		$xml.= "[/][table border=1 width=800 fontsize=11]
[tr][th bgcolor=#262A34 colspan=5][IMG=800,65]https://tweakers.net/i/AeianazjGevCbMKDhp67sWJyBNo=/800x/filters:strip_exif()/f/image/zymLUpFo7l3nFtwNeH1DoVcd.png?f=fotoalbum_large[/IMG][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Keys[/][th]Clicks[/][/]
";
		for ($i = 0; $i < 15; $i++)
			{
			$j = $i + 1;
			$xml.= "[tr][td][i]" . $j . "[/][/][td]" . addDots($arr[$i]['Rank_Keys_today'],'',0) . " " . addColorDotsRank($arr[$i]['diffrence_Rank_Keys']) . "[/][td]" . $arr[$i]['UsernameT'] . "[/][td][blue]" . addDots($arr[$i]['Keys1T'],'',0) . "[/] " . addColorDots($arr[$i]['diffrence_keys'],'',0) . "[/]" . "[td]" . addDots($arr[$i]['ClicksT'],'',0) . " " . addColorDots($arr[$i]['diffrence_clicks'],'',0) . "[/][/]\n";
			}

    //
		// Clicks
    //

		function clickSort($item1, $item2) {
			if ($item1['diffrence_clicks'] == $item2['diffrence_clicks']) {
				return $item1['diffrence_keys'] < $item2['diffrence_keys'] ? 1 : -1;
			} else {
				return ($item1['diffrence_clicks'] < $item2['diffrence_clicks']) ? 1 : -1;
			}
		}

		usort($arr, 'clickSort');
		$xml.= "[/][table border=1 width=800 fontsize=11]
[tr][th bgcolor=#262A34 colspan=5][IMG=800,65]https://tweakers.net/i/yk6NuL3K84b36aNfK_zXKhFZS00=/800x/filters:strip_exif()/f/image/Ny1q5tWlGO0gLKWPHmQCIuB8.png?f=fotoalbum_large[/IMG][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Keys[/][th]Clicks[/][/]
";
		for ($i = 0; $i < 15; $i++) {
			$j = $i + 1;
			$xml.= "[tr][td][i]" . $j . "[/][/][td]" . addDots($arr[$i]['Rank_Clicks_today'],'',0) . " " . addColorDotsRank($arr[$i]['diffrence_Rank_Clicks']) . "[/][td]" . $arr[$i]['UsernameT'] . "[/][td]" . addDots($arr[$i]['Keys1T'],'',0) . " " . addColorDots($arr[$i]['diffrence_keys'],'',0) . "[/]" . "[td][blue]" . addDots($arr[$i]['ClicksT'],'',0) . "[/] " . addColorDots($arr[$i]['diffrence_clicks'],'',0) . "[/][/]\n";
		}

    //
		// Scrolls
    //

	function scrollsSort($item1, $item2) {
		if ($item1['diffrence_Scrolls'] == $item2['diffrence_Scrolls']) return 0;
		return ($item1['diffrence_Scrolls'] < $item2['diffrence_Scrolls']) ? 1 : -1;
	}

	usort($arr, 'scrollsSort');
	$xml.= "[/][table border=1 width=800 fontsize=11]
[tr][th bgcolor=#262A34 colspan=6][IMG=800,65]https://tweakers.net/i/Kse-75knnmIiH2RF4gcgz_Vb8qs=/800x/filters:strip_exif()/f/image/vy0EkR6Mg0yJw2JggY69JSxv.png?f=fotoalbum_large[/IMG][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Scrolls[/][th]Distance (km)[/][/]
";
	for ($i = 0; $i < 15; $i++) {
		$j = $i + 1;
		$xml.= "[tr][td][i]" . $j . "[/][/][td]" . addDots($arr[$i]['Rank_Scrolls_today'],'',0) . " " . addColorDotsRank($arr[$i]['diffrence_Rank_Scrolls']) . "[/][td]" . $arr[$i]['UsernameT'] . "[/][td][blue]" . addDots($arr[$i]['ScrollsT'],'',0) . "[/] " . addColorDots($arr[$i]['diffrence_Scrolls'],'',0) . "[/][td]" . addDots(mi_km($arr[$i]['DistanceT']),'km',3) . " " . addColorDots(mi_km($arr[$i]['diffrence_Distance']),'km',3) . "[/][/]\n";
	}

    //
		// Distance
    //

	function distanceSort($item1, $item2) {
		if ($item1['diffrence_Distance'] == $item2['diffrence_Distance']) return 0;
		return ($item1['diffrence_Distance'] < $item2['diffrence_Distance']) ? 1 : -1;
	}

	usort($arr, 'distanceSort');
	$xml.= "[/][table border=1 width=800 fontsize=11]
[tr][th bgcolor=#262A34 colspan=6][IMG=800,65]https://tweakers.net/i/pmCJsNBA4W_v3itRdiG24lKtIZk=/800x/filters:strip_exif()/f/image/ltCV8vZHSXVzFCH4nZrHs6xT.png?f=fotoalbum_large[/IMG][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Scrolls[/][th]Distance (km)[/][/]
";
	for ($i = 0; $i < 15; $i++) {
		$j = $i + 1;
		$xml.= "[tr][td][i]" . $j . "[/][/][td]" . addDots($arr[$i]['Rank_Scrolls_today'],'',0) . " " . addColorDotsRank($arr[$i]['diffrence_Rank_Scrolls']) . "[/][td]" . $arr[$i]['UsernameT'] . "[/][td]" . addDots($arr[$i]['ScrollsT'],'',0) . " " . addColorDots($arr[$i]['diffrence_Scrolls'],'',0) . "[td][blue]" . addDots(mi_km($arr[$i]['DistanceT']),'km',3) . " " . addColorDots(mi_km($arr[$i]['diffrence_Distance']),'km',3) . "[/][/][/]\n";
	}

    //
		// Download
    //

		function downloadSort($item1, $item2) {
			if ($item1['diffrence_DownloadMB'] == $item2['diffrence_DownloadMB']) return 0;
			return ($item1['diffrence_DownloadMB'] < $item2['diffrence_DownloadMB']) ? 1 : -1;
		}

		usort($arr, 'downloadSort');
		$xml.= "[/][table border=1 width=800 fontsize=11]
[tr][th bgcolor=#262A34 colspan=6][IMG=800,65]https://tweakers.net/i/Ni2fbW6EtHO7jhVAD9tyMegYYwo=/800x/filters:strip_exif()/f/image/kZnyclAdeegao1zK2Vd1z5fM.png?f=fotoalbum_large[/IMG][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Download[/][th]Upload[/][th]Uptime[/][/]
";
		for ($i = 0; $i < 15; $i++) {
			$j = $i + 1;
			$xml.= "[tr][td][i]" . $j . "[/][/][td]" . addDots($arr[$i]['Rank_Download_today'],'',0) . " " . addColorDotsRank($arr[$i]['diffrence_Rank_Download']) . "[/][td]" . $arr[$i]['UsernameT'] . "[/][td][blue]" . addDotsGB($arr[$i]['DownloadMBT']) . "[/] " . addColorDots($arr[$i]['diffrence_DownloadMB'],'MB',0) . "[/][td]" . addDotsGB($arr[$i]['UploadMBT']) . " " . addColorDots($arr[$i]['diffrence_UploadMB'],'MB',0) . "[/][td]" . addDotssec($arr[$i]['UptimeSecondsT'], 'uur') . " " . addColorDots($arr[$i]['diffrence_UptimeSeconds'],'sec',0) . "[/][/]\n";
		}

    //
		// Upload
    //

		function uploadSort($item1, $item2) {
			if ($item1['diffrence_UploadMB'] == $item2['diffrence_UploadMB']) return 0;
			return ($item1['diffrence_UploadMB'] < $item2['diffrence_UploadMB']) ? 1 : -1;
		}

		usort($arr, 'uploadSort');
		$xml.= "[/][table border=1 width=800 fontsize=11]
[tr][th bgcolor=#262A34 colspan=6][IMG=800,65]https://tweakers.net/i/-Q4_Rmc6tPN4BydTYDAU-wtjk-Q=/800x/filters:strip_exif()/f/image/JS9L7TTwp878N2ooSfzjZ0Ph.png?f=fotoalbum_large[/IMG][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Download[/][th]Upload[/][th]Uptime[/][/]
";
		for ($i = 0; $i < 15; $i++) {
			$j = $i + 1;
			$xml.= "[tr][td][i]" . $j . "[/][/][td]" . addDots($arr[$i]['Rank_Upload_today'],'',0) . " " . addColorDotsRank($arr[$i]['diffrence_Rank_Upload']) . "[/][td]" . $arr[$i]['UsernameT'] . "[/][td]" . addDotsGB($arr[$i]['DownloadMBT']) . " " . addColorDots($arr[$i]['diffrence_DownloadMB'],'MB',0) . "[/][td][blue]" . addDotsGB($arr[$i]['UploadMBT']) . "[/] " . addColorDots($arr[$i]['diffrence_UploadMB'],'MB',0) . "[/][td]" . addDotssec($arr[$i]['UptimeSecondsT'], 'uur') . " " . addColorDots($arr[$i]['diffrence_UptimeSeconds'],'sec',0) . "[/][/]\n";
		}

    //
		// Uptime
    //

		function uptimeSort($item1, $item2)	{
			if ($item1['diffrence_UptimeSeconds'] == $item2['diffrence_UptimeSeconds']) return 0;
			return ($item1['diffrence_UptimeSeconds'] < $item2['diffrence_UptimeSeconds']) ? 1 : -1;
		}

		usort($arr, 'uptimeSort');
		$xml.= "[/][table border=1 width=800 fontsize=11]
[tr][th bgcolor=#262A34 colspan=6][IMG=800,65]https://tweakers.net/i/VSgWDAkA1F6cUFww7CSIkTUf8dg=/800x/filters:strip_exif()/f/image/YzRlcVvaB83Y9XnI5jMaijaA.png?f=fotoalbum_large[/IMG][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Download[/][th]Upload[/][th]Uptime[/][/]
";
		for ($i = 0; $i < 15; $i++)	{
			$j = $i + 1;
			$xml.= "[tr][td][i]" . $j . "[/][/][td]" . addDots($arr[$i]['Rank_Upload_today'],'',0) . " " . addColorDotsRank($arr[$i]['diffrence_Rank_Upload']) . "[/][td]" . $arr[$i]['UsernameT'] . "[/][td]" . addDotsGB($arr[$i]['DownloadMBT']) . " " . addColorDots($arr[$i]['diffrence_DownloadMB'],'MB',0) . "[/][td]" . addDotsGB($arr[$i]['UploadMBT']) . " " . addColorDots($arr[$i]['diffrence_UploadMB'],'MB',0) . "[/][td][blue]" . addDotssec($arr[$i]['UptimeSecondsT'], 'uur') . "[/] " . addColorDots($arr[$i]['diffrence_UptimeSeconds'],'sec',0) . "[/][/]\n";
		}

    //
		// end
    //

		$xml.= "[/][/]";
    $xml.= "[b][small][br]Stats site= [url=https://www.grandmasg.nl/WPNEW/#/daily/0/-]WhatPulse stats![/][br]Source= [url=https://github.com/Grandmasg/WPNEW]Github team stats[/][/][/]";
		$xml = str_replace("]", "&#093;", $xml);
		echo $xml;

    //
		// JSON-encode the response
    //
}

?>