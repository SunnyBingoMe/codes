<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';

class CartR0 {
	protected $lines = array();
	public function addLine($line) {
		$this->lines[] = $line;
	}
	public function calcTotal() {
		$total = 0;
		// add totals for each line
		foreach($this->lines as $line) {
			$total += $line->price * $line->qty;
		}
		// add sales tax
		$total *= 1.07;
		return $total;
	}
}
class CartLineR0 {
	public $price = 0;
	public $qty = 0;
}
class CartR1 {
	protected $lines = array();
	public function addLine($line) {
		$this->lines[] = $line;
	}
	public function calcTotal() {
		$total = 0;
		foreach($this->lines as $line) {
			$total += $this->lineTotal($line);
		}
		$total += $this->calcSalesTax($total);
		return $total;
	}
	protected function lineTotal($line) {
		return $line->price * $line->qty;
	}
	protected function calcSalesTax($amount) {
		return $amount * 0.07;
	}
}
class CartR2 {
	protected $lines = array();
	public function addLine($line) {
		$this->lines[] = $line;
	}
	public function calcTotal() {
		$total = 0;
		foreach($this->lines as $line) {
			$total += $line->total();
		}
		$total += $this->calcSalesTax($total);
		return $total;
	}
	protected function calcSalesTax($amount) {
		return $amount * 0.07;
	}
}
class CartLineR2 {
	public $price = 0;
	public $qty = 0;
	public function total() {
		return $this->price * $this->qty;
	}
}



class RefactoringTestCase extends UnitTestCase {
        function RefactoringTestCase($name='') {
                $this->UnitTestCase($name);
        }
        function setUp() {}
        function tearDown() {}
        function TestBaseCode() {
        	$line1 = new CartLineR0;
        	$line1->price = 12; $line1->qty = 2;
        	$line2 = new CartLineR0;
        	$line2->price = 7.5; $line2->qty = 3;
        	$line3 = new CartLineR0;
        	$line3->price = 8.25; $line3->qty = 1;
        	
        	$cart = new CartR0;
        	$cart->addLine($line1);
        	$cart->addLine($line2);
        	$cart->addLine($line3);
        	
        	$this->assertEqual(
        		(12*2+7.5*3+8.25)*1.07,
        		$cart->calcTotal());
        }
        function TestFirstRefactor() {
        	$line1 = new CartLineR0;
        	$line1->price = 12; $line1->qty = 2;
        	$line2 = new CartLineR0;
        	$line2->price = 7.5; $line2->qty = 3;
        	$line3 = new CartLineR0;
        	$line3->price = 8.25; $line3->qty = 1;
        	
        	$cart = new CartR1;
        	$cart->addLine($line1);
        	$cart->addLine($line2);
        	$cart->addLine($line3);
        	
        	$this->assertEqual(
        		(12*2+7.5*3+8.25)*1.07,
        		$cart->calcTotal());
        }
        function TestSecondRefactor() {
        	$line1 = new CartLineR2;
        	$line1->price = 12; $line1->qty = 2;
        	$line2 = new CartLineR2;
        	$line2->price = 7.5; $line2->qty = 3;
        	$line3 = new CartLineR2;
        	$line3->price = 8.25; $line3->qty = 1;
        	
        	$cart = new CartR2;
        	$cart->addLine($line1);
        	$cart->addLine($line2);
        	$cart->addLine($line3);
        	
        	$this->assertEqual(
        		(12*2+7.5*3+8.25)*1.07,
        		$cart->calcTotal());
        }
}

$test = new GroupTest('Refactoring PHP5 Unit Test');
$test->addTestCase(new RefactoringTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

