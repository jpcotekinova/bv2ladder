<?php

##################################################################################
#
#   Baboviolent 2 ranking script ( set on cron )
#
#   v 0.2
#   November 19th, 2009  08:20
#   May 22th, 2009 13:26 - bug fix
#
#   Sasha / msn: me@7sasha.ru
#
#	This calculates everyone's position in the rating
#	making a cache of the top 25 list
#	redraws userbars
#
#################################################################################

chdir(dirname(__FILE__));


require_once 'mysql.php';


error_reporting(E_ALL);


// Updating rating
	mysql_query
	("
		UPDATE `legacy_stats`
		SET `rating` = ROUND(100*(          (`kills`+`damage`)/(2*`deaths`) + `time`/172800           ))
		WHERE `deaths`>49
	");


    	$R1 = mysql_query("SELECT `uid` FROM `legacy_stats` WHERE `deaths`>49 ORDER BY `rating` DESC, `kills` DESC");
		$i = 1; while($R = mysql_fetch_assoc($R1))
		mysql_query("UPDATE `legacy_stats` SET `userbarrank`=".$i++." WHERE `uid` = ".$R['uid']);




foreach ($regions as $region => $regionName):

// regional players rating
    $R1 = mysql_query("SELECT `uid` FROM `legacy_stats`,`legacy_players` WHERE `deaths`>49 AND `legacy_stats`.`uid` = `legacy_players`.`id` AND `legacy_players`.`region` ='$region' ORDER BY `rating` DESC, `kills` DESC");
    $i = 1; while($R = mysql_fetch_assoc($R1)) mysql_query("UPDATE `legacy_stats` SET `regionaluserbarrank`=".$i++." WHERE `uid` = ".$R['uid']);



// Generating stats top 25 list ( and saving it to the cache)

	$F1 = mysql_query
	(
		'SELECT * FROM `legacy_stats`
		LEFT JOIN `legacy_players` ON `legacy_players`.`id`=`legacy_stats`.`uid`
		LEFT JOIN `legacy_countries` ON `legacy_countries`.`id` = `legacy_players`.`country`
		WHERE `legacy_stats`.`deaths`>49 AND `legacy_players`.`region`=\''.$region.'\'
		ORDER BY `userbarrank` LIMIT 25'
	);

	$i = 1; $rating = '';
	while($row = mysql_fetch_assoc($F1) )
	{
		$row['ptime'] = $row['time'] + 0; // copying time, before making time a readable string
		if($row['ptime'] > 10 ) $row['ptime'] = number_format($row['kills']/($row['ptime']/60),2);
                else $row['ptime'] = 0;

                $row['accountname'] = htmlspecialchars( $row['accountname'], ENT_QUOTES);

		$row['time'] = floor($row['time']);
		$hours = floor($row['time']/3600);
		$days = floor($hours/24);
		$row['time'] -= $hours * 3600;
		$hours -= $days*24;
		$days = ($days > 0) ? $days.'d ' : '';
		$row['time'] =
			$days .
			str_pad($hours, 2, "0", STR_PAD_LEFT) . ':'.
			str_pad(floor($row['time']/60), 2, "0", STR_PAD_LEFT). ':' .
			str_pad(floor($row['time']%60), 2, "0", STR_PAD_LEFT);

		if($i == 1) $row['accountname'] = '<b style="color:#FFB90F;">'.$row['accountname'].'</b>';
		if($i == 2) $row['accountname'] = '<b style="color:#E6E8FA;">'.$row['accountname'].'</b>';
		if($i == 3) $row['accountname'] = '<b style="color:#A67D3D;">'.$row['accountname'].'</b>';

		if($row['name'] == '') $row['name'] = 'European Union';

                if($row['deaths'] > 0)  $row['ratio'] = number_format($row['kills']/$row['deaths'],2);
                else $row['ratio'] = 0;

		$rating .=
		'<tr>
		<td>'.$i++.'</td>
		<td class="leftAlign name">
                  <img src="static/flags/'.$row['name'].'.png" style="vertical-align:middle;">&nbsp;&nbsp;
                  <span onmouseover="popup(this, '.
                        $row['ratio'].','.
			$row['caps'].','.
			$row['returns'].','.
			$row['attempts'].','.
                        $row['ptime'].','.
			$row['userbarrank'].','. // world wide position
			($i-1) // regional position
		.')" onmouseout="unpopup()">'.$row['accountname'] .'</span>
		</td>
		<td>'.$row['rating'].'</td>
		<td>'.floor($row['damage']).'</td>
		<td>'.$row['kills'].'</td>
		<td>'.$row['deaths'].'</td>
		<td>'.$row['returns'].'</td>
		<td>'.$row['caps'].'</td>
		<td>'.$row['attempts'].'</td>
		<td>'.$row['time'].'</td>
		</tr>'."\r\n\r\n";
	}


    //chmod('includes/cache/stats.htm',0755);
    // Saving stats in cache
	fwrite(fopen('includes/cache/stats_'.$region.'.htm','w+'), $rating);
	chmod('includes/cache/stats_'.$region.'.htm', 0777);

echo $region."\n";

endforeach;

//echo "all ok?\n";

// Redrawing userbars twice a day.
$CRON_CALLED  = true;
if(date('H') == 1 || date('H') == 13) {
	echo "runing userbar generation\n";
	require_once 'userbars/img.php';
	echo "done userbar generation\n";
}

echo "done.\n\n";

file_put_contents('includes/cache/log.txt', date('d.m.Y H:i')." -- updated ladder stats.\n", FILE_APPEND);
chmod('includes/cache/log.txt', 0777);
