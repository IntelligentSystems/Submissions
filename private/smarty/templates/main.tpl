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
    <p >Perform this sanity check to make sure your bot runs on the final competition server.</p>
    <p><small>Running a bot locally on your computer does not ensure the bot runs on the server on which we run the final competition. 
    Possible reasons for errors are: <ul><li>The use of external libraries or java7 functionality in your bot code, which are missing on other computers</li>
    <li>The use of (relative or absolute) paths pointing to files which are only on your own computer, and not on others</li>
    <li>If your computer is stronger than the hardware we use for the competition server, the bot might take too long for each turn. In that case, try to make your bot more efficient</li>
    </ul> 
    
      <form enctype="multipart/form-data" class="navbar-form well form-horizontal" onSubmit="return validateSanityCheckInput();" action="index.php" method="post" enctype="multipart/form-data">
		<fieldset>
		<div class="control-group">
			<div class="controls">
				<p>Only upload the .java file of your bot, and if applicable, other .java files. Do not submit the Planet.java and PlanetWars.java files. Did you make changes to these files? Then get these in a different java class, and upload again</p>
				<a href="#" class="btn btn-success btn-mini" onclick="addSanityCheckInput();"><i class="icon-white icon-plus"></i> Add another file</a>
				<input type="hidden" name="performSanityCheck" value="1" />
			</div>
		</div>
		
		<div class="control-group" id="SCControlGroup">  
			 
			 <label class="control-label" for="file">File</label> 
			 <div class="controls">
			 	<input onChange='onFileChange($(this));' type="file" name="sanityCheckFile[]" id="sanityCheckFile">
			 	<!--<span class="help-block">Only submit the .java file of your bot</span>-->
			 </div>
		</div>
		<div class="control-group" id="SCButtons">  
			<div class="controls"> 
				<input class="btn btn-primary" type="submit" name="submit" value="Submit">
			</div>
		</div>
		
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