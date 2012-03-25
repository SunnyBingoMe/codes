<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

/**
 * the HwLib helps programmers everywhere write their first program
 * @package HelloWorld
 */
class HwLibV1 {
	/** 
	 * Say "Hello"
	 * @deprec	this function is going away in the future
	 * @return	string
	 */
	function hello() {
		return 'Hello ';
	}
	/**
	 * target audience
	 * @return	string
	 */
	function world() {
		return 'World!';
	}
}

class HwLibV2 {
	function greet() {
		return 'Greetings and Salutations ';
	}
	function world() {
		return 'World!';
	}
}

class HwLibV2ToV1Adapter {
	var $libv2;
	function HwLibV2ToV1Adapter (&$libv2) {
		$this->libv2 =& $libv2;
	}
	function hello() {
		return $this->libv2->greet();
	}
	function world() {
		return $this->libv2->world();
	}
}

function &HwLibInstanceOrig() {
	return new HwLibV1;
}

function &HwLibInstance($ver=false) {
	switch ($ver) {
	case 'V2': 
		return new HwLibV2;
	default:
		return new HwLibV2ToV1Adapter(new HwLibV2);
	}
}

class HwLibGofAdapter extends HwLibV2 {
	function hello() {
		return parent::greet();
	}
}

class AdapterTestCase extends UnitTestCase {
	function TestOriginalApp() {
		$lib =& new HwLibV1;
		$this->assertEqual(
			'Hello World!'
			,$lib->hello().$lib->world());
	}
	function TestOriginalAppWouldFail() {
		$lib =& new HwLibV2;
		$this->assertFalse(method_exists($lib, 'hello'));
		$lib->hello();
	}
	function TestOriginalAppWithAdapter() {
		$lib =& new HwLibV2ToV1Adapter(new HwLibV2);
		$this->assertEqual(
			'Greetings and Salutations World!'
			,$lib->hello().$lib->world());
	}
	function TestAppWithFactory() {
		$lib =& HwLibInstanceOrig();
		$this->assertWantedPattern(
			'/\w+ World!$/'
			,$lib->hello().$lib->world());
	}
	function TestAppWithFactoryLibUpgrade() {
		$lib =& HwLibInstance();
		$this->assertWantedPattern(
			'/\w+ World!$/'
			,$lib->hello().$lib->world());
	}
	function TestHwLibGofAdapter() {
		$lib =& new HwLibGofAdapter;
		$this->assertEqual(
			'Greetings and Salutations World!'
			,$lib->hello().$lib->world());
	}
}

$test = new GroupTest('Adapter Unit Test');
$test->addTestCase(new AdapterTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

