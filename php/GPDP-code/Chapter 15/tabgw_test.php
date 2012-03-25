<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once 'adodb/adodb.inc.php';
require_once 'adodb/drivers/adodb-mysql.inc.php';

//this code the same as chapter 14
class DB {
	//static class, we do not need a constructor
	private function __construct() {}
	
	public static function conn() {
		static $conn;
		if (!$conn) {
			$conn = adoNewConnection('mysql');
			$conn->connect('localhost', 'phpauser', 'phpapass', 'phpa');
			$conn->setFetchMode(ADODB_FETCH_ASSOC);
		}
		return $conn;
	}
}

class BookmarkGateway {
	protected $conn;
	public function __construct($conn) {
		$this->conn = $conn;
	}

	const INSERT_SQL = "
		insert into bookmark (url, name, description, tag, created, updated)
		values (?, ?, ?, ?, now(), now())
		";
	public function add($url, $name, $description, $group) {
		$rs = $this->conn->execute(
			self::INSERT_SQL
			,array($url, $name, $description, $group));
		if (!$rs) {
			trigger_error('DB Error: '.$this->conn->errorMsg());
		}
	}
	
	public function findAll() {
		$rs = $this->conn->execute(
			'select * from bookmark');
		if ($rs) {
			return $rs->getArray();
		} else {
			trigger_error('DB Error: '.$this->conn->errorMsg());
		}
	}
	
	public function findByTag($tag) {
		$rs = $this->conn->execute(
			'select * from bookmark
			 where tag like ?'
			,array($tag.'%'));
		return new AdoResultSetIteratorDecorator($rs);
	}
	
	const UPDATE_SQL = 'update bookmark set
			 url = ?
			,name = ?
			,description = ?
			,tag = ?
			,updated = now()
		where id = ?';
	public function update($bookmark) {
		$this->conn->execute(
			self::UPDATE_SQL
			,array(
				 $bookmark->url
				,$bookmark->name
				,$bookmark->description
				,$bookmark->tag
				,$bookmark->id
				));
	}
}

// this code the same as chapter 14
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
Mock::generate('ADORecordSet_mysql');

class DbTestCase extends UnitTestCase {
	function testBasics() {
		$this->assertTrue(class_exists('DB'));
		$this->assertIsA(DB::conn(), 'ADOConnection');
	}
}

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
		// add(url, name, desc, tag)
		$gateway->add('http://blog.casey-sweat.us/'
			,'Jason\'s Blog'
			,'PHP related thoughts'
			,'php');
		$gateway->add('http://www.php.net/'
			,'PHP homepage'
			,'The main page for PHP'
			,'php');
		$gateway->add('http://slashdot.org/'
			,'/.'
			,'News for Nerds'
			,'news');
		$gateway->add('http://google.com/'
			,'Google'
			,'Google Search Engine'
			,'web');
		$gateway->add('http://www.phparch.com/'
			,'php|architect'
			,'The home page of php|architect, an outstanding monthly PHP publication'
			,'php');
	}
}

class TableDataGatewayTestCase extends BaseTestCase {
	
	function testAdd() {
		$gateway = new BookmarkGateway($conn = DB::conn());
		$gateway->add(
			'http://simpletest.org/',
			'SimpleTest',
			'The SimpleTest homepage',
			'testing');
		$gateway->add(
			'http://blog.casey-sweat.us/',
			'My Blog',
			'Where I write about stuff',
			'php');
		
		$rs = $this->conn->execute('select * from bookmark');
		$this->assertEqual(2,$rs->recordCount());
		$this->assertEqual(2,$conn->Insert_ID());
		
	}
	
	function testManyAdds() {
		$gateway = new BookmarkGateway($conn = DB::conn());
		$this->addSeveralBookmarks($gateway);
		
		$rs = $this->conn->execute('select * from bookmark');
		$this->assertEqual(5,$rs->recordCount());
		$this->assertEqual(5,$conn->Insert_ID());
	}
	
	// return vector of rows as hashes
	function testFindAll() {
		$gateway = new BookmarkGateway(DB::conn());
		$this->addSeveralBookmarks($gateway);
		
		$result = $gateway->findAll();
		$this->assertIsA($result, 'Array');
		$this->assertEqual(5, count($result));
		$this->assertIsA($result[0], 'Array');
		$this->assertEqual(7, count($result[1]));
		
		$expected_keys = array(
			 'id'
			,'url'
			,'name'
			,'description'
			,'tag'
			,'created'
			,'updated');
		$this->assertEqual(
			$expected_keys
			,array_keys($result[3]));
			
		$this->assertEqual('PHP homepage', $result[1]['name']);
		$this->assertEqual('http://google.com/', $result[3]['url']);
	}

	// return collection of Bookmark objects as simple data objects
	function testFindByTag() {
		$gateway = new BookmarkGateway(DB::conn());
		$this->addSeveralBookmarks($gateway);
		
		$result = $gateway->findByTag('php');
		$this->assertIsA($result, 'AdoResultSetIteratorDecorator');

		$count=0;
		foreach($result as $bookmark) {
			++$count;
			$this->assertIsA($bookmark, 'ADOFetchObj');
		}
		$this->assertEqual(3, $count);
	}
	
	function testUpdate() {
		$gateway = new BookmarkGateway(DB::conn());
		$this->addSeveralBookmarks($gateway);
		
		//find one
		$result = $gateway->findByTag('php');
		$bookmark = $result->current();
		$this->assertIsA($bookmark, 'ADOFetchObj');
		$this->assertEqual(
			'http://blog.casey-sweat.us/'
			,$bookmark->url);
		$this->assertEqual(
			'PHP related thoughts' 
			,$bookmark->description);
		
		//change it
		$new_desc = 'A change to see it is updated!';
		$bookmark->description = $new_desc;
		$gateway->update($bookmark);

		//find it again and verify it has changed
		$result = $gateway->findByTag('php');
		$bookmark = $result->current();
		$this->assertEqual(
			'http://blog.casey-sweat.us/'
			,$bookmark->url);
		$this->assertEqual(
			$new_desc
			,$bookmark->description);
		
	}
	
}

require_once 'adodb/adodb-iterator.inc.php';

class AdoResultSetIteratorDecorator implements Iterator {
	protected $rs;
	public function __construct($rs) {
		$this->rs = new ADODB_Iterator($rs);
	}
	
	public function current() {
		return $this->rs->fetchObj();
	}
	
	public function next() {
		return $this->rs->next();
	}
	public function key() {
		return $this->rs->key();
	}
	public function valid() {
		return $this->rs->valid();
	}
	public function rewind() {
		return $this->rs->rewind();
	}
}

class AdoResultSetIteratorDecoratorTestCase extends BaseTestCase {
	function testADOdbDecorator() {
		$gateway = new BookmarkGateway($this->conn);
		$this->addSeveralBookmarks($gateway);
		
		$rs = $this->conn->execute('select * from bookmark');
		
		foreach($rs as $row) {
			$this->assertIsA($row, 'array');
			$this->assertIsA($rs->fetchObj(), 'ADOFetchObj');
		}
	}
	function testRsDecorator() {
		$gateway = new BookmarkGateway($this->conn);
		$this->addSeveralBookmarks($gateway);

		$rs = $this->conn->execute('select * from bookmark');
		$count=0;
		foreach(new AdoResultSetIteratorDecorator($rs) as $bookmark) {
			++$count;
			$this->assertIsA($bookmark, 'ADOFetchObj');
			$this->assertTrue($bookmark->id > 0);
			$this->assertTrue(strlen($bookmark->url) > 10);
		}
		$this->assertEqual(5,$count);
	}
}

$test = new GroupTest('TableDataGateway PHP5 Unit Test');
$test->addTestCase(new DbTestCase);
$test->addTestCase(new TableDataGatewayTestCase);
$test->addTestCase(new AdoResultSetIteratorDecoratorTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());
