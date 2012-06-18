<?php

##################################################################################
#
#   Baboviolent 2 authorization script
#
#   v 0.2
#   November 19th, 2009  11:35
#   Sasha / msn: me@7sasha.ru / jabber: raskin@aoeu.ru
#
#	Baboviolent2 server sends requests here to authorize players
#	Respond with player's ID as stored in the database
#
#
#################################################################################


// Connecting to DB
require_once '../mysql.php';

$dir = dirname(__FILE__).'/auth/'.date('Y_m_d').'/';@mkdir($dir);
@file_put_contents($dir.date('Ymd-His.').preg_replace('/[^(\x20-\x7F)]*/','', $_REQUEST['username']).'.txt', print_r(array($_SERVER, $_REQUEST), 1));

if( isset($_REQUEST['action']) || $_REQUEST['action']  != 'auth')
{


	$account = @mysql_real_escape_string(iconv('CP850', 'utf-8', $_REQUEST['username']));
	$pass = @mysql_real_escape_string($_REQUEST['password']);
	// The password already arrives in md5, no need to md5 it again. Just make sure no SQL -injections happen

	if(empty($account) || empty($pass))
	{
		die('0');
	}
	else
	{
		$r = mysql_query("SELECT * FROM `legacy_players` WHERE `accountname`='$account' AND `pass`='$pass' LIMIT 1", $db);
	}

	if (!$r || mysql_num_rows($r)!=1 )
	{
		die(0);
	}


	$row = mysql_fetch_assoc($r);

	die($row['id']);

}

die('0');
