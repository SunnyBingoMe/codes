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

interface Specification {
	public function isSatisfiedBy($datasource);
}

abstract class BaseSpecification implements Specification {
	protected $field;
	// this is for the second policy example
	public function and_($spec) {
		return new AndSpecification($this, $spec);
	}
	public function or_($spec) {
		return new OrSpecification($this, $spec);
	}
}

class AndSpecification implements Specification {
	protected $spec;
	protected $andSpec;
	public function __construct($spec, $andSpec) {
		$this->spec = $spec;
		$this->andSpec = $andSpec;
	}
	function isSatisfiedBy($datasource) {
		return ($this->spec->isSatisfiedBy($datasource)
			&& $this->andSpec->isSatisfiedBy($datasource));
	}
}

class OrSpecification implements Specification {
	protected $spec;
	protected $orSpec;
	public function __construct($spec, $orSpec) {
		$this->spec = $spec;
		$this->orSpec = $orSpec;
	}
	function isSatisfiedBy($datasource) {
		return ($this->spec->isSatisfiedBy($datasource)
			|| $this->orSpec->isSatisfiedBy($datasource));
	}
}


class FieldEqualSpecification extends BaseSpecification {
	protected $value;
	public function __construct($field, $value) {
		$this->field = $field;
		$this->value = $value;
	}
	public function isSatisfiedBy($datasource) {
		return ($datasource->get($this->field) == $this->value);
	}
}

class FieldMatchSpecification extends BaseSpecification {
	protected $regex;
	public function __construct($field, $regex) {
		$this->field = $field;
		$this->regex = $regex;
	}
	public function isSatisfiedBy($datasource) {
		return preg_match($this->regex, $datasource->get($this->field));
	}
}

class FieldGreaterThanOrEqualSpecification extends BaseSpecification {
	protected $value;
	public function __construct($field, $value) {
		$this->field = $field;
		$this->value = (float)$value;
	}
	public function isSatisfiedBy($datasource) {
		return ($datasource->get($this->field) >= $this->value);
	}
}

class FieldInListSpecification extends BaseSpecification {
	protected $list;
	public function __construct($field, $list) {
		$this->field = $field;
		$this->list = $list;
	}
	public function isSatisfiedBy($datasource) {
		return in_array($datasource->get($this->field), $this->list);
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
	function TestFieldInListSpecification() {
		$valid_sex = new FieldInListSpecification('sex', array('male', 'female'));
		$this->assertTrue($valid_sex->isSatisfiedBy($this->ds));
		
		$this->ds->set('sex','other');
		$this->assertFalse($valid_sex->isSatisfiedBy($this->ds));
	}
}

class PolicyFactory {
	public function createJasonPolicy() {
		$name_jason = new FieldEqualSpecification('name', 'Jason');
		$age_at_least_thirty = new FieldGreaterThanOrEqualSpecification('age', 30);
		$male = new FieldEqualSpecification('sex', 'male');
		$jasons_email = new OrSpecification(
			 new FieldEqualSpecification('email', 'jsweat_php@yahoo.com')
			,new FieldEqualSpecification('email', 'jsweat@users.sourceforge.net'));
		return new AndSpecification(
			$name_jason, new AndSpecification(
				$age_at_least_thirty, new AndSpecification(
					$male, $jasons_email
			)));
	}
	// second policy example
	protected function equal($field, $value) {
		return new FieldEqualSpecification($field, $value);
	}
	protected function gTorEq($field, $value) {
		return new FieldGreaterThanOrEqualSpecification($field, $value);
	}
	public function createPrettierJasonPolicy() {
		return $this->equal('name', 'Jason')->and_(
			$this->gTorEq('age', 30)->and_(
			$this->equal('sex', 'male')->and_(
			$this->equal('email', 'jsweat_php@yahoo.com')->or_(
			$this->equal('email', 'jsweat@users.sourceforge.net')
		))));
	}
}

class PolicyTestCase extends UnitTestCase {
	protected $ds;
	function setup() {
		$this->ds = new DataSource;
		$this->ds = new DataSource;
		$this->ds->set('name', 'Jason');
		$this->ds->set('age', 34);
		$this->ds->set('email', 'jsweat_php@yahoo.com');
		$this->ds->set('sex', 'male');
	}
	function TestPolicy() {
		$policy = PolicyFactory::createJasonPolicy();
		$this->assertTrue($policy instanceof Specification);
		$this->assertTrue($policy->isSatisfiedBy($this->ds));
	}
	function TestPrettyPolicy() {
		$policy_factory = new PolicyFactory;
		$policy = $policy_factory->createPrettierJasonPolicy();
		$this->assertTrue($policy instanceof Specification);
		$this->assertTrue($policy->isSatisfiedBy($this->ds));
	}
}

$test = new GroupTest('Specification WACT DataSource Policy PHP5 Unit Test');
$test->addTestCase(new SpecificationsTestCase);
$test->addTestCase(new PolicyTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

