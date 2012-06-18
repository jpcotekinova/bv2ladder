<?php

// No direct access to this script
	if(!isset($included)) die();


						###########################
						## FRIENDS LIST SECTION  ##
						###########################


// If a friend request has been sent to someone - searching the friend by name
	if(isset($_POST['fraddname']) AND $_POST['fraddname'] != $_SESSION['user']['accountname'])
	{
		$f = mysql_real_escape_string($_POST['fraddname']);
		$variants = mysql_query("SELECT `id`,`accountname`,`region` FROM `legacy_players` WHERE `accountname` LIKE '%$f%' ORDER BY `accountname`");

		$first = @mysql_result($variants,0,1);
		if(mysql_num_rows($variants) == 1 &&  strtolower($first) == strtolower($f))
		{
			$f = mysql_result($variants, 0, 0);
			$isfriend = mysql_query("SELECT COUNT(`id1`) FROM `legacy_friends` WHERE (`id1`=$myid AND `id2`=$f) OR (`id2`=$myid AND `id1`=$f)");

			if(!mysql_result($isfriend,0,0))
				mysql_query("INSERT INTO `legacy_friends` (`id1`,`id2`,`accepted`,`1sees2`,`2sees1`) VALUES ($myid,$f,0,0,0)");

			header('Location: index.php?friends'); die();
		}
		elseif(mysql_num_rows($variants) < 1) $possiblefriends = 'Nobody has been found';
		else
		{
			mysql_data_seek ( $variants,0);
			$possiblefriends = '';
			$i = 1;

			while($friend = mysql_fetch_assoc($variants))
			{
					if($i++ == 1) $name = 'name="add"'; else $name = "";

					$possiblefriends .=
						"<span style='font-size:80%'>$friend[region]</span>
						<a href='?friends&addid=$friend[id]' $name title='Send friendship request to ".
						htmlspecialchars($friend['accountname'], ENT_QUOTES)."'>".
						htmlspecialchars($friend['accountname'], ENT_QUOTES)."</a><br>\r\n";
			}
		}

	}


// Adding friend by ID
	if(isset($_GET['addid']))
	{
		$f = intval($_GET['addid']);
		if($f > 0 and $f != $_SESSION['user']['id'])
		{
			$isexisting = mysql_query("SELECT `id`,`accountname` FROM `legacy_players` WHERE `id`= $f");
			$isfriend = mysql_result(mysql_query("SELECT COUNT(`id1`) FROM `legacy_friends` WHERE (`id1`=$myid AND `id2`=$f) OR (`id2`=$myid AND `id1`=$f)"),0,0);

			if($isexisting && !$isfriend) mysql_query("INSERT INTO `legacy_friends` (`id1`,`id2`,`accepted`,`1sees2`,`2sees1`) VALUES ($myid,$f,0,0,0)");
			header('Location: index.php?friends'); die();
		}
	}

// Removing someone from the list or rejecting his request
	if(isset($_GET['rem']))
	{
		$rem = intval($_GET['rem']);
		if($rem > 0) @mysql_query("DELETE FROM `legacy_friends` WHERE (`id1`='$myid' AND `id2`='$rem') OR (`id1`='$rem' AND `id2`='$myid')");
		header('Location: index.php?friends'); die();
	}


// Accepting someone's request
	if(isset($_GET['acp']))
	{
		$acp= intval($_GET['acp']);
		if($acp > 0) @mysql_query("UPDATE `legacy_friends` SET `accepted`=1,`1sees2`=1,`2sees1`=1
		WHERE (`id1`='$acp' AND `id2`='$myid') ");
		header('Location: index.php?friends'); die();
	}


// Hiding someone from ingame friends list
	if(isset($_GET['hide']) && isset($_GET['1']))
	{
		$h = intval($_GET['hide']);
		$s = 1; if($_GET['1'] == '2') $s = 0;

		if($h > 0)
		{
			@mysql_query("UPDATE `legacy_friends` SET `1sees2`=$s WHERE `id1`='$myid' AND `id2`='$h' ");
			@mysql_query("UPDATE `legacy_friends` SET `2sees1`=$s WHERE `id2`='$myid' AND `id1`='$h' ");
		}
		header('Location: index.php?friends'); die();
	}




                                                        #####################
                                                        ## CLANS SECTION   ##
                                                        #####################



// Alert displayed on action failure
	$error = '';

// Registering clan
	if(isset($_POST['regClan']))
	{
		$name = @mysql_escape_string($_POST['clanname']);
		$tag = @mysql_escape_string($_POST['clantag']);
		$site = @mysql_escape_string($_POST['clansite']);

		if(empty($name) || empty($tag)) $error = 'Incorrect name or clan tag';
		else
		{
			$r = mysql_query("SELECT COUNT(*) FROM `legacy_clans` WHERE `name`='$name' LIMIT 1", $db);
			if(mysql_result($r,0,0)==1) $error = 'Clan with such name already exists';
			else
			{
				mysql_query("INSERT INTO `legacy_clans` (`name`,`tag`,`site`,`admin`) VALUES ('$name','$tag','$site','$myid')",$db);
				if(mysql_error()!='') $error = 'Database error '.mysql_error();
				else
				{
					$id= mysql_insert_id();
					mysql_query('UPDATE `legacy_players` SET `clan`='.$id.' WHERE `id`='.$myid);
					header('Location: index.php?clan');
					die();
				}
			}
		}

	}

// Clans invitatitos
	if(isset($_POST['claninvs']))
	{
		$id = intval($_POST['claninv']);
		$R = mysql_query("SELECT `admin` FROM `legacy_clans` WHERE `clanid`=".$_SESSION['user']['clan']);

		if(!$R || mysql_num_rows($R)!=1) $id = -10;
		elseif(mysql_result($R,0,0) != $myid) $id=-200;


		if($id > 0) mysql_query("INSERT INTO `legacy_claninvites` (`clanid`,`uid`) VALUES (".$_SESSION['user']['clan'].",$id)");
		header('Location: index.php?clan'); die();
	}

// Revoke Clans invitatitos
	if(isset($_GET['clanrev']))
	{
		$id = intval($_GET['clanrev']);

		$R = mysql_query("SELECT `admin` FROM `legacy_clans` WHERE `clanid`=".$_SESSION['user']['clan']);
		if(!$R || mysql_num_rows($R)!=1) $id = -10;
		elseif(mysql_result($R,0,0) != $myid) $id=-200;


		if($id > 0) mysql_query("DELETE FROM `legacy_claninvites` WHERE `clanid` = ".$_SESSION['user']['clan']." AND `uid`= $id");
		header('Location: index.php?clan'); die();
	}

// Removing someone from the list or rejecting his request
	if(isset($_GET['clandec']))
	{
		$dec = intval($_GET['clandec']);
		if($dec > 0) @mysql_query("DELETE FROM `legacy_claninvites` WHERE `clanid`=".$dec." AND `uid`=".$myid);
		header('Location: index.php?clan'); die();
	}


// Accepting someone's request
	if(isset($_GET['clanacp']))
	{
		$acp= intval($_GET['clanacp']);
		if($acp > 0)
		{
			@mysql_query("DELETE FROM `legacy_claninvites` WHERE `clanid`=".$acp." AND `uid`=".$myid);
			$c = mysql_affected_rows();
			if($c>0)
			{	mysql_query("UPDATE `legacy_players` SET `clan`=".$acp." WHERE `id`=$myid");
				$_SESSION['user']['clan'] = $acp;
			}
		}
		header('Location: index.php?clan'); die();
	}





                                                    ##########################
                                                    ## PREFERENCES SECTION  ##
                                                    ##########################



// If a change request has been sent
	if(isset($_POST['prefsubmit']))
	{
		$change = Array();
		$output = '';

		if(isset($_POST['prefregion']))
		{
			$region = $_POST['prefregion'];
			if(isset($regions[$region]) && $region != $_SESSION['user']['region'])
			{
				$change[] = "`region`='$region' ";
				$output .= "Your region was updated<br>";
				$_SESSION['user']['region'] = $region;
			}
		}

		if(isset($_POST['prefcountry']))
		{
			$country = intval($_POST['prefcountry']);
			if($country >0 && $country != $_SESSION['user']['country'])
			{
				$change[] = "`country`='$country' ";
				$output .= "Your country was updated<br>";
				$_SESSION['user']['country'] = $country;
			}
		}

		if(isset($_POST['prefshowoffline']))
		{
			$showoffline = intval($_POST['prefshowoffline']);
			if( $showoffline >= 0 && $showoffline < 2 && $showoffline != $_SESSION['user']['showoffline'])
			{
				$change[] = "`showoffline`='$showoffline' ";
				$output .= "Friends list display changed<br>";
				$_SESSION['user']['showoffline'] = $showoffline;
			}
		}


		if(isset ($_POST['prefnewpass']) && trim($_POST['prefnewpass'])!='' && md5($_POST['prefnewpass']) != $_SESSION['user']['pass'])
		{
			$change[] = "`pass`='".md5($_POST['prefnewpass'])."' ";
			$output .= "The password was changed. Don't forget to log in the game once again.<br>";
			$_SESSION['user']['pass'] = md5($_POST['prefnewpass']);
		}

		if(isset($_POST['prefuserbartext']) && trim($_POST['prefuserbartext'])!='')
		{
			mysql_query("UPDATE `legacy_stats` SET `userbartitle`='".mysql_real_escape_string($_POST['prefuserbartext'])."' WHERE `uid`=".$myid);
			$_SESSION['stats']['userbartitle'] = $_POST['prefuserbartext'];

			//Manual redraw
			$userbar_generator_via_internal_call = $myid;
			require_once 'userbars/img.php';
		}

		if(count($change)>0)
		mysql_query("UPDATE `legacy_players` SET ".implode(',',$change)." WHERE `id`=".intval($_SESSION['user']['id']));
	}



                                                ##########################
                                                ##    STATS SECTION     ##
                                                ##########################

// Truncate stats
	if(isset($_GET['drop']) && ($_SESSION['user']['last_stats_drop'] < time()-14*24*3600))
	{

        mysql_query
        ("
            UPDATE `legacy_stats`
            SET `kills`=0, `deaths`=0, `damage`=0, `caps`=0, `returns`=0, `attempts`=0,`time`=0, `rating`=0
            WHERE `uid`=$myid
        ");

        mysql_query("UPDATE `legacy_players` SET `last_stats_drop`=UNIX_TIMESTAMP() WHERE `id`=$myid");
        $_SESSION['user']['last_stats_drop'] = time();
        $_SESSION['myplacecache'] = null;
        header('Location: index.php?stats'); die();
	}






	                                            ##########################
                                                ##     MAPS SECTION     ##
                                                ##########################



// Approval

	if($_SESSION['user']['inmapteam'] > 0 && isset($_POST['approve']))
	{
		$type =  @mysql_real_escape_string($_POST['gametype']);
                $size =  @mysql_real_escape_string($_POST['size']);
                if(empty($type) || empty($size)) die('not ok');

		$id = @intval($_POST['mapid']);

		if($id > 0)
		{
			foreach($_POST['tags'] as $tag) if(in_array($tag,$tags)) $TAGS[] = $tag;
			$tags2 = mysql_real_escape_string(implode(',',$TAGS));
			@mysql_query("UPDATE `legacy_maps` SET `approved`=1, `approvedby`=$myid, `gametype`='$type',`size`='$size' WHERE `mid`=$id ");

		}

		unlink(realpath(dirname(__FILE__).'/../').'/maps/all.zip');
		unlink(realpath(dirname(__FILE__).'/../').'/maps/all-thumb.zip');

		die('ok');
	}

	if(
		($_SESSION['user']['inmapteam'] > 0 && isset($_GET['declinemap']) ) ||
		($_SESSION['user']['inmapteam'] ==2 && isset($_GET['deletemap'], $_GET['deletemapforsure']) )
	  )
	{
		$id = isset($_GET['deletemapforsure']) ? @intval($_GET['deletemap']) :  @intval($_GET['declinemap']) ;
		if($id<1) die('woot');

		$file = @mysql_result(mysql_query("SELECT `filename` FROM `legacy_maps` WHERE `mid`=$id"), 0, 0);
		if($file)
		{
			mysql_query("DELETE FROM `legacy_maps` WHERE `mid`=$id");
			unlink($file);
			unlink($file.'.jpg');
		}
		die('ok');
	}
