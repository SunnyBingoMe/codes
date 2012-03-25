<?php
error_reporting(E_ALL);

require_once 'widgets.inc.php';

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';


class WidgetTestCase extends UnitTestCase {
	function testTextInput() {
		$text =& new TextInput('foo', 'bar');
		
		$output = $text->paint();
		
		$this->assertWantedPattern('~^<input type="text"[^>]*>$~i', $output);
		$this->assertWantedPattern('~name="foo"~i', $output);
		$this->assertWantedPattern('~value="bar"~i', $output);
	}
	
	function testLabeled() {
		$text =& new Labeled(
			'Email'
			,new TextInput('email'));

		$output = $text->paint();
		
		$this->assertWantedPattern('~^<b>Email:</b> <input~i', $output);	
	}
	
	function testInvalid() {
		$text =& new Invalid(
			new TextInput('email'));
	
		$output = $text->paint();
		
		$this->assertWantedPattern('~^<span class="invalid"><input[^>]+></span>$~i', $output);
	}
	
	function testInvalidLabeled() {
		$text =& new Invalid(
			new Labeled(
				'Email'
				,new TextInput('email')));
		$output = $text->paint();
		
		$this->assertWantedPattern('~<b>Email:</b> <input~i', $output);	
		$this->assertWantedPattern('~^<span class="invalid">.*</span>$~i', $output);
	}
}


class FormHandlerTestCase extends UnitTestCase {

	function testBuild() {
		$this->assertIsA($form = FormHandler::build(new Post), 'Array');
		$this->assertEqual(3, count($form));
		$this->assertIsA($form[1], 'Labeled');
		$this->assertWantedPattern('~email~i', $form[2]->paint());
	}

	function testValidateMissingName() {
		$post =& new Post;
		$post->set('fname', 'Jason');
		$post->set('email', 'jsweat_php@yahoo.com');
		
		$form = FormHandler::build($post);
		$this->assertFalse(FormHandler::validate($form, $post));

		$this->assertNoUnwantedPattern('/invalid/i', $form[0]->paint());
		$this->assertWantedPattern('/invalid/i', $form[1]->paint());
		$this->assertNoUnwantedPattern('/invalid/i', $form[2]->paint());
	}

	function testValidateBadEmail() {
		$post =& new Post;
		$post->set('fname', 'Jason');
		$post->set('lname', 'Sweat');
		$post->set('email', 'jsweat_php AT yahoo DOT com');
		
		$form = FormHandler::build($post);
		$this->assertFalse(FormHandler::validate($form, $post));

		$this->assertNoUnwantedPattern('/invalid/i', $form[0]->paint());
		$this->assertNoUnwantedPattern('/invalid/i', $form[1]->paint());
		$this->assertWantedPattern('/invalid/i', $form[2]->paint());
	}

	function testValidate() {
		$post =& new Post;
		$post->set('fname', 'Jason');
		$post->set('lname', 'Sweat');
		$post->set('email', 'jsweat_php@yahoo.com');
		
		$form = FormHandler::build($post);
		$this->assertTrue(FormHandler::validate($form, $post));

		$this->assertNoUnwantedPattern('/invalid/i', $form[0]->paint());
		$this->assertNoUnwantedPattern('/invalid/i', $form[1]->paint());
		$this->assertNoUnwantedPattern('/invalid/i', $form[2]->paint());
	}


}

$test = new GroupTest('Decorator Unit Test');
$test->addTestCase(new WidgetTestCase);
$test->addTestCase(new FormHandlerTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());