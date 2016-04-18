<?php

#
# This page is showed only to authorized clients
#
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

// No direct access to this script
	if(!isset($included)) die();

// Shortcut
	$myid = &$_SESSION['user']['id'];

// Updating site's last visit time
		mysql_query("UPDATE `legacy_players` SET `last_site_visit`=".time()." WHERE `id`=".$myid);




// Current page
	$require = 'stats';
	if(isset($_GET['news'])) $require = 'news';
	if(isset($_GET['help'])) $require = 'help';
	if(isset($_GET['maps'])) $require = 'maps';
	if(isset($_GET['search'])) $require = 'search';
	if(isset($_GET['friends'])) $require = 'friends';
	if(isset($_GET['preferences'])) $require = 'preferences';

	//if(isset($_GET['administrating'])) $require = 'administrating';

// Handle GET/POST data
	require_once 'includes/GP_handler.php';


?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
 <head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<title>BaboViolent - Accounts service</title>
	<link rel="shortcut icon" href="http://ladder.baboviolent2.ru/favicon.ico">
	<link href="static/style.css" rel="stylesheet" type="text/css" >
	<meta name="Author" content="Alexander Raskin aka Sasha, modifications by keta">
	<meta name="Description" content="Accounts system for the baboviolent 2 shooter friends list, controlling access to private servers and stat collecting service">
 </head>

 <body>
  <div id="main">

	<div id="communities">
	<ul>
		<li><a href="http://forum.baboviolent.org"><img src="/static/flags/United%20States%20of%20America(USA).png" width="24" height="24" alt="North American Forum" />North American Forum</a></li>
		<li><a href="http://eurobabo.forumieren.eu"><img src="/static/flags/European%20Union.png" width="24" height="24" alt="European Community" />European Community</a></li>
		<li><a href="http://baboviolent2.ru"><img src="/static/flags/Russian%20Federation.png" width="24" height="24" alt="Russian Community" />Russian Community</a></li>
		<li><a href="http://sababo.forum.st"><img src="/static/flags/South%20Africa.png" width="24" height="24" alt="South African Community" />South African Community</a></li>
	</ul>
	</div>

	<div id="body">

		<ul id="left">
			<li <?php if($require == 'news') echo" class='active'"; ?>><a href="?news">News</a></li>
			<li <?php if($require == 'stats') echo" class='active'"; ?>><a href="?stats">Stats</a></li>
			<li <?php if($require == 'friends') echo" class='active'"; ?>><a href="?friends">Friends</a></li>
			<li <?php if($require == 'preferences') echo" class='active'"; ?>><a href="?preferences">Preferences</a></li>
			<?php if(0 && $_SESSION['user']['admin']): ?>
			<li <?php if($require == 'administrating') echo" class='active'"; ?>><a href="?administrating">Admining</a></li>
			<?php endif; ?>
			<li <?php if($require == 'maps') echo" class='active'"; ?>><a href="?maps">Maps</a></li>

			<!--
			<li <?php if($require == 'clan') echo" class='active'"; ?>>Clan</li>
			<li <?php if($require == 'stats') echo" class='active'"; ?>>Stats</li>
			<li <?php if($require == 'servers') echo" class='active'"; ?>>Servers</li>
			-->
			<li <?php if($require == 'help') echo" class='active'"; ?>><a href="?help">Help</a></li>
			<li><a href="?logout">Log out</a></li>


<?php if($require == 'maps') {

function format_bytes($size)
{
	$units = array(' b', ' KB', ' MB', ' GB', ' TB');
	for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
	return round($size, 2).$units[$i];
}

	?>
<li class="misc"><div>
<p><strong style="font-size:120%;">Download all</strong></p>
<p><a href="/maps/bv2-maps.zip">Only map files</a><small> (<?php echo format_bytes(filesize(dirname(__FILE__).'/../maps/bv2-maps.zip')); ?>)</small></p>
<p><a href="/maps/bv2-maps-thumbs.zip">With previews</a><small> (<?php echo format_bytes(filesize(dirname(__FILE__).'/../maps/bv2-maps-thumbs.zip')); ?>)</small></p>
</div></li>

<?php } ?>


		</ul>

		<div id="right">

			<?php require_once('includes/'.$require.'.php');	?>
			<br><br>
			<div id="bv">babo violent</div>
		</div>

		<div style="clear:both"></div>

 	</div>
  </div>
 </body>

<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-29723792-2']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
<!--Openstat-->
<span id="openstat2242259"></span>
<script type="text/javascript">
var openstat = { counter: 2242259, next: openstat, track_links: "all" };
(function(d, t, p) {
var j = d.createElement(t); j.async = true; j.type = "text/javascript";
j.src = ("https:" == p ? "https:" : "http:") + "//openstat.net/cnt.js";
var s = d.getElementsByTagName(t)[0]; s.parentNode.insertBefore(j, s);
})(document, "script", document.location.protocol);
</script>
<!--/Openstat-->
</html>