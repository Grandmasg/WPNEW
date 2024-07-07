<?php
/**
 * Returns the list of all teams.
 */
require 'connect.php';

if (isset($_GET['Offset']) && !empty($_GET["Offset"])) {
	$Offset = $_GET['Offset'];
} else {
	$Offset = 0;
}

$team = array();
$dag1 = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset, date("Y")));
$query = "
SELECT `Team` as team, count(`Team`) as aantal FROM `whatpulse_aapdata` where `datum` = '" . $dag1 . "' group by `Team` order by Team asc
";

$result = $mysqli->query($query) or die($mysqli->error . __LINE__);
while ($row = $result->fetch_assoc()) {
	if ($row['team'] == "-") {} else {
		$row['teamname'] = substr($row['team'], 1, -1);
		$team[] = $row;
	}
}

// JSON-encode the response
$json_response = json_encode(['data'=>$team], JSON_NUMERIC_CHECK);

// # Return the response
echo $json_response;
?>