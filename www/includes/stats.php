<?php

// No direct access to this script
	if(!isset($included)) die();


// Region specific stats
	$region = @$_GET['region'];
	if(@ $regions[$region] == null) $region = $_SESSION['user']['region'];

?>


<!-- page header + links to regional stats -->


<b style="text-shadow:0px 1px 1px #fff; ">Stats</b>
<div style="float:right"><font size='2'>
	<?php
		foreach($regions as $abbr => $name)
			if($region != $abbr)
				echo "<a href='?stats&region=$abbr'>$name</a> &nbsp;&nbsp;&nbsp;";
	?>
</font></div>
<br><br>


<!-- Main stats table of the region -->


<table class="tblRating" >
	<thead style="background-color:#3D3C3D;">
		<td style="background-color:rgb(51,51,51);"></td>
		<td width="235" class="leftAlign">Player</td>
		<td width="65">Rating</td>
		<td width="70">Damage</td>
		<td width="70">Kills</td>
		<td width="80">Deaths</td>
		<td width="65">Returns</td>
		<td width="65">Caps</td>
		<td width="65">Attempts</td>
		<td width="135">Time</td>
	</thead>

	<?php  require_once 'includes/cache/stats_'.$region.'.htm'; ?>






<?php
###########################################################
##
##		Show user's stats in the table if needed AND a link to drop the stats
##		AND the amount of returns, attempts and captures
##
###########################################################



	// Showing my personal stats if this is my region
	if($region == $_SESSION['user']['region']) echo display_me_in_the_table();
        ?>

	</table>

	<div id="tooltipdiv" style="display:none;position:absolute;"></div>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>
        <script type="text/javascript" > var region = "<?php echo $regions[$region]; ?>";</script>
	<script type="text/javascript" src="/static/tooltips.js"></script>






<?php

###########################################################
##
##		A function to draw one more line to the stats table with current player's stats there
##
###########################################################



	// I made this as function to have it session-cachable for 2 hours
	function display_me_in_the_table()
	{
		// Displaying stats from chache if available
		if( @ $_SESSION['myplacecacheUpdate'] > time()-7200 ) return $_SESSION['myplacecache'];

		// Fetching user's stats
		$_SESSION['stats'] = mysql_fetch_assoc(mysql_query("SELECT * FROM `legacy_stats` WHERE `uid`=".$_SESSION['user']['id']));

		// Shortcut
		$me = &$_SESSION['stats'];

		// Quering database to find out am I top 25 or not ?
		$F1 = mysql_query
		(
			'SELECT COUNT(*) FROM `legacy_stats`
			LEFT JOIN `legacy_players` ON `legacy_players`.`id`=`legacy_stats`.`uid`
			WHERE `userbarrank`<= '.$me['userbarrank'].'
			AND `legacy_players`.`region`=\''.$_SESSION['user']['region'].'\''
		);


		// Calculating ratio if deaths > 0
		$me['ratio'] = ($me['deaths'] >0) ? number_format(($me['kills']/$me['deaths']),2) : 0;

		// Kills per minute
		$me['ptime'] = $me['time'] + 0; // copying time, before making time a readable string


		if($me['ptime'] > 10 ) $me['ptime'] = number_format($me['kills']/($me['ptime']/60),2);
                else $me['ptime'] = 0;

		// Converting seconds into days, hours, mins, secs of playing
		$metime = floor($me['time']);
		$hours = floor($metime/3600);
		$days = floor($hours/24);
		$metime -= $hours * 3600;
		$hours -= $days*24;
		$days = ($days >0) ? $days.'d ' : '' ;

		$me['readabletime'] =
			$days.
			str_pad($hours, 2, "0", STR_PAD_LEFT) . ':'.
			str_pad(floor($metime/60), 2, "0", STR_PAD_LEFT). ':' .
			str_pad(floor($metime%60), 2, "0", STR_PAD_LEFT);



		// there are more then 25 players with better rating in my region -
		$myPlace = mysql_result($F1,0,0);
		if($myPlace>25):

			$countryname = mysql_result(mysql_query
			(
				"SELECT `name` FROM `legacy_countries` WHERE `id`=".$_SESSION['user']['country'])
				,0,0
			);

			$_SESSION['myplacecache'] =
			'<tr>
				<td><b>'.$myPlace.'</b></td>
				<td class="leftAlign name" >
                   <img style="vertical-align: middle;" src="static/flags/'.$countryname.'.png">
				<span onmouseover="popup(this, '.
					$me['ratio'].','.
					$me['caps'].','.
					$me['returns'].','.
					$me['attempts'].','.
					$me['ptime'].','.
					$me['userbarrank'].','. // world wide position
					$myPlace // regional position
				.')"  onmouseout="unpopup()">'.htmlspecialchars($_SESSION['user']['accountname']).'</span>
				</td>
				<td>'.$me['rating'].'</td>
				<td>'.number_format($me['damage'],2).'</td>
				<td>'.$me['kills'].'</td>
				<td>'.$me['deaths'].'</td>

				<td>'.$me['returns'].'</td>
				<td>'.$me['caps'].'</td>
				<td>'.$me['attempts'].'</td>

				<td>'.$me['readabletime'].'</td>
			</tr>';

			// + cache the results
			$_SESSION['myplacecacheUpdate'] = time();
			return $_SESSION['myplacecache'];

		endif;
	}
