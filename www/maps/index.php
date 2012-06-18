<?php

if(!isset($_GET['dl']) || !ctype_digit($_GET['dl']) || $_GET['dl'] < 1) die();

require_once('../mysql.php');

$row = mysql_query("SELECT * FROM `maps` WHERE `mid`=".intval($_GET['dl']));
if(!$row || !($row = mysql_fetch_assoc($row))) die();


$file = (str_replace('maps/','',$row['filename']));

header ("Content-Type: application/octet-stream");
header ("Accept-Ranges: bytes");
header ("Content-Length: ".filesize($file));
header("Content-Disposition: attachment; filename=\"" . $row['name'] . ".bvm\"");
readfile($file);

