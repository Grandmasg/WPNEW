<?php
error_reporting(1);
/**
 * Returns the list of Daily changes.
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

$dag = date("Y-m-d", mktime(date("H") - 4, 0, 0, date("m"), date("d") + $Offset, date("Y")));
$dag1 = date("Y-m-d", mktime(date("H") - 4, 0, 0, date("m"), date("d") + $Offset - 1, date("Y")));
$dag2 = date("Y-m-d", mktime(date("H") - 4, 0, 0, date("m"), date("d") + $Offset - 2, date("Y")));

$query = "
SELECT * FROM `whatpulse_aapdata`
WHERE datum = '$dag'
";
$result = $mysqli->query($query) or die($mysqli->error . __LINE__);

if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		$today[] = $row;
	}
}

$query = "
SELECT * FROM `whatpulse_aapdata`
WHERE datum = '$dag1'
";
$result = $mysqli->query($query) or die($mysqli->error . __LINE__);

if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		$yesterday[] = $row;
	}
}

$thesameastoday = array();
$added = array();
foreach ($today as $addedtoday) {
  if(!in_array($addedtoday['Username'], array_column($yesterday, 'Username'))){
	if (in_array($addedtoday['UserID'], array_column($yesterday, 'UserID'))){
	  $thesameastoday[] = $addedtoday;
	} else {
	  $added = $addedtoday;
	}
  }  
}

$thesameasyesterday = array();
$left = array();
foreach ($yesterday as $lefttoday) {
  if(!in_array($lefttoday['Username'], array_column($today, 'Username'))){
	if (in_array($lefttoday['UserID'], array_column($today, 'UserID'))){
	  $thesameasyesterday[] = $lefttoday;
	} else {
	  $left = $lefttoday;
	}
  }  
}

$changed = array();
foreach ($thesameastoday as $namechangedtoday) {
  if(in_array($namechangedtoday['UserID'], array_column($today, 'UserID'))){
	$changed['Date changed'] = $namechangedtoday['datum'];
	$changed['New username'] = $namechangedtoday['Username'];
	foreach ($thesameasyesterday as $namechangedyesterday) {
	  if(in_array($namechangedyesterday['UserID'], array_column($yesterday, 'UserID'))){
		$changed['Old Username'] = $namechangedyesterday['Username'];
	  }  
	}
  }
}

// JSON-encode the response
$json_response = json_encode(['Added'=>$added,'Left'=>$left,'Changed'=>$changed], JSON_NUMERIC_CHECK);
// # Return the response
echo $json_response;
	
?>