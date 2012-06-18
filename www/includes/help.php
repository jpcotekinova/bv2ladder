<?php

// No direct access to this script
	if(!isset($included)) die();

// Regional forums
	$forums = Array
	(
		'na' => 'http://forum.baboviolent.org',
		'au' => 'http://www.ugn.com.au/forums/index.php?board=112.0',
		'sa' => 'http://www.isgaming.co.za/forum/49',
		'ru' => 'http://baboviolent.ru/forum/',
		'eu' => 'http://eurobabo.forumieren.eu',
		'other' => 'http://forum.baboviolent.org'
	);
	
?>
<style> #right {font-family:"Trebuchet MS";}</style>
<b style="text-shadow:0px 1px 1px #fff; ">Help</b>
<br><br>

<?php if($_SESSION['user']['region'] != 'ru'): ?>

	Q: <b>What is this site?</b><br>
	A: This site supports and extends the game futures you have in baboviolent 2, <br>such as friends list and statistics.<br>
	<br>
	Q: <b>I've just registered here. What is next ?</b><br>
	A: Use the login and password you registered with to log-in in the game as shown below: <br><br>
	<img src='http://img198.imageshack.us/img198/8838/27131235.png'><br><br>
	Now you can add friends and participate in the ladder.<br>
	Best place to look for new friends would be the forum: 
	<a style="color:orange;" href="<?php echo $forums[$_SESSION['user']['region']];?>">
	<?php echo $forums[$_SESSION['user']['region']];?></a>
	<br><br>
	Q: <b>Nothing works. I don't see my friends, my stats wouldn't collect.</b><br>
	A: Did you try the forum, the link to which is located above?<br>
	<br><br>
	O: By the way, you can locate someone by his name or ID <a href="?search" style="color:orange;">using the special search form</a>.
	<br>
	
<?php else: ?>

	Q: <b>Куда я попал?</b><br>
	A: Этот сайт дополняет игровой клиент baboviolent 2 некоторыми возможностями, такими как игровая статистика, список друзей.<br>
	<br>
	Q: <b>Хорошо, я зарегистрировался. Что дальше ?</b><br>
	A: Авторизуйтесь в игре как показано на картинке используя логин и пароль под которыми Вы зарегистрировались. <br><br>
	<img src='http://img198.imageshack.us/img198/8838/27131235.png'><br><br>
	Отныне вы можете добавлять друзей и следить за их появлением в сети. Для этого достаточно перейти в пункт Друзья (friends) в боковом меню.<br>
	Найти друзей можно в большом русском сообществе со своим собственным сайтом. Не проходите мимо.
	<a style="color:orange;" href="<?php echo $forums[$_SESSION['user']['region']];?>">
	<?php echo $forums[$_SESSION['user']['region']];?></a>
	<br><br>
	Q: <b>Я все сделал как на картинке, но ничего не работает.</b><br>
	A: Попробуйте обратиться на форум, где Вам с радостью помогут. Ссылка на него приведена выше. <br>
	<br><br>
	O: Кстати, найти кого нибудь по нику или ID можно <a href="?search" style="color:orange;">воспользовавшись специальным поиском</a>.
	<br>

<?php endif; ?>