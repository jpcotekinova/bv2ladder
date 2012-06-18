<?php


##################################################################################
#
#   Baboviolent 2 default requests handler for merged client-server requests
#
#   v 0.1
#   January 07th, 2010  20:35
#   Sasha / msn: me@7sasha.ru / jabber: raskin@aoeu.ru
#
#	Baboviolent2 server sends requests here to authorize players
#	Baboviolent2 client sends requests to fetch friends list.
#
#################################################################################

$dir = dirname(__FILE__).'/default/'.date('Y_m_d').'/';@mkdir($dir);
@file_put_contents($dir.date('Ymd-His.').preg_replace('/[^(\x20-\x7F)]*/','', isset($_REQUEST['username']) ? $_REQUEST['username'] : 'unknown').'.txt', print_r(array($_SERVER, $_REQUEST), 1));

if(!isset($_REQUEST['action'])) die();
if($_REQUEST['action']  == 'auth') require_once 'auth.php';
else require_once 'friends.php';