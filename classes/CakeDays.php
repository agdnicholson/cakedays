<?php
/**
*	CakeDays class
*	@author Andrew Nicholson (11 October 2020)
*	
*	Class that takes in array of birthdays and can work out
*		when people receive cakes based on a set of rules.
*	
*	**************  Assumptions *****************
*	No identical names exist. Names sharing the same birthday 
*		(including year - exact date) is not a problem.
*
*	**************	Usage  **********************
*	$cakeDaysObj = new CakeDays($birthdaysArr)
*	$cakeDays = $cakeDaysObj->getCakeDays();
*
*	****** 	INPUT: array with birthday as key (String: YYYY-MM-DD) ***
*	Note: If a birthday only has one name (probably most likely) it
*		still expects an array for the second parameter
*	["YYYY-MM-DD" => ["Name"],]
*
*	Example:
*	[
*		"1986-06-26" => ["Dave", "Jim"],
*		"1950-07-05" => ["Rob"]
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
	private $birthdays;
	private $processed;
	private $holidays;
	private $cakeDaysStack;
	private $cakeDaysExportArr;
	
	/** 
	*	CakeDays constructor
	*	Sets initial values using birthdays array.
	*	@param array birthdays
	*	@return void
	*/
	public function __construct($birthdays) {
		$this->birthdays = $birthdays;
		$this->processed = FALSE;
		$this->cakeDaysStack = [];

		/*
		*	Note the holiday rules; we also need to keep 1st of Jan
		*		next year in mind as birthdays very late in the year
		*		can have their cake days early next year
		*/
		$this->holidays = [
			date("Y-m-d", strtotime("1 January")), 
			date("Y-m-d", strtotime("25 December")), 
			date("Y-m-d", strtotime("26 December")),
			date("Y-m-d", strtotime("1 January ".(date('Y') + 1)))];
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
	*	Private function that works out our cake days
	*		based on the birthday array input. It uses
	*		private helper methods of this class to achieve that.
	*	@return void
	*/
	private function processCakeDays() {
		//remove years from birthdays
		$this->removeBirthdayYears();

		/*
		*	If someone is born in leap year on Feb 29 and this year isn't a leap year 
		*		we pretend that person is born on March 1st for simplicity.
		*/
		$this->leapYearFix();

		//key sort according to birthday
		ksort($this->birthdays);

		//start with basic cake day stack
		$this->populateCakeDaysStack();
		
		//If we have cake tomorrow too, merge the names on tomorrows cakeday.
		$this->tomorrowCakeTooCheckModiy();

		//apply the final health rule to make sure no two cake days in a row.
		$this->healthCheckModify();

		//key sort according to cakeday
		ksort($this->cakeDaysStack);

		//Sets the desired output array based on the cakedays stack
		$this->prepareExport();
	}

	/**
	*	Build initial cake day stack based on birthdays.
	*	Here we consider that an employee receives their cake on the next working day.
	*	IF on the employee's birthday the office is closed we need to
	*		add the employee's name a cakeday entry to the next working day after next.
	*	@return void
	*/
	private function populateCakeDaysStack() {
		foreach($this->birthdays as $date => $names) {
			$fullDate = date("Y-").$date;
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

	/**
	* 	Function to check if today is a working day based on weekends & holidays set.
	* 	@param String date (YYYY-mm-dd)
	* 	@return boolean 
	*/
	private function isWorkingDay($date) {
		$dayToCheck = new DateTime($date);
		if ($dayToCheck->format("l") === 'Saturday' || 
			$dayToCheck->format("l") === 'Sunday' ||
			in_array($dayToCheck->format("Y-m-d"), $this->holidays)) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	* 	Function that returns the next working day based on a date given.
	* 	@param String date (YYYY-mm-dd)
	* 	@return String date (YYYY-mm-dd)
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
	* 	@param String date (YYYY-mm-dd)
	* 	@return String date (YYYY-mm-dd)
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
	*	@return void
	*/
	private function prepareExport() {
		$this->cakeDaysExportArr = [];

		foreach ($this->cakeDaysStack as $date => $names) {
			$cakeDayDetails = ["date" => $date, 
				"small" => count($names) === 1 ? 1 : 0, 
				"large" => count($names) > 1 ? 1 : 0,
				"names" => $names];
			array_push($this->cakeDaysExportArr, $cakeDayDetails);
		}
	}

	/**
	*	Function that checks if there are any birthday entries with "02-29" as index.
	*	In the case this is NOT a leap year we would want to move those birthdays
	*		to March 1st ("03-01")
	*	@return void
	*/
	private function leapYearFix() {
		$year = intval(date('Y'));
		$isLeapYear = (($year % 4 === 0) && (($year % 100 !== 0) || ($year % 400 === 0)));
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
	*	Private function to manipulate the birthday dates,
	*		we want to remove the years as they are not
	*		relevant to cakedays. So we are left with "mm-dd"
	*		as the birthday array key format.
	*	@return void
	*/
	private function removeBirthdayYears() {
		foreach ($this->birthdays as $date => $names) {
			$dateKey = substr($date, 5);
			foreach ($names as $name) {

				//if people share same birthday (mm-dd only)
				if (array_key_exists($dateKey, $this->birthdays)) {
					array_push($this->birthdays[$dateKey], $name);
				} else {
					$this->birthdays[$dateKey] = [$name];
				}
			}

			//sort names alphabetically
			sort($this->birthdays[$dateKey]);

			//remove the full date key entry from the birthdays
			unset($this->birthdays[$date]);
		}
	}
}