<?php

require( APPLICATION_PATH . '/controllers/ExtensionFrontController.php' );

class UserController extends ExtensionFrontController
{
	private $usuarios,$locales,$checkins,$id_usr,$reviews,$friends;

    public function init()
    {
        /* Initialize action controller here */
		$action = $this->_request->getActionName();
		if($action == "logout") {
			return;
		}

		$sesion = new Zend_Session_Namespace("usuario");	
		//if(!$sesion->usuario && $action != "perfil" && $action != "login"){
		//	die("logueate");
		//}
		
		$this->id_usr = $sesion->user_id;

		try{	
        $this->usuarios	= new Model_Usuarios();
        $this->view->seccion  =   'usuarios';
        parent::init();

		$this->locales 	= new Model_Locales();
		$this->checkins = new Model_Checkins();
		$this->reviews 	= new Model_Reviews();
		$this->friends 	= new Model_Friends();
		}catch(Exception $e){
			print_r($e);
			die();
		}
//        $this->view->params =   array( 'lang' => $this->lang );
        //$title  =   'Kuesty - Usuario';
        //$this->setTitle( $title );
		$categorias = new Model_Categorias();
		$this->view->categorias = $categorias->fetchAll();

        
    }

	public function friendsAction() {
	
		$id         = $this->_request->getParam("id");
		$usuario    = $this->usuarios->getUserById($id);

		$friends_id = $this->friends->getFriendsAsIdArray($id);
		if($friends_id) {
			$friends = $this->usuarios->getUsersInArray($friends_id);
		}

		$session = new Zend_Session_Namespace("usuario");
		$user_id = $session->user_id;

		$this->view->usuario = $usuario[0];
		$this->view->user_id = $session->user_id;
		$this->view->friends = $friends;
		
		$this->view->headTitle("Kuesty - Amigos de ".$usuario[0]["user"]);
	}

	public function notificationsAction() {
	
		$session = new Zend_Session_Namespace("usuario");
		if(!$session->user_id) {
			$this->_redirect("/user/login");
		}

		$id   = $session->user_id;
		$user = $this->usuarios->getUserById($id);

		$modelNotif = new Model_Notificaciones();
		$notif = $modelNotif->getNotifications($id); 

		//marco todas como leidas
		$modelNotif->markAllAsRead($id);

		//var_dump($notif);die();

		$this->view->assign("usuario", $user[0]);
		$this->view->assign("notificaciones", $notif);

	}

	public function findfriendsAction() {
	
		$session = new Zend_Session_Namespace("usuario");
		$user_id = $session->user_id;
		
		if(!$user_id) {
			$this->view->assign("notlogged", 1);
			return;
		}

		$is_facebook = (int)$this->_request->getParam("via_facebook");
		if($is_facebook) {
			$this->view->assign("via_facebook", 1);

			require_once("../library/Facebook/src/facebook.php");
			$this->facebook_api = new Facebook(array(
				'appId'  => '106941089437708',
				'secret' => 'fdbf44db3c5f8d9726d592472ad4cf9d',
			));


			$list = $this->facebook_api->api("me/friends?fields=id,name,installed&limit=5000");
//			var_dump($list);

			$list = $list["data"];
			foreach($list as $user) {
				if(isset($user["installed"]) && $user["installed"]) {
					$f_uids[] = $user["id"];
				}	
			}
			$friends = $this->friends->getFriendsAsIdArray($user_id);		
			$this->view->assign("friends_id", $friends);

			$result = $this->friends->getFriendsByFuid($f_uids);
			$this->view->assign("friends", $result);
			return;
		}

		$q    = trim($this->_request->getParam("qf"));
		$page = (int) $this->_request->getParam("page"); 

		if($q != "") {
			$friends = $this->friends->getFriendsAsIdArray($user_id);		
			$this->view->assign("friends_id", $friends);

			$result  = $this->usuarios->findUser($q, $page);
			$this->view->assign("friends", $result);
		}
	
		$this->view->assign("qf", $q);
		$this->view->assign("page", $page);

		$this->view->headTitle("Kuesty - Busqueda de Amigos");
	}

	public function indexAction(){

		$id = $this->_request->getParam("id");
		$usuario = $this->usuarios->getUserById($id);
		$this->view->assign('usuario',$usuario[0]);
//		print_r($usuario[0]);

		$resenas = $this->reviews->getReviewsByUser($id,20,0);
		$this->view->assign("reviews",$resenas);
		//print_r($resenas);

		$checkins = $this->checkins->getCheckinsByUser($id,4,0);
		$this->view->assign("checkins",$checkins);
//		print_r($checkins);

		$amigos  = $this->friends->getFriendsAsIdArray($id);
		$len     = sizeof($amigos);
		$friends = array();
		
		if($len) {
			shuffle($amigos);
			if($len <= 3) {
				$friends = $this->usuarios->getUsersInArray($amigos);
			}else{
				$friends = $this->usuarios->getUsersInArray(array_slice($amigos,0,3));
			}
		}

		$this->view->assign("friends",$friends);
//		print_r($amigos);

		$locales = $this->locales->getLocalFanByUserId($id);
//		print_r($locales);
		$this->view->assign("locales", $locales);	
		
		//si no soy yo
		if($this->id_usr){
			if($this->id_usr != $id){
				//me fijo si es mi amigo
				$friend = $this->friends->isFriend($this->id_usr,$id);
				$this->view->assign("myfriend",$friend);
				$metrics = new My_Mixpanel("147cf3e28614fd0afe0e77286906f8fe");
				$i = $metrics->track("perfil", array(
					"id" => $id,
					"logged"=>1,
					"mio"=>"no"
				));
			}else{
				//soy yo
				$metrics = new My_Mixpanel("147cf3e28614fd0afe0e77286906f8fe");
				$i = $metrics->track("perfil", array(
					"id" => $id,
					"logged"=>1,
					"mio"=>"si"
				));
			}
		}else{

			//no estoy logueado
			$metrics = new My_Mixpanel("147cf3e28614fd0afe0e77286906f8fe");
				$i = $metrics->track("perfil", array(
					"id" => $id,
					"logged"=>0,
					"mio"=>"no se"
				));

		}

		$this->view->headTitle("Kuesty - Perfil de ".$usuario[0]["user"]);
	}

	public function perfilAction(){

		$idUser = $this->_request->getParam("u");
		// si soy yo me mando al perfil
		if($idUser == $this->id_usr) $this->_redirect("/user");

		//me fijo si es mi amigo
		$friend = $this->friends->isFriend($this->id_usr,$idUser);
		$this->view->assign('friends',$friend);

		$usuario = $this->usuarios->getUserById($idUser);
		$this->view->assign('usuario',$usuario[0]);
		
		$resenas = $this->reviews->getReviewsByUser($idUser);
		$this->view->assign("reviews",$resenas);

		$tips = $this->checkins->getCheckinsByUser($idUser);
		$this->view->assign("tips",$tips);
		
		$this->view->headTitle("Kuesty - Perfil de ".$usuario["user"]);
	}

	public function recoverAction() {
	
//		$this->_helper->layout->disableLayout();
		$recaptcha = new Zend_Service_ReCaptcha("6Lcol88SAAAAAB5Hs_2Z7jozhijhK6wb_20pu0m3","6Lcol88SAAAAAA-rXsSiQNse3y6sMD0zBlhz82Hk");
		$this->view->recaptcha = $recaptcha->getHTML();

		$this->view->assign("step", 0);
		if(!$this->_request->isPost()) return;

		$result = $recaptcha->verify(
		    $_POST['recaptcha_challenge_field'],
		    $_POST['recaptcha_response_field']
		);

		if (!$result->isValid()) {
			$this->view->assign("step", 1);
			$this->view->assign("error", "El codigo de verificaci&oacute;n es incorrecto");
			return;
		}

		$model = new Model_Usuarios();
		$email  = $this->_request->getPost("email");

		$user = $model->getUserByMail($email);
//		$user = $model->getUserByNameExactly($name);

		$this->view->headTitle("Kuesty - Recuperar password");
		if(!$user) {
			$this->view->assign("step", 1);
			$this->view->assign("error", "El usuario no existe en Kuesty!");
			return;
		}
		/*
		$pass = $model->generatePassword();
		$model->resetPassword($user["id"], $pass);
		*/
		$recoverKey = mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand();
		$model->setRecoverUser($user["id"],$recoverKey);
		$cabecera = "Content-type: text/html\r\n";
		
		// Additional headers
		$cabecera .= 'From: Equipo de Kuesty <info@kuesty.com>' . "\r\n";

		$mailContent = '<html>
<head>
<title>Bienvenido a Kuesty</title>
</head>
<body>
	<div style="width:700px;height:400px;background:#E6E6E6 url(http://www.kuesty.com/resources/images/new-background.png)">
		<div >
			<img style="margin:30px 0 6px 100px;" src="http://www.kuesty.com/resources/images/logo-mail.png" />
		</div>
		<div style="width:500px;height:220px;background-color:white;border-radius:15px;margin-left:90px;padding:20px;font-family:\'Arial\';font-size:10pt;">
			<p>Hola <b>'.$user["user"].'</b></p><br />
			<p>Has solicitado un cambio de contraseña en Kuesty, haz click en el siguiente link para setear una nueva: </p>
			<p><b>http://www.kuesty.com/user/newpass/email/'.$user["mail"].'/key/'.$recoverKey.'</b></p><br />
			
			<p>Segu&iacute; Kuestyando! </p>
			<p>Saludos.</p>
			<p>El equipo de Kuesty</p>
		</div>
	</div>
</body>
</html>';
	
		mail($user["mail"],"Recupera tu password de Kuesty",$mailContent,$cabecera);
		$this->view->assign("step", 2);

	}

	public function newpassAction(){

		$mail = $this->_request->getParam("email");
		$key = $this->_request->getParam("key");

		//if(!$this->_request->isPost()) return;

		//checkeo si la key corresponde al user
		$this->model = new Model_Usuarios();
		$usuario = $this->model->checkPassKey($mail,$key);

		if(!$usuario){$this->view->form_error = "El codigo personal o el mail son incorrectos";return;}

		//si estaba bien el key y el user abre para cambiar el pass

		if($this->_request->isPost()){
			$pass  = $this->_request->getPost("password");
			$pass2 = $this->_request->getPost("password2");

			if($pass != $pass2) {
				$this->view->assign("error", "Las contrase&ntilde;as no coinciden");
				return;
			}
			if(strlen($pass) < 6){
				$this->view->assign("error", "La contrase&ntilde;a es muy corta, tiene que tener al menos 6 caracteres");
				return;
			}

			$model = new Model_Usuarios();
			$model->addPassChange($usuario["id"],$pass);
			/*
			$model->update(array(
				"pass" => md5($pass),
				"changepasskey" => "",
				"passchanges" => "passchanges + 1"
			), "id = ".$usuario["id"]);

			//borrar el key
			*/

			$this->_redirect("/user/id/".$usuario["id"]."/user/".$usuario["user"]);
		}else{return;}

	}

	public function changemypassAction(){

		if(!$this->_request->isPost()) {
			return;
		}
		
		$pass  = $this->_request->getPost("password");
		$pass2 = $this->_request->getPost("password2");

		if($pass != $pass2) {
			$this->view->assign("form_error", "Las contrase&ntilde;as no coinciden");
			return;
		}

		$model = new Model_Usuarios();
		$model->update(array(
			"pass" => md5($pass)
		), "id = ".$session->user_id);

		$metrics = new My_Mixpanel("147cf3e28614fd0afe0e77286906f8fe");
		$i = $metrics->track("changepass", array(
			"id" => $session->user_id
//			"referer" => $_SERVER["HTTP_REFERER"]
		));

			
		$this->_redirect("/user/?id=".$session->user_id);

	
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

		
		$this->view->headTitle("Kuesty - Login");

		if(!$this->_request->isPost()) return;	
		
		$user = trim($this->_request->getPost('user'));
		$pass = trim($this->_request->getPost('pass'));

		/*
		include_once('../library/Validate/User.php');
		$validator = new Validate_User();
		if(!$validator->isValid($user)) {
			$this->view->assign("error","Usuario o contrase&ntilde;a incorrectos");
			return;
    	}
		 */

		$this->model = new Model_Usuarios();
		$datos = $this->model->validaUser($user,$pass);
		if(!sizeof($datos)) {
			$this->view->assign("error","Usuario o contrase&ntilde;a incorrectos");
			return;
		}
		$datos = $datos[0];

		Zend_Session::start();
		$session = new Zend_Session_Namespace("usuario");
		$session->user_id = $datos["id"];
		$session->user    = $datos["user"];
		$session->avatar  = $datos["avatar"];

		$metrics = new My_Mixpanel("147cf3e28614fd0afe0e77286906f8fe");
		$i = $metrics->track("login", array(
			"id" => $datos["id"]
		));

		$this->_redirect("/user/?id=".$datos["id"]);
	}

	public function createmobilepassAction() {

		$session_id = Zend_Session::getId();
		$session = new Zend_Session_Namespace("usuario");
		$this->view->assign("username", $session->user);

        if(!$session_id){
        	$this->_redirect("/user/login");			
		}
			
		if(!$this->_request->isPost()) {
			return;
		}

		$pass  = $this->_request->getPost("password");
		$pass2 = $this->_request->getPost("password2");

		if($pass != $pass2) {
			$this->view->assign("form_error", "Las contrase&ntilde;as no coinciden");
			return;
		}

		$model = new Model_Usuarios();
		$model->update(array(
			"pass" => md5($pass)
		), "id = ".$session->user_id);

			
		$metrics = new My_Mixpanel("147cf3e28614fd0afe0e77286906f8fe");
		$i = $metrics->track("create mobile pass", array(
			"id" => $session_user_id
		));


		$this->_redirect("/user/?id=".$session->user_id);
		
	}

	public function avatarAction() {
		
		$id      = $this->_request->getParam("id");
		$model   = new Model_Usuarios();
		$usuario = $model->getUserById($id);

		if( (int)$usuario[0]["fb_pic"]) {
			$path = $usuario[0]["fb_avatar"]; 
		}
		else { 
			$path = $usuario[0]["avatar"];
		}

		header('Content-type: image/png'); 
		$data = file_get_contents($path);		
		die($data);
	}

	public function logoutAction() {
	
        Zend_Session::destroy();

		require_once("../library/Facebook/src/facebook.php");	
		$facebook = new Facebook(array(
			'appId'  => '106941089437708',
			'secret' => 'fdbf44db3c5f8d9726d592472ad4cf9d',
		));

		$facebook->destroySession();

		$this->_redirect('/');	

	}
    
}
