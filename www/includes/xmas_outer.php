<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<title>BaboViolent - Accounts service</title>
	<meta name="Author" content="Sasha">

	<style>
		body {background: url('static/ribbontop.jpg')  no-repeat; font-family:Tahoma; margin:0; padding:0; z-index:50;}
		#page { margin-left:20%; margin-top:400px;}
		.input { border: 1px solid #A6A6A6; height:19px; width:182px; padding:5px 0 3px 6px;}
		.inpbg {  background-color:#E6C784; height:29px; width:191px; padding:3px; }
		h2 {color: #DF211F; display:inline;}
		.small { font-size:small;}

	</style>

</head>

<body>

<div id='page'>

	<div id='login'>
	  <form method='post' name='loginform' id='loginform' action="index.php">
	  <input type='hidden' name='sub' value='oeu'>
		<table>
		<tr>
			<td colspan='2'><span class='small'>login:</span></td>
			<td colspan='3'><span class='small'>password:</span></td>
		</tr>
		<tr>

			<td  class='inpbg'> <input type='text' name='login' class='input' id='loginname' value=''></td>
			<td width="20"></td>
			<td class='inpbg'><input type='password' name='pass' class='input' id='loginpass' value=''
			onKeyDown="keyCode=(event.which)?event.which:event.keyCode;if(keyCode==13)go();"></td>
			<td width="20"></td>
			<td> <input type="button" value="Enter" style="padding:2px 20px;" onclick="go()"></td>
		</tr>
		<tr>
			<td colspan="3" align="right" class='small'> <a href="javascript:toggleform('new')" style='color:gray'>Create an account</a></td>
			<td colspan='2'></td>
		</tr>
		</table>
		<input type='submit' style="visibility:hidden">
	  </form>
	</div>

	<div id='reg' style='display:none'>
	  <form method='post' name='regform' id='regform'>

	  <input type='hidden' name='reg' value='eu'>
		<h2>Get an account</h2>
		<table>
		<tr><td>Choose name:</td><td><input type='text' name='account' class='input' id='regname'></td></tr>
		<tr><td>Your password:&nbsp;&nbsp;</td><td><input type='text' name='pass' class='input' id='regpass' ></td></tr>
		<tr><td>Region:</td><td>
			<select id='region' name='region'>
			<?php foreach($regions as $abbr => $name) echo "<option value='$abbr'>$name</option>\r\n"; ?>
			</select>

		</td></tr>
		<tr><td></td> <td align='right'> <input type="button" value="Register" style="padding:2px 20px;" onclick="go()"> </td></tr>
		</table>
	  </form>
	</div>


	</form>


	<div style="position:absolute; bottom:0; left:0; margin: 0 0 10px 20px; font-size:12px;" >

	developing <a href="msnim:chat?contact=me@7sasha.ru" style="color:gray;text-decoration:none;">Sasha</a>,
	graphics by 	<a href="http://www.freedigitalphotos.net/images/view_photog.php?photogid=879" style="color:gray;text-decoration:none;">luigi diamanti</a>,
	happy holidays

	</div>


	<script>

	function go()
	{
		var acc = document.getElementById('loginname').value;
		var login = document.getElementById('regname').value;

		if(acc == '' && login !='' ) document.regform.submit();
		else document.loginform.submit();

	}
	function toggleform(what)
	{
		if(what == 'new')
		{
			document.getElementById('login').style.display='none';
			document.getElementById('reg').style.display='block';
		}
	}

	document.getElementById('loginname').focus();

	</script>

</div>


<div style="position:absolute; bottom:0; right:0;background: url('static/ribbonbottom.jpg')  no-repeat; height:287px; width:460px; z-index:-1; overflow:hidden;">
</div>

<?php if(!empty($error)) echo "<script> alert('$error'); </script>"; ?>

</body>
</html>
