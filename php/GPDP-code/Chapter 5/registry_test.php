<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';

class Registry {
	var $_store = array();
	function isValid($key) {
		return array_key_exists($key, $this->_store);
	}
	function &get($key) {
		if (array_key_exists($key, $this->_store)) 
			return $this->_store[$key];
	}
	function set($key, &$obj) {
		$this->_store[$key] =& $obj;
	}
	function &getInstance() {
		static $instance = array();
		if (!$instance) $instance[0] =& new Registry;
		return $instance[0];
	}
}

// this is meaningless, no point to it being global if you are going
// to access via singleton anyway
define('REGISTRY_GLOBAL_STORE', '__registry_global_store_key__');
class RegistryGlobal {
	var $_store;
	function RegistryGlobal() {
		if (!array_key_exists(REGISTRY_GLOBAL_STORE, $GLOBALS)
			|| !is_array($GLOBALS[REGISTRY_GLOBAL_STORE])) {
			$GLOBALS[REGISTRY_GLOBAL_STORE] = array();			
		}
		$this->_store =& $GLOBALS[REGISTRY_GLOBAL_STORE];
	}
	function isValid($key) {
		return array_key_exists($key, $this->_store);
	}
	function &get($key) {
		if (array_key_exists($key, $this->_store)) 
			return $this->_store[$key];
	}
	function set($key, &$obj) {
		$this->_store[$key] =& $obj;
	}
}

class RegistryMonoState {
	var $_store;
	function &_initRegistry() {
		static $store = array();
		return $store;
	}
	function RegistryMonoState() {
		$this->_store =& $this->_initRegistry();
	}
	function isValid($key) {
		return array_key_exists($key, $this->_store);
	}
	function &get($key) {
		if (array_key_exists($key, $this->_store)) 
			return $this->_store[$key];
	}
	function set($key, &$obj) {
		$this->_store[$key] =& $obj;
	}
}

class RegistryStatic {
	function &_getRegistry() {
		static $store = array();
		return $store;
	}
	function isValid($key) {
		$store =& RegistryStatic::_getRegistry();
		return array_key_exists($key, $store);
	}
	function &get($key) {
		$store =& RegistryStatic::_getRegistry();
		if (array_key_exists($key, $store)) 
			return $store[$key];
	}
	function set($key, &$obj) {
		$store =& RegistryStatic::_getRegistry();
		$store[$key] =& $obj;
	}
}

class RegistryStatic2 {
	function isValid($key) {
		$reg =& Registry::getInstance();
		return $reg->isValid($key);
	}
	function &get($key) {
		$reg =& Registry::getInstance();
		return $reg->get($key);
	}
	function set($key, &$obj) {
		$reg =& Registry::getInstance();
		$reg->set($key, $obj);
	}
}


class RegistryTestCase extends UnitTestCase {
	function setUp() {}
	function tearDown() {
		$reg =& Registry::getInstance();
		//clear the registry
		$reg->_store = array();
	}
	
	function testRegistryIsSingleton() {
		$this->assertIsA($reg =& Registry::getInstance(), 'Registry');
		$this->assertReference($reg, Registry::getInstance());
	}
	function testRegistry() {
		$reg =& Registry::getInstance();
		$this->assertFalse($reg->isValid('key'));
		$this->assertNull($reg->get('key'));
		$test_value = 'something';
		$reg->set('key', $test_value);
		$this->assertTrue($reg->isValid('key'));
		$this->assertReference($test_value, $reg->get('key'));
		//another way to test the reference
		$test_value .= ' else';
		$this->assertEqual(
			'something else'
			,$reg->get('key')
			);
	}
	
	function testEmptyRegistryKeyIsInvalid() {
		$reg =& Registry::getInstance();
		$this->assertFalse($reg->isValid('key'));
	}

	function testEmptyRegistryKeyReturnsNull() {
		$reg =& Registry::getInstance();
		$this->assertNull($reg->get('key'));
	}
	
	function testSetRegistryKeyBecomesValid() {
		$reg =& Registry::getInstance();
		$test_value = 'something';
		$reg->set('key', $test_value);
		$this->assertTrue($reg->isValid('key'));
	}
	
	function testSetRegistryValueIsReference() {
		$reg =& Registry::getInstance();
		$test_value = 'something';
		$reg->set('key', $test_value);
		$this->assertReference($test_value, $reg->get('key'));
		//another way to test the reference
		$test_value .= ' else';
		$this->assertEqual(
			'something else'
			,$reg->get('key')
			);
	}
	
	function testRegistryErrorWithLiteral() {
		$reg =& Registry::getInstance();
		//$reg->set('foo','bar','Fatal error: Cannot pass parameter 2 by reference');
	}
	
	function testCreateRegistryGlobalInitializesGlobal() {
		$this->assertTrue(defined('REGISTRY_GLOBAL_STORE'));
		$this->assertFalse(isset($GLOBALS[REGISTRY_GLOBAL_STORE]));
		$reg =& new RegistryGlobal;
		$this->assertIsA($GLOBALS[REGISTRY_GLOBAL_STORE],'array');
	}
	function testRegistryGlobal() {
		$reg =& new RegistryGlobal;
		$this->assertFalse($reg->isValid('key'));
		$this->assertNull($reg->get('key'));

		$test_value = 'something';
		$reg->set('key', $test_value);
		$this->assertReference($test_value, $reg->get('key'));
	}
	function testRegistryGlobalIsMonoState() {
		$reg =& new RegistryGlobal;
		$reg2 =& new RegistryGlobal;
		$this->assertCopy($reg, $reg2);

		$test_value = 'something';
		$reg->set('test', $test_value);
		$this->assertReference(
			 $reg->get('test')
			,$reg2->get('test'));
	}
	
	
	function testRegistryMonoState() {
		$reg =& new RegistryMonoState;
		$this->assertCopy(
			 $reg =& new RegistryMonoState
			,$reg2 =& new RegistryMonoState);
		$this->assertFalse($reg->isValid('key'));
		$this->assertNull($reg->get('key'));
		$test_value = 'something';
		$reg->set('key', $test_value);
		$this->assertReference($test_value, $reg2->get('key'));
		// equally
		$this->assertReference($reg->get('key'), $reg2->get('key'));
	}
	
	function testRegistryStatic() {
		$this->assertFalse(RegistryStatic::isValid('key'));
		$this->assertNull(RegistryStatic::get('key'));
		$test_value = 'something';
		RegistryStatic::set('key', $test_value);
		$this->assertReference($test_value, RegistryStatic::get('key'));
	}

	function testRegistryStatic2() {		
		$this->assertFalse(RegistryStatic2::isValid('key2'));
		$this->assertNull(RegistryStatic2::get('key2'));
		$test_value = 'something';
		RegistryStatic2::set('key2', $test_value);
		$this->assertReference($test_value, RegistryStatic2::get('key2'));
	}
}

$test = new GroupTest('Registry PHP4 Unit Test');
$test->addTestCase(new RegistryTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

