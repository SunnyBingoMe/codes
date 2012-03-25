<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

class Color {
  var $r=0;
  var $g=0;
  var $b=0;
  function Color($red=0, $green=0, $blue=0) {
  	$this->setRed($red);
	$this->setGreen($green);
	$this->setBlue($blue);
  }
  function setRed($red) {
  	$this->r = $this->validateColor($red);
  }
  function setGreen($green) {
  	$this->g = $this->validateColor($green);
  }
  function setBlue($blue) {
  	$this->b = $this->validateColor($blue);
  }
  function validateColor($color) {
  	$check = (int)$color;
  	if ($check < 0 || $check > 255) {
		trigger_error("color '$color' out of bounds, please specify a number between 0 and 255");
	} else {
		return $check;
	}
  }
  function getRgb() {
  	return sprintf('#%02X%02X%02X', $this->r, $this->g, $this->b);
  }
}

class TestColor extends UnitTestCase {
	function TestColor($name='') {
		$this->UnitTestCase($name);
	}
        function setUp() {}
        function tearDown() {}
	function TestInstanciate() {
		$this->assertIsA($o = new Color, 'Color');
		$this->assertTrue(method_exists($o, 'getRgb'));
	}
	function TestValidateColor() {
		$this->assertEqual(0, Color::validateColor());
		$this->assertErrorPattern('/missing arg.*1/i');
		$this->assertEqual(0,  Color::validateColor('foo'));
		$this->assertEqual(0,  Color::validateColor(false));
		$this->assertEqual(1,  Color::validateColor(true));
		$t = rand(2,255);
		$this->assertEqual($t, Color::validateColor($t));
		$this->assertNull( Color::validateColor(-1) );
		$this->assertErrorPattern('/out.*0.*255/i');
		$this->assertNull( Color::validateColor(1111) );
		$this->assertErrorPattern('/out.*0.*255/i');
	}
	function testColorBoundaries() {
		$color =& new Color(-1);
		$this->assertErrorPattern('/out.*0.*255/i');
		$color =& new Color(1111);
		$this->assertErrorPattern('/out.*0.*255/i');
	}
	function TestGetRgbWhite() {
		$white =& new Color(255,255,255);
		$this->assertEqual('#FFFFFF', $white->getRgb());
	}
	function TestGetRgbRed() {
		$red =& new Color(255,0,0);
		$this->assertEqual('#FF0000', $red->getRgb());
	}
	function TestGetRgbRandom() {
		$color =& new Color(rand(0,255), rand(0,255), rand(0,255));
		$this->assertWantedPattern('/^#[0-9A-F]{6}$/', $color->getRgb());
		$color2 =& new Color($t = rand(0,255), $t, $t);
		$this->assertWantedPattern('/^#([0-9A-F]{2})\1\1$/', $color2->getRgb());
	}
}

class CrayonBox {
	/**
	 * Return valid colors as color name => array(red, green, blue)
	 * 
	 * Note the array is returned from function call 
	 * because we want to have getColor able to be called statically
	 * so we can't have instance variables to store the array
	 * @return	array
	 */
	function colorList() {
		return array(
			 'black' 	=> array(0, 0, 0)
			,'green' 	=> array(0, 128, 0)
			,'silver' 	=> array(192, 192, 192)
			,'lime' 	=> array(0, 255, 0)
			,'gray' 	=> array(128, 128, 128)
			,'olive' 	=> array(128, 128, 0)
			,'white'	=> array(255, 255, 255)
			,'yellow'	=> array(255, 255, 0)
			,'maroon'	=> array(128, 0, 0)
			,'navy'		=> array(0, 0, 128)
			,'red'		=> array(255, 0, 0)
			,'blue'		=> array(0, 0, 255)
			,'purple'	=> array(128, 0, 128)
			,'teal'		=> array(0, 128, 128)
			,'fuchsia'	=> array(255, 0, 255)
			,'aqua'		=> array(0, 255, 255)
		);
	}
	/**
	 * Factory method to return a Color
	 * @param	string	$color_name		the name of the desired color
	 * @return	Color
	 */
	function &getColor($color_name) {
		$color_name = strtolower($color_name);
		if (array_key_exists($color_name, $colors = CrayonBox::colorList())) {
			$color = $colors[$color_name];
			return new Color($color[0], $color[1], $color[2]);
		}
		trigger_error("No color '$color_name' available");
		// default to black
		return new Color;
	}
}


class TestFactory extends UnitTestCase {
	function TestFactory($name='') {
		$this->UnitTestCase($name);
	}
	function setUp() {}
	function tearDown() {}
	function TestGetColor() {
		$this->assertIsA($o =& CrayonBox::getColor('red'), 'Color');
		$this->assertEqual('#FF0000', $o->getRgb());
		$this->assertIsA($o =& CrayonBox::getColor('LIME'), 'Color');
		$this->assertEqual('#00FF00', $o->getRgb());
		// per http://www.w3.org/TR/html4/types.html#type-color
		$test_colors = array('Black' => '#000000','Green' => '#008000',
			'Silver' => '#C0C0C0','Lime' => '#00FF00','Gray' => '#808080',
			'Olive' => '#808000','White' => '#FFFFFF','Yellow' => '#FFFF00',
			'Maroon' => '#800000','Navy' => '#000080','Red' => '#FF0000',
			'Blue' => '#0000FF','Purple' => '#800080','Teal' => '#008080',
			'Fuchsia' => '#FF00FF','Aqua' => '#00FFFF');
		foreach($test_colors as $color_name => $color_value) {
			$this->assertIsA($o =& CrayonBox::getColor($color_name), 'Color');
			$this->assertEqual($color_value, $o->getRgb());
		}
	}
	function TestBadColor() {
		$this->assertIsA($o =& CrayonBox::getColor('Lemon'), 'Color');
		$this->assertErrorPattern('/lemon/i');
		// got black instead
		$this->assertEqual('#000000', $o->getRgb());
	}
}

class TextWriter {
	var $_buffer = '';
	function addText($text) {
		$this->_buffer .= $text;
	}
	function addHighlightedText0($text) {
		$color = new Color(255,255,0);
		$this->_buffer .= '<span style="background-color: '
			.$color->getRgb().'">'.$text.'</span>';
	}
	function addHighlightedText($text, $color=false) {
		if (!(is_object($color)
			&& method_exists($color, 'getRgb'))) {
			$color = new Color(255,255,0);
		}
		$this->_buffer .= '<span style="background-color: '
			.$color->getRgb().'">'.$text.'</span>';
	}
	function addHighlightedText2($text, $color=false) {
		if (is_array($color)
			&& is_object($color[0])
			&& method_exists($color[0], 'getRgb')) {
			$color =& $color[0];
		}
		if (!(is_object($color)
			&& method_exists($color, 'getRgb'))) {
			$color = new Color(255,255,0);
		}
		$this->_buffer .= '<span style="background-color: '
			.$color->getRgb().'">'.$text.'</span>';
	}
	function addHighlightedText3($text, $color_name='yellow') {
		$color =& CrayonBox::getColor($color_name);
		$this->_buffer .= '<span style="background-color: '
			.$color->getRgb().'">'.$text.'</span>';
	}
	function addHighlightedText4($text, $color_name='yellow') {
		$color =& $this->getNamedColor($color_name); 
		$this->_buffer .= '<span style="background-color: '
			.$color->getRgb().'">'.$text.'</span>';
	}
	function &getNamedColor($color_name) {
		return CrayonBox::getColor($color_name);
	}
	function render() {
		$ret = $this->_buffer;
		$this->_buffer = '';
		return $ret;
	}
}

class TestTextWriter extends UnitTestCase {
	function TestTextWriter($name='') {
		$this->UnitTestCase($name);
		Mock::Generate('Color');
	}
	function setUp() {}
	function tearDown() {}
	function TestSimpleText() {
		$o =& new TextWriter;
		$test_string = 'this is some text';
		$o->addText($test_string);
		$this->assertEqual($test_string, $o->render());
		$this->assertEqual('',$o->render());
	}
	function TestHighlightNoColorChange() {
		$o =& new TextWriter;
		$o->addText('This is a string with a ');
		$o->addHighlightedText0('yellow');
		$o->addText(' highlight');
		$this->assertWantedPattern(
			'~string.*<span.*background.*#FFFF00.*yellow</span>.*highlight~i'
			,$o->render());
	}
	function TestHighlightRed() {
		$o =& new TextWriter;
		$o->addText('This is a string with a ');
		$o->addHighlightedText('red', new Color(255,0,0));
		$o->addText(' highlight');
		$this->assertWantedPattern(
			'~string.*<span.*background.*#FF0000.*red</span>.*highlight~i'
			,$o->render());
	}
	function TestHighlightRedWithMock() {
		$o =& new TextWriter;
		$o->addText('This is a string with a ');
		$test_color =& new MockColor($this);
		$test_color->setReturnValue('getRgb', '#FF0000');
		$test_color->expectOnce('getRgb');
		$o->addHighlightedText('red', $test_color);
		$o->addText(' highlight');
		$this->assertWantedPattern(
			'~string.*<span.*background.*#FF0000.*red</span>.*highlight~i'
			,$o->render());
		// will fail, no pass by reference for Mock
		//$test_color->tally();
	}
	function TestHighlight2BlueWithMock() {
		$o =& new TextWriter;
		$o->addText('This is a string with a ');
		$test_color =& new MockColor($this);
		$test_color->setReturnValue('getRgb', '#0000FF');
		$test_color->expectOnce('getRgb');
		$o->addHighlightedText2('blue', array(&$test_color));
		$o->addText(' highlight');
		$this->assertWantedPattern(
			'~string.*<span.*background.*#0000FF.*blue</span>.*highlight~i'
			,$o->render());
		$test_color->tally();
	}
	function TestHighlightWithFactoryCall() {
		$o =& new TextWriter;
		$o->addText('This is a string with a ');
		$o->addHighlightedText3('blue', 'blue');
		$o->addText(' highlight');
		$this->assertWantedPattern(
			'~string.*<span.*background.*#0000FF.*blue</span>.*highlight~i'
			,$o->render());
	}
	function TestHighlightWithFactoryMethodCallingFactory() {
		$o =& new TextWriter;
		$o->addText('This is a string with a ');
		$o->addHighlightedText3('green', 'green');
		$o->addText(' highlight');
		$this->assertWantedPattern(
			'~string.*<span.*background.*#008000.*green</span>.*highlight~i'
			,$o->render());
	}
	function TestHighlightWithFactoryMethodMocked() {
		Mock::generatePartial('TextWriter', 'MockTextWriterNamedColor', array('getNamedColor'));
		$c =& new MockColor($this);
		$c->setReturnValue('getRgb', '#00FF00');
		$c->expectOnce('getRgb');
		$o =& new MockTextWriterNamedColor($this);
		$o->setReturnReference('getNamedColor', $c);
		$o->expectOnce('getNamedColor', array('lime'));
		
		$o->addText('This is a string with a ');
		$o->addHighlightedText4('lime', 'lime');
		$o->addText(' highlight');
		$this->assertWantedPattern(
			'~string.*<span.*background.*#00FF00.*lime</span>.*highlight~i'
			,$o->render());
		
		$o->tally();
		$c->tally();
	}
}

$test = new GroupTest('Factory Unit Test');
$test->addTestCase(new TestColor);
$test->addTestCase(new TestFactory);
$test->addTestCase(new TestTextWriter);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

