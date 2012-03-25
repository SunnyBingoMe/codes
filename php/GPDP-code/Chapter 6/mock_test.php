<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';
require_once 'simpletest/web_tester.php';

require_once 'classes.inc.php';
Session::init();

class ResponseTestCase extends UnitTestCase {
	function TestRedirect() {
		$response =& new Response;
		$response->redirect('foo', false);
		$this->assertErrorPattern('/^Cannot modify header information/i');
	}
	function TestFetch() {
		$response =& new Response;
		$this->assertEqual(
			$response->fetch()
			,'<html><head></head><body></body></html>');
	}
	function TestDisplay() {
		$response =& new Response;
		ob_start();
		$this->assertNull(
			$response->display());
		$this->assertEqual(
			ob_get_clean()
			,'<html><head></head><body></body></html>');
	}
	function TestAddBody() {
		$response =& new Response;
		$response->addBody('body content');
		$this->assertWantedPattern(
			'~<body>body content</body>~i'
			,$response->fetch());
	}
	function TestAddHead() {
		$response =& new Response;
		$response->addhead('head content');
		$this->assertWantedPattern(
			'~<head>head content</head>~i'
			,$response->fetch());
	}
	function TestAddBodyTemplate() {
		$template = 'test.tpl';
		$fh = fopen($template, 'w');
		fwrite($fh, 'this is content from the template');
		fclose($fh);
		
		$response =& new Response;
		$response->addBodyTemplate($template);
		$this->assertWantedPattern(
			'~<body>.*content.*template</body>~i'
			,$response->fetch());
		
		unlink($template);
	}
	function TestAddBodyTemplateBogusFile() {
		$response =& new Response;
		$response->addBodyTemplate('bogus.template');
		$this->assertWantedPattern(
			'~<body></body>~i'
			,$response->fetch());
	}
	function TestAddBodyTemplateWithVariableSubstitution() {
		$template = 'test.tpl';
		$fh = fopen($template, 'w');
		fwrite($fh, 'this is <?php echo $var1; ?> from the template');
		fclose($fh);
		
		$vars = array('var1' => 'content');
		
		$response =& new Response;
		$response->addBodyTemplate($template, $vars);
		$this->assertWantedPattern(
			'~<body>.*content.*template</body>~i'
			,$response->fetch());
		
		unlink($template);
	}
}

class UserLoginTestCase extends UnitTestCase {
	function TestUserLoginValidate() {
		$this->assertFalse(UserLogin::Validate('',''));
		$this->assertFalse(UserLogin::Validate('admin',''));
		$this->assertTrue(UserLogin::Validate('admin','secret'));
	}
	function TestUserLoginValidateNameCaseInsensative() {
		$this->assertFalse(UserLogin::Validate('ADMIN','secret'));
		$this->assertTrue(UserLogin::Validate('ADMIN','secret'));
	}
}

class SessionTestCase extends UnitTestCase {
	function xTestSessionNotStartedError() {
		$session =& new Session;
		$this->assertErrorPattern('/session not started/i');
	}
	function TestIsValidKey() {
		$session =& new Session;
		$this->assertFalse($session->isValid('foobar'));
		$session->set('foobar', 'baz');
		$this->assertTrue($session->isValid('foobar'));
	}
	function TestSetGet() {
		$session =& new Session;
		unset($_SESSION['foo']);
		$this->assertFalse($session->isValid('foo'));
		$session->set('foo','bar');
		$this->assertEqual('bar', $session->get('foo'));
		$this->assertTrue(array_key_exists('foo', $_SESSION));
		$this->assertEqual('bar', $_SESSION['foo']);
	}
}


define('TEST_URL', 'http://localhost/~sweatje/patterns/06-mockobject/code/page.php');

class PageWebTestCase extends WebTestCase {
	function TestInitalFetchNoSecretContent() {
		$this->assertTrue($this->get(TEST_URL));
		$this->assertNoUnwantedPattern('/secret.*content/i');
	}
	function TestInitalFetchContainsLoginForm() {
		$this->assertTrue($this->get(TEST_URL));
		$this->assertField('name');
		$this->assertField('passwd');
		$this->assertWantedPattern('/<form.*<input[^>]*text[^>]*name.*<input[^>]*password[^>]*passwd/ims');
	}
	function TestBogusLoginFailure() {
		$this->assertTrue($this->get(TEST_URL));
		$this->setField('name','foo');
		$this->setField('passwd','bar');
		$this->clickSubmit('Login');
		$this->assertNoUnwantedPattern('/secret.*content/i');
		$this->assertWantedPattern('/<form.*<input[^>]*text[^>]*name.*<input[^>]*password[^>]*passwd/ims');
	}
	function TestSucessfulLogin() {
		$this->assertTrue($this->get(TEST_URL));
		$this->setField('name','admin');
		$this->setField('passwd','secret');
		$this->clickSubmit('Login');
		$this->assertWantedPattern('/welcome\s+admin/i');
		$this->assertWantedPattern('/secret.*content/i');
		$this->assertNoUnwantedPattern('/<form.*<input[^>]*text[^>]*name.*<input[^>]*password[^>]*passwd/ims');
	}
	function TestNewBroswerDoesNotCarrySession() {
		$this->assertTrue($this->get(TEST_URL));
		$this->assertField('name');
		$this->assertField('passwd');
		$this->assertWantedPattern('/<form.*<input[^>]*text[^>]*name.*<input[^>]*password[^>]*passwd/ims');
	}
	function TestLogoutWorks() {
		$this->assertTrue($this->get(TEST_URL));
		$this->setField('name','admin');
		$this->setField('passwd','secret');
		$this->clickSubmit('Login');
		$this->assertWantedPattern('/welcome\s+admin/i');
		$this->assertWantedPattern('/secret.*content/i');
		$this->assertTrue($this->get(TEST_URL.'?clear'));
		$this->assertNoUnwantedPattern('/secret.*content/i');
		$this->assertWantedPattern('/<form.*<input[^>]*text[^>]*name.*<input[^>]*password[^>]*passwd/ims');
	}
}


class PageDirectorTestCase extends UnitTestCase {
	function PageDirectorTestCase ($name='') {
		$this->UnitTestCase($name);
		Mock::Generate('Session');
		Mock::Generate('Response');
		define('SELF', 'testvalue');
	}
	function TestLoggedOutContent() {
		$session =& new MockSession($this);
		$session->setReturnValue('get', null, array('user_name'));
		$session->expectOnce('get', array('user_name'));
		
		$page =& new PageDirector($session, new Response);
		$result = $this->runPage($page);
		$this->assertNoUnwantedPattern('/secret.*content/i', $result);
		$this->assertWantedPattern('/<form.*<input[^>]*text[^>]*'
			.'name.*<input[^>]*password[^>]*passwd/ims'
			,$result);
		
		$session->tally();
	}
	function TestLoggedInContent() {
		$session =& new MockSession($this);
		$session->setReturnValue('get', 'admin', array('user_name'));
		$session->expectAtLeastOnce('get');
		
		$page =& new PageDirector($session, new Response);
		$result = $this->runPage($page);
		$this->assertWantedPattern('/secret.*content/i', $result);
		$this->assertNoUnwantedPattern('/<form.*<input[^>]*text[^>]*'
			.'name.*<input[^>]*password[^>]*passwd/ims'
			,$result);
		
		$session->tally();
	}
	function TestClearLoginFunctionality() {
		$_REQUEST['clear'] = null;

		$session =& new MockSession($this);
		$session->expectOnce('clear', array('user_name'));
		$session->setReturnValue('get', null, array('user_name'));
		$session->expectAtLeastOnce('get');
		
		$response = new MockResponse($this);
		$response->expectOnce('redirect', array(SELF));

		$page =& new PageDirector($session, $response);
		$this->assertEqual('', $this->runPage($page));
		
		$response->tally();
		$session->tally();
		unset($_REQUEST['clear']);
	}
	function TestLoginFromRequest() {
		$_REQUEST['name'] = 'admin';
		$_REQUEST['passwd'] = 'secret';

		$session =& new MockSession($this);
		$session->expectOnce('set', array('user_name','admin'));
		
		$response = new MockResponse($this);
		$response->expectOnce('redirect', array(SELF));

		$page =& new PageDirector($session, $response);
		$this->assertEqual('', $this->runPage($page));
		
		$response->tally();
		$session->tally();
		
		unset($_REQUEST['name']);
		unset($_REQUEST['passwd']);
	}
	function runPage(&$page) {
		ob_start();
		$page->run();
		return ob_get_clean();
	}
}

$test = new GroupTest('MockObject PHP4 Unit Test');
$test->addTestCase(new ResponseTestCase);
$test->addTestCase(new UserLoginTestCase);
$test->addTestCase(new SessionTestCase);
$test->addTestCase(new PageWebTestCase);
$test->addTestCase(new PageDirectorTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

