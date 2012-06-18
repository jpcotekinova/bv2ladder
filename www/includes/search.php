  <style>
	div.form{ 		text-align:center; 		border:1px solid #000; 		width:25%;	border:1px solid yellow;}
	.form input {		width:70%; 		text-align:center;	}
	.form h2 {		background-color:#FFF5EE;	}
	.form h5 {	background-color:#000;		font-weight:bold;		color: #fff;	}
	table, #admright {line-height: 1.6em;}
	#admright {margin:85px 0 0 220px;}
	#box-table-a
	{
		font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;
		font-size: 12px;
		margin: 10px 0 0 -5px;
		width: 480px;
		text-align: left;
		border-collapse: collapse;
	}
	#box-table-a th
	{
		font-size: 13px;
		font-weight: normal;
		padding: 8px;
		background: #000;
		border-top: 4px solid #aabcfe;
		border-bottom: 1px solid #fff;
		color: #FFFFFF;
		font-weight:bold;
	}
	#box-table-a td
	{
		padding: 2px 8px 2px 8px;
		background: grey;
		border-bottom: 1px solid #fff;
		color: #FFFFFF;
		border-top: 1px solid transparent;
	}
	#box-table-a tr:hover td
	{
		background: #d0dafd;
		color: #339;
	}

  </style>
 </head>
 <body bgcolor="#F2F9FF">

 <?php


 require_once 'mysql.php';



if(isset($_POST['change'])):


	$ban = (float) $_POST['ban']; if($ban > 0)	$ban = (time() + $ban * 3600);
	$id = intval($_POST['id']);

	$allow = 'allowed';
	if(isset($_POST['allow']) && $_POST['curstatus']=='allowed') $allow = 'disallowed'  ;

	if($id > 0) mysql_query("UPDATE `legacy_players` SET `status`='$allow', `temporaryban`='$ban' WHERE `id`=".$id);

endif;

?>

<b style="text-shadow:0px 1px 1px #fff; ">Locate an user</b>
<div style="float:right"><font size='2'><?php echo date('M d, Y  — H:i'); ?></font></div>
<br><br>

<form method="post">
  Username or ID: <input type="text" name="admuname" class="addfriendinp"  style="width:140px;" id="admuname">
  <input type="submit" value="Locate &rarr;" name="admsr" id="admsr" class='goarrow'>
   <br>
</form>


<?php

$result = null;
$order = "ORDER BY `accountname`";


// If a friend request has been sent to someone - searching the friend by name
	if(isset($_POST['admuname']))
	{
		$f = mysql_real_escape_string($_POST['admuname']);
		if(ctype_digit($f)) $order = "ORDER BY `id`";

		$idsearch = '';
		$id = intval($f); if($id > 0) $idsearch = " `id`='$id' OR ";

		$result = mysql_query("SELECT `id`,`accountname` FROM `legacy_players`
		WHERE $idsearch `accountname` LIKE '%$f%' $order");

	}




	if($result):
	?>

		<table id="box-table-a" summary="RS players" style="float:left; ">
		<thead>
			<tr>
				<th scope="col">id</th>
				<th scope="col">Login</th>
			</tr>
		</thead>
		<tbody>

	<?php
	while($p = mysql_fetch_assoc($result))
	{
		$status = $p['status'];
		$banleft = 0;

		if($p['temporaryban']!=0 && $p['temporaryban']>time() )
		{
			$status = 'banned until '.date('d.m.y H:i',$p['temporaryban']);
			$banleft = ceil(($p['temporaryban']-time())/3600);
		}

		$data = $p['id'].';'.$p['accountname'].';'.$p['status'].';'.$banleft;

		echo "<tr id='i".$p['id']."'>
		<td>".$p['id'].".</td>
		<td>".htmlspecialchars($p['accountname'])."</td>
		</tr>";
	}
	?>
		</tbody>
		</table>


	<?php
	endif;
	?>

 <script type="text/javascript" language='Javascript'> document.getElementById('admuname').focus(); </script>
 </body>
</html>
