<?php

class Widget {
	function paint() {
		return $this->_asHtml();
	}
}

class TextInput extends Widget {
	var $name;
	var $value;
	function TextInput($name, $value='') {
		$this->name = $name;
		$this->value = $value;
	}
	function _asHtml() {
		return '<input type="text" name="'.$this->name.'" value="'.$this->value.'">';
	}
}

class WidgetDecorator {
	var $widget;
	function WidgetDecorator(&$widget) {
		$this->widget =& $widget;
	}
	function paint() {
		return $this->widget->paint();
	}
}

class Labeled extends WidgetDecorator {
	var $label;
	function Labeled($label, &$widget) {
		$this->label = $label;
		$this->WidgetDecorator($widget);
	}
	function paint() {
		return '<b>'.$this->label.':</b> '.$this->widget->paint();
	}
}

class Invalid extends WidgetDecorator {
	function paint() {
		return '<span class="invalid">'.$this->widget->paint().'</span>';
	}
}

class Post {
	var $store = array();
	function get($key) {
		if (array_key_exists($key, $this->store)) 
			return $this->store[$key];
	}
	function set($key, $val) {
		$this->store[$key] = $val;
	}
	function &autoFill() {
		$ret =& new Post;
		foreach($_POST as $key => $value) {
			$ret->set($key, $value);
		}
		return $ret;
	}
}

class FormHandler {
	function build(&$post) {
		return array(
			 new Labeled('First Name', new TextInput('fname', $post->get('fname')))
			,new Labeled('Last Name', new TextInput('lname', $post->get('lname')))
			,new Labeled('Email', new TextInput('email', $post->get('email')))
			);
	}
	function validate(&$form, &$post) {
		$valid = true;
		// first name required
		if (!strlen($post->get('fname'))) {
			$form[0] =& new Invalid($form[0]);
			$valid = false;
		}
		// last name required
		if (!strlen($post->get('lname'))) {
			$form[1] =& new Invalid($form[1]);
			$valid = false;
		}
		// email has to look real
		if (!preg_match('~\w+@(\w+\.)+\w+~'
				,$post->get('email'))) {
			$form[2] =& new Invalid($form[2]);
			$valid = false;
		}
		return $valid;
	}
}
