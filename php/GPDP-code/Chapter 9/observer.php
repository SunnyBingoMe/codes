<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

class FileErrorLogger {
	var $_fh;
	function FileErrorLogger($file_handle) {
		$this->_fh = $file_handle;
	}
	function write($msg) {
		fwrite($this->_fh, date('Y-m-d H:i:s: ').$msg);
	}
	function update(&$error_handler) {
		$error = $error_handler->getState();
		$this->write($error['msg']);
	}
}

class FileErrorLoggerTestCase extends UnitTestCase {
	var $_fh;
	var $_test_file = 'test.log';
	function setup() {
		@unlink($this->_test_file);
		$this->_fh = fopen($this->_test_file, 'w');
	}
	function TestRequiresFileHandleToInstantiate() {
		$log =& new FileErrorLogger;
		$this->assertErrorPattern('/missing.*1/i');
	}
	function TestWrite() {
		$content = 'test'.rand(10,100);
		$log =& new FileErrorLogger($this->_fh);
		$log->write($content);
		$file_contents = file_get_contents($this->_test_file);
		$this->assertWantedPattern('/'.$content.'$/', $file_contents);
	}
	function TestWriteIsTimeStamped() {
		$content = 'test'.rand(10,100);
		$log =& new FileErrorLogger($this->_fh);
		$log->write($content);
		$time_stamp = substr(file_get_contents($this->_test_file),0,19);
		$this->assertWantedPattern(
			'/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'
			,$time_stamp);
		$this->assertTrue(abs(strtotime($time_stamp)-mktime())<1);
		
	}
}


class EmailErrorLogger {
	var $_addr;
	var $_subject;
	function EmailErrorLogger($addr, $subject='Application Error Message') {
		$this->_addr = $addr;
		$this->_subject = $subject;
	}
	function mail($msg) {
		mail($this->_addr
			,$this->_subject
			,date('Y-m-d H:i:s: ').$msg);
	}
	function update(&$error_handler) {
		$error = $error_handler->getState();
		$this->mail($error['msg']);
	}
}

class EmailErrorLoggerTestCase extends UnitTestCase {
	function TestEmailAddressFirstConstructorParameter() {
		$log =& new EmailErrorLogger;
		$this->assertErrorPattern('/missing.*1/i');
	}
	function TestMail() {
		$log =& new EmailErrorLogger('jsweat_php@yahoo.com');
		$log->mail('test message');
		//now go look at the email, not very automated, eh?
	}
}

class SyslogErrorLogger {
	function SyslogErrorLogger($msg) {
		define_syslog_variables();
		openlog($msg, LOG_ODELAY, LOG_USER);
	}
	function log($msg) {
		syslog(LOG_WARNING, $msg);
	}
	function update(&$error_handler) {
		$error = $error_handler->getState();
		$this->log($error['msg']);
	}
}

class SyslogErrorLoggerTestCase extends UnitTestCase {
	function TestLog() {
		$log =& new SyslogErrorLogger('syslog_test');
		$log->log('test message');
		//now go look at the syslog, and verify presense of error	
	}
}

class ErrorHandler {
	var $_observers=array();
	var $_error_info;
	function attach(&$observer) {
		$this->_observers[] =& $observer;
	}
	function detach(&$observer) {
		foreach(array_keys($this->_observers) as $key) {
			if ($this->_observers[$key] === $observer) {
				unset($this->_observers[$key]);
				return;
			}
		}
	}
	function notify() {
		foreach(array_keys($this->_observers) as $key) {
			$observer =& $this->_observers[$key];
			$observer->update($this);
		}
	}
	function getState() {
		return $this->_error_info;
	}
	function setState($info) {
		$this->_error_info = $info;
		$this->notify();
	}
}

//implement singleton via function
function &getErrorHandlerInstance() {
	static $instance = array();
	if (!$instance) $instance[0] =& new ErrorHandler();
	return $instance[0];
}

class Observer {
	function update() {
		die('abstract method');
	}
}

Mock::Generate('Observer');

class ErrorHandlerTestCase extends UnitTestCase {
	function TestSingletonFunction() {
		$this->assertIsA(
			$eh1 =& getErrorHandlerInstance()
			,'ErrorHandler');
		$this->assertReference(
			getErrorHandlerInstance()
			,$eh1);
	}
	function TestAttach() {
		$eh =& getErrorHandlerInstance();
		$observer1 =& new MockObserver($this);
		$observer1->expectOnce(
			'update'
			//,array(&$eh)); Fatal error: Nesting level too deep - recursive dependency?
			,array('*'));
		
		$eh->attach($observer1);
		$eh->notify();
		
		$observer1->tally();
	}
	function TestDetach() {
		$eh =& getErrorHandlerInstance();
		$observer1 =& new MockObserver($this);
		$observer1->expectOnce('update', array('*'));
		$observer2 =& new MockObserver($this);
		$observer2->expectNever('update', array('*'));
		
		$eh->attach($observer1);
		$eh->attach($observer2);
		$eh->detach($observer2);
		$eh->notify();
		
		$observer1->tally();
		$observer2->tally();
	}
}

function observer_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
	$eh =& getErrorHandlerInstance();
	$eh->setState(array(
		 'number'	=> $errno
		,'msg'		=> $errstr
		,'file'		=> $errfile
		,'line'		=> $errline
		,'context'	=> $errcontext
		));
}


class ObserverTestCase extends UnitTestCase {
	function TestErrorHandler() {
		$eh =& getErrorHandlerInstance();
		$eh->attach(new EmailErrorLogger('jsweat_php@yahoo.com'));
		$eh->attach(new FileErrorLogger(fopen('error.log','w')));
		set_error_handler('observer_error_handler');
		trigger_error('this is a test error');
		restore_error_handler();
	}
}

$test = new GroupTest('Observer PHP4 Unit Test');
$test->addTestCase(new FileErrorLoggerTestCase);
$test->addTestCase(new EmailErrorLoggerTestCase);
$test->addTestCase(new ErrorHandlerTestCase);
$test->addTestCase(new SyslogErrorLoggerTestCase);
$test->addTestCase(new ObserverTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

