// ==================
// = Form Validator =
// ==================
// created by Theo Howell
//
// Requires: jQuery
// Intended for use with bootstrap CSS but can be used by itself.

var firstInvalid = null;

function validateForm(formID, defaultFieldErrorMessage) 
{
	resetFormValidation("mainForm");
	
	var form = el_id(formID);
	
	var errors = 0;
	var inputs = form.getElementsByTagName("input");
	for (var i = 0; i < inputs.length; i++)
	{
		var input = inputs[i];
		if ($(input).is(':hidden')) {
			continue;
		}
		var value = input.value.trim();
		var required = input.getAttribute("required");
		if (required != null && value.length == 0) {
			invalidateField(input, defaultFieldErrorMessage);
			errors++;
		} 
		if (required != null && input.type == "checkbox" && input.checked == false) {
			invalidateField(input, defaultFieldErrorMessage);
			errors++;
		}
		else if (input.type == "email" && (value.indexOf("@") == -1 || value.indexOf(".") == -1)) {
			invalidateField(input, defaultFieldErrorMessage);
			errors++;
		}
		else {
			var minLength = input.getAttribute("data-min-length");
			if (minLength != null && value.length < minLength) {
				input.className = input.className + " invalid";
				input.parentNode.className = input.parentNode.className + " has-errors";
				var divs = input.parentNode.getElementsByClassName("error-message");
				if (divs.length > 0)
					divs[0].innerHTML = "The value for this field must be at least "+minLength+" characters long";
				errors++;
				if (firstInvalid == null)
					firstInvalid = input;
			}
		}
	}
	
	var selects = form.getElementsByTagName("select");
	for (var i = 0; i < selects.length; i++) {
		var s = selects[i];
		if ($(s).is(':hidden')) {
			continue;
		}
		var required = s.getAttribute("required");
		if (required != null && s.options[s.selectedIndex].value == "") {
			invalidateField(input, defaultFieldErrorMessage);
			errors++;
		} 
	}
	
	var textareas = form.getElementsByTagName("textarea");
	for (var i = 0; i < textareas.length; i++) {
		var t = textareas[i];
		if ($(t).is(':hidden')) {
			continue;
		}
		var required = t.getAttribute("required");
		if (required != null && t.value.trim() == "") {
			invalidateField(t, defaultFieldErrorMessage);
			errors++;
		} 
	}
	
	if (firstInvalid != null) {
		//var offset = input.offset.
		var $input = $("#"+firstInvalid.id);
    	$('html, body').animate({scrollTop: $input.offset().top - 20}, 250)
   	 	$input.focus()
	}
	
	return errors;
}

function invalidateField(input, defaultFieldErrorMessage)
{
	if (firstInvalid == null)
		firstInvalid = input;
	
	input.className = input.className + " invalid";
	input.parentNode.className = input.parentNode.className + " has-errors";
	var msg = input.getAttribute("data-error");
	if ((msg == null || msg.length == 0) && defaultFieldErrorMessage != null)
		msg = defaultFieldErrorMessage;
	if (msg != null && msg.length > 0) {
		var divs = input.parentNode.getElementsByClassName("error-message");
		if (divs.length > 0)
			divs[0].innerHTML = msg;
	}
}

function resetFormValidation(formID) 
{
	$("#"+formID).each(function() {
	    $(this).find(".has-errors").removeClass("has-errors");
		$(this).find(".invalid").removeClass("invalid");
		$(this).find(".error-message").empty();
	});
	firstInvalid = null;
}