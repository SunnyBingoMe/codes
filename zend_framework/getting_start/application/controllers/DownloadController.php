<?php

class DownloadController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
/*	    if (userHasNoPermissions) {
	        $this->view->msg = 'This file cannot be downloaded!';
	        $this->_forward('error', 'download');
	        return FALSE;
	    }
*/
        
/*	    header('Content-Type: image/jpeg');
	    header('Content-Disposition: attachment; filename="logo.jpg"');
*/	    readfile( APPLICATION_PATH.'/images/logo.jpg');

	    // disable layout and view
	    $this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
    }

}

