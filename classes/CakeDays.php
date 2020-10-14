<?php
/**
*	CakeDays class
*	@author Andrew Nicholson (14 October 2020)
*	
*	Class that takes in array of birthdays and can work out
*		when people receive cakes this year based on a set of rules.
*	
*	**************  Version history *****************
*	v2.0 - 14 October 2020: Fix context understanding of class and deal with year change over.
*		- ONLY consider a certain year's cakedays.
*		- Birthdays from late previous year can thus potentially 
*			run into this year's cakedays and even what happens early next 
*			year (with holidays or birthdays for example) can influence this year's cakedays.
*		- Make birthday input more flexible and do birthday grouping here so birthdays in input
*			don't need to be unique.
*		- Allow the year to be set / overwritten to make testing easier & more consistent
*			and class to be more useful.
*		- Allow holidays to be set / overwritten.
*		- Consider that holidays falling on weekends will still result 
*			in office closure on the next working day(s).
*		- Validate input (valid unique names required and valid birthdates).
*		- Output can result in error if invalid input is 
*			provided to also catch bad test cases.
*	v1.0 - 11 October 2020: Initial Version. 
*
*	**************  Assumptions *****************
*	No identical names exist in input. Identical or absent names will result in error. 
*		Names sharing the same birthday (including year - exact date) is not 
*		a problem. Birthdate strings need to be valid or error is returned.
*
*	**************	Usage  **********************
*	$cakeDaysObj = new CakeDays($birthdaysArr)
*	$cakeDays = $cakeDaysObj->getCakeDays();
*
*	Custom year can be set as so: 
*	$cakeDaysObj->setYear(2020); 
*	Where year is between 1970 and 2999. An invalid year will have no effect.
*	When getCakeDays() has been called setting the year will no longer have effect either.
*
*	Custom holidays can be set as so (array):
*	$cakeDaysObj->setHolidays(["1 January", "24 December", "25 December", "26 December"]);
*	If holidays fall on weekend days they are instead used as an office closure 
*		on the next working day(s).
*	When getCakeDays() has been called setting the holidays will no longer have effect.
*
*	****** 	INPUT: array of arrays which contains birthday and unique name ***
*	Note: we can have duplicate birthdays but names always need to be unique.
*	If same first names are required, just include last names or another way 
*		to tell people apart (string) in name field.
*	[
*		["YYYY-MM-DD", "Name"]
*	]
*
*	Example:
*	[
*		["1986-06-26", "Dave"],
*		["1986-06-26", "Jim"],
*		["1950-07-05", "Rob"]
*	]
*
*	******	OUTPUT:	associative array ***********
*	[
*		[
*			"date" => "YYYY-MM-DD", 
*       	"small" => 0 or 1 (Integer),
*      		"large" => 0 or 1 (Integer),
*     		"names" => [name of person receiving cake, name of person receiving cake]
*		],	
*	]
*
*	Example:	
*	[
*		[
*			"date" => "2020-07-22",
*        	"small" => 0,
*           "large" => 1,
*           "names" => ["Alex", "Jen"]
*        ],
*		 [
*			"date" => "2020-07-24",
*        	"small" => 1,
*           "large" => 0,
*           "names" => ["Pete"]
*        ],
*	]
*	
*	OR if invalid input is provided:
*	["error" => "reason"]
*	
*	Example:
*
*	["error" => "Duplicate names provided"]
* 	
*	It is therefore recommended to perform a check such as: 
*		array_key_exists("error", $cakeDays)
*		in order to capture any errors before processing the output of this class
*
*	*******	Rules ******************
* 	- A small cake is provided on the employee’s first 
*		working day after their birthday.
* 	- All employees get their birthday off.
*	- The office is closed on weekends, Christmas Day, 
*		Boxing Day and New Year’s Day.
*	- If the office is closed on an employee’s birthday, 
*		they get the next working day off.
*	- If two or more cakes days coincide, we instead provide 
*		one large cake to share.
*	- If there is to be cake two days in a row, we instead provide 
*		one large cake on the second day.
*	- For health reasons, the day after each cake must be cake-free. 
*		Any cakes due on a cakefree day are postponed to the 
*		next working day.
*	- There is never more than one cake a day.
*/
class CakeDays {
	private $birthdaysInput;
	private $birthdays;
	private $processed;
	private $holidays;
	private $officeClosures;
	private $cakeDaysStack;
	private $cakeDaysExportArr;
	private $year;
	private $validInput;

	/** 
	*	CakeDays constructor
	*	Sets initial values using birthdays array.
	*	@param array $birthdaysInput
	*	@return void
	*/
	public function __construct($birthdaysInput) {
		$this->birthdaysInput = $birthdaysInput;
		$this->birthdays = [];
		$this->processed = FALSE;
		$this->cakeDaysStack = [];
		$this->year = intval(date('Y'));
		$this->validInput = TRUE;
		$this->holidays = ["1 January", "25 December", "26 December"]; 
		$this->officeClosures = [];
	}

	/**
	*	Function that returns cakedays as array
	*	@return array
	*/
	public function getCakeDays() {
		if (!$this->processed) {
			$this->processCakeDays();
			$this->processed = TRUE;
		}
		return $this->cakeDaysExportArr;
	}

	/**
	*	Function that allows custom year to be set
	*	@param integer $year
	*	@return void
	*/
	public function setYear($year) {
		$year = intval($year);
		if ($year >= 1970 && $year <= 2999) {
			$this->year = $year;
		}
	}

	/**
	*	Function that allows custom holiday days to be set
	*	The format should be "1 January" for example
	*	@param array $holidays
	*	@return void
	*/
	public function setHolidays($holidays) {
		if(is_array($holidays)) {
			$this->holidays = $holidays;
		}
	}

	/**
	*	Private function that works out our cake days
	*		based on the birthday array input. It uses
	*		private helper methods of this class to achieve that.
	*	@return void
	*/
	private function processCakeDays() {

		//validate input
		$this->validateInput();

		if ($this->validInput) {
			//index (and where required group names) against birthdays and remove years from birthdays
			$this->indexBirthdaysAndRemoveYears();

			//set the office closures according to holidays in previous, current and next calendar years
			$this->prepareOfficeClosures();

			/*
			*	If someone is born in leap year on Feb 29 and this year isn't a leap year 
			*		we pretend that person is born on March 1st for simplicity.
			*/
			$this->leapYearFix();

			//key sort according to birthday
			ksort($this->birthdays);

			//start with basic cake day stack
			$this->populateCakeDaysStack();
			
			//key sort according to cakeday
			ksort($this->cakeDaysStack);

			//If we have cake tomorrow too, merge the names on tomorrows cakeday.
			$this->tomorrowCakeTooCheckModiy();

			//apply the final health rule to make sure no two cake days in a row.
			$this->healthCheckModify();

			//Sets the desired output array based on the cakedays stack
			$this->prepareExport();
		}
	}

	/**
	*	Build initial cake day stack based on birthdays.
	*	Here we consider that an employee receives their cake on the next working day.
	*	IF on the employee's birthday the office is closed we need to
	*		add the employee's name as cakeday entry to the next working day after next.
	*	We also capture all cakedays that are a result of birthdays from last year as some can
	*		be postponed to this year (most likely very late in the year).
	*	In addition we will add next year's birthdays too in case these impact on this year's 
	*		cakedays.
	*	@return void
	*/
	private function populateCakeDaysStack() {
		foreach($this->birthdays as $date => $names) {
			for ($i = -1; $i < 2; $i++) {
				$fullDate = strval($this->year + $i).'-'.$date;
				if ($this->isWorkingDay($fullDate)) {
					$keyDate = $this->getNextWorkingDay($fullDate);
				} else {
					$keyDate = $this->getNextNextWorkingDay($fullDate);
				}
				if (is_array($this->cakeDaysStack) &&
					array_key_exists($keyDate, $this->cakeDaysStack)) {
					$this->cakeDaysStack[$keyDate] = 
						array_merge($this->cakeDaysStack[$keyDate], $names);
					sort($this->cakeDaysStack[$keyDate]);
				} else {
					$this->cakeDaysStack[$keyDate] = $names;
				}
			}
		}
	}

	/**
	* 	Function to check if today is a working day based on weekends & holidays set.
	* 	@param String $date (YYYY-mm-dd)
	* 	@return boolean 
	*/
	private function isWorkingDay($date) {
		$dayToCheck = new DateTime($date);
		if ($dayToCheck->format("l") === 'Saturday' || 
			$dayToCheck->format("l") === 'Sunday' ||
			in_array($dayToCheck->format("Y-m-d"), $this->officeClosures)) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	* 	Function that returns the next working day based on a date given.
	* 	@param String $date (YYYY-mm-dd)
	* 	@return String (YYYY-mm-dd)
	*/
	private function getNextWorkingDay($date) {
		$nextWorkingDay = new DateTime($date);
		$nextWorkingDay->modify("+1 day");
		while (!$this->isWorkingDay($nextWorkingDay->format("Y-m-d"))) {
			$nextWorkingDay->modify("+1 day");
		}
		return $nextWorkingDay->format("Y-m-d");
	}

	/**
	* 	Function that returns the next working day after next based on a date given.
	* 	@param String $date (YYYY-mm-dd)
	* 	@return String (YYYY-mm-dd)
	*/
	private function getNextNextWorkingDay($date) {
		return $this->getNextWorkingDay($this->getNextWorkingDay($date));
	}

	/**
	*	Function that loops through the cake day stack per date. 
	*	It then merges any cake day names with the ones for tomorrow, 
	*		if there are any tomorrow and then deletes today's cake day stack entry.
	* 	@return void
	*/
	private function tomorrowCakeTooCheckModiy() {
		$skipDayCheck = FALSE;
		foreach ($this->cakeDaysStack as $date => $names) {
			if (!$skipDayCheck) {
				$nextDay = new DateTime($date);
				$nextDay->modify("+1 day");
				$nextDayKey = $nextDay->format("Y-m-d");
				
				/*
				*	IF we have names tomorrow we make this day a cake free day
				* 		AND we know we don't need to check the next day 
				*/
				if (array_key_exists($nextDayKey, $this->cakeDaysStack)) {
					$this->cakeDaysStack[$nextDayKey] = array_unique(
						array_merge($this->cakeDaysStack[$nextDayKey], $names));
					sort($this->cakeDaysStack[$nextDayKey]);
					$skipDayCheck = TRUE;
					unset($this->cakeDaysStack[$date]);
				}
			} else {
				// Reset day check
				$skipDayCheck = FALSE;
			}
		}
	}

	/**
	*	Function that loops through the cake day stack.
	*	It then runs a health check and potentially modifies the cakeday stack 
	*		if there was cake yesterday too, we then shift today's names to the 
	*		next working day and deletes today's cakeday stack entry.
	* 	@return void
	*/
	private function healthCheckModify() {
		$skipDayCheck = FALSE;
		foreach ($this->cakeDaysStack as $date => $names) {
			if (!$skipDayCheck) {
				$prevDay = new DateTime($date);
				$prevDay->modify("-1 day");
				$prevDayKey = $prevDay->format("Y-m-d");
				
				/*	IF we had cake yesterday, move names to next working day.
				*		We thus make this day a cake free day and no need to check next day.
				*/
				if (array_key_exists($prevDayKey, $this->cakeDaysStack)) {
					$nextWorkingDayKey = $this->getNextWorkingDay($date);
					if (array_key_exists($nextWorkingDayKey, $this->cakeDaysStack)) {
						$this->cakeDaysStack[$nextWorkingDayKey] = array_unique(
							array_merge($this->cakeDaysStack[$nextWorkingDayKey], $names));
					} else {
						$this->cakeDaysStack[$nextWorkingDayKey] = $names;
					}
					sort($this->cakeDaysStack[$nextWorkingDayKey]);
					$skipDayCheck = TRUE;
					unset($this->cakeDaysStack[$date]);
				}
			} else {
				// Reset day check
				$skipDayCheck = FALSE;
			}
		}
	}

	/**
	*	Function that prepares the export array of this class based on the current cake day stack.
	*	An entry per cake day is generated with either a small cake (if one person is 
	*		getting cake) or a large cake (if more than one) and names of thos receiving cake.
	*	Note we have cakedays from last year, this year and potentially next year, ONLY
	*		include cakedays for the current year!
	*	@return void
	*/
	private function prepareExport() {
		$this->cakeDaysExportArr = [];

		foreach ($this->cakeDaysStack as $date => $names) {
			if (strval(substr($date, 0, 4)) === strval($this->year)) {
				$cakeDayDetails = ["date" => $date, 
					"small" => count($names) === 1 ? 1 : 0, 
					"large" => count($names) > 1 ? 1 : 0,
					"names" => $names];
				array_push($this->cakeDaysExportArr, $cakeDayDetails);
			}
		}
	}

	/**
	*	Function that checks if there are any birthday entries with "02-29" as index.
	*	In the case this is NOT a leap year we would want to move those birthdays
	*		to March 1st ("03-01")
	*	@return void
	*/
	private function leapYearFix() {
		$isLeapYear = (($this->year % 4 === 0) && 
			(($this->year % 100 !== 0) || 
			 ($this->year % 400 === 0)));
		$oldKey = "02-29";
		if (array_key_exists($oldKey, $this->birthdays) && !$isLeapYear) {
			$newKey = "03-01";
			$names = $this->birthdays[$oldKey];
			if (array_key_exists($newKey, $this->birthdays)) {
				$this->birthdays[$newKey] = array_unique(
					array_merge($this->birthdays[$newKey], $names));
			} else {
				$this->birthdays[$newKey] = $names;
			}
			sort($this->birthdays[$newKey]);
			unset($this->birthdays[$oldKey]);
		}
	}

	/**
	*	Create an array of office closures based on holidays given.
	*	If holiday falls on weekend day then it will roll to the next
	*		working day.
	*	We also store holidays from last year and next year as those close
	*		to the year changes can have effect on current year's cakedays.
	*	@return void
	*/
	private function prepareOfficeClosures() {
		for ($i = -1; $i < 2; $i++) {
			for ($j = 0; $j < count($this->holidays); $j++) {
				$curHoliday = date("Y-m-d", 
						strtotime($this->holidays[$j].strval($this->year + $i)));
				if ($this->isWorkingDay($curHoliday)) {
					array_push($this->officeClosures, $curHoliday);
				} else {
					array_push($this->officeClosures, 
						$this->getNextWorkingDay($curHoliday));
				}
			}
		}
	}

	/**
	*	Function that takes the birthdays input array and indexes our data against dates.
	*	We also remove the birth years as these are not needed so our indexes look like "mm-dd"
	*	Names (even if it is just one) are stored against the index as an array to support 
	*		people potentially sharing the same birthday in a year.
	*	@return void
	*/
	private function indexBirthdaysAndRemoveYears() {
		foreach ($this->birthdaysInput as $birthdayEntry) {
			$birthday = substr(trim($birthdayEntry[0]), 5);
			$name = trim($birthdayEntry[1]);

			//if people share same birthday (mm-dd only)
			if (array_key_exists($birthday, $this->birthdays)) {
				array_push($this->birthdays[$birthday], $name);
			} else {
				$this->birthdays[$birthday] = [$name];
			}

			//sort names alphabetically
			sort($this->birthdays[$birthday]);
		}
	}

	/**
	*	Validation of input, checks we have unique names only (and are not empty) and
	*		valid dates are supplied.
	*	@return void
	*/ 	
	private function validateInput() {
		$namesSoFar = [];
		foreach($this->birthdaysInput as $birthdayEntry) {
			$birthday = trim($birthdayEntry[0]);
			$name = trim($birthdayEntry[1]);
			if (in_array($name, $namesSoFar)) {
				$this->cakeDaysExportArr = ["error" => "Duplicate name supplied"];
				$this->validInput = FALSE;
				break;
			}
			if (strlen($name) === 0) {
				$this->cakeDaysExportArr = ["error" => "Empty name supplied"];
				$this->validInput = FALSE;
				break;
			}
			array_push($namesSoFar, $name);

			/* The valid date check is attributed to Jon & Martin
			* 	in response to the stackoverflow question.
			*	See: https://stackoverflow.com/questions/13194322/php-regex-to-check-date-is-in-yyyy-mm-dd-format
	        */
			$dt = DateTime::createFromFormat("Y-m-d", $birthday);
			if (!($dt !== FALSE && !array_sum($dt::getLastErrors()))) {
				$this->cakeDaysExportArr = ["error" => "Invalid birthdate supplied"];
				$this->validInput = FALSE;
				break;
			}
		}
	}
}