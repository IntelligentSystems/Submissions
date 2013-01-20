function addSanityCheckInput() {
	var controlGroup = $('#SCControlGroup');
	var clone = controlGroup.clone(true);
	clone.removeAttr('id');
	clone.find('#sanityCheckFile').val('');
	$('#SCButtons').before(clone);
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
	valid = true;
	fileForBotFound = false;
	//we need to define the bot name. check it!
	if ($("#SCBotName").val() == undefined || $("#SCBotName").val().length == 0) {
		valid = false;
		var div = $("#SCBotName").parents("div.control-group");
		div.addClass("error");
		if ($("#SCBotName").siblings().size() == 0) {
			$("#SCBotName")
					.after(
							'<span class="label label-important" style="margin-left: 8px;">You need to specify your bot name, otherwise we can\'t run it!</span>');
		}
	}
	anyFileSubmitted = false;
	
	//check all submitted files
	$("input:file[name='sanityCheckFile[]']").each(function(){
		if ($(this).val() != null && $(this).val().length > 0) {
			anyFileSubmitted = true;
		}
		if (valid && ($("#SCBotName").val() == getFilename($(this).val()) || $("#SCBotName").val() + ".java" == getFilename($(this).val()))) {
			fileForBotFound = true;
		}
		validationResult = validFilename($(this));
		if (validationResult != -1) {
			valid = false;
			addFilenameError($(this), validationResult);
		}
	});
	if (!anyFileSubmitted) {
		addFilenameError($("#sanityCheckFile"), "You forgot to upload the java files");
		valid = false;
	} else if (!fileForBotFound) {
		valid = false;
		var div = $("#SCBotName").parents("div.control-group");
		div.addClass("error");
		if ($("#SCBotName").siblings().size() == 0) {
			$("#SCBotName")
					.after(
							'<span class="label label-important" style="margin-left: 8px;">The name you specified does not correspond with any of the files you uploaded..</span>');
		}
	}
	return valid;
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
	if (element.val() != undefined && element.val().length > 0) {
		if (getExtension(element.val()) != "java") {
			return "Only upload a .java file";
		}
		var filename = getFilename(element.val());
		if (filename == "Planet.java" || filename == "PlanetWars.java") {
			return "Do not submit the Planet.java or PlanetWars.java files";
		}
		
		//check postfix
		var basename = getBasename(filename);
		console.log(basename.match(/^.*\d+/));
		if (!basename.match(/^.*\d+/)) {
			return "Append your group number to the file name, e.g. RandomBot14.java";
		}
	}
	return -1;
}
function getExtension(filePath) {
	var re = /(?:\.([^.]+))?$/;
	return re.exec(filePath)[1];
}
function getFilename(filePath) {
	return filePath.replace(/^.*[\\\/]/, '');
}
function getBasename(filePath) {
	filename = getFilename(filePath);
	var array =filename.split('.');
	return array[0];
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
function onBotNameChange(element) {
	removeError(element);
}


function removeError(element) {
	var div = element.parents("div.control-group");
	div.removeClass("error");
	if (element.siblings().size() > 0) {
		element.next().remove();
	}
}
