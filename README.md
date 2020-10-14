# CakeDays

A CakeDays App that takes in a birthdays CSV file and works out 
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

An input file is necessary with valid birthdays input. By default the app expects
	the filename to be called "birthdays.csv", however this can be customised.

The file should contain a name on each line, a comma and their birthday.\ 
For example: Dave, 1986-06-26.

See birthdays-example.csv for a comprehensive example.

You can run "cp birthdays-example.csv birthdays.csv" to create the initial 
	birthdays example file for the app to be able to run.

The app itself does not require installation and can be run simply 
	by running the following CLI command in the project directory:\
php cakedays-app.php

By default the ouput file cakedays.csv will be created. However the output filename
	can also be customised.

## Optional parameters
It is possible to set the processing cakedays year with the optional -y parameter:\
php cakedays-app.php -y 2019

It is possible to set custom holiday dates (in string format) with the optional -h parameter:\
php cakedays-app.php -h "1 January, 25 December" 

It is possible to set a custom input filename with the optional -i parameter:\
php cakedays-app.php -i birthdays-input.csv

It is possible to set a custom output filename with the optional -o parameter:\
php cakedays-app.php -o cake.csv

All switches can be used together as so (any order is possible):\
php cakedays-app.php -h "1 January, 25 December" -y 2021 -i birthdays-input.csv -o cake.csv

##	To run the tests
To run the tests composer is required.\
Once composer is installed:\
Please run "composer install". 

This should restore the vendor directory for the PHPUnit test framework.

The following command should run the tests:\
./vendor/bin/phpunit tests

## Version history
v2.0 - 14 October 2020: Fix context understanding of class and deal with year change over.
- Allow for command line arguments to set the holidays, year and filenames.
- Remove birthday grouping (done in class now) in app / csv processing.
- Remove some of the validation in app / csv processing (such as unique names - done in class now)
- Deal with the potential validation errors from the CakeDays class in app / csv processing.
- ONLY consider a certain year's cakedays.
- Birthdays from late previous year can thus potentially 
	run into this year's cakedays and even what happens early next 
	year (with holidays or birthdays for example) can influence this year's cakedays.
- Make birthday input more flexible and do birthday grouping here so birthdays in input
	don't need to be unique.
- Allow the year to be set / overwritten to make testing easier & more consistent
	and class to be more useful.
- Allow holidays to be set / overwritten.
- Consider that holidays falling on weekends will still result 
	in office closure on the next working day(s).
- Validate input (valid unique names required and valid birthdates).
- Output can result in error if invalid input is 
	provided to also catch bad test cases.

v1.0 - 11 October 2020: Initial Version. 

### Prerequisites
- PHP 7 is required.
- Composer and PHPUnit are required to run tests.

Tested with PHP 7.3.11

## Built With

* [Composer](https://getcomposer.org/) - PHP Dependency manager
* [PHPUnit](https://phpunit.de/) - The testing framework used

## Authors

* **[Andrew Nicholson](https://github.com/agdnicholson)**
