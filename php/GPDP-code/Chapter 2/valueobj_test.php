<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';

class Dollar {
	var $_amount;
	function Dollar($amount=0.0) {
		$this->_amount = (float)$amount;
	}
	function getAmount() {
		return $this->_amount;
	}
	function add($dollar) {
		return new Dollar($this->_amount + $dollar->getAmount());
	}
	function debit($dollar) {
		return new Dollar($this->_amount - $dollar->getAmount());
	}
	function divide($divisor) {
		$ret = array();
		$alloc = round($this->_amount / $divisor,2);
		$cumm_alloc = 0.0;
		foreach(range(1,$divisor-1) as $i) {
			$ret[] = new Dollar($alloc);
			$cumm_alloc += $alloc;
		}
		$ret[] = new Dollar(round($this->_amount - $cumm_alloc,2));
		return $ret;
	}
}

class ValueObjTestCase extends UnitTestCase {
	function ValueObjTestCase($name='') {
		$this->UnitTestCase($name);
	}
	function setUp() {}
	function tearDown() {}
	function TestDollar() {
		$d = new Dollar;
		$this->assertEqual(0,$d->getAmount());
		
		$d2 = new Dollar(10);
		$this->assertEqual(10, $d2->getAmount());
	}
	function TestChangeAmount() {
		$d = new Dollar(5);
		$this->assertEqual(5, $d->getAmount());
		
		//only possible in php4 by not respecting the _private convention
		$d->_amount = 10;
		$this->assertEqual(10, $d->getAmount());
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
	function testDollarDivide() {
		$test_amount = 7;
		$num_parts = 3;
		$this->assertNotEqual(
			round($test_amount/$num_parts,2),$test_amount/$num_parts
			,'Make sure we are testing a non-trivial case [%s]');
		
		$d = new Dollar($test_amount);
		$this->assertIsA($a = $d->divide($num_parts), 'array');
		$this->assertEqual($num_parts, count($a));
		$sum = 0;
		foreach(array_keys($a) as $i) {
			$this->assertIsA($a[$i], 'Dollar');
			$this->assertTrue($a[$i]->getAmount() > 0);
			$this->assertTrue($a[$i]->getAmount() < $test_amount);
			$sum += $a[$i]->getAmount();
			if ($i) {
				$this->assertTrue(abs($a[$i]->getAmount()-$a[$i-1]->getAmount()) <= 0.1);
			}
		}
		$this->assertEqual($sum, $test_amount);
	}
}

$test = new GroupTest('Value Object PHP4 Unit Test');
$test->addTestCase(new ValueObjTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

