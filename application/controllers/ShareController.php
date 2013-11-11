<?php

require( APPLICATION_PATH . '/controllers/ExtensionFrontController.php' );

class ShareController extends ExtensionFrontController
{

    public function init()
    {
		parent::init();
    }

	public function indexAction(){

		//$this->_helper->getHelper('Layout')->disableLayout();

	}

}
