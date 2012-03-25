<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

class DbConn {
	/**
	 * static property to hold instance
	 */
	static $instance = false;
	/**
	 * constructor
	 * private so only getInstance() method can instantiate
	 * @return void
	 */
	private function __construct() {}
	/**
	 * factory method to return the singleton instance
	 * @return DbConn
	 */
	public function getInstance() {
		if (!DbConn::$instance) {
			DbConn::$instance = new DbConn;
		}
		return DbConn::$instance;
	}
	public function getInstanceOrig() {
		static $instance = array();
		if (!$instance) $instance[0] = new DbConn;
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
			$this->assertIsA($obj1 = DbConn::getInstance(), 'DbConn');
			$this->assertIdentical($obj1, $obj2 = DbConn::getInstance());
	}
	function TestGetInstanceOrig() {
			$this->assertIsA($obj1 = DbConn::getInstanceOrig(), 'DbConn');
			$this->assertIdentical($obj1, $objOrig = DbConn::getInstanceOrig());
	}
	function xTestBadInstantiate() {
		$obj = new DbConn;
		$this->assertErrorPattern('/private.*__construct/i');
	}
	function TestBadInstantiate() {
		//$this->dump(get_class_methods('DbConn'));
		$o = new ReflectionClass('DbConn');
		$this->assertTrue($o->getConstructor()->isPrivate(), 'DbConn::__construct() is private');
	}
}

class Singleton {
	private function __construct() {}
	public function getInstance() {
			static $instance = array();
			if (!$instance) $instance[0] = new DbConn;
			return $instance[0];
	}
	private function findClass() {
		return debug_backtrace();
	}
}

class SingletonPrivate {
	static $instance = array();
	private function __construct() {}
	public function getInstance() {
		if (!$this->instance) {
			$this->instance[0] = new DbConn;
		}
		return $this->instance[0];
	}
}


class one extends Singleton {}
class two extends Singleton {}

class TestSingletonBaseclass extends UnitTestCase {
	function TestSingletonBaseclass($name='') {
                $this->UnitTestCase($name);
        }
        function setUp() {}
        function tearDown() {}

        function xTestGetInstance() {
		$this->dump(Singleton::getInstance());
		$this->dump(one::getInstance());
		$this->dump(two::getInstance());
	}
}

$test = new GroupTest('Singleton PHP5 Unit Test');
$test->addTestCase(new TestSingleton);
$test->addTestCase(new TestSingletonBaseclass);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

