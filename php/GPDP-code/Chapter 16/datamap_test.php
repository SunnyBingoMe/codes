<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once 'adodb/adodb.inc.php';

// same as prior two chapters
class DB {
	//static class, we do not need a constructor
	private function __construct() {}
	
	public static function conn() {
		static $conn;
		if (!$conn) {
			$conn = adoNewConnection('mysql');
			$conn->connect('localhost', 'user', 'passwd', 'database');
			$conn->setFetchMode(ADODB_FETCH_ASSOC);
		}
		return $conn;
	}
}

class BookmarkMapper {
	protected $map = array();
	protected $conn;
	public function __construct($conn) {
		$this->conn = $conn;

		foreach(simplexml_load_file('bookmark.xml') as $field) {
			$this->map[(string)$field->name] = $field;
		}
	}
	
	const INSERT_SQL = "
		insert into bookmark (url, name, description, tag, created, updated)
		values (?, ?, ?, ?, now(), now())
		";
	protected function insert($bookmark) {
		$rs = $this->conn->execute(
			self::INSERT_SQL
			,array(
				 $bookmark->getUrl()
				,$bookmark->getName()
				,$bookmark->getDesc()
				,$bookmark->getGroup()));
		if ($rs) {
			$inserted = $this->findById($this->conn->Insert_ID());
			// clean up database related fields in parameter bookmark instance
			if (method_exists($inserted,'setId')) {
				$bookmark->setId($inserted->getId());
				$bookmark->setCrtTime($inserted->getCrtTime());
				$bookmark->setModTime($inserted->getModTime());
			}
		} else {
			throw new Exception('DB Error: '.$this->conn->errorMsg());
		}
	}
	public function add($url, $name, $description, $group) {
		$bookmark = new Bookmark;
		$bookmark->setUrl($url);
		$bookmark->setName($name);
		$bookmark->setDesc($description);
		$bookmark->setGroup($group);
		
		$this->insert($bookmark);
		return $bookmark;
	}
	
	const UPDATE_SQL = "
		update bookmark set
			url = ?,
			name = ?,
			description = ?,
			tag = ?,
			updated = now()
		where id = ?
		";
	protected function update($bookmark) {
		$binds = array();
		foreach(array('url','name','description','tag','id') as $fieldname) {
			$field = $this->map[$fieldname];
			$getprop = (string)$field->accessor;
			$binds[] = $bookmark->$getprop();
		}
	
		$this->conn->execute(
			self::UPDATE_SQL
			,$binds);
	}

	public function save($bookmark) {
		if ($bookmark->getId()) {
			$this->update($bookmark);
		} else {
			$this->insert($bookmark);
		}
	}

	public function findById($id) {
		$row = $this->conn->getRow(
			'select * from bookmark where id = ?'
			,array((int)$id)
			);
		if ($row) {
			return $this->createBookmarkFromRow($row);
		} else {
			return false;
		}
	}
	protected function createBookmarkFromRow($row) {
		$bookmark = new Bookmark;
		foreach($this->map as $field) {
			$setprop = (string)$field->mutator;
			$value = $row[(string)$field->name];
			if ($setprop && $value) {
				call_user_func(array($bookmark, $setprop), $value);
			}
		}
		return $bookmark;
	}
	public function findByGroup($group) {
		$rs = $this->conn->execute(
			'select * from bookmark where tag like ?'
			,array($group.'%'));
		if ($rs) {
			$ret = array();
			foreach($rs->getArray() as $row) {
				$ret[] = $this->createBookmarkFromRow($row);
			}
			return $ret;
		}
	}
	
	public function delete($bookmark) {
		$this->conn->execute(
			'delete from bookmark where id = ?'
			,array((int)$bookmark->getId()));
	}
}


class Bookmark {
	protected $id;
	protected $url;
	protected $name;
	protected $desc;
	protected $group;
	protected $crttime;
	protected $modtime;

	public function setId($id) {	
		if (!$this->id) {
			$this->id = $id;
		}
	}
	
	public function getUrl() {
		return $this->url;
	}
	public function setUrl($url) {
		$this->url = $url;
	}
	
	public function __call($name, $args) {
		if (preg_match('/^(get|set)(\w+)/', strtolower($name), $match)
			&& $attribute = $this->validateAttribute($match[2])) {
			if ('get' == $match[1]) {
				return $this->$attribute;
			} else {
				$this->$attribute = $args[0];
			}
		} else {
			throw new Exception('Call to undefined method Bookmark::'.$name.'()');
		}
	}
	protected function validateAttribute($name) {
		if (in_array(strtolower($name),
			array_keys(get_class_vars(get_class($this))))) {
			return strtolower($name);
		}
	}
	
	public function fetch() {
		return file_get_contents($this->url);
	}
}

define('BOOKMARK_TABLE_DDL', <<<EOS
CREATE TABLE `bookmark` (
	`id` INT NOT NULL AUTO_INCREMENT ,
	`url` VARCHAR( 255 ) NOT NULL ,
	`name` VARCHAR( 255 ) NOT NULL ,
	`description` MEDIUMTEXT,
	`tag` VARCHAR( 50 ) ,
	`created` DATETIME NOT NULL ,
	`updated` DATETIME NOT NULL ,
PRIMARY KEY ( `id` )
)
EOS
);

Mock::generate('ADOConnection');

class BaseTestCase extends UnitTestCase {
	protected $conn;
	function __construct($name='') {
		$this->UnitTestCase($name);
		$this->conn = DB::conn();
	}

	function setup() {
		$this->conn->execute('drop table bookmark');
		$this->conn->execute(BOOKMARK_TABLE_DDL);
	}
	
	function addSeveralBookmarks($gateway) {
		$gateway->add('http://blog.casey-sweat.us/' //url
			,'Jason\'s Blog' //name
			,'PHP related thoughts' //desc
			,'php'); //tag
		$gateway->add('http://www.php.net/'
			,'PHP homepage'
			,'The main page for PHP'
			,'php');
		$gateway->add('http://slashdot.org/'
			,'/.'
			,'News for Nerds'
			,'new');
		$gateway->add('http://google.com/'
			,'Google'
			,'Google Search Engine'
			,'web');
		$gateway->add('http://www.phparch.com/'
			,'php|architect'
			,'The home page of php|architech, and outstanding PHP monthly publication'
			,'php');
	}
}


class BookmarkMapperTestCase extends BaseTestCase {

	function testSave() {
		$bookmark = new Bookmark;
		
		$bookmark->setUrl('http://phparch.com/');
		$bookmark->setName('php|architect');
		$bookmark->setDesc('php|arch magazine homepage');
		$bookmark->setGroup('php');
		
		$this->assertNull($bookmark->getId());
		
		$mapper = new BookmarkMapper($this->conn);
		$mapper->save($bookmark);
		
		$this->assertEqual(1, $bookmark->getId());
		
		// a row was added to the database table
		$this->assertEqual(1, $this->conn->getOne(
			'select count(1) from bookmark'));
	}

	
	function testAdd() {
		$mapper = new BookmarkMapper($this->conn);
		$bookmark =
			$mapper->add('oneUrl', 'oneName', 'oneDesc', 'oneGroup');
		
		$this->assertEqual(1,
			$this->conn->getOne('select count(1) from bookmark'));
		
		$this->assertEqual('oneUrl', $bookmark->getUrl());
		$this->assertEqual('oneName', $bookmark->getName());
		$this->assertEqual('oneDesc', $bookmark->getDesc());
		$this->assertEqual('oneGroup', $bookmark->getGroup());
	}


	
	function XtestSimpleXml() {
		$x = simplexml_load_file('bookmark.xml');
		var_dump($f = $x->field[0]);
		var_dump((string)$f->attributes());
		var_dump(get_class_methods($f));
		/*
		object(SimpleXMLElement)#21 (1) {
		  ["field"]=>
		  array(7) {
			[0]=>
			object(SimpleXMLElement)#15 (3) {
			  ["name"]=>
			  string(2) "id"
			  ["accessor"]=>
			  string(5) "getId"
			  ["mutator"]=>
			  string(5) "setId"
			}
			[1]=>
			object(SimpleXMLElement)#19 (3) {
			  ["name"]=>
			  string(3) "url"
			  ["accessor"]=>
			  string(6) "getUrl"
			  ["mutator"]=>
			  string(6) "setUrl"
			}
			[2]=>
			object(SimpleXMLElement)#12 (3) {
			  ["name"]=>
			  string(4) "name"
			  ["accessor"]=>
			  string(7) "getName"
			  ["mutator"]=>
			  string(7) "setName"
			}
			[3]=>
			object(SimpleXMLElement)#11 (3) {
			  ["name"]=>
			  string(11) "description"
			  ["accessor"]=>
			  string(7) "getDesc"
			  ["mutator"]=>
			  string(7) "setDesc"
			}
			[4]=>
			object(SimpleXMLElement)#23 (3) {
			  ["name"]=>
			  string(3) "tag"
			  ["accessor"]=>
			  string(8) "getGroup"
			  ["mutator"]=>
			  string(8) "setGroup"
			}
			[5]=>
			object(SimpleXMLElement)#14 (2) {
			  ["name"]=>
			  string(7) "created"
			  ["mutator"]=>
			  string(10) "setCrtTime"
			}
			[6]=>
			object(SimpleXMLElement)#13 (2) {
			  ["name"]=>
			  string(7) "updated"
			  ["mutator"]=>
			  string(10) "setModTime"
			}
		  }
		}
		
		*/
		foreach($x as $b) {
			//var_dump($b);
		}
	}
	
	function testSetup() {
		$rs = $this->conn->execute('select * from bookmark');
		$this->assertEqual(0,$rs->recordCount());
		foreach(array(
			'id',
			'url',
			'name',
			'description',
			'tag',
			'created',
			'updated') as $i => $name) {
			$this->assertEqual($name, $rs->fetchField($i)->name);
		}
	}
	
	/*
class BookmarkMapperTestCase extends BaseTestCase {
	// ...
	
	*/
	
	function testFindByGroup() {
		$mapper = new BookmarkMapper($this->conn);
		$this->addSeveralBookmarks($mapper);
		
		$this->assertIsA(
			$php_links = $mapper->findByGroup('php')
			,'array');
		$this->assertEqual(3, count($php_links));
		foreach($php_links as $link) {
			$this->assertIsA($link, 'Bookmark');
		}
	}
	
	function testDelete() {
		$mapper = new BookmarkMapper($this->conn);
		$this->addSeveralBookmarks($mapper);

		$this->assertEqual(5, $this->countBookmarks());
		$delete_me = $mapper->findById(3);
		$mapper->delete($delete_me);
		$this->assertEqual(4, $this->countBookmarks());
		
	}
	
	function countBookmarks() {
		return $this->conn->getOne(
			'select count(1) from bookmark');
	}
}

Mock::generate('Bookmark');

class BookmarkTestCase extends BaseTestCase {
	function testUnsetIdIsNull() {
		$bookmark = new Bookmark;
		$this->assertNull($bookmark->getId());
	}
	function testIdOnlySetOnce() {
		$bookmark = new Bookmark;
		
		$id = 10; //just a random value we picked
		$bookmark->setId($id);
		$this->assertEqual($id, $bookmark->getId());
		
		$another_id = 20; // another random value, != $id
		//state the obvious
		$this->assertNotEqual($id, $another_id);
		
		$bookmark->setId($another_id);
		// still the old id
		$this->assertEqual($id, $bookmark->getId());
	}
	
	function testUrl() {
		$bookmark = new Bookmark(false);
		
		$this->assertNull($bookmark->getUrl());
		$url = 'http://www.phparch.com/';
		
		$bookmark->setUrl($url);
		$this->assertEqual($url
			,$bookmark->getUrl());
			
		$url2 = 'http://www.php.net/manual/en/';
		$bookmark->setUrl($url2);
		$this->assertNotEqual($url
			,$bookmark->getUrl());
		$this->assertEqual($url2
			,$bookmark->getUrl());
	}
	
	function testAccessorsAndMutators() {
		$bookmark = new Bookmark(false);
		
		$props = array('Url', 'Name', 'Desc', 'Group', 'CrtTime', 'ModTime');
		foreach($props as $prop) {
			$getprop = "get$prop";
			$setprop = "set$prop";
			$this->assertNull($bookmark->$getprop());
			
			$val1 = 'some_val'.rand(1,100);
			$bookmark->$setprop($val1);
			$this->assertEqual($val1,
				$bookmark->$getprop());
			
			$val2 = 'other_val'.rand(1,100);
			$bookmark->$setprop($val2);
			$this->assertNotEqual($val1,
				$bookmark->$getprop());
			$this->assertEqual($val2,
				$bookmark->$getprop());
		}
	}
	
	function testSaveNewBookmarkUsesInsertStatement() {
		$db_bookmark = new MockBookmark($this);
		
		$db = new MockADOConnection($this);
		$db->setReturnValue('execute', $db_bookmark);
		$db->expectOnce('execute'
			,array(new WantedPatternExpectation('/\binsert.*bookmark.*values\b/ims')
			,'*'));
		$mapper = new BookmarkMapper($db);
		$bookmark = new Bookmark;
		
		$mapper->save($bookmark);
		
		$db->tally();
	}
	function testSaveExistingBookmarkUsesUpdateStatement() {		
		$db = new MockADOConnection($this);
		$db->expectOnce('execute'
			,array(new WantedPatternExpectation('/\bupdate\s+bookmark\s+set\b/ims')
			,'*'));
		$mapper = new BookmarkMapper($db);
		$bookmark = new Bookmark;
		$bookmark->setId(1);
		
		$mapper->save($bookmark);
		
		$db->tally();
	}
			
	function testSaveUpdatesDatabase() {
		$mapper = new BookmarkMapper($this->conn);
		$this->addSeveralBookmarks($mapper);
		$bookmark = $mapper->findById(1);
		
		$this->assertEqual(
			'http://blog.casey-sweat.us/'
			,$bookmark->getUrl());
		
		$bookmark->setUrl(
			'http://blog.casey-sweat.us/wp-rss2.php');
		$mapper->save($bookmark);

		$bookmark2 = $mapper->findById(1);
		$this->assertNotEqual(
			'http://blog.casey-sweat.us/'
			,$bookmark2->getUrl());
		$this->assertEqual(
			'http://blog.casey-sweat.us/wp-rss2.php'
			,$bookmark2->getUrl());
	}
	
	function testBadGetSetExceptions() {
		$bookmark = new Bookmark;
		
		try {
			$this->assertNull($bookmark->getFoo());
			$this->fail('no exception thrown');
		}
		catch (Exception $e) {
			$this->assertWantedPattern('/undefined.*getfoo/i', $e->getMessage());
		}
		
		try {
			$this->assertNull($bookmark->setFoo('bar'));
			$this->fail('no exception thrown');
		}
		catch (Exception $e) {
			$this->assertWantedPattern('/undefined.*setfoo/i', $e->getMessage());
		}
	}
	
	function testFetch() {
		$bookmark = new Bookmark;
		$bookmark->setUrl('http://www.google.com/');
		
		$page = $bookmark->fetch();
		$this->assertWantedPattern('~<input[^>]*name=q[^>]*>~im', $page);
	}
}

$test = new GroupTest('DataMapper PHP5 Unit Test');
$test->addTestCase(new BookmarkTestCase);
$test->addTestCase(new BookmarkMapperTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());
