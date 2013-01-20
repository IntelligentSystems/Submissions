<?php
error_reporting(E_ERROR | E_WARNING);
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
	$errors = validateFiles(false);
	drawSanityCheckResult($errors);
} elseif ($_POST['performSubmission'] && validFormFields()) {
	//always save a copy to our backup folder just in case
	copyFile($_FILES['submissionFile']['tmp_name'], getBackupDir(), date("Y-m-d_H:i:s")."_".$_FILES['submissionFile']['name']);
	
	$errors = checkDeadline();
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
function checkDeadline() {
	global $config;
	$dateFormat = 'Y-m-d H:i:s';
	$errors = array();
	$deadline = $config['deadlines']['deadline'];
	if (!strlen($deadline)) {
		$errors[] = "No deadline set in system. Notify course supervisors";
	} else {
		$deadlineDate = DateTime::createFromFormat($dateFormat, $deadline);
		if ($deadlineDate === false) {
			$errors[] = "Unable to parse deadline. Notify course supervisors";
		} else {
			$now =  new DateTime("now");
			if ($deadlineDate < $now) {
				$errors[] = "The deadline has passed. Unable to submit your bot. Current time: ".$now->format($dateFormat).". Deadline: ".$deadlineDate->format($dateFormat);
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
function validateFiles($submission) {
	$_FILES['sanityCheckFile']['tmp_name'];
	$errors = array();
	
	//get  upload dir and clean it
	$uploadDir = getTempDir($submission);
	if (is_dir($uploadDir)) {
		shell_exec("rm ".$uploadDir."*");
	}
	foreach ($_FILES['sanityCheckFile']['tmp_name'] AS $key => $filename) {
		if (strlen($filename)) {
			//check valid filename (need to have numerical postfix!)
			$errors = array_merge($errors, validateFilename($_FILES['sanityCheckFile']['name'][$key]));
			//check whether it is a java class (veeeery naively)
			$fileContent = file_get_contents($filename);
			if (strpos($fileContent, "public class") === false) {
				$errors[] = "Uploaded java file ". $_FILE['sanityCheckFile'][$key]['name'] ." has no valid class description. Are you sure you uploaded a correct .java file?";
			}
	
			$toFilename = ($submission? $_FILES['submissionFile']['name'][$key]: $_FILES['sanityCheckFile']['name'][$key]);
			$newFilename = copyFile($filename, $uploadDir, $toFilename);
		}
	}
	if (count($errors) === 0) {
		$errors = array_merge($errors, testCompilation($uploadDir));
	}
	//check whether code runs in playgame
	if (count($errors === 0)) {
		$errors = array_merge($errors, testPlayGame($uploadDir));
	}
	return $errors;
}

function validateFilename($filename) {
	$errors = array();
	$filearray = explode('.', $filename);
	$extension = end($filearray);
	if ($extension !== "java") {
		$errors[] = "Invalid file extension for file " . basename($filename) . ". Should be a .java file";
	} else if (!preg_match("/^.*\d+/", basename($filename, ".java"))) {
		$errors[] = "Add your group number to the end of your files, e.g. RandomBot14.java. Current filename: " . $filename;
		
	}
	return $errors;
}

/**
 * Run it shortly. Plays against itself, for just 2 turns
 * 
 * @param String $dir Directory where we want to run the game
 * @return errors, array of errors. Empty if everything is fine
 */
function testPlayGame($dir) {
	global $config;
	$errors = array();
	$engineDir = getPath($config['paths']['pwEngineDir']);
	
	//copy engine stuff to this uploaded directory
	shell_exec("cp ".$engineDir."PlayGame.jar ".$engineDir."map.txt " .$dir);
	
	//backup current dir, so we can reset (chdir again) to original directory afterwards
	$workingDir = getcwd();
	chdir($dir);
	$botName = getBotName();
	if (!strlen($botName)) $errors[] = "Unable to find a matching java file for your bot name";
	$cmd = "java -Xmx" . $config['game']['maxMemSanity'] ."m -jar PlayGame.jar map.txt";
	$cmd .= " \"java ".$botName."\" \"java ".$botName."\"";
	$cmd .= " parallel ".$config['game']['numTurns']." ".$config['game']['maxTurnTime']." 2>&1";
	$result = shell_exec($cmd);
	if (strpos($result, "Wins") === false && strpos($result, "Draw") === false) {
		//Every game should have a winner or should be a draw. 
		//The output doesnt contain the string indicating this, so something must have gone wrong
		$errors[] = "Unable to run the bot. Are you sure <br>- the bot properly compiled?<br>Output of PlayGame.jar: <div class=\"well\">" . $result."</div>";
	}
	if (strpos($result, "you missed a turn!") !== false) {
		$errors[] = "Your bot was too slow. The maximum time per turn is set to ".$config['game']['maxTurnTime']."ms. Try to make your bot more efficient.";
	}
	//reset to original working directory
	chdir($workingDir);
	
	return $errors;
}

function getBotName() {
	$botName = "";
	if ($_POST['performSanityCheck']) {
		$botName = $_POST['SCBotName'];
	} else {
		$botName = $_POST['SBotName'];
	}
	if (strlen($botName)) {
		if (substr($botName, -5) == ".java") {
			$botName = substr($botName, 0, -5);
		}
	}
	return $botName;
}

/**
 *  Try compiling the java file
 *  
 * @param String $dir Directory to compile java files in
 * @return array Errors found when compiling. Empty if everything is fine
 */
function testCompilation($dir) {
	$errors = array();
	global $config;
	//move compiled api to location of submitted file. otherwise file wont compile
	shell_exec("cp ".getPath($config['paths']['pwBotDir'])."*.class ".$dir);
	//Change dir to dir of java file. 
	//This way compilation sees other api classes, and file is saved in proper location
	//backup current dir, so we can reset (chdir again) to original directory afterwards
	$workingDir = getcwd();
	chdir($dir);
	$result = shell_exec("javac *.java 2>&1");
	//use last part to get error channel
	//than it return the actual string error msg, instead of null
	$allClassesCreated = true;
	foreach (glob("*.java") as $filename) {
		//check whether class name is indeed created
		if (!file_exists(basename($filename, ".java").".class")) {
			$allClassesCreated = false;
			break;
		}
	}
	//check whether class name is indeed created
	if (!$allClassesCreated) {
		$errors[] = "Failed to compile java file(s). Are you sure <br>-  there are no absolute/relative paths in your code which point to files on your computer?<br>- you don't use external libraries or java7 functionality in your code?<br>If you not sure what causes this compilation errors, and you have no problem compiling the code on your own computer, contact the course supervisors.<br>Compilation error message: <br><div class=\"well\">". $result."</div>\n";
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
		return getPath($config['paths']['uploadDir'])."group".$_POST['group']."/";
	} else {
		return getPath($config['paths']['tmpDir']).uniqid();
	}
}


function getDropboxDir() {
	global $config;
	return getPath($config['paths']['dropboxDir']). "group".$_POST['group']."/";
}

function getBackupDir() {
	global $config;
	return getPath($config['paths']['backupDir'])."group".$_POST['group']."/";
}


/**
 * Checks whether all form fields are filled in
 * 
 * @return boolean
 */
function validFormFields() {
	if (count($_FILES['submissionFile']['name']) > 0 /*&& (int)$_POST['week'] > 0*/ && count($_POST['group']) > 0) {
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