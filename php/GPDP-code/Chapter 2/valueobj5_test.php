<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';

class BadDollar {
	protected $amount;
	public function __construct($amount=0) {
		$this->amount = (float)$amount;
	}
	public function getAmount() {
		return $this->amount;
	}
	public function add($dollar) {
		$this->amount += $dollar->getAmount();
	}
}
class Work {
	protected $salary;
	function __construct() {
		$this->salary = new BadDollar(200);
	}
	function payDay() {
		return $this->salary;
	}
}
class Person {
	public $wallet;
}

class Dollar {
	protected $amount;
	public function __construct($amount=0) {
		$this->amount = (float)$amount;
	}
	public function getAmount() {
		return $this->getAmount();
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

class Player {
	protected $name;
	protected $savings;
	/**
	 * constructor
	 * set name and initial balance
	 * @param  string $name the players name
	 * @return void
	 */
	public function __construct($name) {
		$this->name = $name;
		$this->savings = new Dollar(1500);
	}
	/**
	 * receive a payment
	 * @param  Dollar $amount the amount received
	 * @return void
	 */
	public function collect($amount) {
		$this->savings = $this->savings->add($amount); 
	}
	/**
	 * make a payment
	 * @param  Dollar $amount the amount to pay
	 * @return Dollar the amount payed
	 */
	public function pay($amount) {
		$this->savings = $this->savings->debit($amount); 
		return $amount;
	}
	/**
	 * return player balance
	 * @return	float
	 */
	public function getBalance() {
		return $this->savings->getAmount();
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

class ValueObjTestCase extends UnitTestCase {
	function __construct($name='') {
			$this->UnitTestCase($name);
	}
	function setUp() {}
	function tearDown() {}
	
	function testBadDollar() {
		$d1 = new BadDollar(10);
		$this->assertEqual(10,$d1->getAmount());
		$d1->add(new BadDollar(5));
		$this->assertEqual(15,$d1->getAmount());
	}
	function testBadDollarWorking() {
		$job = new Work;
		$p1 = new Person;
		$p2 = new Person;
		$p1->wallet = $job->payDay();
		$this->assertEqual(200, $p1->wallet->getAmount());
		$p2->wallet = $job->payDay();
		$this->assertEqual(200, $p2->wallet->getAmount());
		$p1->wallet->add($job->payDay());
		$this->assertEqual(400, $p1->wallet->getAmount());
		//this is bad
		$this->assertEqual(400, $p2->wallet->getAmount());
		//this is really bad
		$this->assertEqual(400, $job->payDay()->getAmount());
	}

	function testDollar() {
		$d = new Dollar;
		$this->assertEqual(0,$d->getAmount());

		$d2 = new Dollar(10);
		$this->assertEqual(10, $d2->getAmount());
	}
	function testDollarDivideEasy() {
		$test_amount = 6.80;
		$d = new Dollar($test_amount);
		$this->assertIsA($a = $d->divide(4), 'array');
		$this->assertEqual(4, count($a));
		foreach(array_keys($a) as $i) {
			$this->assertIsA($a[$i], 'Dollar');
			$this->assertEqual(1.70, $a[$i]->getAmount());
		}
	}
	function testDollarDivideReturnsArrayOfDivisorSize() {
		$full_amount = new Dollar(8);
		$parts = 4;
		$this->assertIsA(
			$result = $full_amount->divide($parts)
			,'array');
		$this->assertEqual($parts, count($result));
	}
	function testDollarDrivesEquallyForExactMultiple() {
		$test_amount = 1.25;
		$parts = 4;
		$dollar = new Dollar($test_amount*$parts);
		foreach($dollar->divide($parts) as $part) {
			$this->assertIsA($part, 'Dollar');
			$this->assertEqual($test_amount, $part->getAmount());
		}
	}
	function XtestDollarDivideImmuneToRoundingErrors() {
		$test_amount = 7;
		$parts = 3;
		$this->assertNotEqual(
			round($test_amount/$parts,2)
			,$test_amount/$parts
			,'Make sure we are testing a non-trivial case [%s]');
		
		$total = new Dollar($test_amount);
		$last_amount = false;
		$sum = new Dollar(0);
		foreach($total->divide($parts) as $part) {
			if ($last_amount) {
				$difference = abs($last_amount-$part->getAmount());
				$this->assertTrue($difference <= 0.01);
			}
			$last_amount = $part->getAmount();
			$sum = $sum->add($part);
		}
		$this->assertEqual($sum->getAmount(), $test_amount);
	}
}

class MonopolyTestCase extends UnitTestCase {
	
	function TestGame() {
		$game = new Monopoly;
		$player1 = new Player('Jason');
		$this->assertEqual(1500, $player1->getBalance());
		$game->passGo($player1);
		$this->assertEqual(1700, $player1->getBalance());
		$game->passGo($player1);
		$this->assertEqual(1900, $player1->getBalance());
	}

	function TestRent() {
		$game = new Monopoly;
		$player1 = new Player('Madeline');
		$player2 = new Player('Caleb');
		$this->assertEqual(1500, $player1->getBalance());
		$this->assertEqual(1500, $player2->getBalance());
		$game->payRent($player1, $player2, new Dollar(26));
		$this->assertEqual(1474, $player1->getBalance());
		$this->assertEqual(1526, $player2->getBalance());
	}
	function __construct($name='') {
			$this->UnitTestCase($name);
	}
	function setUp() {}
	function tearDown() {}

}

$test = new GroupTest('Value Object PHP5 Unit Test');
$test->addTestCase(new ValueObjTestCase);
$test->addTestCase(new MonopolyTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

