<?php

error_reporting(E_ALL);

// No direct access to this script
    if(!isset($included)) die();

// Some conf
    $maps_per_page = 20;


// Get all maps

    $where = ' (`approved`=1 OR `uploader`='.$myid.') ';
    $order = '';

    if($_SESSION['user']['inmapteam'] > 0) { $where = '1 '; $order = ' `approved` ASC , ';}

// Search

    if(isset($_GET['mapname'])):
        if(!empty($_GET['mapname']))
        {
            $where .= " AND `name` LIKE '%".mysql_real_escape_string($_GET['mapname'])."%' ";
        }
        else
        {
            IF(isset($_GET['size']) && $_GET['size']!='Any') $where .= " AND `size`='".mysql_real_escape_string($_GET['size'])."' ";
            IF(isset($_GET['gametype']) && $_GET['gametype']!='Any') $where .= " AND `gametype`='".mysql_real_escape_string($_GET['gametype'])."' ";
        }
    endif;

    $page = @intval($_GET['page']) ;
    if($page ==0) $page = 1;
    $start = ($page-1) * $maps_per_page;

    $maps = mysql_query("SELECT * FROM `legacy_maps` WHERE $where ORDER BY $order `upload_time` DESC LIMIT $start,$maps_per_page");
    $total_maps = mysql_result(mysql_query("SELECT COUNT(*) FROM `legacy_maps` WHERE $where"),0,0);


?>

<b style="text-shadow:0px 1px 1px #fff; ">Maps storage</b>
<br>
<div style="float:right"><a href="javascript:showUploadForm()" style="color:#FFB90F; font-size:small;">Upload</a></div>
<font size='2'><br>
    <form method="GET" action="index.php?maps" name="sform" id="sform">
    Search:  <select name="gametype">
        <option value="Any">Any</option>
        <option value="Normal">Normal</option>
        <option value="Funny">Funny</option>
        <option value="Koth">Koth</option>
        <option value="Soccer">Soccer</option>
        <option value="Domination">Domination</option>
        <option value="Runners">Runners</option>
        <option value="Ressurection">Ressurection</option>
        <option value="Stupid" >Stupid</option>
    </select>
    <select name="size">
        <option value="Any">Any</option>
        <option value="small">Small</option>
        <option value="medium">Medium</option>
        <option value="large">Large</option>
        <option value="Huge">Huge</option>
    </select>
    &nbsp;&nbsp;&nbsp; -OR- &nbsp;&nbsp;&nbsp;
    Name: <input type="text" name="mapname" id="mapname" style="background-color:#595959;color:white;">
    <input type="submit"  style="background-color:#595959;color:white;" value="Search">
    <input type="hidden" name="maps" />
    </form>
</font>
<br>



<br><br>

<div id="uploadForm" style="display:none">
	<form enctype="multipart/form-data" method="POST" action="MapReader.php" target="mapdlframe">
	<input type="hidden" name="MAX_FILE_SIZE" value="1024000" />
	Map: <input name="uploadedfile" type="file" class="mapUloadField"/><input type="submit" value="Upload map" name="up"/>
	</form>
	<small>zip or bvm. Map will be observed by the map team first.</small>
	<br>	<br>	<br>
</div>



<table width="100%" class='maptable'>
<tr>


<?php

$rowsCounter = 0;

// Output the maps

    while($maps && $map = mysql_fetch_assoc($maps) )
    {
            $w = intval($map['dimension']);
            $h = ($map['dimension']-$w)*100;

            if($map['approved'] < 1 && $_SESSION['user']['inmapteam'] > 0)
            {
                $selected = Array
                (
                    0 => '',
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '',
                    5 => '',
                    6 => '',
                    'a' => '',
                    'b' => '',
                    'c' => '',
                    'd' => ''
                );


                if( stripos($map['name'], 'koth-') !== false  ) $selected[2] = " selected='selected' ";
                elseif( stripos($map['name'], 'soc-') !== false  ) $selected[3] = " selected='selected' ";
                elseif( stripos($map['name'], 'dom-') !== false  ) $selected[4] = " selected='selected' ";
                elseif( stripos($map['name'], 'res-') !== false  ) $selected[6] = " selected='selected' ";
                elseif( stripos($map['name'], 'run-') !== false  ) $selected[5] = " selected='selected' ";

                $area = $w * $h;

                if($area < 400) $selected['a'] = " selected='selected' ";
                elseif ($area < 800) $selected['b'] = " selected='selected' ";
                elseif ($area < 1125) $selected['c'] = " selected='selected' ";
                else $selected['d'] = " selected='selected' ";

                $cell = '
                <div class="mapleftcell" id="mapcell'.$map['mid'].'">
                <form enctype="multipart/form-data" method="POST" action="index.php" target="mapdlframe" onsubmit="mapdecided('.$map['mid'].'); return true;">

                    Author: '.htmlspecialchars($map['author']).' <br>
                     '.$w.'<small>w</small> x '.$h.'<small>h</small> <br>

                    Gametype:  <select name="gametype">
                        <option value="Normal" '.$selected[0].'>Normal</option>
                        <option value="Funny" '.$selected[1].'>Funny</option>
                        <option value="Koth" '.$selected[2].'>Koth</option>
                        <option value="Soccer" '.$selected[3].'>Soccer</option>
                        <option value="Domination" '.$selected[4].'>Domination</option>
                        <option value="Runners" '.$selected[5].'>Runners</option>
                        <option value="Ressurection" '.$selected[6].'>Ressurection</option>
                        <option value="Stupid" >Stupid</option>
                    </select><br/>
                    Size: <select name="size">
                        <option value="small" '.$selected['a'].'>Small</option>
                        <option value="medium" '.$selected['b'].'>Medium</option>
                        <option value="large" '.$selected['c'].'>Large</option>
                        <option value="Huge" '.$selected['d'].'>Huge</option>
                    </select>

                     <input type="hidden" name="mapid" value="'.$map['mid'].'">

                     <span id="controls'.$map['mid'].'">
                     <br><br><input type="submit" name="approve" value="Approve map" >
                     <a href="javascript:decline('.$map['mid'].')">Decline</a>
                     </span>

                </form>
                </div>';

            }
            else
            {

                $cell = '
                <div class="mapleftcell" >
                     Author: '.htmlspecialchars($map['author']).' <br>
                     '.$w.'<small>w</small> x '.$h.'<small>h</small> <br>
                     '.$map['size'].' map for a  '.$map['gametype'].' game
                </div>';

            }


            if($rowsCounter++ % 2 == 0) echo '</tr><tr>';
            else echo '<td width="5"></td>';

            echo '
            <td class="map" width="48%" valign="top">

                <div class="head" onclick="dlthis(',$map['mid'],')"><span>',htmlspecialchars($map['name']),'</span>',
                $map['approved'] ? '' : ' &nbsp; '
                ,'</div>
                <div class="body" >
                    ',$cell,'
                    <div class="maprightcell">
                        <img src="',htmlspecialchars($map['filename']),'.jpg" alt="',htmlspecialchars($map['name']),'">
                    </div>
                    <div style="clear:both"></div>
                </div>

            <br>
            </td>
            ';


    }


?>


</tr></table>




<script type="text/javascript" language="JavaScript">
function dlthis(that)
{
	document.getElementById("mapdlframe").src = "/maps/index.php?dl="+that;
}

function decline(mapid)
{
	var mapdiv = document.getElementById("mapcell"+mapid).parentNode.parentNode;
	mapdiv.style.visibility='hidden';
	document.getElementById("mapdlframe").src = "index.php?blank&declinemap="+mapid;
}

function getContentFromIframe(iFrameName)
{

    var myIFrame = document.getElementById(iFrameName);
    var content = myIFrame.contentWindow.document.body.innerHTML;
    alert(content);
	content2 = content.split(";");
	if(content2 == "good") window.location.reload();

    content = "The inside of my frame has now changed";
    myIFrame.contentWindow.document.body.innerHTML = content;

}

function mapdecided(id)
{
	var controls = document.getElementById("controls"+id);
	controls.style.visibility = 'hidden';
	return true;
}


function showUploadForm()
{
	var upform = document.getElementById('uploadForm');
	if(upform.style.display == 'none') upform.style.display = 'block';
	else upform.style.display = 'none';
}

</script>

<iframe id="mapdlframe" name="mapdlframe" style="height:1px; width:1px; visibility:hidden;" src="/static/blank.htm"></iframe>


<br><br><br>



<div class="paginator" id="paginator2" style="margin:20px auto 0 auto;"></div>
<div class="paginator_pages"><?php echo $total_maps;?> maps, <?php echo ceil($total_maps/$maps_per_page);?> pages</div>
<script type="text/javascript" src="/static/paginator/paginator.js"></script>
<script type="text/javascript"> pag2 = new Paginator('paginator2', <?php echo ceil($total_maps/$maps_per_page);?>, 18, <?php echo $page;?>, paginator_curpage()+"page=");</script>

