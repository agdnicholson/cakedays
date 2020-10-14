<?php
require_once('classes/CakeDays.php');

$inputFileName = "birthdays.csv";
$outputFileName = "cakedays.csv";
$year = 0;
$holidays = "";

//Allow custom input for holidays, year, input filename and output filename
$options = getopt("h:y:i:o:");
if (isset($options['h'])) {
	$holidays = explode(", ", trim($options['h']));
}
if (isset($options['y']) && strlen($options['y']) > 0) {
	$year = intval(trim($options['y']));
}
if (isset($options['i']) && strlen($options['i']) > 0) {
	$inputFileName = trim($options['i']);
}
if (isset($options['o']) && strlen($options['o']) > 0) {
	$outputFileName = trim($options['o']);
}

$inputError = FALSE;
$fileError = FALSE;
$birthdaysArr = [];

//CSV Input
try {
	$fp = fopen($inputFileName, "r");
	if ($fp !== FALSE &&
		file_get_contents($inputFileName) !== "") {
	    while (($data = fgetcsv($fp, 1000, ",")) !== FALSE) {
	        $numFields = count($data);
	        if ($numFields > 1) {
	        	$birthday = trim(strval($data[1]));
	        	$name = trim(strval($data[0]));
				array_push($birthdaysArr, [$birthday, $name]);
	        } else {
	        	$inputError = TRUE;
	        }
	    }
	    fclose($fp);
	} else {
		$fileError = TRUE;
	}
} catch(Exception $e) {
	$fileError = TRUE;
}

if ($fileError) die('Problem with input file. Make sure ' . $inputFileName .
	' exists and contains valid data and has appropriate read permissions.');
if ($inputError) die('Problem with input. Please check birthdays.csv file.');

$cakeDaysObj = new CakeDays($birthdaysArr);
if ($year > 0) {
	$cakeDaysObj->setYear($year);
}
if (is_array($holidays)) {
	$cakeDaysObj->setHolidays($holidays);
}
$cakeDays = $cakeDaysObj->getCakeDays();

//CSV Output
//catch obvious input errors from cakedays class validation.
if(isset($cakeDays['error'])) die($cakeDays['error']);

try {
	$fp = fopen($outputFileName, 'w');
	if ($fp !== FALSE) {
		foreach ($cakeDays as $cakeDay) {
			$fields = [
				$cakeDay["date"], 
				$cakeDay["small"], 
				$cakeDay["large"],
				implode($cakeDay["names"], " ")];
		    fputcsv($fp, $fields);
		}
		fclose($fp);
	} else {
		$fileError = TRUE;
	}
} catch(Exception $e) {
	$fileError = TRUE;
}

if ($fileError) die('Problem with generating output file. Make sure this folder and/or ' . $outputFileName .
	' has appropriate (over)writing permissions.');
