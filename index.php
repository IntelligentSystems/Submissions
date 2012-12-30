<?php
require('lib/Smarty-3.1.8/libs/Smarty.class.php');
ini_set('display_errors',1);
// error_reporting(E_ALL);
error_reporting(E_ALL ^ E_NOTICE);
$config = parse_ini_file(__DIR__."/config.ini", true);
if ($_POST['performCheck'] && validUpload()) {
	
} elseif ($_POST['performSubmission'] && validFormFields()) {
	$errors = validateFile($_FILES['file']['tmp_name']);
	$drawSubmissionDonePage($errors);
} else {
	drawRegularPage();
}


/**
 * performs all required checks to validate file. 
 * Checks whether we can compile the file, and whether it runs in PlanetWars
 * @param unknown $filename
 */
function validateFile($filename) {
	$errors = array();
	
	//check whether it is a java class (veeeery naively)
	$fileContent = file_get_contents($filename);
	if (strpos($fileContent, "public class") === false) {
		$errors[] = "Uploaded java file has no valid class description";
	} 
	//ok, so it's a java file. Now copy it to the upload dir
	$newFilename = copyFile($filename);
	
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
	$engineDir = $config['paths']['pwEngineDir'];
	
	//copy engine stuff to this uploaded directory
	shell_exec("cp ".$engineDir."PlayGame.jar ".$engineDir."map.txt " .dirname($filename));
	
	//backup current dir, so we can reset (chdir again) to original directory afterwards
	$workingDir = getcwd();
	chdir(dirname($filename));
	$cmd = "java -jar PlayGame.jar map.txt 100 2 tmp \"java RandomBot\" \"java RandomBot\" 2>&1";
	
	$result = shell_exec($cmd);
	if (strpos($result, "Wins") === false) {
		//Every game should have a winner. 
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
	shell_exec("cp ".$config['paths']['pwBotDir']."*.class ".dirname($newFilename));
	
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
function copyFile($fromFilename) {
	global $config;
	$newFilename = $config['paths']['uploadDir']. $_POST['week']."/".$_POST['group']."/";
	if (!file_exists($newFilename)) {
		mkdir($newFilename,0777, true);
	}
	$newFilename = $newFilename."/".$_FILES['file']['name'];
	
	copy($fromFilename, $newFilename);
	return $newFilename;
}

/**
 * Checks file name whether it is a java file (just checks extension)
 * @return boolean
 */
function validUpload() {
	if (count($_FILES['file']['name']) > 0) {//ends with java
		return true;
	} else {
		return false;
	}
}

/**
 * Checks whether all form fields are filled in
 * 
 * @return boolean
 */
function validFormFields() {
	if (validUpload() && (int)$_POST['week'] > 0 && count($_POST['group']) > 0) {
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

function drawSubmissionDonePage() {
	global $config;
	$main = getSmarty();
	$main->display("main.tpl");
}
function getSmarty() {
	global $config;
	$smarty = new Smarty();
	$smarty->setTemplateDir($config['paths']['templateDir']);
	$smarty->setCompileDir($config['paths']['templateCompileDir']);
	$smarty->setCacheDir($config['paths']['smartyCacheDir']);
	$smarty->setConfigDir($config['paths']['smartyConfigDir']);
	return $smarty;
}