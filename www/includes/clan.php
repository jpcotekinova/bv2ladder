<?php

// No direct access to this script
	if(!isset($included)) die();

?>

<b style="text-shadow:0px 1px 1px #fff; ">Your clan:</b><br>

<?php

if($_SESSION['user']['clan'] != 0)
{

	$r = mysql_query('SELECT * FROM `legacy_clans` WHERE `clanid`='.intval($_SESSION['user']['clan']));
	if($r && mysql_num_rows($r)==1) $clan = mysql_fetch_assoc($r);
	else $_SESSION['user']['clan'] = 0;
}

if($_SESSION['user']['clan'] == 0): ?>
You are in no clans at the moment<br><br>
<b>Register your clan:</b>
<form method="post" action="" enctype="multipart/form-data" target="_self">

	<table style="margin-top:15px">
		<tr>
			<td>Clan's name:</td>
			<td ><input type="text" name="clanname" class="list"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			Tag: <input type="text" name="clantag" class="list" style="width:51px;"></td>
		</tr>

		<tr>
			<td>Web Site: </td>
			<td ><input type="text" name="clansite" class="list" style="width:300px;"></td>
		</tr>
		<tr><td colspan='3'><input type="submit" name="regClan" value="Register clan" style="margin-top:13px;"></td></tr>
	</table>

</form>

<?php

	$R = mysql_query("SELECT `legacy_clans`.`name`, `legacy_claninvites`.* FROM `legacy_clans`,`legacy_claninvites`
	WHERE `legacy_claninvites`.`uid`=$myid AND `legacy_claninvites`.`clanid`=`legacy_clans`.`clanid` ");


	if(mysql_num_rows($R) > 0)
	{
		echo '<br> You have been invited to: <br>';
		while($clanny = mysql_fetch_assoc($R))
		{

			echo htmlspecialchars($clanny['name']),'
			<a href="?clanacp=',$clanny['clanid'],'" class="revoke">Accept</a>
			<a href="?clandec=',$clanny['clanid'],'" class="revoke">Decline</a><br>';

		};
	};


 elseif(isset($clan)):

	if(!$clan['img']) $clan['img'] = 'http://eob.clan.su/NoLogo.jpg';

	$rank = mysql_result(mysql_query('SELECT COUNT(`clanid`) FROM `legacy_clans` WHERE `score`<'.$clan['score']),0,0) +1;
	echo '

	<table style="font-size:13px">
	<tr>
	<td  valign="middle" width="185">

		<b><font size="4">',htmlspecialchars($clan['tag']),' ',htmlspecialchars($clan['name']),'</font><br><br>
		Rank: ',$rank,'<br>
		Score: ',$clan['score'],'
		</b>

	</td><td valign="middle" width="130">

		<b>Stats</b><br>
		<br>
		Matches: ',($clan['victories'] + $clan['defeats'] + $clan['draws'] ),'<br>
		Victories: ',$clan['victories'],'

	</td><td valign="middle">
		<br><br>
		Defeats: ',$clan['defeats'],'<br>
		Draws: ',$clan['draws'],'


	</td>
	</tr>
	</table>

	';


	$membersR = mysql_query
	(
		"SELECT `legacy_players`.`accountname`, `legacy_stats`.* FROM `legacy_players`,`legacy_stats`
		WHERE `legacy_players`.`clan`='".$clan['clanid']."' AND `legacy_players`.`id` = `legacy_stats`.`uid`
		ORDER BY `legacy_stats`.`time` DESC "
	);

	echo "<br><table style='color:#C0C0C0;'>";

	$i = 0;
	$memb = Array($myid);

	while($mem = mysql_fetch_assoc($membersR))
	{
		$memb[] = $mem['uid'];

		if( $mem['time']/3600 > 1) { $meters = 'hours'; $mem['time'] = floor($mem['time']/3600); }
		else { $meters = 'minutes'; $mem['time'] = floor($mem['time']/60); }
		if($mem['time'] == 1) $meters = substr($meters,0,-1);
		$mem['time'] .= ' '.$meters.' on filed';

		echo '<tr>
			<td>',(++$i),'.</td>
			<td width="100"><font color="white">',htmlspecialchars($mem['accountname']),'</font></td>
			<td width="200">',$mem['time'],'</td>
			<td width="230">',($mem['caps']+$mem['returns']),' captures and returns</td>
			<td>',$mem['kills'],' kills</td>
		</tr>';


	}

	echo '</table>';


	if($clan['admin'] == $myid)
	{

		$InvitedR = mysql_query('SELECT `legacy_players`.`id`, `legacy_players`.`accountname`
		FROM `legacy_players`,`legacy_claninvites` WHERE `legacy_claninvites`.`clanid`='.$clan['clanid']." AND `legacy_claninvites`.`uid`=`legacy_players`.`id`" );

		$invites = '';
		while($inv = mysql_fetch_assoc($InvitedR))
		{
			$memb[] = $inv['id'];
			$invites .= htmlspecialchars($inv['accountname']).' <a href="?clanrev='.$inv['id'].'" class="revoke">revoke</a><br>';
		}


		$r = mysql_query("SELECT `id`,`accountname` FROM `legacy_players` WHERE `id` NOT IN (".implode(',',$memb).") AND `clan`=0 ORDER BY `accountname`",$db);
		while($p = mysql_fetch_row($r))	$options .= "<option value='$p[0]'>".htmlspecialchars($p[1])."</option>\r\n";

		 if(!empty($options)) echo  '
					<br><br>
					 <form method="post">
					<b>Invite an member:</b><br>

					  <select name="claninv" id="sendrequest">
					  ',$options,'
					  </select>
					  <input type="submit" value="Invite" name="claninvs" id="frsub"><br>
					</form>';

		echo $invites;

		echo '<br><br>';
	}


endif; ?>



<?php if(!empty($error)) echo "<script> alert('$error'); </script>"; ?>