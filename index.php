<?php
require('lib/Smarty-3.1.8/libs/Smarty.class.php');
ini_set('display_errors',1);
// error_reporting(E_ALL);
error_reporting(E_ALL ^ E_NOTICE);
$config = parse_ini_file(__DIR__."/config.ini", true);
if ($_POST['performCheck'] && validUpload()) {
	
} elseif ($_POST['performSubmission'] && validFormFields()) {
	$errors = validateFile($_FILES['file']['tmp_name']);
	if (count($errors) === 0) {
		copyFile($_FILES['file']['tmp_name']);
	}
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
	
	//check whether code actually compiles
	if (count($errors) === 0 && testCompilation($filename) == false) {
		
	}
	
	//run code against example bot
	
	var_export($result);
	return $result;
	
}

function testCompilation($filename) {
	$newFilename = copyFile($filename);
	echo "compilation: "."javac ".$newFilename;
	$result = shell_exec("javac ".$newFilename);
	var_export($result);
}

function copyFile($fromFilename) {
	global $config;
	$newFilename = $config['paths']['uploadDir']. $_POST['week']."/";
	if (!file_exists($newFilename)) {
		mkdir($newFilename,0777, true);
	}
	$newFilename = $newFilename."/".$_POST['group'].".java";
	
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
function getSmarty() {
	global $config;
	$smarty = new Smarty();
	$smarty->setTemplateDir($config['paths']['templateDir']);
	$smarty->setCompileDir($config['paths']['templateCompileDir']);
	$smarty->setCacheDir($config['paths']['smartyCacheDir']);
	$smarty->setConfigDir($config['paths']['smartyConfigDir']);
	return $smarty;
}