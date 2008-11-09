/*
	interface.js - a javascript file for interface functions
		
	author:	P E Sartain	
	date:	21/10/2008
*/

// We keeep a variable up here to save calling the function every request on the same page.
var xmlHttp;

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
		document.getElementById("light").innerHTML='Loading ... ';
		document.getElementById('fade').style.display='none';
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

// Functions for the csceditor
var timerID = 0;
function updateCSC(){
	updateDivText('saveinfo','Unsaved ...','unsaved');
	if (timerID != 0) {
		clearTimeout(timerID);
	}
	timerID = setTimeout("saveCSC()",4000);		
}

function saveCSC(fobj){
	updateDivText('saveinfo','Saving ...','unsaved');
	
	//Build a parameter list from the form elements
	var str = "";	
	var valueArr = null;
	var val = "";
	var cmd = "";
	
	for(var i = 0;i < fobj.elements.length;i++)	{
		
	str += fobj.elements[i].name +
	
	"=" + escape(fobj.elements[i].value) + "&";
	
	break;
	
	case "select-one":
	
	str += fobj.elements[i].name +
	
	"=" + fobj.elements[i].options[fobj.elements[i].selectedIndex].value + "&";
	
	break;
	
	}
	
	}
	
	str = str.substr(0,(str.length - 1)); 
	
	ajaxPost('cscInterface.php','saveCSC()',null,'updateDivText(\"saveinfo\",\"Saved.\",\"saved\")');
	clearTimeout(timerID);
}

function unlockAchievements(id){
	var char_id = document.getElementById('charlist'+id).value;
	var role_id = document.getElementById('rolelist'+id).value;
	
	if ((char_id == '-1') || (role_id == '-1')) {
		// CSC is unset, lock the achievements
	} else {
		// Both character & role are set, thus a CSC exists
	}
}

// Functions for the calendar interface
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
	xmlHttp.setRequestHeader("Content-length", parameters.length);
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
