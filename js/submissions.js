function validateSubmissionInput() {
	var valid = true;
	//group needs to have value
	if ($("#group").val() == undefined || $("#group").val().length == 0) {
		valid = false;
		alert("Please provide your group name or number");
	}
	//week needs to have value
	if ($("#week").val() == undefined || $("#week").val() < 1 || $("#week").val() > 4) {
		valid = false;
		alert("Please provide for which week you wish to upload your submission");
	}
	//uploaded file needs to have .java extension
	var re = /(?:\.([^.]+))?$/;
	var extension = re.exec($("#file").val())[1];
	if (extension != "java") {
		valid = false;
		alert("Please upload the .java file");
	}
	return valid;
}

