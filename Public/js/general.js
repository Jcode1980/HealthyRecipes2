// ========================================================
// = general.js - Basic general purpose scripting methods =
// ========================================================
// Author: Theo Howell
// Last Modified: 28/09/2012

function el_id(id)
{
	return document.getElementById(id);
}

function supports_input_placeholder() 
{
	var i = document.createElement('input');
	return 'placeholder' in i;
}

function debugln(msg)
{
	if ( ! window.console ) 
		console = { log: function(){} };
	console.log(msg);
}

function implode(delim, array) 
{
    var combined = "";
    var len = array.length;
    for (var i = 0; i < len; i++) {
        combined += array[i];
        if (i < len-1)
            combined += delim;
    }
    return combined;
}

function safeJSON(json) 
{
    try {
        var r = JSON.parse(json);
        return r;
    } catch (e) {
    	
    	alert(e.message);
        return null;
    }
}

// ===================
// = Class additions =
// ===================

if (typeof String.prototype.endsWith != 'function') 
{
	String.prototype.endsWith = function(suffix) {
	    return this.indexOf(suffix, this.length - suffix.length) !== -1;
	};
}

if (typeof String.prototype.startsWith != 'function') 
{
	String.prototype.startsWith = function(prefix) { 
		return this.slice(-prefix.length) == prefix;
	};
}

if (typeof String.prototype.trim != 'function')
{
	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g, '');
	};
}

/*
function validateLogin()
{
	if (validateForm("mainForm", "Please fill in this field.") == 0) 
	{
        var error = el_id("loginError");
        error.style.display = "none";
        el_id("alertMsg").style.display = "none";
        var progress = el_id("progressSC");
        progress.style.display = "inline";
        
        
        el_id("page").value = "%08y%05%00%80%CD%E0i%CA%23%22%DA%E1%99%B5X";
		el_id("action").value = "L%B6%CA4%EE%3C%02g%A4%D7%97%08N%94O%9B";
        
		
       ajaxSubmitForm("index.php", "mainForm", function(oq, result) {
            var result = safeJSON(response);
            if (result == null) {
                console.error(result);
                return;
            }
            if (result["error"] != 0) {
                error.style.display = "block";
                error.innerHTML = result["message"];
            }
            else {
                window.location = window.location;
            }
            progress.style.display = "none";
        }, 
        function(originalQuery, errorResponse) {
            error.style.display = "block";
            el_id("loginError").value = "There was a problem trying to log you in, please try again later.";
            progress.style.display = "none";
        });
	}
}*/


function validateLogin(page, action) {
	
    var error = el_id("loginError");
    error.style.display = "none";
    var progress = el_id("progressSC");
    progress.style.display = "inline";

    var login = encodeURIComponent(el_id("login").value);
    var password = encodeURIComponent(el_id("password").value);
    var data = "page="+page+"&action="+action+"&login="+login+"&password="+password;
    //alert("validateLogin: goat here: " + data);
    sendRequest("index.php", data, function(originalQuery, response) {
    	alert("blah blah " + response);
    	var result = safeJSON(response);
    	//alert(stackTrace());
    	//alert(print_r(debug_backtrace(), TRUE));
    	//alert("got the response : " + response);
    	//alert("response is  null?  : " + (response == null));
    	//alert("got the result : " + result);
        if (result == null) {
            console.error(response);
			alert("There was a problem trying to log you in, please try again later.");
            return;
        }
        if (result["error"] != 0) {
            error.style.display = "block";
            error.innerHTML = result["message"];
        }
        else {
            window.location = window.location;
        }
        progress.style.display = "none";
    }, 
    function(originalQuery, errorResponse) {
        error.style.display = "block";
        el_id("loginError").value = "There was a problem trying to log you in, please try again later.";
        progress.style.display = "none";
    });
}

function stackTrace() {
    var err = new Error();
    return err.stack;
}

