<?php

//require( APPLICATION_PATH . '/controllers/ExtensionFrontController.php' );

//class ErrorController extends ExtensionFrontController
class ErrorController extends  Zend_Controller_Action
{

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
	$controller = $errors->request->getControllerName();

	$error		=	$errors->exception->getMessage();
	$error		=	$errors->exception;

	if( strtolower( $controller ) == 'api' ){

		$errorModel	=	new Model_Errorlog();
		$user		=	1;
		$error		=	$errors->exception->getMessage();
		$action		= 	$errors->request->getActionName();
		$url		=	$this->getRequest()->getScheme() . '://' . $this->getRequest()->getHttpHost() . $this->getRequest()->getRequestUri();
		$type		=	$errors->type;
		$line		=	$errors->exception->getLine();
		$file		=	$errors->exception->getFile();


		$errorModel->addNewError( $error, $type, $user, $controller, $action,$file, $line, $url );


		die(json_encode(array("error" => 'En este momento no podemos realizar la peticion. Pruebe nuevamente mas tarde' )));

	}

	$this->_redirect("/error/e404");
print_r( $errors );die("<br> Error");
        $lang =   ( !$this->getRequest()->getParam( 'language' ) ) ? 'en' : $this->getRequest()->getParam( 'language' ) ;
        $this->view->lang   =   $lang;

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
//                $this->view->message = $this->translate->_( 'error404' );
                break;
            default:
                // application error

                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                $this->_redirect( "/$lang/" );
                break;

        }

        // Log exception, if logger available
//        if ($log = $this->getLog()) {
//            $log->crit($this->view->message, $errors->exception);
//        }
        $this->_redirect( "/" );
        die('llega');
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request;
	}

	public function e404Action(){

		$this->_helper->layout->disableLayout();

	}

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasPluginResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }


}

