<?php

if (!isset($_GET['regenerate_zip_file']))
{
	die;
}

$path = dirname(__FILE__);

require_once('../mysql.php');

$result = mysql_query('SELECT `name`, `filename` FROM `maps` WHERE 1;');
if (!$result)
{
	die;
}

$maps = array();

while ($row = mysql_fetch_assoc($result))
{
	$maps[] = $row;
}


$zip1 = new ZipArchive();
if (!$zip1->open($path.'/bv2-maps.zip', ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE))
{
	die;
}

echo "<pre>ZIP 1\n";

foreach ($maps as $file)
{
	$file['name'] = strtr($file['name'], '/\\.', '___');
	echo $file['filename'], '->', $file['name'], '</pre>';
	$zip1->addFile($path.'/../'.$file['filename'], $file['name'].'.bvm');
}
$zip1->close();
chmod($zip1->filename, 0777);
unset($zip1);

echo "\n\n\n";



sleep(15);

echo "<pre>ZIP 2\n";


$zip1 = new ZipArchive();
if (!$zip1->open($path.'/bv2-maps-thumbs.zip', ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE))
{
	die;
}

foreach ($maps as $file)
{
	$file['name'] = strtr($file['name'], '/\\.', '___');
	echo $file['filename'], '->', $file['name'], '</pre>';
	$zip1->addFile($path.'/../'.$file['filename'], $file['name'].'.bvm');
	$zip1->addFile($path.'/../'.$file['filename'].'.jpg', $file['name'].'.jpg');
}

$zip1->close();
chmod($zip1->filename, 0777);
unset($zip1);

echo "\n\nAll done";
