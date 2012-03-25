<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

class Traveler {
	public	$min_temp;
}

class Destination {
	protected $avg_temps;
	public function __construct($avg_temps) {
		if (is_array($avg_temps) && 12 == count($avg_temps)) {
			$this->avg_temps = $avg_temps;
		} else {
			trigger_error('bad temps array passed');
		}
	}
	public function getAvgTempByMonth($month) {
		$key = (int)$month - 1;
		if (array_key_exists($key, $this->avg_temps)) {
			return $this->avg_temps[$key];
		}
	}
}

class Trip {
	public $date;
	public $traveler;
	public $destination;
}


class TripRequiredTemperatureSpecification {
	public function isSatisfiedBy($trip) {
		$trip_temp = $trip->destination->getAvgTempByMonth(date('m', $trip->date));
		return ($trip_temp >= $trip->traveler->min_temp);
	}
}

class TripSpecificationTestCase extends UnitTestCase {
	protected $destinations = array();
	function setup() {
		$this->destinations = array(
			'Toronto' => new Destination(array(24, 25, 33, 43, 54, 63, 69, 69, 61, 50, 41, 29))
			,'Cancun' => new Destination(array(74, 75, 78, 80, 82, 84, 84, 84, 83, 81, 78, 76))
			);
	}
	function TestTripTooCold() {
		$vicki = new Traveler;
		$vicki->min_temp = 70;
		
		$toronto = $this->destinations['Toronto'];
		
		$trip = new Trip;
		$trip->traveler = $vicki;
		$trip->destination = $toronto;
		$trip->date = mktime(0,0,0,2,11,2005);
		
		$warm_enough_check = new TripRequiredTemperatureSpecification;
		$this->assertFalse($warm_enough_check->isSatisfiedBy($trip));
	}
	function TestTripWarmEnough() {
		$vicki = new Traveler;
		$vicki->min_temp = 70;
		
		$cancun = $this->destinations['Cancun'];

		$trip = new Trip;
		$trip->traveler = $vicki;
		$trip->destination = $cancun;
		$trip->date = mktime(0,0,0,2,11,2005);
		
		$warm_enough_check = new TripRequiredTemperatureSpecification;
		$this->assertTrue($warm_enough_check->isSatisfiedBy($trip));
	}
}



class DestinationRequiredTemperatureSpecification {
	protected $temp;
	protected $month;
	public function __construct($traveler, $date) {
		$this->temp = $traveler->min_temp;
		$this->month = date('m', $date);
	}
	function isSatisfiedBy($destination) {
		return ($destination->getAvgTempByMonth($this->month) >= $this->temp);
	}
}

class DestinationSpecificationTestCase extends UnitTestCase {
	protected $destinations = array();
	function setup() {
		$this->destinations = array(
			'Toronto' => new Destination(array(24, 25, 33, 43, 54, 63, 69, 69, 61, 50, 41, 29))
			,'Cancun' => new Destination(array(74, 75, 78, 80, 82, 84, 84, 84, 83, 81, 78, 76))
			);
	}
	function TestFindingDestinations() {
		$this->assertEqual(2, count($this->destinations));
		
		$valid_destinations = array();
		$vicki = new Traveler;
		$vicki->min_temp = 70;
		$travel_date = mktime(0,0,0,2,11,2005);
		
		$warm_enough = new DestinationRequiredTemperatureSpecification(
			$vicki, $travel_date);
		
		foreach($this->destinations as $dest) {
			if ($warm_enough->isSatisfiedBy($dest)) {
				$valid_destinations[] = $dest;
			}
		}
		
		$this->assertEqual(1, count($valid_destinations));
		$this->assertIdentical($this->destinations['Cancun'], $valid_destinations[0]);
	}
}

$test = new GroupTest('Specification PHP5 Unit Test');
$test->addTestCase(new TripSpecificationTestCase);
$test->addTestCase(new DestinationSpecificationTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

