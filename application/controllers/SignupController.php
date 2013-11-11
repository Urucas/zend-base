<?php

require( APPLICATION_PATH . '/controllers/ExtensionFrontController.php' );

class SignupController extends ExtensionFrontController
{
	private $model,$paises;

    public function init()
    {
        /* Initialize action controller here */
        parent::init();
		$this->paises 		= new Model_Paises();
        
    }

	public function indexAction(){
		
		$categorias = new Model_Categorias();
		$this->view->categorias = $categorias->fetchAll();

		if(!$this->_request->isPost()) {
		
			return;
		}
		$sexo = strtolower($this->_request->getPost("sexo"));
		$data = array(
			"nombre"    => trim($this->_request->getPost("nombre")),
			"apellido"  => trim($this->_request->getPost("apellido")),
			"mail"     => trim($this->_request->getPost("mail")),
			"user"      => trim($this->_request->getPost("user")),
			"pass"      => trim($this->_request->getPost("pass")),
			"pass2"     => trim($this->_request->getPost("pass2")),
			"sexo"      => $sexo == "m" ? 'm' : 'f',
			"localidad" => (int) $this->_request->getPost("localidad")
		);
		
		$dia  = (int) $this->_request->getPost("dia");
		$mes  = (int) $this->_request->getPost("month");
		$anio = (int) $this->_request->getPost("year");
		$data["fecha_nacimiento"] = $anio."-".$mes."-".$dia;

		$this->view->data = $data;

		foreach($data as $param) {
			if($param == "") {
				var_dump($data);
				$this->view->error = "Todos los campos son obligatorios!";
				return;
			}
		}
		$validator = new Zend_Validate_EmailAddress();
		if(!$validator->isValid($data["mail"])) {
			$this->view->assign("error", "El email tiene formato incorrecto!");
			return;
		}
		$usuarios = new Model_Usuarios();
		if($usuarios->emailExists($data["mail"])) {
			$this->view->assign("error", "Ya existe una persona registrada con ese email!");
			return;
		}
        include_once('../library/Validate/User.php');
        $validator = new Validate_User();
        if(!$validator->isValid($data['user'])) {
			$this->view->assign("error", "El campo Usuario debe contener al menos 5 caracteres alfanumericos!");
            return;
        }

		if($usuarios->userExists($data["user"])) {
			$this->view->assign("error", "Ya existe una persona registrada con ese nombre de usuario!");
			return;
		}
		if($data['pass'] != $data['pass2']) {
			$this->view->assign("error", "La contrase&ntildea y su repetici&oacute;n no coinciden!");
			return;
		}

		unset($data["pass2"]);
		$data['estado'] = "P";
		$data['puntos'] = "20";

		$actKey = mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand();
		$data['activationKey'] = $actKey;
		$data['pass'] = md5($data['pass']);

		$id = $usuarios->addUser($data);

		//pone la fotito por defecto
		mkdir('./resources/avatars/'.$id.'/',0777,true);
		if($data['sexo'] == 'm'){
			copy('./resources/avatars/no_image_men.png','./resources/avatars/'.$id.'/avatar.jpg');
		}else{
			copy('./resources/avatars/no_image_woman.png','./resources/avatars/'.$id.'/avatar.jpg');
		}
		$usuarios->update(array("avatar"=>"http://kuesty.com/resources/avatars/".$id."/avatar.jpg"),"id = ".$id);

		$notificaciones = new Model_Notificaciones();
		$notificaciones->signupValidation($id);

		$letra = ($data['sexo'] == 'm') ? 'o' : 'a';
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
			<p>Hola <b>'.$data['nombre'].'</b></p><br />
			<p>Bienvenid'.$letra.' a Kuesty! Para poder empezar a usar Kuesty debes confirmar tu
			direcci&oacute;n de correo. Ten&eacute;s 2 dias para activar tu cuenta de kuesty si no lo haces se autodestruir&aacute;. Haz click en el siguiente link para activarla:</p>

			<p><a href="http://www.kuesty.com/activar/usuario/'.$id.'/'.$actKey.'" style="color:#ff5f00;font-weight:bold;">http://www.kuesty.com/activar/usuario/'.$id.'/'.$actKey.'</a></p>

			<p>Tu usuario en Kuesty es: <b> '.$data['user'].'</b></p>


			<p>Segu&iacute; Kuestyando! </p>
			<p>El equipo de Kuesty</p>
		</div>
	</div>
</body>
</html>';
	

		mail($data['mail'],"Activa tu cuenta de Kuesty",$mailContent,$cabecera);
		
		$comunidad = new Model_Comunidad();
		$comunidad->newUser(array('id' => $id,'user' => $data['user']));

		$this->view->data = array();
		$this->view->sent = 1;
	}

	public function reenviaractivacionesAction(){

		$usuarios = new Model_Usuarios();
		$u_inactivos = $usuarios->getInactiveUsers();
		//$u_inactivos = $usuarios->getUs();

		foreach($u_inactivos as $k=>$v){

			$data = $v;
			$letra = ($data['sexo'] == 'm') ? 'o' : 'a';
			$cabecera = "Content-type: text/html\r\n";

//			$tr = new Zend_Mail_Transport_Smtp('smtp.secureserver.net');
//			Zend_Mail::setDefaultTransport($tr);

			$mail = new Zend_Mail();
			$mail->setFrom('info@kuesty.com', 'Equipo de Kuesty');
			$mail->addTo($data['mail'], $data['nombre']);
			$mail->setReplyTo('info@kuesty.com', 'Kuesty');
			$mail->setSubject('Recuerda activar tu cuenta de Kuesty');

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
				<p>Hola <b>'.$data['nombre'].'</b></p><br />
				<p>Bienvenid'.$letra.' a Kuesty! 
				Para continuar utilizando Kuesty debes activar tu cuenta.
				Haz click en el siguiente link para activarla:</p>
				<p><a href="http://www.kuesty.com/activar/usuario/'.$data['id'].'/'.$data['activationkey'].'" style="color:#ff5f00;font-weight:bold;">http://www.kuesty.com/activar/usuario/'.$data['id'].'/'.$data['activationkey'].'</a></p>

				<p>Tu usuario en Kuesty es: <b> '.$data['user'].'</b></p>
				<p>Segu&iacute; Kuestyando! </p>
				<p>El equipo de Kuesty</p>
				</div>
				</div>
				</body>
				</html>';

			$mail->setBodyHtml($mailContent);
		//	$mail->send();
			echo $mailContent . "\n";
			//mail($data['mail'],"Activa tu cuenta de Kuesty",$mailContent,$cabecera);

		}
			$mensaje = "Activacion enviada a ".sizeof($u_inactivos)." usuarios";
			die($mensaje);
	}
	public function loginAction(){}
		
	public function confirmationAction(){}
}
