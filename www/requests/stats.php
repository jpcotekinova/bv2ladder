<?php

##################################################################################
#
#   Baboviolent 2 stats feeder
#
#   v 0.2
#   Dec 15th, 2009  11:35
#	June 03, 2010	22.59
#   Sasha / msn: me@7sasha.ru / jabber: raskin@aoeu.ru
#
#	People request stats
#	We give them
#   This is basically used by the baboviolent.ru site to display top4 russian players
#
#################################################################################

if(!isset($_GET['param'])) die();

// Connecting to DB
require_once '../mysql.php';


switch($_GET['param']):

	case 'rutop4':
		$F1 = mysql_query
		(
			'SELECT * FROM `legacy_stats`
			LEFT JOIN `legacy_players` ON `legacy_players`.`id`=`legacy_stats`.`uid`
			LEFT JOIN `legacy_countries` ON `legacy_countries`.`id` = `legacy_players`.`country`
			WHERE `legacy_players`.`region`=\'ru\' AND `legacy_stats`.`deaths` > 50 and `legacy_userbarrank`>0
			ORDER BY `legacy_userbarrank` LIMIT 4'
		);
		while($row = mysql_fetch_assoc($F1)) $arr[] = $row['accountname'];
		echo serialize($arr);
	break;

	case 'userstats':
		if(	!isset($_REQUEST['username']) ||  empty($_REQUEST['username'])	  ) break;

		$F1 = mysql_query
		(
			"SELECT
				`legacy_stats`.`time` as 'timeOnField',
				`legacy_stats`.`kills`,
				`legacy_stats`.`deaths`,
				`legacy_stats`.`damage`,
				`legacy_stats`.`caps` as 'captures',
				`legacy_stats`.`returns`,
				`legacy_stats`.`attempts`,
				`legacy_stats`.`userbartitle`,
				`legacy_stats`.`regionaluserbarrank` as 'regionalRank',
				`legacy_stats`.`userbarrank` as 'worldWideRank'


			FROM `legacy_stats` , `legacy_players`
			WHERE `legacy_players`.`accountname` = '".mysql_real_escape_string($_REQUEST['username'])."'
			AND `legacy_stats`.`uid` = `legacy_players`.`id`
			LIMIT 1"
		);

		if(mysql_num_rows($F1) < 1) echo "User not found";
		else echo serialize(mysql_fetch_assoc($F1)) ;

	break;

endswitch;

die();
