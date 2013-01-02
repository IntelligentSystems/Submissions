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
    <!--<li class=""><a href="#submission" data-toggle="tab">Submit your bot</a></li>-->
    <li class=""><a href="#sanityCheck" data-toggle="tab">Sanity Check your bot!</a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane" id="submission">
      
	<form class="navbar-form well form-horizontal" onSubmit="return validateSubmissionInput();" action="index.php" method="post" enctype="multipart/form-data">
		<fieldset>
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
		<!--<div class="control-group">  
			 <label class="control-label" for="week">Week</label> 
			 <div class="controls">   
			<select onChange="onWeekChange();" id="week" name="week">
				<option value=""></option>
				{for $week=1 to 4}
					<option value="{$week}">{$week}</option>
				{/for}
			</select>
			</div>
		</div>-->
		<div class="control-group">  
			 <label class="control-label" for="file">File</label> 
			 <div class="controls">
			 	<input onChange='onFileChange("submissionFile");' type="file" name="submissionFile" id="submissionFile">
			 	<span class="help-block">Only submit the .java file of your bot</span>
			 </div>
		</div>
		<input class="btn btn-primary" type="submit" name="submit" value="Submit">
		</fieldset>
	</form>
    </div>
    <div class="tab-pane" id="sanityCheck">
    <p >Perform this sanity check to make sure your bot runs in our environment</p>
    <p><small>Running a bot locally on your computer does not ensure that the bot runs on other computers (e.g. the computer where we run the final competition on). 
    Reasons are: <ul><li>The use of external libraries or java7 functionality, which are missing on other computers</li>
    <li>The use of (relative or absolute) paths pointing to files which are only on your own computer, and not on others</li></ul> 
      <form class="navbar-form well form-horizontal" onSubmit="return validateSanityCheckInput();" action="index.php" method="post" enctype="multipart/form-data">
		<fieldset>
		<input type="hidden" name="performSanityCheck" value="1" />
		<div class="control-group">  
			 <label class="control-label" for="file">File</label> 
			 <div class="controls">
			 	<input onChange='onFileChange("sanityCheckFile");' type="file" name="sanityCheckFile" id="sanityCheckFile">
			 	<span class="help-block">Only submit the .java file of your bot</span>
			 </div>
		</div>
		<input class="btn btn-primary" type="submit" name="submit" value="Submit">
		</fieldset>
	</form>
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
	{if $sanityCheck}
		{if $errors|@count == 0}
			<div class="alert alert-success">
				<p>Congrats! We were able to run your bot.</p>
			</div>
		{else}
			<div class="alert alert-error">
			<p>Failed to run your bot. The error(s) we encountered:</p>
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