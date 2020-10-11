# Project Title

CakeDays App that takes in a birthdays CSV file and works out 
	when employees will get cake this calendar year.
The calculation will be based on a set out of rules:

- A small cake is provided on the employee’s first 
	working day after their birthday.
- All employees get their birthday off.
- The office is closed on weekends, Christmas Day, 
	Boxing Day and New Year’s Day.
- If the office is closed on an employee’s birthday, 
	they get the next working day off.
- If two or more cakes days coincide, we instead provide 
	one large cake to share.
- If there is to be cake two days in a row, we instead provide 
	one large cake on the second day.
- For health reasons, the day after each cake must be cake-free. 
	Any cakes due on a cakefree day are postponed to the 
	next working day.
- There is never more than one cake a day.

Export is in CSV file.

## Getting Started

A birthdays.csv file is necessary in the root directory of the project. 
The file should contain a name on each line, a comma and their birthday. 
For example: Dave, 1986-06-26
See birthdays-example.csv for a comprehensive example.

The app itself does not require installation and can be run simply 
	by running the following CLI command in the project directory:
php cakedays-app.php

To run the tests however:
Please run "composer install". 
This should restore the vendor directory for the PHPUnit test framework.
The following command should run the tests:
./vendor/bin/phpunit tests

### Prerequisites

Tested with PHP 7.3.11
Older versions will probably work however.

## Built With

* [PHPUnit](https://phpunit.de/) - The testing framework used

## Authors

* **[Andrew Nicholson](https://github.com/agdnicholson)**
