<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';


class DbConn {
	function DbConn($fromGetInstance=false) {
		if (M_E != $fromGetInstance) {
			trigger_error('The DbConn class is a Singleton,'
				.' please do not instantiate directly.');
		}
	}
	function &getInstance() {
		static $instance = array();
		if (!$instance) $instance[0] =& new DbConn(M_E);
		return $instance[0];
	}
	function &getInstancePureStatic() {
		static $instance = false;
		if (!$instance) $instance =& new DbConn(true);
		return $instance;
	}
	function &getInstanceGlobal() {
		$key = '__some_unique_key_for_the_DbConn_instance__';
		if (!(array_key_exists($key, $GLOBALS)
			&& is_object($GLOBALS[$key])
			&& 'dbconn' == get_class($GLOBALS[$key])
			)) {
			$GLOBALS[$key] =& new DbConn(true);
		}
		return $GLOBALS[$key];
	}
}

class DbConn2 {
	function DbConn2() {
		$token = '__some_DbConn_instance_create_semaphore__';
		if (!array_key_exists($token, $GLOBALS)) {
			trigger_error('The DbConn class is a Singleton,'
			.' please do not instanciate directly.');
		}
	}
	function &getInstance() {
		static $instance = array();
		if (!$instance) {
			$token = '__some_DbConn_instance_create_semaphore__';
			$GLOBALS[$token] = true;
			$instance[0] =& new DbConn();
			unset($GLOBALS[$token]);
		}
		return $instance[0];
	}
}


class TestSingleton extends UnitTestCase {
        function SingletonTestCase($name='') {
                $this->UnitTestCase($name);
        }
        function setUp() {}
        function tearDown() {}

        function TestGetInstance() {
                $this->assertIsA($obj1 =& DbConn::getInstance(), 'DbConn');
                $this->assertReference($obj1, $obj2 =& DbConn::getInstance());
        }
	function TestGetInstanceBook() {
		$this->assertIsA(
			$obj1 =& DbConn::getInstance(), 
			'DbConn',
			'The returned object is an instance of DbConn');
		$this->assertReference(
			$obj1, 
			$obj2 =& DbConn::getInstance(),
			'two calls to getInstance() return the same object');
	}
	function TestGetInstanceStatic() {
		$this->assertIsA(
			$obj1 =& DbConn::getInstancePureStatic(), 
			'DbConn',
			'The returned object is an instance of DbConn');
		$this->assertReference(
			$obj1, 
			$obj1, //supposed to fail if using the version below
			//$obj2 =& DbConn::getInstancePureStatic(),
			'two calls to getInstance() return the same object');
	}
	function TestBadInstantiate() {
		$obj =& new DbConn;
		$this->assertErrorPattern(
			'/(bad|nasty|evil|do not|don\'t|warn).*(instance|create|new|direct)/i');
	}
	function TestBadInstantiate2() {
		$obj =& new DbConn2;
		$this->assertErrorPattern(
			'/(bad|nasty|evil|do not|don\'t|warn).*(instance|create|new|direct)/i');
	}
	function TestgetInstanceGloballobal() {
			$this->assertIsA($obj1 =& DbConn::getInstanceGlobal(), 'DbConn');
			$this->assertReference($obj1, $obj2 =& DbConn::getInstanceGlobal());
	}
}

class ApplicationConfig {
	var $_state;
	function ApplicationConfig() {
		$key = '__stealth_singleton_state_index__';
		if (!(array_key_exists($key, $GLOBALS)
			&& is_array($GLOBALS[$key]))) {
			$GLOBALS[$key] = array();
		}
		$this->_state =& $GLOBALS[$key];
	}
	function set($key, $val) {
		$this->_state[$key] = $val;
	}
	function get($key) {
		if (array_key_exists($key, $this->_state)) {
			return $this->_state[$key];
		}
	}
}

class ApplicationConfigUnitCase extends UnitTestCase {
	function TestApplConfig() {
		$this->assertIsA($obj1 =& new ApplicationConfig, 'ApplicationConfig');
		$this->assertIsA($obj2 =& new ApplicationConfig, 'ApplicationConfig');
		$test_val = '/path/to/cache'.rand(1,100);
		$obj1->set('cache_path', $test_val);
		$this->assertEqual($test_val, $obj2->get('cache_path'));
	}
}

$test = new GroupTest('Singleton Unit Test');
$test->addTestCase(new TestSingleton);
$test->addTestCase(new ApplicationConfigUnitCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

