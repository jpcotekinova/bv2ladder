<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<title>Ladder Accounts</title>
	<link rel="shortcut icon" href="http://ladder.baboviolent.ru/favicon.ico">
	<meta name="Author" content="Sasha">
	<style>
		body {background: #000 url('static/body.png'); color:white; font-family:Tahoma; margin:0; padding:0; }
		#login, #reg
		{
			float:left;
			border:5px solid #818181;
			-moz-border-radius: 15px;
			-webkit-border-radius: 15px;
			padding:10px 20px 22px 20px;
			line-height:35px;
		}
		#page { margin:250px auto 0 auto; width:840px; }
		#reg { margin-left: 10px; width:430px; }

		.input{ background-color:black; border:1px solid white; padding:3px; color:white; width:160px;  height:18px; }
		option, select { background-color:black; color:white; padding-bottom:0; width:140px; }
		h2 { margin-top:0px; padding-top:0px;}

		#button { text-align:right; font-weight:bolder; margin:10px 20px; color:white; text-decoration:none; float:right;}
		table { margin-top:0; padding-top:0;}
		h2 { margin-bottom:0; padding-bottom:0;}
	</style>

</head>
<body>

<div id='page'>

	<div id='login'>
	  <form method='post' name='loginform' id='loginform'>
	  <input type='hidden' name='sub' value='oeu'>
		<h2>Login</h2>
		<table>
		<tr><td>Account name: </td><td><input type='text' name='login' class='input' id='loginname'></td></tr>
		<tr><td>Password:</td><td><input type='password' name='pass' class='input' id='loginpass' ></td></tr>
		<tr><td align="left"><input type='checkbox' id='rem' name='rem' checked='checked'></td><td><label for='rem'>Remember me</label></td></tr>
		</table>
	  </form>
	</div>

	<div id='reg'>
	  <form method='post' name='regform' id='regform'>
	  <input type='hidden' name='reg' value='eu'>
		<h2>Get an account</h2>
		<table>
		<tr><td>Account:</td><td><input type='text' name='account' class='input' id='regname'></td></tr>
		<tr><td>Password:&nbsp;&nbsp;</td><td><input type='text' name='pass' class='input' id='regpass' ></td></tr>
		<tr><td>Region:</td><td>
			<select id='region' name='region'>
				<?php foreach($regions as $abbr => $name) echo "<option value='$abbr'>$name</option>\r\n"; ?>
			</select>
		</td></tr>
		</table>
	  </form>
	</div>


	</form>

	<div style="clear:both"></div>
	<a href='javascript:go()' id='button'> Go in &rarr; </a>
	<div style="clear:both"></div>
	<script>

	document.getElementById('loginpass').onkeypress = document.getElementById('region').onkeypress = function(e)
	{
		if (window.event) key = window.event.keyCode;
		else if (e) key = e.which;
		if(key==13) go();
	}

	function go()
	{
		var acc = document.getElementById('loginname').value;
		var login = document.getElementById('regname').value;

		if(acc == '' && login !='' ) document.regform.submit();
		else document.loginform.submit();

	}
	document.getElementById('loginname').focus();
	</script>
	<?php if(!empty($error)) echo "<script> alert('$error'); </script>"; ?>
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
<!--[fingerprint:z4PhNX7vuL3xVChQ1m2AB9Yg5AULVxXcg]-->
</html>
