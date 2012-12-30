#!/usr/bin/php
<?php
$debug = true;
$config = parse_ini_file(__DIR__."/config.ini", true);


$botDir = $config['paths']['pwBotDir'];
if (!count($botDir)) {
	echo "invalid config file. Couldnt find bot directory\n";
	exit;
}


//delete all previously compiled files
if (count(glob($botDir."*.class"))) {
	shell_exec("rm ".$botDir."*.class");
	if (count(glob($botDir."*.class"))) {
		echo "Couldnt clean bot directory (wanted to remove previously compiled stuff. permissions wrong? exiting...\n";
		exit;
	}
}

$result = shell_exec("javac ".$config['paths']['pwBotDir']."*.java".($debug? " 2>&1":""));
if (count(glob($config['paths']['pwBotDir']."*.class"))) {
	echo "Succesfully compiled bot api\n";
} else {
	echo "Unable to compile bot api. Compilation output: ".$result."\n";
}

