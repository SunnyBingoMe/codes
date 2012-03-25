<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once 'adodb/adodb.inc.php';

class DB {
	//static class, we do not need a constructor
	private function __construct() {}
	
	public static function conn() {
		static $conn;
		if (!$conn) {
			$conn = adoNewConnection('mysql');
			$conn->connect('localhost', 'username', 'passwd', 'database');
			$conn->setFetchMode(ADODB_FETCH_ASSOC);
		}
		return $conn;
	}
}

class Bookmark {
	const NEW_BOOKMARK = -1;
	protected $id = Bookmark::NEW_BOOKMARK;
	protected $conn;
	public $url;
	public $name;
	public $description;
	public $tag;
	public $created;
	public $updated;

	const SELECT_BY_ID = 'select * from bookmark where id = ?';
	public function __construct($id=false, $conn=false) {
		$this->conn = ($conn) ? $conn : DB::conn();
		
		if ($id) {
			$rs = $this->conn->execute(
				self::SELECT_BY_ID
				,array((int)$id));

			if ($rs) {	
				$row = $rs->fetchRow();
				foreach($row as $field => $value) {
					$this->$field = $value;
				}
			} else {
				trigger_error('DB Error: '.$this->conn->errorMsg());
			}
		}
	}
	
	public function getId() {
		return $this->id;
	}

	const SELECT_COUNT_OF_ID_SQL = "
		select count(1) as cnt
		from bookmark
		where id = ?
		";
	public static function findById($id) {
		$count = DB::conn()->getOne(
			trim(self::SELECT_COUNT_OF_ID_SQL),
			array((int)$id)
			);
		if (1 == $count) {
			return new Bookmark($id);
		} else {
			return false;
		}
	}
	
	const SELECT_BY_URL = "
		select id
		from bookmark
		where url like ?";
	public static function findByUrl($url) {
		$rs = DB::conn()->execute(
			self::SELECT_BY_URL
			,array("%$url%"));
		$ret = array();
		if ($rs) {
			foreach ($rs->getArray() as $row) {
				$ret[] = new Bookmark($row['id']);
			}
		}
		return $ret;
	}
	
	public function save() {
		if ($this->id == Bookmark::NEW_BOOKMARK) {
			$this->insert();
		} else {
			$this->update();
		}
		$this->setTimeStamps();
	}
	const INSERT_SQL = "
		insert into bookmark (url, name, description, tag, created, updated)
		values (?, ?, ?, ?, now(), now())
		";
	protected function insert() {
		$rs = $this->conn->execute(
			self::INSERT_SQL
			,array($this->url, $this->name, $this->description, $this->tag));
		if ($rs) {
			$this->id = (int)$this->conn->Insert_ID();
		} else {
			trigger_error('DB Error: '.$this->conn->errorMsg());
		}
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
	protected function update() {
			$this->conn->execute(
				self::UPDATE_SQL
				,array(
					$this->url,
					$this->name,
					$this->description,
					$this->tag,
					$this->id));
	}
	protected function setTimeStamps() {
		$rs = $this->conn->execute(
			self::SELECT_BY_ID
			,array($this->id));
		if ($rs) {
			$row = $rs->fetchRow();
			$this->created = $row['created'];
			$this->updated = $row['updated'];
		}
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

class ActiveRecordTestCase extends UnitTestCase {
	protected $conn;
	function __construct($name='') {
		$this->UnitTestCase($name);
		$this->conn = DB::conn();
	}
	
	// no test interference
	function setup() {
		$this->conn->execute('drop table bookmark');
		$this->conn->execute(BOOKMARK_TABLE_DDL);
	}
	
	function testSetupLeavesTableEmptyWithCorrectStructure() {
		$rs = $this->conn->execute('select * from bookmark');
		$this->assertIsA($rs, 'ADORecordSet');
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
	function testNew() {
		$link = new Bookmark;
		
		$link->url = 'http://simpletest.org/';
		$link->name = 'SimpleTest';
		$link->description = 'SimpleTest project homepage';
		$link->tag = 'testing';
		
		$link->save();
		$this->assertEqual(1, $link->getId());
		
		// fetch the table as an array of hashes
		$rs = $this->conn->getAll('select * from bookmark');
		$this->assertEqual(1, count($rs), 'returned 1 row');
		foreach(array('url', 'name', 'description', 'tag') as $key) {
			$this->assertEqual($link->$key, $rs[0][$key]);
		}
	}
	function testAdd() {
		$this->add('http://php.net', 'PHP', 'PHP Language Homepage', 'php');
		$this->add('http://phparch.com', 'php|architect', 'php|arch site', 'php');
		$rs = $this->conn->execute('select * from bookmark');
		$this->assertEqual(2,$rs->recordCount());
		$this->assertEqual(2,$this->conn->Insert_ID());
	}
	
	function testDbFailure() {
		$conn = new MockADOConnection($this);
		$conn->expectOnce('execute', array('*','*'));
		$conn->setReturnValue('execute',false);
		$conn->expectOnce('errorMsg');
		$conn->setReturnValue('errorMsg', 'The database has exploded!!!!');
		
		$link = new Bookmark(1,$conn);
		$this->assertErrorPattern('/exploded/i');
		
		$conn->tally();
	}

	function testAddReturnsBookmark() {
		$link = $this->add(
			'http://blog.casey-sweat.us/',
			'My Blog',
			'Where I write about stuff',
			'php');
		$this->assertIsA($link, 'Bookmark');
		$this->assertWantedPattern('/sweat/i', $link->url);
		$this->assertEqual('My Blog', $link->name);
		$this->assertWantedPattern('/write/i', $link->description);
		$this->assertTrue((time()-strtotime($link->created))<2);
		$this->assertEqual($link->created, $link->updated);
	}

	function testGetId() {
		$this->add('http://php.net', 'PHP', 
			'PHP Language Homepage', 'php');
		// second bookmark, id=2
		$link = $this->add('http://phparch.com', 
			'php|architect', 'php|arch site', 'php');
		
		$this->assertEqual(2, $link->getId());
		
		$alt_test = $this->conn->getOne(
			"select id from bookmark where url = 'http://phparch.com'");
		$this->assertEqual(2, $alt_test);
		//alternatively
		$this->assertEqual($link->getId(), $alt_test);
	}
	
	function testCreateById() {
		$link = $this->add(
			'http://blog.casey-sweat.us/',
			'My Blog',
			'Where I write about stuff',
			'php');
		$this->assertEqual(1, $link->getId());
		
		$link2 = new Bookmark(1);
		$this->assertIsA($link2, 'Bookmark');
		$this->assertEqual($link, $link2);
	}

	function testFindByUrl() {
		$this->add('http://blog.casey-sweat.us/', 'My Blog',
			'Where I write about stuff', 'php');
		$this->add('http://php.net', 'PHP', 
			'PHP Language Homepage', 'php');
		$this->add('http://phparch.com', 'php|architect', 
			'php|arch site', 'php');
			
		$result = Bookmark::findByUrl('php');
		
		$this->assertIsA($result, 'array');
		$this->assertEqual(2, count($result));
		$this->assertEqual(2, $result[0]->getId());
		$this->assertEqual('php|architect', $result[1]->name);
	}

	function testSave() {
		$link = $this->add(
			'http://blog.casey-sweat.us/',
			'My Blog',
			'Where I write about stuff',
			'php');
		sleep(2);
		$link->description = 'Where I write about PHP, Linux and other stuff';
		$link->save();
		
		$link2 = Bookmark::findById($link->getId());
		$this->assertEqual($link->getId(), $link2->getId());
		$this->assertNotEqual($link2->created, $link2->updated);
		$this->assertTrue(strtotime($link2->updated)>strtotime($link2->created));
		$this->assertEqual($link->updated, $link2->updated);
	}
	
	function add($url, $name, $description, $tag) {
		$link = new Bookmark;
		
		$link->url = $url;
		$link->name = $name;
		$link->description = $description;
		$link->tag = $tag;
		
		$link->save();
		return $link;
	}

}

$test = new GroupTest('ActiveRecord PHP5 Unit Test');
$test->addTestCase(new ActiveRecordTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());
