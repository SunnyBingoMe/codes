<?php
class Sunny_Form_Element_Cancel extends Zend_Form_Element_Submit {
    function __construct($elementName = 'cancel') {
        parent::__construct($elementName);
    }
    
	function init() {
		$this
			->setLabel("Cancel");
	    ;

	}
}
