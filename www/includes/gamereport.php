<?php

##################################################################################
#
#   Baboviolent 2 receiving game report script
#
#   v 0.4
#   June 08th, 2010  11:20
#   Sasha / msn: me@7sasha.ru / jabber: raskin@aoeu.ru
#
#	The list of commands to manipulate server reporting
#
#	- addreporturl
#	- listreporturls
#	- removereporturl
#	- removeallreporturls
#	- set sv_report true
#
#################################################################################



// Do you want the report to be saved ?
	$SAVE_REPORT = true;


// VAlidate SERVERS IP

	$trusted = true;
	$allowedIPS = Array
	(
		'94.23.198.209',
		'66.36.231.106',
		'188.165.211.46',
		'81.59.216.158',
		'94.23.198.209',
		'62.152.62.166',
		'72.44.89.18',
		'216.52.143.176',
		'196.38.180.95',
		'202.22.232.146',
		'60.234.75.82',
		'202.60.88.247',
		'77.66.207.196'
	);



// What response would the server get
	$output_for_server = 'ok. Thank you. reported at: ',date('d.m.y H:i', time()+7*3600+900); // Isreali time
	if(!in_array($_SERVER['REMOTE_ADDR'], $allowedIPS))
	{
		$trusted = false;
		$output_for_server = "Untrusted report. Please contact Sasha";
	}

// Sending reply, closing the connection and continue proccessing

	ob_end_clean();
	header("Connection: close\r\n");
	header("Content-Encoding: none\r\n");
	ignore_user_abort(true); // optional
	ob_start();

	echo $output_for_server;

	$size = ob_get_length();
	header("Content-Length: $size");
	ob_end_flush();     // Strange behaviour, will not work
	flush();            // Unless both are called !
	ob_end_clean();

	sleep(1);
	if(!$trusted) die();

// Defining errors

    define('NOT_ENOUGH_PLAYERS_ERR','Not enough players');
	define('NOT_ENOUGH_REGISTERED_PLAYERS_ERR','Not enough registered players');

// Woot ?

	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
	if(!isset($_REQUEST['report']) || $_REQUEST['action'] != 'report' ) die('fail');
	//fputs(fopen('a.txt','w+'),print_r($_REQUEST,true)."\r\n\r\n");



// Connection

	require_once '../mysql.php';


// awwww

	$colors = Array
	(
		'0' => '',
		'' => 'blue',
		'' => 'green',
		'' => 'cyan',
		'' => 'red',
		'' => 'pink',
		'' => 'orange',
		'' => 'grey',
		'' => 'white',
		'	' => 'yellow',
		'<![CDATA[' => '',
		']]>' => ''
	);


// Parse report

	$report = base64_decode($_REQUEST['report']);
	$report = preg_replace('/(&#x0[1-9];)|[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/','',$report);
	//$report = preg_replace('/<!\[CDATA\[(.*?)\]\]>/ie', '', $report); // Removing CDATA <name> because it contains malicious symbols
	$report = str_replace(array_keys($colors),$colors,$report);
	$report = xml2array($report) or fail_report('XML2Array error');




// At least 2 registered members needed on field to count the report valid

	/*if( count($report['bv2gamereport']['game']['players']['player']) < 2) fail_report(NOT_ENOUGH_REGISTERED_PLAYERS_ERR);*/
    if(isset($report['bv2gamereport']['game']['players']['player']['playerid'])) fail_report(NOT_ENOUGH_REGISTERED_PLAYERS_ERR);

// Checking the report against some validity rules

	$isInsta = 0; // Was the game played in instagib mode



// Gloabal vars to store stuff

	$queries = array();		// Stores the queries to perform in the database


// Updating some stats

	foreach($report['bv2gamereport']['game']['players']['player'] as $player)
	{

		// Personal stats

		if($player['kills'] == $player['damage']) $isInsta++;

		// if($player['deaths'] > 0 && $player['kilss']/$player['deaths'] > 4 && $player['kills']>30)
		// fail_report('Hell no');

		if($player['deaths'] == 0 && $player['kills'] > 20) fail_report('Peter petrelly: 20 kls, 0 deaths?');
		// 3.014 was TOF67 best score in the old ladder

		if($player['kills'] > 60 && $player['kills']/$player['deaths']>3.1)
		  fail_report('more then 60 kills and incredible ratio is not allowed. Try being noob..');

		if($player['kills'] > 150) fail_report('Superman ftw ?150 kills');


		if($isInsta >= 2)	 fail_report('Insta');
		//if($isStatsSpanning) fail_report('Cheat away');
		//if($isWrongTiming)	 fail_report('from 5 to 20 minutes per match please');

		//$playerIDs[] = $player['playerid'];

		$queries[] =
		"
			UPDATE `legacy_stats` SET
				`time` = `time`+'".floatval($player['time'])."',
				`kills` = `kills` + ".intval($player['kills']).",
				`deaths` = `deaths` + ".intval($player['deaths'])." ,
				`damage` = `damage` + ".floatval($player['damage'])." ,
				`caps` = `caps` + ".intval($player['caps'])." ,
				`returns` = `returns` + ".intval($player['returns'])." ,
				`attempts` = `attempts` +  ".intval($player['attempts']).",
				`last_update` = UNIX_TIMESTAMP()
			WHERE `uid` = ".intval($player['playerid'])."
			LIMIT 1;
		";

		$queries[] = "
		UPDATE `legacy_players` SET
		`last_used_nick` = '".mysql_real_escape_string(trim($player['name']))."'
		WHERE `id` =" .intval($player['playerid'])	;

	}


// Proceed queries

	for($i = 0; $i < count($queries); $i++) mysql_query($queries[$i]);
	fail_report('Successfully saved ' . mysql_error());




/**
* Saves the report and aborts the script
*
* If the error is NOT_ENOUGH_PLAYERS_ERR - the report is not being saved. Why would we need it.
* @param mixed $str - error text
* @author — Sasha
*/
function fail_report($str = '')
{

	// If you do not want to save the report
	if(!$GLOBALS['SAVE_REPORT']) die($str);

    //if($str == NOT_ENOUGH_REGISTERED_PLAYERS_ERR) die(); // Empty report
	global $report, $report2;

    $ILtime = time()+7*3600+900; // Israel time
	// the time+7 hours is to make it Jerusalem's time. Easier for me to track stuff if needed
	mkdir('gamereports/'.date('d_m_y', $ILtime));

	$svname = str_replace
    (
        array('\\' ,'/', ':', '*', '?', '"', '<', '>', '|') ,
        '',
        $report['bv2gamereport']['game']['gameinfo']['servername']
    );

	$saveReportF = fopen('gamereports/'.date('d_m_y', $ILtime).'/'.date('H_i', $ILtime).'_'.$svname.'.txt','w+');
	fputs($saveReportF,print_r($report,1)."\r\n" . $str .$phperror_msg. "\r\n\r\n".$report2);

	die($str);
}

















/**
* xml2array() will convert the given XML text to an array in the XML structure.
* Link: http://www.bin-co.com/php/scripts/xml2array/
* Arguments : $contents - The XML text
* $get_attributes - 1 or 0.
* Return: The parsed XML in an array form.
*/

function xml2array($contents, $get_attributes=0)
{

	if(!$contents) return false;
	if(!function_exists('xml_parser_create')) return false;


	//Get the XML parser of PHP - PHP must have this module for the parser to work
	$parser = xml_parser_create();
	xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
	xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
	xml_parse_into_struct( $parser, $contents, $xml_values );
	xml_parser_free( $parser );

	if(!$xml_values) return false; //Hmm...

	//Initializations
	$xml_array = array();
	$parents = array();
	$opened_tags = array();
	$arr = array();

	$current = &$xml_array;

	//Go through the tags.
	foreach($xml_values as $data)
	{
		unset($attributes,$value);//Remove existing values, or there will be trouble

		//This command will extract these variables into the foreach scope
		// tag(string), type(string), level(int), attributes(array).
		extract($data);//We could use the array by itself, but this cooler.

		$result = '';
		//The second argument of the function decides this.
		if($get_attributes)
		{
			$result = array();
			if(isset($value)) $result['value'] = $value;

			//Set the attributes too.
			if(isset($attributes)) {
			foreach($attributes as $attr => $val) {
			if($get_attributes == 1) $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
			/** :TODO: should we change the key name to '_attr'? Someone may use the tagname 'attr'. Same goes for 'value' too */
			}
			}
		} elseif(isset($value))
		{
			$result = $value;
		}


		//See tag status and do the needed.
		if($type == "open") //The starting of the tag "
		{
			$parent[$level-1] = &$current;

			if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
			$current[$tag] = $result;
			$current = &$current[$tag];

			} else { //There was another element with the same tag name
			if(isset($current[$tag][0])) {
			array_push($current[$tag], $result);
			} else {
			$current[$tag] = array($current[$tag],$result);
			}
			$last = count($current[$tag]) - 1;
			$current = &$current[$tag][$last];
			}

		} elseif($type == "complete") //Tags that ends in 1 line "
		{
			//See if the key is already taken.
			if(!isset($current[$tag])) { //New Key
			$current[$tag] = $result;

			} else { //If taken, put all things inside a list(array)
			if((is_array($current[$tag]) and $get_attributes == 0)//If it is already an array…
			or (isset($current[$tag][0]) and is_array($current[$tag][0]) and $get_attributes == 1)) {
			array_push($current[$tag],$result); // …push the new element into that array.
			} else { //If it is not an array…
			$current[$tag] = array($current[$tag],$result); //…Make it an array using using the existing value and the new value
			}
			}

		} elseif($type == 'close') //End of tag "
		{
			$current = &$parent[$level-1];
		}
	}

	return($xml_array);
}





















/*

// Cheking wheather it was a clan match or not. All players belongs to the same team

	$R = mysql_query("SELECT `id` ,`clan` FROM `players` WHERE `id` IN (".implode(',',array_keys($matchmembers)).")");
	while($player = mysql_fetch_assoc($R))
	{
		if($player['clan'] == 0) die();		// Player not in clan. No CM then
		$matchMembersComparison[$player['id']] = $player['clan'];
	}

// The comparison itself
	if(count($matchMembersComparison) != 2) die(); // More then 2 different clans

 	foreach($matchmembers as $player => $clan)
	{
		if(!isset($teamIDs[$clan])) { $teamIDs[$clan] = $matchMembersComparison[$player]; continue; }
		if($matchMembersComparison[$player] != $teamIDs[$clan]) die(); // Different clan players in one team. not cm
	}

// Locate the winner

	$x=$xmlDoc->getElementsByTagName('team');
	foreach($x as $team) $clans[$teamIDs[$team['teamid']]] = array($team['score'],0);

// Calculate ELO

	$R = mysql_query("SELECT `score`, `clanid` FROM `clans` WHERE `clanid` IN (".implode(',',$matchMembersComparison).")");
	while($clan = mysql_fetch_assoc($R)) $clans[$clan['clanid']][1] = $clan['score'];

	// $clans [ clanid ] = array( matchscore, eloscore )
	$winner = $teamIDs[0];
	$loser = $teamIDs[1];
	if($clans[ $winner ][0] < $clans[ $loser ][0]) {$winner = $teamIDs[1]; $loser = $teamIDs[0]; }


	if( $clans[ $winner ][0] != $clans[ $loser ][0]  && $clans[ $loser ][1] != $clans[ $winner ][1])
	{
		$quantify = 10 * $clans[ $loser ][1] / $clans[ $winner ][1];
		if($clans[ $winner ][0] == $clans[ $loser ][0])
		{
			$vic = '`draws` = `draws` + 1';
			$def = '`draws` = `draws` + 1';
		}
		else
		{
			$vic = '`victories` = `victories` + 1';
			$def = '`defeats` = `defeats` + 1';
		}

		mysql_query("UPDATE `clans` SET $vic, `score` = `score` + ".$quantify." WHERE `clanid` = ".$winner);
		mysql_query("UPDATE `clans` SET $def, `score` = `score` - ".$quantify." WHERE `clanid` = ".$loser);
	}
	else mysql_query("UPDATE `clans` SET `draws` = `draws` + 1 WHERE `clanid` = ".$winner ." OR `clanid` = ".$loser);

*/
