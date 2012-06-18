<?php

$dir = '../';

if(!isset($db)) require_once $dir.'mysql.php';

$userbars_folder = $dir.'userbars/';
$userbars_source = 'userbar_source_bj2bkjb3jkb62kj.jpg';

$rankingFont = 'ariblk.ttf';
$titleFont = 'visitor2.ttf';


// Creates and stores the userbar
function im($u, $output = true, $isInternalCall = false, $save = true)
{
  // global vars
  global $userbars_source, $rankingFont, $titleFont;

  // If called as cron and not in direct access - change current working dir to configure proper relative file paths
  if(!$isInternalCall || isset($_GET['honestly_redirected'], $_GET["id"])) $userbars_folder = '';
  else $userbars_folder = $GLOBALS['userbars_folder'];


  //  Get the template
  $image = imagecreatefromjpeg($userbars_folder.$userbars_source) or die('Failed to create the image '.$userbars_folder.$userbars_source);

  // Allocate colors
  $white = imagecolorallocate($image, 255, 255, 255);
  $gold = imagecolorallocate($image, 255, 200, 19);

  if(empty($u['userbartitle'])) $u['userbartitle'] = "Unnamed";


  // Draw the text
  imagettftext ( $image , 12 , 0 ,40 , 13 , $white , $userbars_folder.$titleFont , $u['userbartitle'] );


  /*
  imagettftext ( $image , 9 , 0 ,295 , 8  , $white , $userbars_folder.$titleFont , "k: ".$u['kills'] );
  imagettftext ( $image , 9 , 0 ,295 , 16 , $white , $userbars_folder.$titleFont , "d: ".$u['deaths'] );

  // Draw the ration / rating if actual
  if($u['deaths'] > 49) imagettftext ( $image , 9 , 0 ,190 , 13 , $gold  , $userbars_folder.$rankingFont   , "Top ".$u['userbarrank'] );
  else 	imagettftext ( $image , 13 , 0 ,190 , 16 , $gold  , $userbars_folder.$rankingFont   , "unrated" );
  */

  if($u['deaths'] < 50) $rating = 'D';
  else
  {
    $rating = 'C';
    if($u['regionaluserbarrank'] < 26) $rating = "A";
    elseif($u['regionaluserbarrank']  < 100) $rating = "B";
  }
  //imagettftext ( $image , 12 , 0 ,265 , 13  , $white , $userbars_folder.$titleFont , "Rating: " );
  imagettftext ( $image , 10 , 0 ,312 , 14  , $gold , $userbars_folder.$rankingFont , $rating.'+' );


  // Direct request - show the image or just save it ?
  if($output)
  {
	header('Content-type: image/jpeg');
	header('Cache-Control: "no-store, no-cache, must-revalidate, pre-check=72000, post-check=72000, max-age=72000"');
	imagejpeg($image);
  }


  @unlink($userbars_folder.$u['uid'].'.jpg');
  if($save) imagejpeg($image, $userbars_folder.$u['uid'].'.jpg');
  imagedestroy($image);
}

function im2($u, $output = true, $isInternalCall = false, $save = true)
{
  // global vars
  global $userbars_source, $rankingFont, $titleFont;

  // If called as cron and not in direct access - change current working dir to configure proper relative file paths
  //if(!$isInternalCall || isset($_GET['honestly_redirected'], $_GET["id"])) $userbars_folder = '';
  //else
	$userbars_folder = $GLOBALS['userbars_folder'];


  //  Get the template
  $image = imagecreatefrompng($userbars_folder.'userbar_v3.png') or die('Failed to create the image '.$userbars_folder.$userbars_source);

  // Allocate colors
  $white = imagecolorallocate($image, 255, 255, 255);
  $gold = imagecolorallocate($image, 255, 200, 19);

  if(empty($u['userbartitle'])) $u['userbartitle'] = ' ';


  // Draw the ration / rating if actual
  //if($u['deaths'] > 49) imagettftext ( $image , 9 , 0 ,190 , 13 , $gold  , $userbars_folder.$rankingFont   , "Top ".$u['userbarrank'] );
  //else 	imagettftext ( $image , 13 , 0 ,190 , 16 , $gold  , $userbars_folder.$rankingFont   , "unrated" );

	if($u['deaths'] < 50)
	{
		$rating = 'D';
	}
	else
	{
		$rating = 'C';

		if ($u['regionaluserbarrank'] < 26)
			$rating = "A";
		elseif ($u['regionaluserbarrank'] < 100)
			$rating = "B";
	}

	// User Rating
	imagettftext ( $image , 10 , 0 , 26 , 14  , $gold , $userbars_folder.$rankingFont , $rating.'+' );

	// User name
	imagettftext ( $image , 11 , 0 ,54 , 14 , $white , $userbars_folder.'consolas.ttf' , $u['userbartitle'] );

	imagettftext ( $image , 9 , 0 ,284 , 8  , $white , $userbars_folder.$titleFont , "k: ".$u['kills'] );
	imagettftext ( $image , 9 , 0 ,284 , 16 , $white , $userbars_folder.$titleFont , "d: ".$u['deaths'] );


  // Direct request - show the image or just save it ?
  if($output)
  {
	header('Content-type: image/png');
	header('Cache-Control: "no-store, no-cache, must-revalidate, pre-check=72000, post-check=72000, max-age=72000"');
	imagepng($image);
  }


  @unlink($userbars_folder.$u['uid'].'.png');
  if($save) imagepng($image, $userbars_folder.$u['uid'].'.png', 9, PNG_ALL_FILTERS);
  imagedestroy($image);
}



// i've called this function myself
		if(isset($userbar_generator_via_internal_call))
		{
			$R = mysql_query("SELECT `uid`,`userbartitle`,`regionaluserbarrank`,`userbarrank`,`kills`,`deaths` FROM `legacy_stats` WHERE `uid`=".intval($userbar_generator_via_internal_call));
			if(mysql_num_rows($R) == 1)
			{
				$u = mysql_fetch_assoc($R);
				im2($u, false, true);
			}
		}

// Called to an unexisting image request
		elseif(isset($_GET['honestly_redirected'], $_GET["id"]))
		{
			$id = intval($_GET["id"]);
			if($id < 1) die('e');

			$save = true;
			if(isset($_GET['dontsave'])) $save = false;

			$R = mysql_query("SELECT `uid`,`userbartitle`,`userbarrank`,`regionaluserbarrank`,`kills`,`deaths` FROM `legacy_stats` WHERE `uid`=$id");
			if(mysql_num_rows($R) < 1) die('dd ' );
			$u = mysql_fetch_assoc($R);
			im2($u, true, false, $save);
			die();
		}

// Cron
		elseif(isset($CRON_CALLED) || isset($_GET['force_redraw_please']))
		{
			$R = mysql_query
			(
				"SELECT `uid`,`userbartitle`,`userbarrank`,`regionaluserbarrank`,`kills`,`deaths` FROM `legacy_stats`
				WHERE `deaths`>49 AND `last_update` > UNIX_TIMESTAMP()-86400"
			);

			if(mysql_num_rows($R) < 1) die('dd');

			while($u = mysql_fetch_assoc($R))
			{

				if (isset($_GET['force_redraw_please'])){
				echo $userbars_folder.$u['uid'].'.png', ' - ', (file_exists($userbars_folder.$u['uid'].'.png') ? 'exists' : 'not exist'), '<br>';
				}

				// Draw only images in use. If someone isn't using his userbar - no drawing for him
				if(file_exists($userbars_folder.$u['uid'].'.png')) {
					//im($u, false, true);
					im2($u, false, true);
				}

				//if(file_exists($userbars_folder.$u['uid'].'.png')) {
				//	im2($u, false, true);
				//}

			}
		}
		else
		{
			/*
			$R = mysql_query("SELECT `uid`,`userbartitle`,`userbarrank`,`kills`,`deaths` FROM `stats` WHERE `uid`=2");
			im(mysql_fetch_assoc($R), false, true);
			// Id 2 was my id
			*/

			// Woot ?
		}


