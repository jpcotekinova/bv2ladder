<?php


$db = mysql_connect('%host%','%login%','%password%');
mysql_select_db('%database%',$db);

mysql_query('SET NAMES \'UTF8\'',$db);
if(!$db || mysql_error()!='') die('DataBase Error '.mysql_error());


// Another var

	$regions = Array
	(
		'eu'	=> 'Europe',
		'na'	=> 'North America',
		'au'	=> 'Australia',
		'ru'	=> 'Russia',
		'sa'	=> 'South Africa',
		'other' => 'Other'
	);