/*
	interface.js - a javascript file for interface functions
		
	author:	P E Sartain	
	date:	21/10/2008
*/

// We keeep a variable up here to save calling the function every request on the same page.
var xmlHttp

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



function makeEditBox(week_num,week_info,id) {
	ajax('calendarInterface.php','showString(\'Loading ...\')',id,null)
	ajax('calendarInterface.php','makeWeekEditBox(\''+week_num+'\',\''+week_info+'\',\''+id+'\')',id,"document.getElementById('editWeekBox'+id).focus()")
}

function saveEditBox(week_num,id) {
	ajax('calendarInterface.php','showString(\'Saving ...\')',id,null)
	var new_info = document.getElementById('editWeekBox'+id).value
	ajax('calendarInterface.php','saveWeekEditBox(\''+week_num+'\',\''+new_info+'\',\''+id+'\')',id,null)
}

// The major ajax function, used to call Func files to change content dynamically.
function ajax(file,func,id,posthook) { 
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null) {
		alert ("Browser does not support HTTP Request")
		return
	}
	var url=file+"?func="+func+"&sid="+Math.random()

	xmlHttp.onreadystatechange=function(){stateChanged(id,posthook)};

	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)
}

function stateChanged(id,posthook) {
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
	{
		document.getElementById(id).innerHTML=xmlHttp.responseText
		//id.innerHTML=xmlHttp.responseText
		eval(posthook)
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
