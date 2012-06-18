<?php

##################################################################################
#
#   Baboviolent 2 receiving game report script
#
#   v 0.5
#   July 02nd, 2010  11:20
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
		'62.152.62.166', // gamenest.ru servers (including brlm)

		'213.27.30.69', // azov ctf
		'80.80.104.172', // rostov ctf

		'89.150.97.228', // vudip cssyd

		'178.63.232.115', // websnooker.com

		'94.23.198.209', // pordesign.eu rs1

		'81.59.216.158', // babo violent europe (crapper's)

		// standouthost
		'184.154.83.115',
		'176.9.41.117',
		'108.60.159.11',
		'205.185.119.80',
		'216.189.1.221',
		'213.229.82.244',

		/*
		'46.4.184.106', // jmainguy germany
		'213.229.82.244', // standouthost.com united kingdom
		'188.40.136.150', // standouthost.com germany
		'108.60.159.11', // standouthost.com new york
		'85.153.34.22', // standouthost.com easten euro
		'216.231.132.19', // standouthost.com chicago
		'109.169.63.142', // standouthost.com new jersey
		'74.63.229.145', // standouthost.com texas
		'216.189.1.221', // standouthost.com south carolina
		*/

		'41.185.60.18', // south africa wage
		'196.38.180.95', // south africa is

		'210.50.4.15', // ipgn bv2

		'144.140.154.81', // gamearena

		'81.24.121.18', // realweb

		'143.107.97.120', // Joao Pedro (Brazil)
		'187.38.116.36',  // Joao Pedro (Brazil)

		'46.173.208.224', // parking.ru

		'176.9.41.117', // Standouthost Germany
		'108.60.159.11', // Standouthost New York
		'205.185.119.80', // Standouthost San Jose
		'69.85.88.33', // Standouthost South Carolina
		'213.229.82.244', // Standouthost United Kingdom

		'85.195.123.70', // Fragnet
	);



// What response would the server get
	$output_for_server = 'OK. Thank you. Reported at: '.date('d.m.y H:i').' (Moscow time)';

	if(!in_array($_SERVER['REMOTE_ADDR'], $allowedIPS))
	{
		$trusted = false;
		$output_for_server = "Untrusted report. Please contact ketamine (ketamine@warlabs.ru)";
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

	ob_end_flush();
	flush();

	sleep(1);


// Woot ?

	error_reporting(0);
	if(!isset($_REQUEST['report']) || $_REQUEST['action'] != 'report' ) die('fail');
	//fputs(fopen('a.txt','w+'),print_r($_REQUEST,true)."\r\n\r\n");

// someone sending fake reports? nabs!

	if(!$trusted) fail_report("Untrusted server: " . $_SERVER['REMOTE_ADDR']);

// Defining errors

    define('NOT_ENOUGH_PLAYERS_ERR','Not enough players');
	define('NOT_ENOUGH_REGISTERED_PLAYERS_ERR','Not enough registered players');


// Connection

	require_once '../mysql.php';




// Parse report

	$report = base64_decode($_REQUEST['report']);

	if ($SAVE_REPORT)
	{
		$_ILtime = time();
		$_ILt = date('H_i_s', $_ILtime);
		$_ILdir = dirname(__FILE__).'/gamereports/'.date('y_m_d', $_ILtime).'/';
		@mkdir($_ILdir, 0777, true);

		$file_parsed = $_ILdir.$_ILt.'_'.$_SERVER['REMOTE_ADDR'].'.xml';
		@file_put_contents($file_parsed, $report);
		@chmod($file_parsed, 0666);

	}


	$colors = Array
	(
		'' => 'blue',
		'' => 'green',
		'' => 'cyan',
		'' => 'red',
		'' => 'pink',
		'' => 'orange',
		'' => 'grey',
		'' => 'white',
		'	' => 'yellow',
		//'<![CDATA[' => '',
		//']]>' => ''
	);

function _obj2array ( $data ) {
	if (is_object($data))
	{
		$data = get_object_vars($data);
	}
	$data =  is_array($data)
		? array_map('_obj2array', $data)
		: iconv('utf-8', 'windows-1252', $data);
	return $data;
}


	$report = preg_replace('/(&#x0[1-9];)|[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/','',$report);
	$report = str_replace(chr(10), '', $report);
		//$report = preg_replace('/<!\[CDATA\[(.*?)\]\]>/ie', '', $report); // Removing CDATA <name> because it contains malicious symbols
	$report = str_replace(array_keys($colors),$colors,$report);
	$report = iconv('CP850', 'UTF-8', $report);

	$report = _obj2array(new SimpleXMLElement($report, LIBXML_NOCDATA));


// At least 2 registered members needed on field to count the report valid

	if(count($report['game']['players']['player'])<1 ) fail_report(NOT_ENOUGH_PLAYERS_ERR);
    if(isset($report['game']['players']['player']['playerid'])) fail_report(NOT_ENOUGH_REGISTERED_PLAYERS_ERR);

// Checking the report against some validity rules

	$isInsta = 0; // Was the game played in instagib mode

// Gloabal vars to store stuff

	$queries = array();		// Stores the queries to perform in the database


// Updating some stats

	foreach($report['game']['players']['player'] as $player)
	{

		// annulate negative values

		$player['kills'] = max(0,$player['kills']);
		$player['deaths'] = max(0,$player['deaths']);
		$player['damage'] = max(0,$player['damage']);
		$player['caps'] = ($report['game']['gameinfo']['gamemode'] != 'CTF') ? 0 : max(0,$player['caps']);
		$player['returns'] = ($report['game']['gameinfo']['gamemode'] != 'CTF') ? 0 : max(0,$player['returns']);
		$player['attempts'] = ($report['game']['gameinfo']['gamemode'] != 'CTF') ? 0 : max(0,$player['attempts']);






		// Personal stats

		if($player['kills'] == $player['damage']) $isInsta++;

		// if($player['deaths'] > 0 && $player['kilss']/$player['deaths'] > 4 && $player['kills']>30)
		// fail_report('Hell no');

		if($player['deaths'] == 0 && $player['kills'] > 20) fail_report('Peter petrelly: 20 kls, 0 deaths?');
		// 3.014 was TOF67 best score in the old ladder

		if($player['kills'] > 60 && $player['kills']/$player['deaths']>3.1)
		  fail_report('more then 60 kills and incredible ratio is not allowed. Try being noob..');

		if($player['kills'] > 150) fail_report('Superman ftw >150 kills');


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
		WHERE `id` =" .intval($player['playerid']);

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

	global $report, $report2, $_ILtime;

    $ILtime = $_ILtime; //time();//+7*3600+1800; // Israel time

	$svname = str_replace
    (
        array('\\' ,'/', ':', '*', '?', '"', '<', '>', '|', ' ') ,
        '',
        $report['game']['gameinfo']['servername']
    );

	if($svname == '') $svname = "forbidden_filename";

	@file_put_contents($file, 		print_r($report,1)."\r\n" .
		$str ."\r\n".$phperror_msg. "\r\n\r\n".
		$report2."\r\n\r\n".
		"Server IP:" . $_SERVER['REMOTE_ADDR']." In array: " .((int) in_array($_SERVER['REMOTE_ADDR'], $GLOBALS['allowedIPS'])).
		"\r\n".$svname."\r\nCount: ".count($report['game']['players']['player'])
	);

	@chmod($file, 0777);

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


