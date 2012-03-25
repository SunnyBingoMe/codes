<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

class Accumulator {
	var $total=0;
	function add($item) {
		$this->total += $item;
	}
	function total() {
		return $this->total;
	}
}

function calc_total($items, &$sum) {
	foreach($items as $item) {
		$sum->add($item);
	}
}

function calc_tax(&$amount, $rate=0.07) {
	return round($amount->total() * $rate,2);
}

Mock::generate('Accumulator');
class MockObjectTestCase extends UnitTestCase {
	function testCalcTotal() {
		$sum =& new Accumulator;
		
		calc_total(array(1,2,3), $sum);
		$this->assertEqual(6, $sum->total());
	}

	function testCalcTax() {
		$amount =& new MockAccumulator($this);
		$amount->setReturnValue('total',200);
		$amount->expectOnce('total');
		
		$this->assertEqual(
			14, calc_tax($amount));
		
		$amount->tally();
	}
	
	function testCalcTotalAgain() {
		$sum =& new MockAccumulator($this);
		$sum->expectCallCount('add', 3);
		
		calc_total(array(1,2,3), $sum);
		
		$sum->tally();
	}
}

$test = new GroupTest('MockObject PHP4 Unit Test');
$test->addTestCase(new MockObjectTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

