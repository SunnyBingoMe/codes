<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';

// please note the implicit assumption you have a subdirectory "cache"
// which is writable for the cache files.
class VarCache {
	var $_name;
	var $_type;
	function VarCache($name, $type='serialize') {
		$this->_name = 'cache/'.$name;
		$this->_type = $type;
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
	function _getTemplate() {
		$template = '<?php $cached_content = ';
		switch ($this->_type) {
		case 'string': 
			$template .= "'%s';";
			break;
		case 'serialize':
			$template .= "unserialize(stripslashes('%s'));";
			break;
		case 'zip':
			$template .= "unserialize(gzuncompress(base64_decode('%s')));";
			break;
		case 'numeric':
			$template .= '%s;';
			break;
		default:
			trigger_error('invalid cache type');
		}
		return $template;
	}
	function set($value) {
		$file_handle = fopen($this->_name.'.php', 'w');
		switch ($this->_type) {
		case 'string': 
			$content = sprintf($this->_getTemplate()
				,str_replace("'","\\'",$value));
			break;
		case 'serialize':
			$content = sprintf($this->_getTemplate()
				,addslashes(serialize($value)));
			break;
		case 'zip':
			$content = sprintf($this->_getTemplate()
				,base64_encode(gzcompress(serialize($value))));
			break;
		case 'numeric':
			$content = sprintf($this->_getTemplate()
				,(float)$value);
			break;
		default:
			trigger_error('invalid cache type');
		}
		fwrite($file_handle, $content);
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

$test = new GroupTest('Strategy PHP4 Baseline Unit Test');
$test->addTestCase(new VarCacheTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

