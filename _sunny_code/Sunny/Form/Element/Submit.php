<?php
class Sunny_Form_Element_Submit extends Zend_Form_Element_Submit {
    function __construct($elementName = 'submit') {
        parent::__construct($elementName);
    }
    
	function init() {
		$this
			->setLabel("  O K  ");
	    ;

	}
}
