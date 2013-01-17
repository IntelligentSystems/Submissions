function addSanityCheckInput() {
	var controlGroup = $('#SCControlGroup');
	var clone = controlGroup.clone(true);
	clone.removeAttr('id');
	clone.find('#sanityCheckFile').val('');
	$('#SCButtons').before(clone);
//	clone.appendTo('.input_holder');
//	SCControlGroup
};

$('.remove_field').click(function() {

	if ($('.input_holder input:last-child').attr('id') != 'input_clone') {
		$('.input_holder input:last-child').remove();
	}

});

function validateSubmissionInput() {
	var valid = true;
	//group needs to have value
	if ($("#group").val() == undefined || $("#group").val().length == 0) {
		valid = false;
		var div = $("#group").parents("div.control-group");
		div.addClass("error");
		if ($("#group").siblings().size() == 0) {
			$("#group")
					.after(
							'<span class="label label-important" style="margin-left: 8px;">Please specify your group</span>');
		}
	}
	//uploaded file needs to have .java extension
	if (!validFilename("submissionFile")) {
		valid = false;
		addFilenameError("submissionFile");
	}
	return valid;
}

function validateSanityCheckInput() {
	//TODOOOOOOOOOOOOOOOOOOOOOOO
	
	
	
	//uploaded file needs to have .java extension
	validationResult = validFilename();
	if (validationResult != -1) {
		valid = false;
		addFilenameError(element, validationResult);
	}
}

function addFilenameError(element, message) {
	var div = element.parents("div.control-group");
	div.addClass("error");
	//remove previous error message if there is one (new message might be different)
	if (element.siblings().size() > 0) {
		element.next().remove();
	}
	if (element.siblings().size() == 0) {
		element.after(
						'<span class="label label-important" style="margin-left: 8px;">' + message  + '</span>');
	}
}

function validFilename(element) {
	var re = /(?:\.([^.]+))?$/;
	var extension = re.exec(element.val())[1];
	if (extension != "java") {
		return "Only upload a .java file";
	}
	var filename = element.val().replace(/^.*[\\\/]/, '');
	if (filename == "Planet.java" || filename == "PlanetWars.java") {
		return "Do not submit the Planet.java or PlanetWars.java files";
	}
	return -1;
}

function onGroupChange() {
	if ($("#group").val() != undefined && $("#group").val() > 0) {
		var div = $("#group").parents("div.control-group");
		div.removeClass("error");
		if ($("#group").siblings().size() > 0) {
			$("#group").next().remove();
		}
	}
}
function onWeekChange() {
	if ($("#week").val() != undefined && $("#week").val() > 0) {
		var div = $("#week").parents("div.control-group");
		div.removeClass("error");
		if ($("#week").siblings().size() > 0) {
			$("#week").next().remove();
		}
	}
}
function onFileChange(element) {
	validationResult = validFilename(element);
	if (validationResult != -1) {
		addFilenameError(element, validationResult);
	} else if (element.val() != undefined && element.val().length > 0) {
		removeError(element);
		
	}
}
function removeError(element) {
	var div = element.parents("div.control-group");
	div.removeClass("error");
	if (element.siblings().size() > 0) {
		element.next().remove();
	}
}
