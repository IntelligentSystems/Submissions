<?php
$privDir = "/home/lrd900/code/is_submissions/private/";
if (!file_exists(getPath('lib/Smarty-3.1.8/libs/Smarty.class.php'))) {
	echo "wanted to include file, but couldnt find it. Is the variable used in locating the private directory properly set??\n";
	exit;
}


require(getPath('lib/Smarty-3.1.8/libs/Smarty.class.php'));
ini_set('display_errors',1);
// error_reporting(E_ALL);
error_reporting(E_ALL ^ E_NOTICE);
$config = parse_ini_file(getPath("config.ini"), true);
if ($_POST['performSanityCheck'] && count($_FILES['sanityCheckFile']['name']) > 0) {
	$errors = validateFile($_FILES['sanityCheckFile']['tmp_name'], false);
	drawSanityCheckResult($errors);
} elseif ($_POST['performSubmission'] && validFormFields()) {
	$errors = checkWeek();
	$errors = array_merge($errors, validateFile($_FILES['submissionFile']['tmp_name'], true));
	if (count($errors) === 0) {
		copyFile($_FILES['submissionFile']['tmp_name'], getDropboxDir(), $_FILES['submissionFile']['name']);
	}
	drawSubmissionDonePage($errors);
} else {
	drawRegularPage();
}

/**
 * Checks whether is submission is in time for the deadline
 * 
 * @return array of errors containing error messages
 */
function checkWeek() {
	global $config;
	$dateFormat = 'Y-m-d H:i:s';
	$errors = array();
	$deadline = $config['deadlines']['week'.$_POST['week']];
	if (!strlen($deadline)) {
		$errors[] = "No deadline set in system for week ".$_POST['week'].". Notify course supervisors";
	} else {
		$deadlineDate = DateTime::createFromFormat($dateFormat, $deadline);
		if ($deadlineDate === false) {
			$errors[] = "Unable to parse deadline for week ".$_POST['week'].". Notify course supervisors";
		} else {
			$now =  new DateTime("now");
			if ($deadlineDate < $now) {
				$errors[] = "The deadline has passed for week ".$_POST['week'].". Unable to submit your bot. Current time: ".$now->format($dateFormat).". Deadline: ".$deadlineDate->format($dateFormat);
			}
		}
	}
	return $errors;
}

/**
 * performs all required checks to validate file. 
 * Checks whether we can compile the file, and whether it runs in PlanetWars
 * @param unknown $filename
 */
function validateFile($filename, $submission) {
	$errors = array();
	
	//check whether it is a java class (veeeery naively)
	$fileContent = file_get_contents($filename);
	if (strpos($fileContent, "public class") === false) {
		$errors[] = "Uploaded java file has no valid class description";
	}
	//ok, so it's a java file. Now clean upload dir and copy file
	$uploadDir = getTempDir($submission);
	if (is_dir($uploadDir)) {
		shell_exec("rm ".$uploadDir."*");
	}
	
	$toFilename = ($submission? $_FILES['submissionFile']['name']: $_FILES['sanityCheckFile']['name']);
	$newFilename = copyFile($filename, $uploadDir, $toFilename);
	
	//check whether code actually compiles
	if (count($errors) === 0) {
		$errors = array_merge($errors, testCompilation($newFilename));
	}
	
	//check whether code runs in playgame
	if (count($errors === 0)) {
		$errors = array_merge($errors, testPlayGame($newFilename));
	}
	
	return $errors;
}

/**
 * Run it shortly. Plays against itself, for just 2 turns
 * 
 * @param String $filename Java file name to execute
 * @return errors, array of errors. Empty if everything is fine
 */
function testPlayGame($filename) {
	global $config;
	$errors = array();
	$engineDir = getPath($config['paths']['pwEngineDir']);
	
	//copy engine stuff to this uploaded directory
	shell_exec("cp ".$engineDir."PlayGame.jar ".$engineDir."map.txt " .dirname($filename));
	
	//backup current dir, so we can reset (chdir again) to original directory afterwards
	$workingDir = getcwd();
	chdir(dirname($filename));
	$botName = basename($filename, ".java");
	$cmd = "java -jar PlayGame.jar map.txt 100 2 tmp \"java ".$botName."\" \"java ".$botName."\" 2>&1";
	
	$result = shell_exec($cmd);
	if (strpos($result, "Wins") === false && strpos($result, "Draw") === false) {
		//Every game should have a winner or should be a draw. 
		//The output doesnt contain the string indicating this, so something must have gone wrong
		$errors[] = "Unable to run the bot. Output of PlayGame.jar: " . $result;
	}
	//reset to original working directory
	chdir($workingDir);
	
	return $errors;
}

/**
 *  Try compiling the java file
 *  
 * @param String $filename
 * @return array Errors found when compiling. Empty if everything is fine
 */
function testCompilation($newFilename) {
	$errors = array();
	global $config;
	
	//move compiled api to location of submitted file. otherwise file wont compile
	shell_exec("cp ".getPath($config['paths']['pwBotDir'])."*.class ".dirname($newFilename));
	
	//Change dir to dir of java file. 
	//This way compilation sees other api classes, and file is saved in proper location
	//backup current dir, so we can reset (chdir again) to original directory afterwards
	$workingDir = getcwd();
	chdir(dirname($newFilename));
	$result = shell_exec("javac ".basename($newFilename) ." 2>&1");
	//use last part to get error channel
	//than it return the actual string error msg, instead of null
	
	//check whether class name is indeed created
	if (!count(glob(basename($newFilename, ".java").".class"))) {
		$errors[] = "Failed to compile. Compile result: ". $result."\n";exit;
	}
	
	//reset to original working directory
	chdir($workingDir);
	return $errors;
}

/**
 * Copy uploaded file (in tmp dir) to upload folder
 * 
 * @param String $fromFilename From file
 * @return string Location of new file (based on form fields such as 'week' and 'group'
 */
function copyFile($fromFilename, $toDir, $toFilename) {
	if (!file_exists($toDir)) {
		mkdir($toDir,0777, true);
	}
	$newFilename = $toDir."/".$toFilename;
	
	copy($fromFilename, $newFilename);
	return $newFilename;
}

/**
 * Directory where we compile and test stuff. Content gets deleted when we're done.
 * 
 * @param boolean $submission Whether to create a directory for a assignment submission, or for a sanity check
 * @return string
 */
function getTempDir($submission) {
	global $config;
	if ($submission) {
		return getPath($config['paths']['uploadDir']). "week".$_POST['week']."/group".$_POST['group']."/";
	} else {
		return getPath($config['paths']['tmpDir']).uniqid();
	}
}


function getDropboxDir() {
	global $config;
	return getPath($config['paths']['dropboxDir']). "week".$_POST['week']."/group".$_POST['group']."/";
}



/**
 * Checks whether all form fields are filled in
 * 
 * @return boolean
 */
function validFormFields() {
	if (count($_FILES['submissionFile']['name']) > 0 && (int)$_POST['week'] > 0 && count($_POST['group']) > 0) {
		return true;
	} else {
		return false;
	}
}

/**
 * Draw regular page containing all forms
 */
function drawRegularPage() {
	global $config;
	$main = getSmarty();
	$main->display("main.tpl");
}

function drawSubmissionDonePage($errors) {
	global $config;
	$main = getSmarty();
	$main->assign("submission", true);
	$main->assign("errors", $errors);
	$main->display("main.tpl");
}
function drawSanityCheckResult($errors) {
	global $config;
	$main = getSmarty();
	$main->assign("sanityCheck", true);
	$main->assign("errors", $errors);
	$main->display("main.tpl");
	
}
function getSmarty() {
	global $config;
	$smarty = new Smarty();
	$smarty->setTemplateDir(getPath($config['paths']['templateDir']));
	$smarty->setCompileDir(getPath($config['paths']['templateCompileDir']));
	$smarty->setCacheDir(getPath($config['paths']['smartyCacheDir']));
	$smarty->setConfigDir(getPath($config['paths']['smartyConfigDir']));
	return $smarty;
}

/**
 * checks whether input is absolute or relative. 
 * when it is relative, assume we are looking for something in the private dir, and prepend this dir location to make it absolute
 * @param unknown $path
 */
function getPath($path) {
	global $privDir;
	if (substr($path, 0, 1) == "/") {
		//absolute
		return $path;
	} else {
		//relative: prepend location of private directory
		return $privDir.$path;
	}
}