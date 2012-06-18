<?php

session_start();
header('Content-Type: text/html; charset=utf-8');


if(isset($_SESSION['user']) AND  $_SESSION['user']['admin']) error_reporting(E_ALL);
else error_reporting(0);



// Log out

	if(isset($_GET['logout']))
	{
		$_SESSION = array();
		session_destroy();
		setcookie("rem", '', time()-964000);
		header('Location: index.php');
		die();
	}



// Connecting to DB

	require_once 'mysql.php';



// SOME vars

	$error = '';
	$included = true;


// If user is authorized

	if(isset($_SESSION['user']) )
	{
		require_once 'includes/inner.php';
		die();
	}




// If isset authorization cookie (remember me) - checking who is authorized here

	if( isset($_COOKIE['rem']) )
	{
		$pass = @mysql_escape_string(substr($_COOKIE['rem'],-32));
		$login = @mysql_escape_string(substr($_COOKIE['rem'],0,-32));
		if(!empty($login) && !empty($pass))
		{
			$r = mysql_query
			("
				SELECT * FROM `legacy_players` WHERE
				`accountname`='$login' AND MD5(CONCAT(`pass`,'dksxoshxsh25h2d1tns)(*^(*^(@Isha'))='$pass'
				LIMIT 1", $db
			);

			if($r && mysql_num_rows($r)==1 )
			{
				$_SESSION['user'] = mysql_fetch_assoc($r);
				$_SESSION['stats'] =
			         mysql_fetch_assoc(mysql_query("SELECT * FROM `legacy_stats` WHERE `uid`=".$_SESSION['user']['id']));
				goInside();
			}
		}
	}



// Login procedure

	if(isset($_POST['sub'])):

		$account = @mysql_escape_string($_POST['login']);
		$pass = @md5($_POST['pass']);
		$rem = @$_POST['rem'];



		if(empty($account) || empty($_POST['pass'])) $error = 'Incorrect login-password';
		else $r = mysql_query("SELECT * FROM `legacy_players` WHERE `accountname`='$account' AND `pass`='$pass' LIMIT 1", $db);

		if(!isset($r) || mysql_num_rows($r)!=1 ) $error = 'Incorrect login-password';
		else
		{
			//if($rem)
			setcookie("rem", $_POST['login'].md5($pass.'dksxoshxsh25h2d1tns)(*^(*^(@Isha'), time()+864000);

			$_SESSION['user'] = mysql_fetch_assoc($r);
			$_SESSION['stats'] =
			         mysql_fetch_assoc(mysql_query("SELECT * FROM `legacy_stats` WHERE `uid`=".$_SESSION['user']['id']));

			goInside();
		}

	endif;


// Registration procedure

	if(isset($_POST['reg'])):

		$account =  @mysql_real_escape_string($_POST['account']);
		$pass = @md5($_POST['pass']);

		$region = 'other';
		if( $regions[$_POST['region']] != null ) $region = mysql_real_escape_string($_POST['region']);

		$error = $phperror_msg;
		if( !preg_match("#^([A-z0-9_\.\-])+$#i",$account) ) $error = 'Use only A to z and 0 to 9 in your account name.';
		elseif(trim($_POST['pass']) == '') $error = 'Incorrect account name or password';
		elseif(strpos($account, "@") !== false ) $error = "Choose login name, not email! (@ in the name is forbidden)";
		else
		{
			$r = mysql_query("SELECT COUNT(*) FROM `legacy_players` WHERE `accountname`='$account' LIMIT 1", $db);
			if(mysql_result($r,0,0)==1) $error = 'This account name is already in use. Choose another one';
			else
			{
				$countries = Array
				(
					'eu'	=> 72,
					'na'	=> 230,
					'au'	=> 16,
					'ru'	=> 181,
					'sa'	=> 199,
					'other' => 1
				);
				$country = $countries[$region];

				mysql_query("INSERT INTO `legacy_players` (`accountname`,`pass`,`region`,`country`) VALUES ('$account','$pass','$region','$country')",$db);
				if(mysql_error()!='') $error = 'Database error ';
				else
				{
					$id = mysql_insert_id();
					mysql_query("INSERT INTO `legacy_stats` (`uid`,`userbarrank`) VALUES ($id,$id)");
					$r = mysql_query('SELECT * FROM `legacy_players` WHERE `id`='.$id);
					$_SESSION['user'] = mysql_fetch_assoc($r);
					header("Location: index.php?help");
					die('88');
				}
			}
		}

	endif;



function goInside()
{
	if(isset($_GET['rd']) && $_GET['rd']=='fr')	header("Location: /friends");
	else header("Location: /");
	die();
}


// If the user is not logged in - showing him the outer page. (auth-reg form)
require_once('includes/outer.php');
