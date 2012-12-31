<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Information Systems: Planet Wars submissions</title>
<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body>

<div class="tabbable tabs-left">
  <ul class="nav nav-tabs">
    <li class=""><a href="#submission" data-toggle="tab">Submit your bot</a></li>
    <li class=""><a href="#sanityCheck" data-toggle="tab">Sanity Check your bot!</a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane" id="submission">
      
	<form class="navbar-form well form-horizontal" onSubmit="return validateSubmissionInput();" action="index.php" method="post" enctype="multipart/form-data">
		<fieldset>
		
		<!--<legend>When uploading a submission, an automatic check is performed as well</legend>-->
		<input type="hidden" name="performSubmission" value="1" />
		<div class="control-group">  
            <label class="control-label" for="group">Group</label> 
            <div class="controls">    
            <select onChange="onGroupChange();" id="group" name="group">
				<option value=""></option>
				{for $group=1 to 30}
					<option value="{$group}">{$group}</option>
				{/for}
			</select>
			</div>
         </div>
		<div class="control-group">  
			 <label class="control-label" for="week">Week</label> 
			 <div class="controls">   
			<select onChange="onWeekChange();" id="week" name="week">
				<option value=""></option>
				<option value="1">week1</option>
				<option value="2">week2</option>
				<option value="3">week3</option>
				<option value="4">week4</option>
			</select>
			</div>
		</div>
		<div class="control-group">  
			 <label class="control-label" for="file">File</label> 
			 <div class="controls">
			 	<input onChange="onFileChange();" type="file" name="file" id="file">
			 	<span class="help-block">Only submit the .java file of your bot</span>
			 </div>
		</div>
		<input class="btn btn-primary" type="submit" name="submit" value="Submit">
		</fieldset>
	</form>
    </div>
    <div class="tab-pane" id="sanityCheck">
      <p>Howdy, I'm in Section B.</p>
    </div>
    
    
  </div>
</div>
	{if $submission}
		{if $errors|@count == 0}
			<div class="alert alert-success">
				<p>Sucessfully uploaded your bot. Use the regular submission form to resubmit a newer version of the bot before the deadline.</p>
			</div>
		{else}
			<div class="alert alert-error">
			<p>Failed to submit your bot. The error(s) we encountered:</p>
			<ul>
				{foreach from=$errors item=error}
					<li>{$error}</li>
				{/foreach}
			</ul>
		
		</div>
		{/if}
	{/if}
            
            
	


	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/submissions.js"></script>
</body>
</html>