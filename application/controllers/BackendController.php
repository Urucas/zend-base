<?php

class BackendController extends Zend_Controller_Action
{
    public function init(){
		
		$this->_helper->getHelper('Layout')->setLayout( 'back' );
		$this->auth = new Zend_Session_Namespace('admin');
		$this->view->assign("id",$this->auth->id);
		$this->view->assign("user",$this->auth->user);
    }

    public function indexAction(){
        //$this->checkSession();
	}

	public function loginAction(){
	
        $this->_helper->layout->disableLayout();
		if(!$this->_request->isPost()) return;	
		
		$user = trim($this->_request->getPost('user'));
		$pass = trim($this->_request->getPost('pass'));
		$this->model = new Model_Administradores();
		$datos = $this->model->validaUser($user,$pass);

		if(!sizeof($datos)){
			$this->view->assign("error","1");
			$this->view->assign("message","Usuario o contraseña incorrectos");
			return;
		}

		$sesion = new Zend_Session_Namespace("admin");
		$sesion->id = $datos[0]["id"];
		$sesion->user 	= $datos[0]["user"];
		$sesion->nombre = $datos[0]["nombre"];
		$this->_redirect("/backend/");
	}

	public function logoutAction(){
		Zend_Session::destroy();
		$this->_redirect("/backend/login");
	}

    private function checkSession() {

		$sesion = new Zend_Session_Namespace("admin");
		if($sesion->id) {
			return;
			$session;
		}
		$this->_redirect("/backend/login");
	}

    
}
