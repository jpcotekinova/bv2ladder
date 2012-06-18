<?php

#
# This script displays friends list
#

// No direct access to this script
	if(!isset($included)) die();


	$options = '';			// List of players to send requests too
	$inlist = Array($myid);         // ID's of players who we wouldn't display in the list of players
	$friends = Array();		// My friends. Gonna be in the that table


	$F1 = mysql_query
	(
		'SELECT *, `legacy_players`.`accountname` FROM `legacy_friends`
		LEFT JOIN `legacy_players` ON `legacy_players`.`id`=IF(`legacy_friends`.`id1` <> '.$myid.', `legacy_friends`.`id1`,`legacy_friends`.`id2`)
		WHERE `id1` = '.$myid.' OR `id2`='.$myid.' AND `accountname` != \'\'
		ORDER BY `legacy_players`.`accountname`', $db
	);


	while($row = mysql_fetch_assoc($F1) )
	{
		$notmyid = $row['id1'];

		$me = '2';
		$him = '1';

		if($row['id1'] == $myid) { $me = '1'; $him = '2'; $notmyid = $row['id2']; }
		$inlist[] = $notmyid;

		if($row['accepted'] == 1)	$friends[] = Array($row['accountname'],$notmyid,$row[$me.'sees'.$him]);
		elseif( $row['id1'] == $myid ) $Irequested[] = Array($row['accountname'],$notmyid);
		else $meRequestedBy[] = Array($row['accountname'],$notmyid);
	}
/*
	$r = mysql_query("SELECT `id`,`accountname` FROM `players` WHERE `region`='".$_SESSION['region']."'
	AND `id` NOT IN (".implode(',',$inlist).") ORDER BY `accountname`",$db);
	while($p = mysql_fetch_row($r))	$options .= "<option value='$p[0]'>".htmlspecialchars($p[1])."</option>\r\n";
*/



?>

<b style="text-shadow:0px 1px 1px #fff; ">Your Friends:</b><br>

<br><a href="/friends" target="_blank" style="color:#FF9900">See who is online</a><br>

				<table class="onoflist friends" cellspacing='0' cellpadding='0' style="float:left">
				<tr>
				<?php

					$totalFriends =  count($friends);

					$rows = 0;
					if($totalFriends > 0)
					{
						$cols = $totalFriends / 6;
						if($cols > 5) $cols = 5;
						$rows = ceil($totalFriends / $cols);
					}

					for($i = 0; $i < $rows; $i++)
					{
						if($i>0) echo "\r\n</tr>\r\n<tr>\r\n";
						for($j = 0; $j<$cols; $j++)
						{
							$class = '';  $c = 0;
							if(!isset($friends[$j*$rows+$i])) break;
							if($friends[$j*$rows+$i][2] == 0) { $class = ' class="hiden"'; $c = 1;}
							echo "\t",'<td><a href="javascript:manage(\'',$friends[$rows*$j+$i][0],'\',\'',$friends[$j*$rows+$i][1],'\',',$c,')" ',
								  $class,'>', htmlspecialchars($friends[$rows*$j+$i][0]),'</a></td>',"\r\n";
						}
					}
				?>
				</tr>
				</table>
				<div style="clear:both"><span id='manage'></span></div>


			<br>

			<table width="100%">
			<tr>
			<td width="60%" valign="top">

			 <?php

			 Echo '
			 <form method="post" action="?friends#add">
			 <b>Send friendship request to:</b><br>
			  <input type="text" name="fraddname" class="addfriendinp"  style="width:240px;" id="fraddname">
			  <input type="submit" value="Send &rarr;" name="fr" id="frsub"><br>
			</form>';

			if( isset($possiblefriends) ) echo $possiblefriends;

/*
			 Echo '
			 <form method="post">
			 <b>Send friend request to:</b><br>

			<select class="list" onchange="document.location=\'?friends&region=\'+this.value+\'\'">
			<option '.($_SESSION['region']=='eu' ? "selected='selected'" : '').' value="1">Europe</option>
			<option '.($_SESSION['region']=='na' ? "selected='selected'" : '').' value="2">North America</option>
			<option '.($_SESSION['region']=='au' ? "selected='selected'" : '').' value="3">Australia</option>
			<option '.($_SESSION['region']=='ru' ? "selected='selected'" : '').' value="4">Russia</option>
			<option '.($_SESSION['region']=='other' ? "selected='selected'" : '').' value="5">Other</option>
			</select>';


			 if(!empty($options)) echo  '
			  <select name="friend" id="sendrequest" class="list">
			  ',$options,'
			  </select>
			  <input type="submit" value="Send &rarr;" name="fr" id="frsub"><br>
			</form>';
*/

			?>
			</td>
			<td valign="top">
				<?php
				if(!empty($meRequestedBy))
				{
					echo '<b>Incoming requests:</b><br>';
					for($i= 0; $i < count($meRequestedBy); $i++)
						echo htmlspecialchars($meRequestedBy[$i][0]),'
						<a href="?acp=',$meRequestedBy[$i][1],'" class="revoke">accept</a>
						<a href="?rem=',$meRequestedBy[$i][1],'" class="revoke">decline</a><br>';
					echo "<br><br>";
				}

				if(!empty($Irequested))
				{
					echo '<b>Outgoing requests:</b><br>';
					for($i= 0; $i < count($Irequested); $i++)
						echo htmlspecialchars($Irequested[$i][0]),' <a href="?rem=',$Irequested[$i][1],'" class="revoke">revoke</a><br>';
				}
				?>


			</td>
			</tr>
			</table>
<script  type="text/javascript">
	function manage(name,id,invisible)
	{
		if(!invisible) hide = '<a href="?hide='+id+'&1=2" class="revoke">Hide in game list<\a>';
		else hide = '<a href="?hide='+id+'&1=3" class="revoke">Show in game list<\a>';
		document.getElementById('manage').innerHTML = name+': <a href="?rem='+id+'" class="revoke">remove<\a> OR '+hide + '<br>';
	}
	document.getElementById('fraddname').focus();
</script>
