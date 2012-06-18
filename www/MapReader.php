<?php
error_reporting(E_ALL);
set_time_limit(0);

// Check whether the user is authorized
session_start();
if(!isset($_SESSION['user']['id'])) die2('Authorization failure');

// Connect to the DB
require_once('mysql.php');

/**
 * This function add's the JS function to invoke an alert withit the iframe
 * as this script works in IFRAME. It terminates the execution, btw :)
 * @param <$string> $mes the message to abort with
 */
function die2($mes)
{
    die($mes.";<script> window.parent.getContentFromIframe('mapdlframe'); </script>");
}


// Checking whether Upload was invoked properly
if(!isset($_POST['up'])) die2("#1 Upload failed");


// Checking whether the uploaded file is a zip or a bvm
$ext = end(explode(".", $_FILES['uploadedfile']['name']));

if($ext != 'zip' && $ext != 'bvm') die2('bvm and zip only are allowed');
elseif($ext == 'bvm')
{
    // Upload the file
    $target_path = "maps/bv2map_".time().'_'.rand().'.bvm';
    if(!move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) die2('upload failure');
    if(filesize($target_path) < 4 ) die2('upload failure.');
    chmod(target_path, 0777);

    // Check whether we don't have such map already
    $md5 = md5_file($target_path);
    $exists = mysql_result(mysql_query("SELECT COUNT(`mid`) FROM `legacy_maps` WHERE `checksum`='$md5'"),0,0);
    if($exists)
    {
        unlink($target_path);
        die2('This map already exists in the storage');
    }

    $maparray = parse_map($target_path);
    if($maparray === null) die2('Failed parsing map file');

    $desc = '';
    $map = &$maparray['map'];
    $author = &$maparray['author'];
    $name = str_ireplace('.bvm','',$_FILES['uploadedfile']['name']);

    if(isset($_POST['description'])) $desc = trim($_POST['description']);

    addMapToDatabase(&$name, &$author, &$target_path, &$desc, &$md5, count($map), count($map[0]));

    $img = drawMapsThumbnail(&$map); // Generate image
    img_resize($img, 80,80, $target_path.'.jpg');      // Save image
    imagedestroy($img);             // Free memeroy

    die2("Map uploaded succesfully");

}
elseif($ext == 'zip')
{
    // Upload the file
    $target_path = "maps/archive_".rand().'.zip';
    //print_R($_FILES);
    if(!move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path))
            die2('Failed saving uploaded file');

    $zip = zip_open($target_path);
    $names = '';
    if ($zip)
    {
        while ($zip_entry = zip_read($zip))
        {
            $name = zip_entry_name($zip_entry); $names .= ' '.$name;
            $path = "maps/bv2map_".time().'_'.rand().'.bvm';

            if (zip_entry_open($zip, $zip_entry, "r"))
            {
                $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                zip_entry_close($zip_entry);
            }

            $md5 = md5($buf);

            $exists = mysql_result(mysql_query("SELECT COUNT(`mid`) FROM `legacy_maps` WHERE `checksum`='$md5'"),0,0);
            if($exists) continue;

            $h = fopen($path,'w+');
            fputs($h, $buf);
            fclose($h);
            chmod($path, 0777);

            $maparray = parse_map($path);
            if($maparray === null)
            {
                unlink($path);
                continue;
            }

            $desc = '';
            $map = &$maparray['map'];
            $author = &$maparray['author'];
            $name = str_ireplace('.bvm','',$name);

            addMapToDatabase(&$name, &$author, &$path, &$desc, &$md5, count($map), count($map[0]));

            $img = drawMapsThumbnail(&$map); // Generate image
            img_resize($img, 80,80, $path.'.jpg');      // Save image
            imagedestroy($img);             // Free memeroy

        }

        zip_close($zip);

    }

    unlink($target_path);
    die2($names. " here");
}


/**
 *
 * @param <string> $filename the path to the file to parse
 * @return <Array> Array('map' => $map, 'author' => $author), null on failure
 */
function parse_map($filename)
{
    $possible_versions = Array(20202, 10010, 10011, 20201);

    try
    {
        // open the map file for reading
        $handle = fopen($filename, 'r');
        if(!$handle) Throw new Exception('Cannot read file');

        // Get map's version
        $version = unpack('L',fread($handle,4));
        $version = $version[1];

        // If the version is improper - skip file
        if(!in_array($version, $possible_versions)) throw new Exception('Invalid map file');

        // Determine author's name
       	if($version == 20202)
        {
            $a = unpack('c*',fread($handle,25));
            for($i = 1; $i < 25; $i++) if($a[$i] > 31 ) $author .= chr($a[$i]);
        }

        // Some map specif parameters
        if($version > 20000)
        {
            $theme = unpack('s',fread($handle,2));      $theme = $theme[1];
            $weather = unpack('s',fread($handle,2));	$weather = $weather[1];
        }

        // Map's size
        $width = unpack('s',fread($handle,2));		$width = $width[1];
	$height = unpack('s',fread($handle,2));		$height = $height[1];


        // Building 2-dimensional array of wall's data
        for($y = 0; $y < $height; $y++)
            for($x = 0; $x < $width; $x++)
            {
                // Get the numeric represantation of wall
                $data = unpack('H*',fread($handle,1));
                $data = hexdec($data[1]);

                // What it sais
                $wallHeight = ($data & 127);
                $passable = ($data & 128);

                // If the is 0height wall but it's a wall - mark it
                $wall = 0;
                if(!$passable && $wallHeight > 0) $wall = 1;
                if(!$passable && $wallHeight == 0) $wall = 0.5;

                $map[$x][$y] = $wall;

                // 1 byte of dirt or something
                fread($handle,1);
            }


        if($version == 20202)
        {
            // Skip pink spawns data
            $pinkSpawns = unpack('C*',fread($handle,2)); $pinkSpawns = $pinkSpawns[1];
            if($pinkSpawns > 0) fread($handle, 4 * 3 * $pinkSpawns);

            // Game-type specific content (DM-0, TDM-1, CTF-2 , BOMBERMAN-3)
            // They might appear unordered (DM, than CTF, than TDM) WTF???

            for($i = 0; $i< 4; $i++):

                $gameType = unpack('c*',fread($handle,2));
                $gameType = $gameType[1];

                switch($gameType):
                    case 0: break;
                    case 1: break;
                    case 3: break;
                    case 2:

                        $flag1[0] = unpack('f*',fread($handle,4));
                        $flag1[1] = unpack('f*',fread($handle,4));
                        fread($handle,4);

                        $flag2[0] = unpack('f*',fread($handle,4));
                        $flag2[1] = unpack('f*',fread($handle,4));
                        fread($handle,4);


                        $flagx = floor($flag1[0][1]);
                        $flagy = floor($flag1[1][1]);
                        $map[$flagx][$flagy] = 3;


                        $flagx = floor($flag2[0][1]);
                        $flagy = floor($flag2[1][1]);
                        $map[$flagx][$flagy] = 2;

                    break;
                endswitch;
            endfor;
        }
        else
        {
            $flag1[0] = unpack('f*',fread($handle,4));
            $flag1[1] = unpack('f*',fread($handle,4));
            fread($handle,4);

            $flag2[0] = unpack('f*',fread($handle,4));
            $flag2[1] = unpack('f*',fread($handle,4));
            fread($handle,4);


            $flagx = floor($flag1[0][1]);
            $flagy = floor($flag1[1][1]);
            $map[$flagx][$flagy] = 3;

            $flagx = floor($flag2[0][1]);
            $flagy = floor($flag2[1][1]);
            $map[$flagx][$flagy] = 2;
        }
    }
    catch(Exception $x) {return null;}
    return Array('map' => $map, 'author' => $author);
}





















function addMapToDatabase($name, $author, $path, $description, $filemd5, $width, $height)
{
    $wh = $width.'.'.$height;

    mysql_query
    (
        "INSERT INTO `legacy_maps` (`author`, `name`, `filename`, `dimension`, `checksum`, `upload_time`, `description`,`uploader`)
        VALUES
        (
            '".mysql_real_escape_string($author)."',
            '".mysql_real_escape_string($name)."',
            '".mysql_real_escape_string($path)."',
            '$wh',
            '$filemd5',
            NOW(),
            '".mysql_real_escape_string($description)."',
            ".$_SESSION['user']['id']."
        )"
    );
}

/**
 *
 * @param <array> $map 2-dimensional array containing walls and flags info
 * @return <resource> GD image - thumbnail of the map
 */
function drawMapsThumbnail($map)
{
    $width = count($map);
    $height = count($map[0]);

    $img = imagecreatetruecolor($width, $height);

    $red   = imagecolorallocate($img, 255, 0, 0);
    $white = imagecolorallocate($img, 255, 255,255);
    $blue  = imagecolorallocate($img, 0, 0, 255);
    $grey  = imagecolorallocate($img, 127,127,127);

    for($y = 0; $y < $height; $y++)
        for($x = 0; $x < $width; $x++)
        {
            if($map[$x][$y] == .5) imagesetpixel($img, $x, $height-$y-1, $grey);    // 0-height wall
            if($map[$x][$y] == 1)  imagesetpixel($img, $x, $height-$y-1, $white);   // normal wall
            if($map[$x][$y] == 2)  imagesetpixel($img, $x, $height-$y-1, $red);     // red flag
            if($map[$x][$y] == 3)  imagesetpixel($img, $x, $height-$y-1, $red);    // blue flag
        }

    return $img;
}


/**
 * Resizes the image to the given size proportionally and saves it
 * @param <Resource> $img the GD image handle to the image
 * @param <int> $width new images width
 * @param <int> $height new image's height
 * @param <string> $filename the filename under which the image should be saved
 * @return <bool> true on success
 */
function img_resize($img, $width, $height, $filename)
{
    $size[] = imagesx($img);
    $size[] = imagesy($img);

    $x_ratio = $width / $size[0];
    $y_ratio = $height / $size[1];

    $ratio       = min($x_ratio, $y_ratio);
    $use_x_ratio = ($x_ratio == $ratio);

    $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
    $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
    $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
    $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);


    $idest = imagecreatetruecolor($new_width, $new_height);

    imagefill($idest, 0, 0, 0xFFFFFF);
    imagecopyresampled($idest, $img, 0,0, 0, 0,
    $new_width, $new_height, $size[0], $size[1]);

    imagejpeg($idest, $filename, 100);
    imagedestroy($idest);
    return true;

}



























/*

                    oka, the map goes like this

                    (y)
                    ^^
                    ||			  (w,h)
                    ||
                    ||
                    ||
                    ++===============> (x)
      (0,0)

*/

    /*case 3: // this thing is the X bomb sites
                    $site1[0] = unpack('f*',fread($handle,4));
                    $site1[1] = unpack('f*',fread($handle,4));
                    fread($handle,4);

                    $site2[0] = unpack('f*',fread($handle,4));
                    $site2[1] = unpack('f*',fread($handle,4));
                    fread($handle,4);

                    $flagx = floor($site1[0][1]);
                    $flagy = floor($site1[1][1]);
                    $map[$flagx][$flagy] = 4;

                    $flagx = floor($site2[0][1]);
                    $flagy = floor($site2[1][1]);
                    $map[$flagx][$flagy] = 5;

                    $blueSpawns = unpack('C*',fread($handle,2)); $blueSpawns = $blueSpawns[1];
                    fread($handle, 4 * 3 * $blueSpawns);

                    $redSpawns = unpack('C*',fread($handle,2)); $redSpawns = $redSpawns[1];
                    fread($handle, 4 * 3 * $redSpawns);

                    break;*/
