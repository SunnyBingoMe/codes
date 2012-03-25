<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

class TestObj {
}

class RegistryMonoState {
	protected static $store = array();
	function isValid($key) {
		return array_key_exists($key, RegistryMonoState::$store);
	}
	function get($key) {
		if (array_key_exists($key, RegistryMonoState::$store)) 
			return RegistryMonoState::$store[$key];
	}
	function set($key, $obj) {
		RegistryMonoState::$store[$key] = $obj;
	}
	function clear() {
		RegistryMonoState::$store = array();
	}
}


class RegistryTestCase extends UnitTestCase {
	function __construct($name='') {
			$this->UnitTestCase($name);
	}
	function setUp() {}
	function tearDown() {
		RegistryMonoState::clear();
	}
	
	function testRegistryMonoState() {
		$reg = new RegistryMonoState;
		$this->assertCopy(
			 $reg = new RegistryMonoState
			,$reg2 = new RegistryMonoState);
		$this->assertFalse($reg->isValid('key'));
		$this->assertNull($reg->get('key'));
		$test_value = new TestObj;
		$reg->set('key', $test_value);
		$this->assertReference($test_value, $reg2->get('key'));
		$this->assertIdentical($test_value, $reg2->get('key'));
		// equally
		$this->assertIdentical($reg->get('key'), $reg2->get('key'));
	}

	function testRegistryMonoStateStaticCalls() {
		$this->assertFalse(RegistryMonoState::isValid('key'));
		$this->assertNull(RegistryMonoState::get('key'));
		$test_value = new TestObj;
		RegistryMonoState::set('key', $test_value);
		$this->assertIdentical($test_value, RegistryMonoState::get('key'));
	}
}
$test = new GroupTest('Registry PHP5 Unit Test');
$test->addTestCase(new RegistryTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

