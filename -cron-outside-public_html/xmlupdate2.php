<?php
require 'connect.php';
set_time_limit(1860);
$time = microtime(); 
$time = explode(" ", $time); 
$time = $time[1] + $time[0]; 
$start = $time;
$i = 1;
$count = 0;
$id = "";
$xmlteamlink = 'http://api.whatpulse.org/team.php?team=1295&members=yes&format=xml';
$xml = simplexml_load_file($xmlteamlink);

$yesterday = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
$test = date("His Ymd", mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));
$test1 = date("His",strtotime($test));
$today = date("Ymd",strtotime($test));
$Username = "";
$LastPulse = "";
sleep(60);
$query = "select datum from whatpulse_aapdata WHERE datum = '".$today."'";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
$new = 0;
if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $new++;
    }
}

foreach ($xml->Members as $entry){
	$insert = 0;
	echo '<table class="ruimte">';
	foreach ($entry as $child){
		$name = $child->getName();		
		echo '<tr><TH BGCOLOR="#99CCFF">'.$i.'</TH>';//ROWSPAN=2
		echo '<td bgcolor="#999999" nowrap="nowrap">'.$name.'</td></tr>';
		echo '<tr>';
			foreach ($child as $child1){
				echo '<td bgcolor="#CCCCCC" nowrap="nowrap">'.$child1->getName().'</td>';
				if ( $child1->getName() == "UserID") {
					$id = $child1;
					$xmluserlink = 'http://api.whatpulse.org/user.php?user='.$id;
					$xmluser = simplexml_load_file($xmluserlink);
					$j = 1;
					while($j <= 3 && $xmluser === false ){
						$j++;
						$xmluser = simplexml_load_file($xmluserlink);
						sleep(3);
					}
					echo '<td bgcolor="#CCCCCC" nowrap="nowrap">'.$xmluser->Pulses->getName().'</td>';
				}
			}
		echo '<tr></tr>';		
			foreach ($child as $child1){
				echo '<td bgcolor="#CCCCCC" nowrap="nowrap">'.$child1.'</td>';
				$pos = strpos(strtolower($child1), "[deapen]");
				if ( $child1->getName() == "Username") {
					$Username = $child1;
					
					$letter_position1 = ''; $letter_position2 = ''; $Team = '';
				    $letter_position1 = strpos($Username, "[", 0);
				    $letter_position2 = strpos($Username, "]", 0);
					if (strpos($Username, "[", 0) == 0 && strpos($Username, "]", 0) >= 1) {
					    $Team = strtolower( substr($Username, $letter_position1, $letter_position2+1) );
				    } else {
				    	$Team = "-";
				    }
					
				}
				if ( $child1->getName() == "UserID") {
					$UserID = $child1;
					echo '<td bgcolor="#CCCCCC" nowrap="nowrap">'.$xmluser->Pulses.'</td>';
					$Pulses = $xmluser->Pulses;
					$Rank_Keys = $xmluser->Ranks->Keys;
					$Rank_Clicks = $xmluser->Ranks->Clicks;
					$Rank_Download = $xmluser->Ranks->Download;
					$Rank_Upload = $xmluser->Ranks->Upload;
					$Rank_Uptime = $xmluser->Ranks->Uptime;
				}
				if ( $child1->getName() == "Keys") { $Keys1 = $child1; }
				if ( $child1->getName() == "Clicks") { $Clicks = $child1;	}
				if ( $child1->getName() == "DownloadMB") { $DownloadMB = $child1;	}
				if ( $child1->getName() == "UploadMB") { $UploadMB = $child1;	}
				if ( $child1->getName() == "Download") { $Download = $child1;	}
				if ( $child1->getName() == "Upload") { $Upload = $child1;	}
				if ( $child1->getName() == "UptimeSeconds") { $UptimeSeconds = $child1;	}
				if ( $child1->getName() == "UptimeShort") { $UptimeShort = $child1;	}
				if ( $child1->getName() == "UptimeLong") { $UptimeLong = $child1;	}
				if ( $child1->getName() == "LastPulse") { $LastPulse = $child1;	}
				if ( $LastPulse == '0000-00-00 00:00:00' ) { $LastPulse = '1000-01-01 00:00:00'; } //date 0000-00-00 00:00:00 not valid in sql
			}
		echo '</tr>';

		if ($new == 0) {
			$query = "INSERT INTO whatpulse_aapdata (datum, Username, Team, UserID, Pulses, Keys1, Clicks, DownloadMB, UploadMB, Download, Upload, UptimeSeconds, UptimeShort, UptimeLong, Rank_Keys, Rank_Clicks, Rank_Download, Rank_Upload, Rank_Uptime, LastPulse) VALUES ('$today', '$Username', '$Team', '$UserID', '$Pulses', '$Keys1', '$Clicks', '$DownloadMB', '$UploadMB', '$Download', '$Upload', '$UptimeSeconds', '$UptimeShort', '$UptimeLong', '$Rank_Keys', '$Rank_Clicks', '$Rank_Download', '$Rank_Upload', '$Rank_Uptime', '$LastPulse' )";
			$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
			$insert = 1;
		}
		
		$query = "select id, UserID from whatpulse_aapdata WHERE datum = '$today' AND UserID='$UserID'";
		$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
		$new1 = 0;
		if($result->num_rows > 0) {
		    while($row = $result->fetch_assoc()) {
		        $new1++;
				$curid = $row['id'];
				$curuserid = $row['UserID'];
		    }
		}

		if ($new1 == 1 && $insert == 0) {
			$query = "DELETE FROM `whatpulse_aapdata` WHERE id = '$curid' AND UserID='$curuserid'";
			$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
			$query = "INSERT INTO whatpulse_aapdata (id, datum, Username, Team, UserID, Pulses, Keys1, Clicks, DownloadMB, UploadMB, Download, Upload, UptimeSeconds, UptimeShort, UptimeLong, Rank_Keys, Rank_Clicks, Rank_Download, Rank_Upload, Rank_Uptime, LastPulse) VALUES ('$curid', '$today', '$Username', '$Team', '$UserID', '$Pulses', '$Keys1', '$Clicks', '$DownloadMB', '$UploadMB', '$Download', '$Upload', '$UptimeSeconds', '$UptimeShort', '$UptimeLong', '$Rank_Keys', '$Rank_Clicks', '$Rank_Download', '$Rank_Upload', '$Rank_Uptime', '$LastPulse' )";
			$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
		}	elseif ($new1 == 0) {
			$query = "INSERT INTO whatpulse_aapdata (datum, Username, Team, UserID, Pulses, Keys1, Clicks, DownloadMB, UploadMB, Download, Upload, UptimeSeconds, UptimeShort, UptimeLong, Rank_Keys, Rank_Clicks, Rank_Download, Rank_Upload, Rank_Uptime, LastPulse) VALUES ('$today', '$Username', '$Team', '$UserID', '$Pulses', '$Keys1', '$Clicks', '$DownloadMB', '$UploadMB', '$Download', '$Upload', '$UptimeSeconds', '$UptimeShort', '$UptimeLong', '$Rank_Keys', '$Rank_Clicks', '$Rank_Download', '$Rank_Upload', '$Rank_Uptime', '$LastPulse' )";
			$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
		}	
				
		$i++;
	}
	echo '</table>';
}

$time = microtime(); 
$time = explode(" ", $time); 
$time = $time[1] + $time[0]; 
$deel = $time; 
$totaltime = ($deel - $start); 
echo "<br>";

printf ("This took %f seconds to load.", $totaltime);
?>