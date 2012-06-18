<?php

// No direct access to this script
	if(!isset($included)) die();

// Userbars location
	$userbarsLocation = 'http://ladder.baboviolent2.ru'.dirname($_SERVER['PHP_SELF']);


// Generating drop down list of regions
	/*
	$reginosList = '';
	foreach($regions as $abbr => $name)
	{
		$selected = "";
		if($abbr == $_SESSION['user']['region']) $selected = " selected='selected' ";
		$reginosList .= "<option value='$abbr' $selected >$name</option>\r\n";
	}
	*/


// USERBAR text

	if( isset( $_SESSION['stats']['userbartitle']) ) $userbartitle = &$_SESSION['stats']['userbartitle'];
	else $userbartitle = mysql_result(mysql_query("SELECT `userbartitle` FROM `legacy_stats` WHERE `uid`=".$myid),0,0);

?>



<!-- TITLE -->

	<b style="text-shadow:0px 1px 1px #fff; ">Preferences:</b><br><br>
	<style>.inp {background-color:#595959; color:white; width:140px;}</style>
	<?php if(isset($output)) echo $output,'<br>'; /* Generated in the GP_handler.php */ ?>




<!-- CHANGE PREFERENCES -->


	<form method="post" >
	<table>


	<tr>
		<td>Country: </td>
		<td><select name="prefcountry" id="prefcountry" style="width:201px;" ><?php require 'static/countries.htm'; ?></select></td>
		<td><i style="font-size:90%">&nbsp;&nbsp;&nbsp;do you want a flag?</i></td>
	</tr>

	<!-- Playing Region Form commented out
	<tr>
		<td>Playing region: </td>
		<td><select class="list" name="prefregion" style="width:200px;"><?php /* echo $reginosList;*/ ?></select></td>
		<td><i style="font-size:90%">&nbsp;&nbsp;&nbsp;your playing location</i></td>
	</tr>
	-->

	<tr>
		<td>Display offline friends: </td>
		<td>
			<?php echo '<select class="list" name="prefshowoffline" style="width:200px;">
				<option ',($_SESSION['user']['showoffline']==1 ? "selected='selected'" : ''),' value="1">Yes, please</option>
				<option ',($_SESSION['user']['showoffline']==0 ? "selected='selected'" : ''),' value="0">No, thank you</option>
				</select>'; ?>
		</td>
		<td><i style="font-size:90%">&nbsp;&nbsp;&nbsp;</i></td>
	</tr>


	<tr>
		<td>Userbar text: </td>
		<td><input type="text" name="prefuserbartext" class="inp" value="<?php echo htmlspecialchars($userbartitle); ?>" style="width:200px;"></td>
		<td><i style="font-size:90%">&nbsp;&nbsp;&nbsp;you better type your nickname in here </i></td>
	</tr>


	<tr>
		<td>New password: </td>
		<td><input type="text" name="prefnewpass" class="inp" style="width:200px;"></td>
		<td><i style="font-size:90%">&nbsp;&nbsp;&nbsp;or leave empty </i> </td>
	</tr>

	<tr><td colspan="2" align="right"><input type="submit" value="save" name="prefsubmit"></td><td></td></tr>

	</table>
	</form>



	<br>

	Your ladder ID is : <b><?php echo $myid; ?></b><br>
	<?php if($_SESSION['user']['last_stats_drop'] < time()-14*24*3600): ?>
	<br>
	<a style="color:gold; text-decoration:underline;"
	   href="javascript:if(confirm('Are you sure you want to drop your stats?')) document.location='?stats&drop'">Drop my stats</a> &nbsp;
    <i style="font-size:90%">(14 days from drop to drop)</i><br>
	<?php endif ?>
	<br>

	<img src="<?php echo $userbarsLocation,'userbars/', $myid, '.png?dontsave&', rand() ?>" alt="userbar"><br>
	<input type="text" style="width:350px; border:none; background:transparent; color:#E4C48B; font-size:small; margin-top:10px;"
		   value="[img]<?php echo $userbarsLocation,'userbars/', $myid; ?>.png[/img]">
	<br>

	<br><i style="font-size:90%">Userbars being updated once a day.</i>


<script language="javascript" type="text/javascript">
(function(){
var c = document.getElementById('prefcountry'),
	l = c.length,
	o = c.options;
	while (l)
	{
		if (c[--l].value == <?php echo ($_SESSION['user']['country']);?>)
		{
			c.selectedIndex = l;
			l = 0;
		}
	}
})()
</script>
