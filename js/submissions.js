function validateSubmissionInput() {
	var valid = true;
	//group needs to have value
	if ($("#group").val() == undefined || $("#group").val().length == 0) {
		valid = false;
		var div = $("#group").parents("div.control-group");
		div.addClass("error");
		if($("#group").siblings().size() == 0) {
			$("#group").after('<span class="label label-important" style="margin-left: 8px;">Please specify your group</span>');
		}
	}
	//week needs to have value
	if ($("#week").val() == undefined || $("#week").val() < 1 || $("#week").val() > 4) {
		valid = false;
		var div = $("#week").parents("div.control-group");
		div.addClass("error");
		if($("#week").siblings().size() == 0) {
			$("#week").after('<span class="label label-important" style="margin-left: 8px;">Please provide for which week you wish to upload your submission</span>');
		}
	}
	//uploaded file needs to have .java extension
	if (!validFilename()) {
		valid = false;
		addFilenameError();
	}
	return valid;
}

function addFilenameError() {
	var div = $("#file").parents("div.control-group");
	div.addClass("error");
	console.log($("#file").siblings().size());
	if($("#file").siblings().size() == 1) {
		$("#file").after('<span class="label label-important" style="margin-left: 8px;">Only upload a .java file</span>');
	}
}

function validFilename() {
	var re = /(?:\.([^.]+))?$/;
	var extension = re.exec($("#file").val())[1];
	if (extension != "java") {
		return false;
	} else {
		return true;
	}
}

function onGroupChange() {
	if ($("#group").val() != undefined && $("#group").val() > 0) {
		var div = $("#group").parents("div.control-group");
		div.removeClass("error");
		if($("#group").siblings().size() > 0) {
			$("#group").next().remove();
		}
	}
}
function onWeekChange() {
	if ($("#week").val() != undefined && $("#week").val() > 0) {
		var div = $("#week").parents("div.control-group");
		div.removeClass("error");
		if($("#week").siblings().size() > 0) {
			$("#week").next().remove();
		}
	}
}
function onFileChange() {
	if (!validFilename()) {
		addFilenameError();
	} else if ($("#file").val() != undefined && $("#file").val().length > 0) {
		var div = $("#file").parents("div.control-group");
		div.removeClass("error");
		if($("#file").siblings().size() > 1) {
			$("#file").next().remove();
		}
	}
}
