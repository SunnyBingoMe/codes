<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';

define('TAX_RATE', 0.07);
function calculate_sales_tax($amount) {
	return round($amount * TAX_RATE,2);
}

class TestingTestCase extends UnitTestCase {
	function TestingTestCase($name='') {
		$this->UnitTestCase($name);
	}
	function setUp() {}
	function tearDown() {}

	function TestSalesTax() {
		$this->assertEqual(7, calculate_sales_tax(100));
	}
	
	function TestSomeMoreSalesTax() {
		$this->assertEqual(3.5, calculate_sales_tax(50));
	}
		
	function TestRandomValuesSalesTax() {
		$amount = rand(500,1000);
		$this->assertTrue(defined('TAX_RATE'));
		$tax = round($amount*TAX_RATE*100)/100;
		$this->assertEqual($tax, calculate_sales_tax($amount));
	}
	
	function TestRandomValuesSalesTax2() {
		$amount = rand(500,1000);
		$this->assertTrue(calculate_sales_tax($amount)<$amount*0.20);
	}
}


$test = new TestingTestCase('Testing Unit Test');
$test->run(new TextReporter());
exit;

$test = new GroupTest('Practices Unit Test');
$test->addTestCase(new TestingTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

