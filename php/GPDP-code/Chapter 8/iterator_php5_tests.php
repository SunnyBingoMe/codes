<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

class PhpArrayIteratorTestCase extends UnitTestCase {
	function testIteration() {
		$test = array('one', 'two', 'three');
		$output = '';
		do {
			$output .= current($test).key($test);
		} while (next($test));
		$this->assertEqual($output, 'one0two1three2');
		$this->assertFalse(next($test));
		
		reset($test);
		$this->assertEqual(current($test).key($test), 'one0');
		$this->assertEqual(next($test), 'two');
	}
}


class Loanable {
	public $status = 'library';
	public $borrower = '';
	
	public function checkout($borrower) {
		$this->status = 'borrowed';
		$this->borrower = $borrower;
	}
	
	public function checkin() {
		$this->status = 'library';
		$this->borrower = '';
	}
}

class Media extends Loanable {
	public $name;
	public $type;
	public $year;
	
	public function __construct($name, $year, $type='dvd') {
		$this->name = $name;
		$this->type = $type;
		$this->year = (int)$year;
	}
}

class LoanableTestCase extends UnitTestCase {
	function TestCheckout() {
		$item = new Loanable;
		$this->assertFalse($item->borrower);
		$item->checkout('John');
		$this->assertEqual('borrowed', $item->status);
		$this->assertEqual('John', $item->borrower);
	}
	function TestCheckin() {
		$item = new Loanable;
		$item->checkout('John');
		$item->checkin();
		$this->assertEqual('library', $item->status);
		$this->assertFalse($item->borrower);
	}
}

class MediaTestCase extends UnitTestCase {
	function TestInstantiateRequiresParameters() {
		$dvd = new Media;
		$this->assertErrorPattern('/missing.*1/i');
		$this->assertErrorPattern('/missing.*2/i');
	}
	function TestFirstParamIsName() {
		$dvd = new Media('test', '');
		$this->assertEqual('test', $dvd->name);
	}
	function TestSecondParamIsYear() {
		$dvd = new Media('name', 2000);
		$this->assertEqual(2000, $dvd->year);
	}
	function TestTypeDefaultsToDvd() {
		$dvd = new Media('name', 2000);
		$this->assertEqual('dvd', $dvd->type);
    
		$cd = new Media('album', 1985, 'cd');
		$this->assertEqual('cd', $cd->type);
	}
	function TestStatusDefaultsToLibrary() {
		$dvd = new Media('name', 2000);
		$this->assertEqual('library', $dvd->status);
	}
}

class Library {
	protected $collection = array();
	
	function count() {
		return count($this->collection);
	}
	function add($item) {
		$this->collection[] = $item;
	}
	function del($item) {
		$count = $this->count();
		$this->collection = array_merge(array_diff($this->collection, array($item)));
		return ($count != $this->count());
	}
	function get($key) {
		if (array_key_exists($key, $this->collection)) {
			return $this->collection[$key];
		}
	}
	function keys() { 
		return array_keys($this->collection); 
	}
	/* original getIterator before polymorphic code
	function getIterator() {
		return new LibraryGofIterator($this->collection);
	}
	*/
	function getIterator($type=false) {
		switch (strtolower($type)) {
		case 'keyed': 
			return new LibraryGofExternalIterator($this);
		case 'external': 
			return new LibraryExternalIterator($this);
		case 'releaseexternal':
			return new LibraryReleasedExternalIterator($this);
		case 'media':
			$iterator_class = 'LibraryIterator';
			break;
		case 'available':
			$iterator_class = 'LibraryAvailableIterator';
			break;
		case 'released':
			$iterator_class = 'LibraryReleasedIterator';
			break;
		default:
			$iterator_class = 'LibraryGofIterator';
		}
		return new $iterator_class($this->collection);
	}
}


class LibraryGofIterator {
	protected $collection;
	function __construct($collection) {
		$this->collection = $collection;
	}
	function first() {
		reset($this->collection);
	}
	function next() {
		return (false !== next($this->collection));
	}
	function isDone() {
		return (false === current($this->collection));
	}
	function currentItem() {
		return current($this->collection);
	}
}

class LibraryGofExternalIterator {
	protected $key = 0;
	protected $collection;
	function __construct($collection) {
		$this->collection = $collection;
	}
	function first() {
		$this->key=0;
	}
	function next() {
		return (++$this->key < $this->collection->count());
	}
	function isDone() {
		return ($this->key >= $this->collection->count());
	}
	function currentItem() {
		return $this->collection->get($this->key);
	}
}


class LibraryIterator {
	protected $collection;
	protected $first=true;
	function __construct($collection) {
		$this->collection = $collection;
	}
	// show fail case with just next($this->collection)
	function next() {
		if ($this->first) {
			$this->first = false;
			return current($this->collection);
		}
		return next($this->collection);
	}
}

/* not shown in book, but aluded to in mentioning the index gap problem */
class LibraryExternalIterator {
	protected $collection;
	protected $key_map;
	protected $key=-1;
	function __construct($collection) {
		$this->collection = $collection;
		$this->key_map = $collection->keys();
	}
	function next() {
		if (++$this->key >= $this->collection->count()) {
			return false;
		} else {
			return $this->collection->get($this->key_map[$this->key]);
		}
	}
}


class LibraryAvailableIterator {
	protected $collection = array();
	protected $first=true;
	function __construct($collection) {
		$this->collection = $collection;
	}
	function next() {
		if ($this->first) {
			$this->first = false;
			$ret = current($this->collection);
		} else {
			$ret = next($this->collection);
		}
		if ($ret && 'library' != $ret->status) {
			return $this->next();
		}
		return $ret;
	}
}

class LibraryReleasedIterator extends LibraryIterator {
	function __construct($collection) {
		usort($collection
			,create_function('$a,$b','return ($a->year - $b->year);'));
		$this->collection = $collection;
	}
}

class LibraryReleasedExternalIterator {
	protected $collection;
	protected $sorted_keys;
	protected $key=-1;
	function __construct($collection) {
		$this->collection = $collection;
		$sort_funct = create_function(
			'$a,$b,$c=false',
			'static $collection; 
			if ($c) { 
				$collection = $c; 
				return; 
			} 
			return ($collection->get($a)->year - $collection->get($b)->year);');
		$sort_funct(null,null,$this->collection);
		$this->sorted_keys = $this->collection->keys();
		usort($this->sorted_keys, $sort_funct);
	}
	function next() {
		if (++$this->key >= $this->collection->count()) {
			return false;
		} else {
			return $this->collection->get($this->sorted_keys[$this->key]);
		}
	}
	function reset() {
		$this->key = -1;
	}
}


class LibraryTestCase extends UnitTestCase {
	function TestCount() {
		$lib = new Library;
		$this->assertEqual(0, $lib->count());
	}
	function TestAdd() {
		$lib = new Library;
		$lib->add('one');
		$this->assertEqual(1, $lib->count());
	}
	function TestGet() {
		$lib = new Library;
		$lib->add($dvd1 = new Media('name1', 2000));
		$lib->add($dvd2 = new Media('name2', 2001));
		$this->assertIdentical($lib->get(0), $dvd1);
		$this->assertIdentical($lib->get(1), $dvd2);
		$this->assertNull($lib->get(2));
	}
	function TestDel() {
		$lib = new Library;
		$lib->add($dvd1 = new Media('name1', 2000));
		$lib->add($dvd2 = new Media('name2', 2001));
		$lib->add($dvd3 = new Media('name3', 2002));
		$dvd4 = new Media('not added to lib', 2002);
		$this->assertEqual(3,$lib->count());
		$this->assertTrue($lib->del($dvd2));
		$this->assertEqual(2,$lib->count());
		$this->assertFalse($lib->del($dvd4));
	}
	function TestDelKeysRemainSequential() {
		$lib = new Library;
		$lib->add($dvd1 = new Media('name1', 2000));
		$lib->add($dvd2 = new Media('name2', 2001));
		$lib->add($dvd3 = new Media('name3', 2002));
		$lib->add($dvd4 = new Media('name4', 2002));

		$this->assertEqual(4,$lib->count());
		$this->assertTrue($lib->del($dvd2));
		$this->assertEqual(3,$lib->count());
		$this->assertEqual(range(0,2), $lib->keys());
	}
}

class IteratorTestCase extends UnitTestCase {
	protected $lib;
	function setup() {
		$this->lib = new Library;
		$this->lib->add(new Media('name1', 2000));
		$this->lib->add(new Media('name2', 2002));
		$this->lib->add(new Media('name3', 2001));
	}
	function TestGetGofIterator() {
		$this->assertIsA($it = $this->lib->getIterator(), 'LibraryGofIterator');
		$this->assertFalse($it->isdone());
		$this->assertIsA($first = $it->currentItem(), 'Media');
		$this->assertEqual('name1', $first->name);
		$this->assertFalse($it->isdone());
		$this->assertTrue($it->next());
		$this->assertIsA($second = $it->currentItem(), 'Media');
		$this->assertEqual('name2', $second->name);
		$this->assertFalse($it->isdone());
		$this->assertTrue($it->next());
		$this->assertIsA($third = $it->currentItem(), 'Media');
		$this->assertEqual('name3', $third->name);
		$this->assertFalse($it->next());
		$this->assertTrue($it->isdone());
	}
	function TestGofIteratorUsage() {
		$output = '';
		for ($it = $this->lib->getIterator(); !$it->isDone(); $it->next()) {
			$output .= $it->currentItem()->name;
		}
		$this->assertEqual('name1name2name3', $output);
	}
	function TestGofExternalIteratorUsage() {
		$output = '';
		for ($it = $this->lib->getIterator('keyed'); !$it->isDone(); $it->next()) {
			$output .= $it->currentItem()->name;
		}
		$this->assertEqual('name1name2name3', $output);
	}
	function TestMediaIteratorUsage() {
		$this->assertIsA($it = $this->lib->getIterator('media'), 'LibraryIterator');
		$output = '';
		while ($item = $it->next()) {
			$output .= $item->name;
		}
		$this->assertEqual('name1name2name3', $output);
	}
	function TestMediaExternalIteratorUsage() {
		$this->assertIsA($it = $this->lib->getIterator('external'), 'LibraryExternalIterator');
		$output = '';
		while ($item = $it->next()) {
			$output .= $item->name;
		}
		$this->assertEqual('name1name2name3', $output);
	}
	function TestAvailableIteratorUsage() {
		$this->lib->add($dvd = new Media('test', 1999));
		$this->lib->add(new Media('name4', 1999));
		$this->assertIsA($it = $this->lib->getIterator('available'), 'LibraryAvailableIterator');
		$output = '';
		while ($item = $it->next()) {
			$output .= $item->name;
		}
		$this->assertEqual('name1name2name3testname4', $output);
		
		$dvd->checkOut('Jason');
		$it = $this->lib->getIterator('available');
		$output = '';
		while ($item = $it->next()) {
			$output .= $item->name;
		}
		$this->assertEqual('name1name2name3name4', $output);
	}
	function TestReleasedIteratorUsage() {
		$this->lib->add(new Media('second', 1999));
		$this->lib->add(new Media('first', 1989));
		$this->assertIsA($it = $this->lib->getIterator('released'), 'LibraryReleasedIterator');
		$output = array();
		while ($item = $it->next()) {
			$output[] = $item->name .'-'. $item->year;
		}
		$this->assertEqual(
			'first-1989 second-1999 name1-2000 name3-2001 name2-2002'
			, implode(' ',$output));
	}
	function TestReleasedExternalIteratorUsage() {
		$this->lib->add(new Media('second', 1999));
		$this->lib->add(new Media('first', 1989));
		$this->assertIsA($it = $this->lib->getIterator('releaseexternal'), 'LibraryReleasedExternalIterator');
		$output = array();
		while ($item = $it->next()) {
			$output[] = $item->name .'-'. $item->year;
		}
		$this->assertEqual(
			'first-1989 second-1999 name1-2000 name3-2001 name2-2002'
			, implode(' ',$output));
	}
	function TestReleasedExternalIteratorReset() {
		$this->lib->add(new Media('second', 1999));
		$this->lib->add(new Media('first', 1989));
		$this->assertIsA($it = $this->lib->getIterator('releaseexternal'), 'LibraryReleasedExternalIterator');
		$output = array();
		while ($item = $it->next()) {
			$output[] = $item->name .'-'. $item->year;
		}
		$this->assertEqual(
			'first-1989 second-1999 name1-2000 name3-2001 name2-2002'
			, implode(' ',$output));
		//use reset and test again
		$it->reset();
		$output = array();
		while ($item = $it->next()) {
			$output[] = $item->name .'-'. $item->year;
		}
		$this->assertEqual(
			'first-1989 second-1999 name1-2000 name3-2001 name2-2002'
			, implode(' ',$output));
	}
}

class ForeachableLibrary extends Library implements Iterator {
	protected $valid;
	function current() {
		return current($this->collection);
	}
	function next() {
		$this->valid = (false !== next($this->collection)); 
	}
	function key() {
		return key($this->collection);
	}
	function valid() {
		return $this->valid;
	}
	function rewind() {
		$this->valid = (false !== reset($this->collection)); 
	}
}

class PolymorphicForeachableLibrary extends Library implements Iterator {
	protected $iterator_type;
	protected $iterator;
	function __construct() {
		$this->iteratorType();
	}
	function iteratorType($type=false) {
		switch(strtolower($type)) {
		case 'released':
			$this->iterator_type = 'ReleasedLibraryIterator';
			break;
		default:
			$this->iterator_type = 'StandardLibraryIterator';
		}
		$this->rewind();
	}
	function current() {
		return $this->iterator->current();
	}
	function next() {
		return $this->iterator->next();
	}
	function key() {
		return $this->iterator->key();
	}
	function valid() {
		return $this->iterator->valid();
	}
	function rewind() {
		$type = $this->iterator_type;
		$this->iterator = new $type($this->collection);
		$this->iterator->rewind();
	}	
}

class StandardLibraryIterator {
	protected $valid;
	protected $collection;
	function __construct($collection) {
		$this->collection = $collection;
	}
	function current() {
		return current($this->collection);
	}
	function next() {
		$this->valid = (false !== next($this->collection)); 
	}
	function key() {
		return key($this->collection);
	}
	function valid() {
		return $this->valid;
	}
	function rewind() {
		$this->valid = (false !== reset($this->collection)); 
	}
}

class ReleasedLibraryIterator extends StandardLibraryIterator {
	function __construct($collection) {
		usort($collection
			,create_function('$a,$b','return ($a->year - $b->year);'));
		$this->collection = $collection;
	}
}

class SplIteratorTestCase extends UnitTestCase {
	protected $lib;
	function setup() {
		$this->lib = new ForeachableLibrary;
		$this->lib->add(new Media('name1', 2000));
		$this->lib->add(new Media('name2', 2002));
		$this->lib->add(new Media('name3', 2001));
	}
	function TestForeach() {
		$output = '';
		foreach($this->lib as $item) {
			$output .= $item->name;
		}
		$this->assertEqual('name1name2name3', $output);
	}
}

class PolySplIteratorTestCase extends UnitTestCase {
	protected $lib;
	function setup() {
		$this->lib = new PolymorphicForeachableLibrary;
		$this->lib->add(new Media('name1', 2000));
		$this->lib->add(new Media('name2', 2002));
		$this->lib->add(new Media('name3', 2001));
	}
	function TestForeach() {
		$output = '';
		foreach($this->lib as $item) {
			$output .= $item->name;
		}
		$this->assertEqual('name1name2name3', $output);
	}
	function TestReleasedForeach() {
		$this->lib->add(new Media('second', 1999));
		$this->lib->add(new Media('first', 1989));
		$output = array();
		$this->lib->iteratorType('Released');
		foreach($this->lib as $item) {
			$output[] = $item->name .'-'. $item->year;
		}
		$this->assertEqual(
			'first-1989 second-1999 name1-2000 name3-2001 name2-2002'
			, implode(' ',$output));
	}
}

$test = new GroupTest('Iterator PHP5 Unit Test');
$test->addTestCase(new PhpArrayIteratorTestCase);
$test->addTestCase(new LoanableTestCase);
$test->addTestCase(new MediaTestCase);
$test->addTestCase(new LibraryTestCase);
$test->addTestCase(new IteratorTestCase);
$test->addTestCase(new SplIteratorTestCase);
$test->addTestCase(new PolySplIteratorTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

