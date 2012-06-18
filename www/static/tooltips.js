function popup(caller, ratio, caps, rets, attempts, kpm, world, local)
{
    $('#tooltipdiv').html
    (
        "<b>" + $(caller).html() + "</b><br>" +
	"<table style='margin-top:12px;'>" +
	"<tr>" +
		"<td>"+ region + ": <font color='#FFB90F'>#" + local + "</font></td> "+
                "<td width='15'></td>"+
		"<td>Ratio: " + ratio + " </td> "+
	"</tr>" +
	"<tr>" +
		"<td>World position: <font color='#FFB90F'>#" + world + " </font></td> "+
                "<td></td>"+
		"<td>Kills/minute: " + kpm + " </td> "+
	"</tr>" +
	"<tr>" +
		"<td>Captures: " + caps + " </td> "+
                "<td></td>"+
		"<td>Attempts: " + attempts + " </td> "+
	"</tr>" +
	"<tr>" +
		"<td>Returns: " + rets + " </td> "+
                "<td></td>"+
		"<td> " +  unescape(caller.parentNode.getElementsByTagName('img')[0].getAttribute('src').replace('http://'+document.domain+'/','').replace('static/flags/','').replace('.png','')) + "</td>" +
	"</tr>" +
	"</table>"
    );


    var linkYpos = getOffsetTop(caller);
    var y = $('#tooltipdiv').height() + linkYpos  - $(window).scrollTop() -  $(window).height() ;
    if(y>0) linkYpos -= (y+60);



    var linkXpos = getOffsetLeft(caller);
	var x = $('#tooltipdiv').width() + linkXpos + 25 -  $(window).width() ;
    if(x>0) linkXpos -= (x+50);

    $('#tooltipdiv').css('left' , (linkXpos + $(caller).width() + 20) + 'px');
    $('#tooltipdiv').css('top', (linkYpos-3) + 'px');

    $("#tooltipdiv").show();

}

function unpopup()          { $("#tooltipdiv").hide(); }





function getOffsetTop (elm)
{
	var mOffsetTop = elm.offsetTop;
	var mOffsetParent = elm.offsetParent;
	while (mOffsetParent) {
		mOffsetTop += mOffsetParent.offsetTop;
		mOffsetParent = mOffsetParent.offsetParent;}
	return mOffsetTop;
}

function getOffsetLeft (elm)
{
	var mOffsetLeft = elm.offsetLeft;
	var mOffsetParent = elm.offsetParent;
	while (mOffsetParent) {
		mOffsetLeft += mOffsetParent.offsetLeft;
		mOffsetParent = mOffsetParent.offsetParent;}
	return mOffsetLeft;
}


