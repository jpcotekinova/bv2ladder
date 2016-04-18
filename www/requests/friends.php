<?php

$DUMP_REQUESTS = false;

$show_all = array(
	13009,
);

//if(!isset($_GET['request'])) error_reporting(0);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);


$isBrowser = (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) && !isset($_GET['debug']);

if(!isset($_REQUEST['action']) && !$isBrowser) die('no request');

// Connecting to DB
require_once '../mysql.php';



if($isBrowser)
{
	session_start();

	if(isset($_SESSION['user']))
	{
		$_REQUEST['username'] = $_SESSION['user']['accountname'];
		$_REQUEST['password'] = $_SESSION['user']['pass'];
	}
	elseif(isset($_COOKIE['rem']))
	{
		$pass = @mysql_escape_string(substr($_COOKIE['rem'],-32));
		$login = @mysql_escape_string(substr($_COOKIE['rem'],0,-32));

		if(!empty($login) && !empty($pass))
		{
			$r = mysql_query
			("
				SELECT `pass` FROM `legacy_players` WHERE
				`accountname`='$login' AND MD5(CONCAT(`pass`,'dksxoshxsh25h2d1tns)(*^(*^(@Isha'))='$pass'
				LIMIT 1", $db
			);

			if($r && mysql_num_rows($r)==1 )
			{
				$_REQUEST['username'] = stripslashes($login);
				$_REQUEST['password'] = mysql_result($r, 0, 0);
			}
		}
	}
	elseif(!isset($_GET['username'], $_GET['pass']))
	{
		header("Location: /index.php?rd=fr");
		die();
	}
}

$colors = Array
(
	'0' => '',
	'' => 'blue',
	'' => 'green',
	'' => 'cyan',
	'' => 'red',
	'' => 'pink',
	'' => 'orange',
	'' => 'white',
	'' => 'grey',
	'	' => 'yellow',
	'blue'   =>'',
	'green'  =>'' ,
	'cyan'   =>'' ,
	'red'    =>'' ,
	'pink'   =>'' ,
	'orange' =>'' ,
	'grey'   =>'' ,
	'white'  => chr(8) ,
	'yellow' =>'	'
);

$login = @mysql_real_escape_string($_REQUEST['username']);
$pass = @mysql_real_escape_string($_REQUEST['password']);

$bad_auth = false;

if(empty($login) || empty($pass))
{
	$bad_auth = true;
}
else
{
	$pR = mysql_query("SELECT * FROM `legacy_players` WHERE `accountname`='$login' AND `pass`='$pass' LIMIT 1");
	if(!$pR || mysql_num_rows($pR)<1)
	{
		$bad_auth = true;
	}
	else
	{
		$row = mysql_fetch_assoc($pR);

		if ($DUMP_REQUESTS)
		{
			file_put_contents('gamereports/friends/' . time() . '.txt', print_r($_REQUEST, 1));
		}

		if ($_REQUEST['action'] == 'updatestatus')
		{
			$l = intval($_REQUEST['location']);
			$sn = isset($_REQUEST['server_name']) ? mysql_real_escape_string($_REQUEST['server_name']) : '';
			$si = isset($_REQUEST['server_ip']) ? mysql_real_escape_string($_REQUEST['server_ip']) : '';
			$sp = isset($_REQUEST['server_port']) ? mysql_real_escape_string($_REQUEST['server_port']) : '';
			$ip = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
			$t = time();

			$ipUpdate = '';
			if (!$isBrowser)
			{
				$ipUpdate = ", `last_used_ip`='$ip'";
			}

			mysql_query("UPDATE `legacy_players` SET `location`='$l', `server_name`='$sn', `last_update`='$t',
					`server_ip`='$si', `server_port`='$sp' $ipUpdate WHERE `id`=" . $row['id']);
			die();
		}
	}
}


$data = $prepend = '';
if($isBrowser || ($_REQUEST['action']=='getfriends'))
{

/*
	$data .= '<friend name="'.$colors['white'].'SERVERS" server_name="--------------------" location="0" last_update="0" server_ip="62.152.62.166" server_port="3333"></friend>';

	$data .= '<friend name="'.$colors['green'].'Public" server_name="'.$colors['white'].'Mo'.$colors['blue'].'sc'.$colors['red'].'ow '.$colors['white'].'CTF" location="2" last_update="0" server_ip="62.152.62.166" server_port="3333"></friend>';
	$data .= '<friend name="" server_name="'.$colors['yellow'].'German CTF" location="2" last_update="0" server_ip="176.9.41.117" server_port="3333"></friend>';
	$data .= '<friend name="" server_name="'.$colors['gray'].'Chicago CTF" location="2" last_update="0" server_ip="184.154.83.115" server_port="3333"></friend>';
	$data .= '<friend name="" server_name="'.$colors['green'].'Texas CTF" location="2" last_update="0" server_ip="74.63.212.147" server_port="3333"></friend>';
	$data .= '<friend name="" server_name="'.$colors['cyan'].'South Africa Public" location="2" last_update="0" server_ip="196.38.180.95" server_port="3335"></friend>';

	$data .= '<friend name="'.$colors['red'].'Private" server_name="'.$colors['red'].'German '.$colors['yellow'].' RealSquad" location="2" last_update="0" server_ip="176.9.41.117" server_port="3226"></friend>';
	$data .= '<friend name="" server_name="'.$colors['green'].'New York '.$colors['yellow'].' Squad" location="2" last_update="0" server_ip="108.60.159.11" server_port="3332"></friend>';

	$data .= '<friend name="'.$colors['white'].'FRIENDS" server_name="--------------------" location="0" last_update="0" server_ip="62.152.62.166" server_port="3333"></friend>';
*/

	if ($bad_auth)
	{
		$data .= '<friend name="'.$colors['red'].'Incorrect username or"  server_name="'.$colors['red'].'password" location="1" last_update="0" server_ip="62.152.62.166" server_port="3333"></friend>';
		$data .= '<friend name="Register '.$colors['yellow'].'for free'.$colors['white'].' at"    server_name="http://'.$colors['cyan'].'ladder.baboviolent2.ru" location="1" last_update="0" server_ip="62.152.62.166" server_port="3333"></friend>';
	}
	else
	{

		$dnu = false;
		if (in_array(intval($row['id']), $show_all))
		{
			$dnu = true;
			$myF = mysql_query
			(
				'SELECT * FROM `legacy_players` WHERE `location` > 0 AND `last_update` > '.(time() - 960).' ORDER BY `location` DESC, `server_name` DESC, `last_update` DESC', $db
			);
		}
		else
		{
			$myF = mysql_query
			(
				'SELECT *, `legacy_players`.`accountname` FROM `legacy_friends`
				LEFT JOIN `legacy_players` ON `legacy_players`.`id`=IF(`legacy_friends`.`id1` <> '.$row['id'].', `legacy_friends`.`id1`,`legacy_friends`.`id2`)
				WHERE `accepted`=1 AND `id1` = '.$row['id'].' OR `id2`='.$row['id'].'
				ORDER BY `legacy_players`.`location` DESC, `legacy_players`.`last_update` DESC', $db
			);
		}

		$outputLength = 0;

		if( isset($_REQUEST['online']) )
		{
			$online = intval($_REQUEST['online']);
			if( $row['showoffline'] != $online)
			{
				$row['showoffline'] = $online;
				mysql_query("UPDATE `legacy_players` SET `showoffline`=$online WHERE `id`=$row[id]");
			}
		}

		// New events - add a note in the friends list
		if( $row['last_site_visit'] < intval(mysql_result(mysql_query("SELECT `val` FROM `legacy_config` WHERE `cfg`='last_update'"),0,0)))
		$data .= '<friend name="'.$colors['cyan'].'New events " server_name="'.$colors['orange'].' on the ladder site" location="2" last_update="0" server_ip="" server_port=""></friend>';

		// Incoming friendsip requests
		if( mysql_result(mysql_query("SELECT COUNT(*) FROM `legacy_friends` WHERE `id2`='".$row['id']."' AND `accepted`=0"),0,0) )
		$data .= '<friend name="'.$colors['cyan'].'New friendship request" server_name="'.$colors['orange'].' on the ladder site" location="2" last_update="0" server_ip="" server_port=""></friend>';

		// Noob
		if($row['country']==72)
		$data .= '<friend name="'.$colors['cyan'].'Change your country" server_name="'.$colors['orange'].'FTW!!" location="2" last_update="0" server_ip="" server_port=""></friend>';

		// If map team
		if($row['inmapteam'] > 0)
		{
			$count = mysql_result(mysql_query("SELECT COUNT(`mid`) FROM `legacy_maps` WHERE `approved`=0"),0,0);
			if($count > 0)
				$data .= '<friend name="'.$colors['green'].$count.' maps are waiting" server_name="'.$colors['yellow'].'for approval" location="2" last_update="0" server_ip="" server_port=""></friend>';
		}

		$t = 0;
		while($y = mysql_fetch_assoc($myF))
		{
			$update = round((time() - $y['last_update'])/60);

			if (!$dnu)
			{
				$me = '2';
				$him = '1';

				if($row['id'] == $y['id1']) { $me = '1'; $him = '2'; }
				if(!$y[$me.'sees'.$him]) continue;

				if($update > 16 && $y['location'] != 0)
				{
					$y['location'] = 0;
					$y['server_name'] = '';
					$y['server_ip'] = '';
					$y['server_port'] = '';
					mysql_query("UPDATE `legacy_players` SET `location`=0,`server_name`='',`server_ip`='',`server_port`='' WHERE `id`=".$y['id'.$him]);

				}
				if($y['location'] == 0 && !$row['showoffline'] ) continue;
			}

			if($isBrowser)
			{
				$y['accountname'] = htmlspecialchars($y['accountname']);
				$y['server_name'] = htmlspecialchars($y['server_name']);
			}

			$temp = '<friend name="'.$y['accountname'].'" server_name="'.$y['server_name'].'" location="'.$y['location'].'" last_update="'.$update.'" server_ip="'.$y['server_ip'].'" server_port="'.$y['server_port'].'"></friend>';
			//if ($outputLength++ > 80 && !$isBrowser) break;
			if (!$isBrowser && ((strlen($data.$temp) + 58) > 8192))
			{
				break;
			}
			$data.= $temp;

			$t++;
		}

		if ($dnu)
		{
			//while($y = mysql_fetch_assoc($myF)) { $t++; }
			$data = '<friend name="Total in last 15 min:" server_name="'.$t.'" location="0" last_update="" server_ip="" server_port=""></friend>'
				.$data;
		}

		// If nobody = tell nobody is online
		if(!$row['showoffline'] && $outputLength == 0 )
		$data .= '<friend name="No friends online" server_name="" location="0" last_update="0" server_ip="" server_port=""></friend>';

	}

	header('Content-Type: text/xml');
	header('Connection: close');

	ob_start();

	// for browsers
	if($isBrowser)
	{

		$data = preg_replace('/(&#x0[1-9];)|[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/','',$data);

		echo '<?', 'xml version="1.0" encoding="UTF-8"?', '>',
			 '<','?xml-stylesheet href="/static/friendslist.xsl" type="text/xsl" ?', '>',
			 '<friends>'.$data.'</friends>';
	}
	// Game client
	else
	{
		echo '<', '?xml version="1.0" encoding="UTF-8"?><friends>', $data.'</friends>';
	}

	$size = ob_get_length();
	header('Content-Length: '.$size);

	ob_end_flush();
	flush();

	die();
}
