<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Information Systems: Planet Wars submissions</title>
<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body>

	When uploading a submission, an automatic check is performed as well
	<form onSubmit="return validateSubmissionInput();" action="index.php" method="post" enctype="multipart/form-data">
		<input type="hidden" name="performSubmission" value="1" />
		<label>Group Name<input type="text" id="group" name="group" value="" size="30"></label>
		<label>Week<select id="week" name="week">
		<option value=""></option>
		<option value="1">week1</option>
		<option value="2">week2</option>
		<option value="3">week3</option>
		</select>
		</label>
		<label>
		<label>Filename: <input type="file" name="file" id="file">
		</label><br> 
		
		<input type="submit" name="submit" value="Submit">
	</form>


	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/submissions.js"></script>
</body>
</html>