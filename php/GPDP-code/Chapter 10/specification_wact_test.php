<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

if (true) { // true = WACT code

	require_once 'dictionary.inc.php';
	class DataSource extends DictionaryDataSource {}

} else { // false = Registry like code

	class DataSource {
	  protected $store = array();
	  function get($key) {
		if (array_key_exists($key, $this->store)) 
		  return $this->store[$key];
	  }
	  function set($key, $val) {
		$this->store[$key] = $val;
	  }
	}
	
}

class FieldEqualSpecification {
	protected $field;
	protected $value;
	public function __construct($field, $value) {
		$this->field = $field;
		$this->value = $value;
	}
	public function isSatisfiedBy($datasource) {
		return ($datasource->get($this->field) == $this->value);
	}
}

class FieldMatchSpecification {
	protected $field;
	protected $regex;
	public function __construct($field, $regex) {
		$this->field = $field;
		$this->regex = $regex;
	}
	public function isSatisfiedBy($datasource) {
		return preg_match($this->regex, $datasource->get($this->field));
	}
}

class FieldGreaterThanOrEqualSpecification {
	protected $field;
	protected $value;
	public function __construct($field, $value) {
		$this->field = $field;
		$this->value = (float)$value;
	}
	public function isSatisfiedBy($datasource) {
		return ($datasource->get($this->field) >= $this->value);
	}
}

class SpecificationsTestCase extends UnitTestCase {
	protected $ds;
	function setup() {
		$this->ds = new DataSource;
		$this->ds->set('name', 'Jason');
		$this->ds->set('age', 34);
		$this->ds->set('email', 'jsweat_php@yahoo.com');
		$this->ds->set('sex', 'male');
	}
	function TestFieldEqualSpecification() {
		$name_jason = new FieldEqualSpecification('name', 'Jason');
		$this->assertTrue($name_jason->isSatisfiedBy($this->ds));
		
		$sex_other = new FieldEqualSpecification('sex', 'other');
		$this->assertFalse($sex_other->isSatisfiedBy($this->ds));
	}
	function TestFieldMatchSpecification() {
		$valid_email = new FieldMatchSpecification('email', '/^[^\s@]+@[^\s.]+(?:\.[^\s.]+)+/');
		$this->assertTrue($valid_email->isSatisfiedBy($this->ds));
		
		$name_ten_letters = new FieldMatchSpecification('name', '/^\w{10}$/');
		$this->assertFalse($name_ten_letters->isSatisfiedBy($this->ds));
	}
	function TestFieldGreaterThanOrEqualSpecification() {
		$adult = new FieldGreaterThanOrEqualSpecification('age', 18);
		$presidential_age = new FieldGreaterThanOrEqualSpecification('age', 35);
				
		$this->assertTrue($adult->isSatisfiedBy($this->ds));
		$this->assertFalse($presidential_age->isSatisfiedBy($this->ds));
	}
}


$test = new GroupTest('Specification WACT DataSource PHP5 Unit Test');
$test->addTestCase(new SpecificationsTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

