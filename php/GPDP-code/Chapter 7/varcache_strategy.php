<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';

class CacheWriter {
	function store($file_handle, $var) {
		die('abstract class-implement in concrete CacheWriter');
	}
}

class StringCacheWriter /* implements CacheWriter */ {
	function store($file_handle, $string) {
		$content = sprintf(	
			"<?php\n\$cached_content = '%s';"
			,str_replace("'","\\'",$string));
		fwrite($file_handle, $content);
	}
}

class NumericCacheWriter /* implements CacheWriter */ {
	function store($file_handle, $numeric) {
		$content = sprintf("<?php\n\$cached_content = %s;"
			,(double)$numeric);
		fwrite($file_handle, $content);
	}
}

class SerializingCacheWriter /* implements CacheWriter */ {
	function store($file_handle, $var) {
		$content = sprintf(
			"<?php\n\$cached_content = unserialize(stripslashes('%s'));"
			,addslashes(serialize($var)));
		fwrite($file_handle, $content);
	}
}

// Congratulations for reading this far,
// you have uncovered the bonus strategy not covered in the chapter!
// Enjoy :)
class CompressingCacheWriter /* implements CacheWriter */ {
	function store($file_handle, $var) {
		$content = sprintf(
			"<?php\n\$cached_content = unserialize(gzuncompress(base64_decode('%s')));"
			,base64_encode(gzcompress(serialize($var))));
		fwrite($file_handle, $content);
	}
}

class VarCache {
	var $_name;
	var $_type;
	function VarCache($name, $type='serialize') {
		$this->_name = 'cache/'.$name;
		switch (strtolower($type)) {
		case 'string':
			$strategy = 'String';
			break;
		case 'numeric':
			$strategy = 'Numeric';
			break;
		case 'zip':
			$strategy = 'Compressing';
			break;
		case 'serialize':
		default:
			$strategy = 'Serializing';
		}
		$strategy .= 'CacheWriter';
		$this->_type =& new $strategy;
	}
	function isValid() {
		return file_exists($this->_name.'.php');
	}
	function get() {
		if ($this->isValid()) {
			include $this->_name.'.php';
			return $cached_content;
		}
	}
	function set($value) {
		$file_handle = fopen($this->_name.'.php', 'w');
		$this->_type->store($file_handle, $value);
		fclose($file_handle);
	}
}

class VarCacheTestCase extends UnitTestCase {
	function setup() {
		@unlink('cache/foo.php');
		@unlink('cache/bar.php');
	}
	function TestUnsetValueIsInvalid() {
		$cache =& new VarCache('foo');
		$this->assertFalse($cache->isValid());
	}
	function TestIsValidTrueAfterSet() {
		$cache =& new VarCache('foo');
		$cache->set('bar');
		$this->assertTrue($cache->isValid());
	}
	function TestCacheRetainsValue() {
		$test_val = 'test'.rand(1,100);
		$cache =& new VarCache('foo');
		$cache->set($test_val);
		$this->assertEqual($test_val, $cache->get());
	}
	function TestStringCacheWorks() {
		$test_val = 'test with \' and " in it'.rand(1,100);
		$cache =& new VarCache('foo', 'string');
		$cache->set($test_val);
		$this->assertEqual($test_val, $cache->get());
	}
	function TestStringFailsForArray() {
		$test_val = array('one','two');
		$cache =& new VarCache('foo', 'string');
		$cache->set($test_val);
		$this->assertError('Array to string conversion');
		$this->assertNotEqual($test_val, $cache->get());
		$this->assertEqual('array',strtolower($cache->get()));
	}
	function TestStringFailsForObject() {
		$cache =& new VarCache('foo', 'string');
		$cache->set($cache);
		$this->assertError('Object to string conversion');
		$this->assertNotEqual($cache, $cache->get());
		$this->assertEqual('object',strtolower($cache->get()));
	}
	function TestNumericCacheWorks() {
		$test_val = rand(1,100) / rand(2,30);
		$cache =& new VarCache('foo', 'numeric');
		$cache->set($test_val);
		$this->assertEqual(
			round($test_val,10), 
			round($cache->get(),10));
	}
	function TestSerializeCacheWorks() {
		$test_val = array_fill(0,9,'Lorem ipsum dolor sit amet.');
		$cache =& new VarCache('foo', 'serialize');
		$cache->set($test_val);
		$this->assertEqual($test_val, $cache->get());
	}
	function TestZipCacheWorks() {
		$test_val = array_fill(0,9,'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Etiam eleifend venenatis justo. Donec vestibulum enim vel lorem. Ut placerat. Donec ultrices varius dolor. Suspendisse cursus tempor urna. Pellentesque dui libero, gravida gravida, gravida ac, ullamcorper et, nulla. Pellentesque elit. Pellentesque auctor elementum mi. Aenean at lacus. Maecenas rutrum bibendum risus. Sed tincidunt laoreet ante. Sed id diam. Quisque tempus wisi hendrerit quam. Phasellus molestie. Etiam faucibus tortor eu tellus. Duis diam massa, varius non, auctor nec, commodo vehicula, lorem. Aenean bibendum convallis orci. Ut ac mi at elit molestie scelerisque.');
		$cache =& new VarCache('foo', 'zip');
		$cache->set($test_val);
		$this->assertEqual($test_val, $cache->get());
		$cache2 =& new VarCache('bar', 'serialize');
		$cache2->set($test_val);
		$this->assertTrue(filesize('cache/foo.php') < filesize('cache/bar.php'));
	}
}

$test = new GroupTest('Strategy PHP4 Unit Test');
$test->addTestCase(new VarCacheTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

