/*
	interface.js - a javascript file for interface functions
		
	author:	P E Sartain	
	date:	21/10/2008
*/

// Some variables that we need to keep fresh and available.
var xmlHttp;
var timerID = 0;
var iTimeID = 0;
var iTimeCount = 0;

/************************
 *  Achievement editor  *
 ************************/
function updateAchievements(id,fobj){
	var imgsrc = document.getElementById('img'+id).src;
	var icosrc = document.getElementById('icon'+id).value;
	if(imgsrc != icosrc) {
		document.getElementById('img'+id).src = "lib/plugins/pqraid/images/"+document.getElementById('icon'+id).value;
	}

	iTimeCount = 8;
	if (timerID != 0) {
		clearTimeout(timerID);
	}
	if (iTimeID != 0) {
		clearInterval(iTimeID);
	}
	iTimeID = setInterval(function(){updateDivText('saveinfo','Unsaved. Saving in '+(iTimeCount--),'unsaved')},800);
	timerID = setTimeout(function(){saveAchievements(fobj);fobj=null},8000);			
}

function saveAchievements(fobj){

	updateDivText('saveinfo','Saving ...','unsaved');
	
	//Build a parameter list from the form elements
	var params = getForm(fobj);
	
		ajaxPost('achieveInterface.php?func=saveAchievements()',params,'achievements','updateDivText(\"saveinfo\",\"Saved.\",\"saved\")');
	//ajaxPost('achieveInterface.php?func=saveAchievements()',params,'saveinfo',null);	
	clearTimeout(timerID);
	clearInterval(iTimeID);
}

function unsavedAchieve(id){
	var imgsrc = document.getElementById('newimg'+id).src;
	var icosrc = document.getElementById('newicon'+id).value;
	if(imgsrc != icosrc) {
		document.getElementById('newimg'+id).src = "lib/plugins/pqraid/images/"+document.getElementById('newicon'+id).value;
	}
	updateDivText('saveinfo','Unsaved. Push [+] to add the new achievement.','unsaved');
}

function addAchievement(fobj){

	updateDivText('saveinfo','Saving ...','unsaved');
	var params = getForm(fobj);
	ajaxPost('achieveInterface.php?func=addAchievement()',params,'achievements','updateDivText(\"saveinfo\",\"Saved.\",\"saved\")');
}

/****************
 *  CSC Editor  *
 ****************/
function updateCSC(fobj){
	iTimeCount = 4;
	if (timerID != 0) {
		clearTimeout(timerID);
	}
	if (iTimeID != 0) {
		clearInterval(iTimeID);
	}
	iTimeID = setInterval(function(){updateDivText('saveinfo','Unsaved. Saving in '+(iTimeCount--),'unsaved')},800);
	timerID = setTimeout(function(){saveCSC(fobj);fobj=null},4000);		
}

function saveCSC(fobj){
	updateDivText('saveinfo','Saving ...','unsaved');
	
	//Build a parameter list from the form elements
	var params = getForm(fobj);
	
	ajaxPost('cscInterface.php?func=saveCSC()',params,null,'updateDivText(\"saveinfo\",\"Saved.\",\"saved\")');
	//ajaxPost('cscInterface.php?func=saveCSC()',params,'saveinfo',null);	
	clearTimeout(timerID);
	clearInterval(iTimeID);
}

/**************
 *  Calendar  *
 **************/
function updateUnavail(fobj){
	iTimeCount = 2;
	if (timerID != 0) {
		clearTimeout(timerID);
	}
	if (iTimeID != 0) {
		clearInterval(iTimeID);
	}
	iTimeID = setInterval(function(){updateDivText('saveinfo','Unsaved. Saving in '+(iTimeCount--),'unsaved')},800);
	timerID = setTimeout(function(){saveUnavail(fobj);fobj=null},2000);
}

function saveUnavail(fobj){
	updateDivText('saveinfo','Saving ...','unsaved');
	
	//Build a parameter list from the form elements
	var params = getForm(fobj);
	var rstr = 'calendarInterface.php?func=saveUnavailable()';
	ajaxPost(rstr,params,null,'updateDivText(\"saveinfo\",\"Saved. Refresh the page to update the availability information.\",\"saved\")');
//	ajaxPost(rstr,params,'saveinfo',null);
	clearTimeout(timerID);
	clearInterval(iTimeID);
}

function showRaid(raid_id){
	boxit('in');
	ajax('calendarInterface.php','showRaid(\''+raid_id+'\')','light',null);
}

// This was used to try and rewrite all the availability information after a
// local ajax update. It didn't work well. Deprecated.
/*
function updateUnavailDisplay(start,end) {

	var rstr = 'calendarInterface.php';

	// 86400 = 1 day in seconds
	for(var days=start;days<end; days=days+86400) {
		fstr = 'updateUnavailDisplay(\''+days+'\')';
		ajax(rstr,fstr,days+"box",null);
	}
}
*/

function makeEditBox(week_num,week_info,id) {
	//ajax('calendarInterface.php','showString(\'Loading ...\')',id,null);
	updateDivText(id,'Loading ...',null);
	ajax('calendarInterface.php','makeWeekEditBox(\''+week_num+'\',\''+week_info+'\',\''+id+'\')',id,"document.getElementById('editWeekBox'+id).focus()");
}

function saveEditBox(week_num,id) {
	var new_info = document.getElementById('editWeekBox'+id).value;
	//ajax('calendarInterface.php','showString(\'Saving ...\')',id,null);
	updateDivText(id,'Saving ...',null);
	ajax('calendarInterface.php','saveWeekEditBox(\''+week_num+'\',\''+new_info+'\',\''+id+'\')',id,null);
}

/**********************
 *  HELPER FUNCTIONS  *
 **********************/

// Mouseover tooltip code, largely ripped from tooltip.js:
// http://www.gerd-tentler.de/tools/
function showtip(thisid) {
	getMouseXY;
	var obj = document.getElementById(thisid);
//	obj.style.left = mouseX;
//	obj.style.top = mouseY;
	obj.style.left = (mouseX+5)+'px';
	obj.style.top = (mouseY+5)+'px';
	obj.style.visibility = 'visible';
}

function hidetip(thisid) {
	var obj = document.getElementById(thisid);
	obj.style.visibility = 'hidden';
}

function getMouseXY(e) {
	if(e && e.pageX != null) {
		mouseX = e.pageX;
		mouseY = e.pageY;
	}
	else if(event && event.clientX != null) {
		mouseX = event.clientX + getScrX();
		mouseY = event.clientY + getScrY();
	}
		if(mouseX < 0) mouseX = 0;
		if(mouseY < 0) mouseY = 0;
}

var mouseX = mouseY = 0;
document.onmousemove = getMouseXY;

// A mouse over function. Used for rollover classes
function mover(tDiv,theclass) {
	//var theDiv = document.getElementById(tDiv);
	tDiv.className = theclass;
}

// Lightbox code
function boxit(btype) {
	if (btype == "in") {
		document.getElementById('light').style.display='block';
		document.getElementById('fade').style.display='block';
	} else {
		document.getElementById('light').style.display='none';
		document.getElementById('fade').style.display='none';
		document.getElementById("light").innerHTML='Loading ... ';
	}
}

// Quick & dirty code to write text and/or change a classname on a div.
function updateDivText(id,txt,class){
	var tdiv = document.getElementById(id);
	if (class != null) {
		tdiv.className=class;
	}
	tdiv.innerHTML=txt;
}

// Form validation/collection for use in ajax, from:
// http://www.ajaxtutorial.net/index.php/2006/07/07/ajax-generic-form-parser/
function getForm(fobj) {  
	var str = "";  
	var ft = "";  
	var fv = "";  
	var fn = "";  
	var els = "";  
	for(var i = 0;i < fobj.elements.length;i++) {  
		els = fobj.elements[i];  
		ft = els.title;  
		fv = els.value;  
		fn = els.name;  
		switch(els.type) {  
		case "text":  
		case "hidden":  
		case "password":  
		case "textarea":  
			// is it a required field?  
			if(encodeURI(ft) == "required" && encodeURI(fv).length < 1) {  
				alert('\''+fn+'\' is a required field, please complete.');  
				els.focus();  
				return false;  
			}  
			str += fn + "=" + encodeURI(fv) + "&";  
		break;   

		case "checkbox":  
		case "radio":  
			if(els.checked) str += fn + "=" + encodeURI(fv) + "&";  
		break;      

		case "select-one":  
			str += fn + "=" +  
			els.options[els.selectedIndex].value + "&";  
		break;  
		} // switch
	} // for  
	str = str.substr(0,(str.length - 1));  
	return str;  
}

/****************
 *  AJAX HOOKS  *
 ****************/
// The major ajax function, used to call files to change content dynamically.
function ajax(file,func,id,posthook) { 
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null) {
		alert ("Browser does not support HTTP Request");
		return;
	}
	var url="lib/plugins/pqraid/"+file+"?func="+func+"&sid="+Math.random();

	xmlHttp.onreadystatechange=function(){stateChanged(id,posthook)};

	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

// Used to post form information via ajax for submission.
function ajaxPost(file,params,id,posthook) {
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null) {
		alert ("Browser does not support HTTP Request");
		return;
	}

	var url="lib/plugins/pqraid/"+file+"&sid="+Math.random();

	xmlHttp.onreadystatechange=function(){stateChanged(id,posthook)};

	xmlHttp.open('POST', url, true);
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", params.length);
	xmlHttp.setRequestHeader("Connection", "close");
	xmlHttp.send(params);

//	xmlHttp.open("GET",url,true);
//	xmlHttp.send(null);
}

function stateChanged(id,posthook) {
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete");
	{
		if (id != null){
			document.getElementById(id).innerHTML=xmlHttp.responseText;
		}
		//id.innerHTML=xmlHttp.responseText;
		eval(posthook);
	} 
}

// Provide a hook for XML http requests
function GetXmlHttpObject() {
var xmlHttp=null;
try
 {
 // Firefox, Opera 8.0+, Safari
 xmlHttp=new XMLHttpRequest();
 }
catch (e)
 {
 //Internet Explorer
 try
  {
  xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
  }
 catch (e)
  {
  xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
 }
return xmlHttp;
}
