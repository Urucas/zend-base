<?php

class IndexController extends Zend_Controller_Action
{
    public function init()
	{
        /* Initialize action controller here */
        parent::init();
    }

	public function indexAction(){
	}

	public function loginAction(){
	
		if(!$this->_request->isPost()) return;	
		
		$user = trim($this->_request->getPost('user'));
		$pass = trim($this->_request->getPost('pass'));

		include_once('../library/Validate/User.php');
		$validator = new Validate_User();
		if(!$validator->isValid($user)) ;{
			$this->view->assign('error', 1);
			$this->view->assign('message', 'Usuario incorrecto');
			return;
    	}
		
		$this->model = new Model_Usuarios();
		$datos = $this->model->validaUser($user,$pass);

		if(count($datos)){
			$this->view->assign("error","0");
			$this->view->assign("usuario",$datos);
		}else{
			$this->view->assign("error","1");
			$this->view->assign("message","usuario o contraseña incorrectos");
			return;
		}

		$sesion = new Zend_Session_Namespace("usuario");
		$sesion->id = $datos[0]["id"];
		$sesion->usuario = $datos[0]["id"];
		$sesion->user 	= $datos[0]["user"];
		$sesion->nombre = $datos[0]["nombre"];
		$this->_redirect("/user");
	}

	public function signoutAction(){
		Zend_Session::destroy();
		$this->_redirect("/");
	}
    
}
