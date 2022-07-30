<?php
error_reporting(0);
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
select *, Username as UsernameFull from whatpulse_aapdata WHERE datum = '$dag' AND Username LIKE '%$search%'
GROUP BY UserID
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