<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';

// from Chapter 2 Value Object
class Dollar {
	protected $amount;
	public function __construct($amount=0) {
		$this->amount = (float)$amount;
	}
	public function getAmount() {
		return $this->amount;
	}
	public function add($dollar) {
		return new Dollar($this->amount + $dollar->getAmount());
	}
	public function debit($dollar) {
		return new Dollar($this->amount - $dollar->getAmount());
	}
	public function divide($divisor) {
	return array_fill(0,$divisor,new Dollar($this->amount / $divisor));
		if ($divisor != (int)$divisor
			|| (int)$divisor < 1) {
			trigger_error('Invalid Divisor');
			return;
		}
		$ret = array();
		$alloc = round($this->amount / $divisor,2);
		$cumm_alloc = 0.0;
		foreach(range(1,$divisor-1) as $i) {
			$ret[] = new Dollar($alloc);
			$cumm_alloc += $alloc;
		}
		$ret[] = new Dollar(round($this->amount - $cumm_alloc,2));
		return $ret;
	}
}
class Monopoly {
	protected $go_amount;
	/**
	 * game constructor
	 * initialize the game and set "Go" payment to $200
	 * @return void
	 */
	public function __construct() {
		$this->go_amount = new Dollar(200);
	}
	/**
	 * pay a player for passing "Go"
	 * @param  Player $player  the player to pay
	 * @return void
	 */
	public function passGo($player) {
		$player->collect($this->go_amount);
	}
	/**
	 * pay rent from one player to another
	 * @param Player $from the player paying rent
	 * @param Player $to   the player collecting rent
	 * @param Dollar $rent the amount of the rent
	 * @return void
	 */
	public function payRent($from, $to, $amount) {
		$to->collect($from->pay($amount));
	}
}

abstract class Property {
	protected $name;
	protected $price;
	protected $game;
	
	function __construct($game, $name, $price) {
		$this->game = $game;
		$this->name = $name;
		$this->price = new Dollar($price);
	}
	
	abstract protected function calcRent();
	
	public function purchase($player) {
		$player->pay($this->price);
		$this->owner = $player;
	}
	
	public function rent($player) {
		if ($this->owner
			&& $this->owner != $player) {
			$this->owner->collect(
				$player($this->calcRent())
				);
		}
	}
}

class Street extends Property {
	protected $base_rent;
	public $color;
	public function setRent($rent) {
		$this->base_rent = new Dollar($rent);
	}
	protected function calcRent() {
		if ($this->game->hasMonopoly($this->owner, $this->color)) {
			return $this->base_rent->add($this->base_rent);
		}
		return $this->base_rent;
	}

}
class RailRoad extends Property {
	protected function calcRent() {
		switch($this->game->railRoadCount($this->owner)) {
		case 1: return new Dollar(25);
		case 2: return new Dollar(50);
		case 3: return new Dollar(100);
		case 4: return new Dollar(200);
		default: return new Dollar;
		}
	}
}
class Utility extends Property {
	protected function calcRent() {
		switch ($this->game->utilityCount($this->owner)) {
		case 1: return new Dollar(4*$this->game->lastRoll());
		case 2: return new Dollar(10*$this->game->lastRoll());
		default: return new Dollar;
		}
	}
}

class PropertyInfo {
	const TYPE_KEY  = 0;
	const PRICE_KEY = 1;
	const COLOR_KEY = 2;
	const RENT_KEY  = 3;
	public $type;
	public $price;
	public $color;
	public $rent;
	public function __construct($props) {
		$this->type  = $this->propValue($props, 'type',  self::TYPE_KEY);
		$this->price = $this->propValue($props, 'price', self::PRICE_KEY);
		$this->color = $this->propValue($props, 'color', self::COLOR_KEY);
		$this->rent  = $this->propValue($props, 'rent',  self::RENT_KEY);
	}
	protected function propValue($props, $prop, $key) {
		if (array_key_exists($key, $props)) {
			return $this->$prop = $props[$key];
		}
	}
}

class RealEstateAgent {
	protected $game;
	public function setGame($game) { $this->game = $game; }
	public function getProperty($name) {
		$prop_info = $this->getPropInfo($name);
		switch($prop_info->type) {
		case 'Street':
			$prop = new Street($this->game, $name, $prop_info->price);
			$prop->color = $prop_info->color;
			$prop->setRent($prop_info->rent);
			return $prop;
		case 'RailRoad':
			return new RailRoad($this->game, $name, $prop_info->price);
			break;
		case 'Utility':
			return new Utility($this->game, $name, $prop_info->price);
			break;
		default: //should not be able to get here
		}
	}
	
	protected $prop_info = array(
		//streets
		 'Mediterranean Ave.' => array('Street', 60, 'Purple', 2)
		,'Baltic Ave.'        => array('Street', 60, 'Purple', 2)
		//...
		,'Boardwalk'          => array('Street', 400, 'Blue', 50)
		//railroads
		,'Short Line R.R.'    => array('RailRoad', 200)
		//...
		//utilities
		,'Electric Company'   => array('Utility', 150)
		,'Water Works'        => array('Utility', 150)
		);

	protected function getPropInfo($name) {
		if (!array_key_exists($name, $this->prop_info)) {
			throw new InvalidPropertyNameException($name);
		}
		return new PropertyInfo($this->prop_info[$name]);
	}
}

class InvalidPropertyNameException extends Exception {
	private $invalid_name;
	public function __construct($name) {
		$this->invalid_name = $name;
	}
}

class TestableRealEstateAgent extends RealEstateAgent {
	public function getPropInfo($name) {
		return RealEstateAgent::getPropInfo($name);
	}
}

class TestPropertyFactory extends UnitTestCase {
	function testPropertyInfo() {
		$list = array('type','price','color','rent');
		$this->assertIsA($testprop = new PropertyInfo($list), 'PropertyInfo');
		foreach($list as $prop) {
			$this->assertEqual($prop, $testprop->$prop);
		}
	}
	function testPropertyInfoMissingColorRent() {
		$list = array('type','price');
		$this->assertIsA($testprop = new PropertyInfo($list), 'PropertyInfo');
		$this->assertNoErrors();
		foreach($list as $prop) {
			$this->assertEqual($prop, $testprop->$prop);
		}
		$this->assertNull($testprop->color);
		$this->assertNull($testprop->rent);
	}

	function testGetPropInfoReturn() {
		$agent = new TestableRealEstateAgent;
		$this->assertIsA($agent->getPropInfo('Boardwalk'), 'PropertyInfo');
	}
	
	function testBadPropNameReturnsException() {
		$agent = new TestableRealEstateAgent;
		$exception_caught = false;
		
		try { $agent->getPropInfo('Main Street'); }
		catch (InvalidPropertyNameException $e) {
			$exception_caught = true;
		}
		$this->assertTrue($exception_caught);
		$this->assertNoErrors();
	}
	
	function testGetStreet() {
		$agent = new RealEstateAgent;
		$this->assertIsA($agent->getProperty('Boardwalk'), 'Property');
	}
}


$test = new GroupTest('Factory PHP5 Unit Test');
$test->addTestCase(new TestPropertyFactory);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

