<?php

require( APPLICATION_PATH . '/controllers/ExtensionFrontController.php' );

class IndexController extends ExtensionFrontController
{
	private $model,$locales,$checkins;

    public function init()
	{
        /* Initialize action controller here */
//        $this->model	=   new Model_Front();
        $this->view->seccion  =   'home';
        parent::init();
		// $this->locales = new Model_Locales();
		// $this->checkins = new Model_Checkins();
//        $this->view->params =   array( 'lang' => $this->lang );
//        $title  =   'Kuesty, sabés donde ir';
//        $this->setTitle( $title );
        
    }

	public function consumerAction(){
	
		$model = new Model_Oauth();
		$model->newconsumerkeyAction();

	}

	public function indexAction(){
//		$_SESSION = array();
		$categorias = new Model_Categorias();
		$this->view->categorias = $categorias->fetchAll();

		$locales = new Model_Locales();
		$modelo = new Model_CheckinEspecial();
			

		$coords = $this->getVisitorCoords();
		if($coords["lat"] != 0) {

			$nearby = $locales->getNearby($coords["lat"], $coords["lng"], 5, null);
			
			foreach($nearby as $i => $local) {
				$checkin = $modelo->hasCheckinEspecial($local["id"]);
				if($checkin){
					$nearby[$i]["has_checkinespecial"] = 1;
					$nearby[$i]["desc_checkin"]        = $modelo->getCheckinEspecialLongDesc($checkin, $local);
				}					
			}

			$this->view->assign('nearby',$nearby);

		}

		$tops = $locales->TopRated(5,0);
		foreach($tops as $i => $local) {
			$checkin = $modelo->hasCheckinEspecial($local["id"]);
			if($checkin){
				$tops[$i]["has_checkinespecial"] = 1;
				$tops[$i]["desc_checkin"]        = $modelo->getCheckinEspecialLongDesc($checkin,$local);
			}					
		}		

		$this->view->assign('tops',$tops);
	
		$usuarios = new Model_Usuarios();
		$users    = $usuarios->TopPoints(10);
		$this->view->assign('top_users', $users);


		


		$this->view->headTitle("Guia movil de bares y restaurantes de Rosario - Kuesty");

/*
		$checkins = $this->checkins->LastCheckins(4);
		$this->view->assign('checkins',$checkins);
		
		$ultimos = $this->locales->LastCreated(6);
		$this->view->assign('last_locales',$ultimos);

		$mejores = $this->mayoresContribuidores();
*/
	}

	public function activarAction() {
	
		$user   = (int) $this->_request->getParam("usuario");
		$actkey = $this->_request->getParam("actkey");

		$this->usuarios = new Model_Usuarios();
		if($this->usuarios->userIsActivo($user)) {
			$this->view->activated = "1";
			return;
		}

		$this->view->activated = "0";

		$affected = $this->usuarios->activateUser($user, $actkey);
		if($affected) {
		
			$this->notificaciones = new Model_Notificaciones();
			$this->notificaciones->delActivacion($user);
			$this->view->activated = "1";
		}

	}

	public function politicasAction() {
	
	}

	public function ayudaAction() {
	
		$modelCfg = new Model_Configuraciones();

		$data = $modelCfg->getWebHelp();
		$data = $data[0];

		$this->view->assign("ayuda", $data);
	}

	public function terminosAction() {
	
		
	}

    public function placaAction()
    {
		if(!$this->_request->isPost()) return;	

		$email = $this->_request->getPost('email');
		$validator = new Zend_Validate_EmailAddress();
		if(!$validator->isValid($email)) {
			$this->view->assign('error', 1);
			$this->view->assign('message', 'Email incorrecto');
			return;
    	    }
		
		$this->model = new Model_Newsletter();
		$this->model->insert(array('email' => $email));
		$this->view->assign('added', 1);	
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
