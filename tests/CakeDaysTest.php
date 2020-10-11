<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

//NOTE: 2020 Tests only. The expected dates will be completely different for subsequent years.

final class CakeDaysTest extends TestCase
{
    public function testSingleSmallCake(): void
    {
        $birthdaysArrTestData = ["1979-10-21"=>["Andrew"]];
        $expectedCakeDays = [];
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-10-22",
                "small"=>1,
                "large"=>0,
                "names"=>["Andrew"]
            ]);


        $cakeDaysObj = new CakeDays($birthdaysArrTestData);
        $this->assertEquals($expectedCakeDays, 
            $cakeDaysObj->getCakeDays());
    }

    public function testSingleLargeCake(): void
    {
        $birthdaysArrTestData = [
            "1979-05-21"=>["Andrew"],
            "1978-05-21"=>["Katie"]
            ];
        $expectedCakeDays = [];
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-05-22",
                "small"=>0,
                "large"=>1,
                "names"=>["Andrew", "Katie"]
            ]);

        $cakeDaysObj = new CakeDays($birthdaysArrTestData);
        $this->assertEquals($expectedCakeDays, 
            $cakeDaysObj->getCakeDays());
    }

    public function testSingleLargeCakeSameBirthday(): void
    {
        $birthdaysArrTestData = [
            "1979-05-21"=>["Andrew", "Katie"]
            ];
        $expectedCakeDays = [];
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-05-22",
                "small"=>0,
                "large"=>1,
                "names"=>["Andrew", "Katie"]
            ]);

        $cakeDaysObj = new CakeDays($birthdaysArrTestData);
        $this->assertEquals($expectedCakeDays, 
            $cakeDaysObj->getCakeDays());
    }

    public function testOriginalExamples(): void
    {
        $birthdaysArrTestData = [
            "1979-06-26"=>["Dave"],
            "1950-07-05"=>["Rob"],
            "1971-07-13"=>["Sam"],
            "1983-07-14"=>["Kate"],
            "1988-07-20"=>["Alex"],
            "1984-07-21"=>["Jen"],
            "1991-07-22"=>["Pete"]
        ];

        $expectedCakeDays = [];
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-06-29",
                "small"=>1,
                "large"=>0,
                "names"=>["Dave"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-07-07",
                "small"=>1,
                "large"=>0,
                "names"=>["Rob"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-07-15",
                "small"=>0,
                "large"=>1,
                "names"=>["Kate", "Sam"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-07-22",
                "small"=>0,
                "large"=>1,
                "names"=>["Alex", "Jen"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-07-24",
                "small"=>1,
                "large"=>0,
                "names"=>["Pete"]
            ]);
        $cakeDaysObj = new CakeDays($birthdaysArrTestData);
        $this->assertEquals($expectedCakeDays, 
            $cakeDaysObj->getCakeDays());
    }

    public function testOctoberWeek(): void
    {
        $birthdaysArrTestData = [
            "1979-10-12"=>["Harry"],
            "1960-10-12"=>["Xavier"],
            "1961-10-13"=>["William"],
            "1993-10-14"=>["Elizabeth"],
            "1958-10-15"=>["Jeff"],
            "1974-10-15"=>["Bob"],
            "1951-10-16"=>["Luka"],
            "1986-10-17"=>["Ellie"],
            "1988-10-17"=>["Norman"],
            "1984-10-18"=>["Fiona"]
        ];

        $expectedCakeDays = [];
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-10-14",
                "small"=>0,
                "large"=>1,
                "names"=>["Harry", "William", "Xavier"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-10-16",
                "small"=>0,
                "large"=>1,
                "names"=>["Bob", "Elizabeth", "Jeff"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-10-20",
                "small"=>0,
                "large"=>1,
                "names"=>["Ellie", "Fiona", "Luka", "Norman"]
            ]);
        $cakeDaysObj = new CakeDays($birthdaysArrTestData);
        $this->assertEquals($expectedCakeDays, 
            $cakeDaysObj->getCakeDays());
    }

    public function testOctoberWeekendsSmallCake(): void
    {
        $birthdaysArrTestData = [
            "1979-10-03"=>["Harry"],
            "1960-10-10"=>["Xavier"],
            "1961-10-17"=>["William"],
            "1993-10-24"=>["Elizabeth"],
            "1958-10-31"=>["Jeff"]
        ];

        $expectedCakeDays = [];
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-10-06",
                "small"=>1,
                "large"=>0,
                "names"=>["Harry"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-10-13",
                "small"=>1,
                "large"=>0,
                "names"=>["Xavier"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-10-20",
                "small"=>1,
                "large"=>0,
                "names"=>["William"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-10-27",
                "small"=>1,
                "large"=>0,
                "names"=>["Elizabeth"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-11-03",
                "small"=>1,
                "large"=>0,
                "names"=>["Jeff"]
            ]);
        $cakeDaysObj = new CakeDays($birthdaysArrTestData);
        $this->assertEquals($expectedCakeDays, 
            $cakeDaysObj->getCakeDays());
    }

    public function testOctoberWeekendsLargeCake(): void
    {
        $birthdaysArrTestData = [
            "1979-10-03"=>["Harry"],
            "1960-10-10"=>["Xavier"],
            "1961-10-17"=>["William"],
            "1993-10-24"=>["Elizabeth"],
            "1958-10-31"=>["Jeff"],
            "1974-10-04"=>["Bob"],
            "1951-10-11"=>["Luka"],
            "1986-10-18"=>["Ellie"],
            "1988-10-25"=>["Norman"],
            "1984-11-01"=>["Fiona"]
        ];

        $expectedCakeDays = [];
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-10-06",
                "small"=>0,
                "large"=>1,
                "names"=>["Bob", "Harry"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-10-13",
                "small"=>0,
                "large"=>1,
                "names"=>["Luka", "Xavier"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-10-20",
                "small"=>0,
                "large"=>1,
                "names"=>["Ellie", "William"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-10-27",
                "small"=>0,
                "large"=>1,
                "names"=>["Elizabeth", "Norman"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-11-03",
                "small"=>0,
                "large"=>1,
                "names"=>["Fiona", "Jeff"]
            ]);
        $cakeDaysObj = new CakeDays($birthdaysArrTestData);
        $this->assertEquals($expectedCakeDays, 
            $cakeDaysObj->getCakeDays());
    }

    /**
    *   2020 is a leap year and the weekend interferes 
    *       but still worth checking things don't break
    */
    public function testLeapYear(): void
    {
        $birthdaysArrTestData = [
            "1979-02-29"=>["Andrew"],
            "1988-02-29"=>["Elizabeth"],
            "1980-03-01"=>["Dave"]
            ];
        $expectedCakeDays = [];
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-03-03",
                "small"=>0,
                "large"=>1,
                "names"=>["Andrew", "Dave", "Elizabeth"]
            ]);


        $cakeDaysObj = new CakeDays($birthdaysArrTestData);
        $this->assertEquals($expectedCakeDays, 
            $cakeDaysObj->getCakeDays());
    }

    public function testSingleHolidaysScenario(): void
    {
        $birthdaysArrTestData = ["1979-12-25"=>["Andrew"]];
        $expectedCakeDays = [];
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-12-29",
                "small"=>1,
                "large"=>0,
                "names"=>["Andrew"]
            ]);


        $cakeDaysObj = new CakeDays($birthdaysArrTestData);
        $this->assertEquals($expectedCakeDays, 
            $cakeDaysObj->getCakeDays());
    }
    
    /**
    *   Probably the most thorough test to see if everything is working as expected
    */
    public function testFullHolidays(): void
    {
        $birthdaysArrTestData = [
            "1984-01-01"=>["Fred"],
            "1961-12-24"=>["William"],
            "1979-12-25"=>["Andrew", "Harry"],
            "1960-12-26"=>["Xavier"],
            "1961-12-26"=>["William"],
            "1993-12-27"=>["Elizabeth"],
            "1958-12-28"=>["Jeff"],
            "1974-12-29"=>["Bob"],
            "1951-12-30"=>["Luka"],
            "1986-12-30"=>["Ellie"],
            "1988-12-31"=>["Norman"],
            "1984-12-31"=>["Fiona"]
            ];
        $expectedCakeDays = [];
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-01-03",
                "small"=>1,
                "large"=>0,
                "names"=>["Fred"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-12-29",
                "small"=>0,
                "large"=>1,
                "names"=>["Andrew", "Elizabeth", "Harry", "Jeff", "William", "Xavier"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2020-12-31",
                "small"=>0,
                "large"=>1,
                "names"=>["Bob", "Ellie", "Luka"]
            ]);
        array_push($expectedCakeDays, 
            [
                "date"=>"2021-01-04",
                "small"=>0,
                "large"=>1,
                "names"=>["Fiona", "Norman"]
            ]);

        $cakeDaysObj = new CakeDays($birthdaysArrTestData);
        $this->assertEquals($expectedCakeDays, 
            $cakeDaysObj->getCakeDays());
    }
}
