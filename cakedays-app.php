<?php
require_once('classes/CakeDays.php');

$inputError = FALSE;
$fileError = FALSE;
$birthdaysArr = [];
$namesSoFar = [];

//CSV Input
try {
	$fp = fopen("birthdays.csv", "r");
	if ($fp !== FALSE &&
		file_get_contents("birthdays.csv") !== "") {
	    while (($data = fgetcsv($fp, 1000, ",")) !== FALSE) {
	        $numFields = count($data);
	        if ($numFields > 1) {
	        	$birthday = trim(strval($data[1]));
	        	$name = trim(strval($data[0]));

	        	/*
				* We validate fields here. An invalid date is for example 30 Feb.
				*	We do this here as it is not necessarily the cakedays class'
				*	responsibility. Also check name has at least 1 character and is unique.
				* The valid date check is attributed to Jon & Martin
				* 	in response to the stackoverflow question.
				*	See: https://stackoverflow.com/questions/13194322/php-regex-to-check-date-is-in-yyyy-mm-dd-format
	        	*/
				$dt = DateTime::createFromFormat("Y-m-d", $birthday);
				if (!($dt !== FALSE && !array_sum($dt::getLastErrors()))) {
					$inputError = TRUE;
					break;
				}
				if (strlen($name) === 0) {
					$inputError = TRUE;
					break;
				}
				if (in_array($name, $namesSoFar)) {
					$inputError = TRUE;
					break;
				}
				array_push($namesSoFar, $name);

	        	/*
	        	* People could be born on the same day so store as array against date.
	        	* The reason why we don't want to add this functionality into the 
	        	*	cakedays class is it is a separation of concerns and cakedays
	        	*	shouldn't have that responsibility necessarily. We could for example
	        	*	run into the scneario that there are duplicate names. 
	        	*	We may perhaps want to process the data first to ensure we de-dupe them
	        	*	somehow, either adding birth year or something else to the name. Again
	        	*	this is not the cakedays class' responsibility.
	        	*	
	        	*	The cakedays class instead expects an input that has birthday dates 
	        	*		as array key and any employee names born on that day in an 
	        	*		array as value and all names across the input to be unique.
	        	*/
	        	if (array_key_exists($birthday, $birthdaysArr)) {
					array_push($birthdaysArr[$birthday], $name);
				} else {
					$birthdaysArr[$birthday] = [$name];
				}
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

if ($fileError) die('Problem with input file. Make sure birthdays.csv '
	.'exists and contains valid data and has appropriate read permissions.');
if ($inputError) die('Problem with input. Please check birthdays.csv file. '
	.'Unique names and valid birthdays are required.');

$cakeDaysObj = new CakeDays($birthdaysArr);
$cakeDays = $cakeDaysObj->getCakeDays();

//CSV Output
try {
	$fp = fopen('cakedays.csv', 'w');
	if ($fp !== FALSE) {
		foreach ($cakeDays as $cakeDay) {
			$fields = [$cakeDay["date"], 
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

if ($fileError) die('Problem with generating output file. Make sure this folder and/or cakedays.csv '
	.'has appropriate (over)writing permissions.');
