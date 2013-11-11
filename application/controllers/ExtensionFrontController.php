<?php
class ExtensionFrontController extends Zend_Controller_Action
{
	protected $lang;
	protected $translate;
    	protected $seccion;
	protected $facebook_api;


	public function init(){

		$session = new Zend_Session_Namespace("usuario");
		$user    = array(
			"id"   => $session->user_id,
			"user" => $session->user,
			"avatar" => $session->avatar
		);
		// var_dump($user);

		$this->getNotifCount($session->user_id);

		$this->view->assign("session_user", $user);			
		$this->session_user = $user;
//		Zend_Session::destroy();


		if(!$this->hasCoords()) {
			$this->view->assign("getCoords", 1);
		}	

		if($session->user_id) {
		
			$this->view->mixpanel_user = '<script>mixpanel.identify("'.$session->user_id.'");</script>';
		}


		if(!$session->user_id) {
			
			require_once("../library/Facebook/src/facebook.php");
	
			$this->facebook_api = new Facebook(array(
				'appId'  => '106941089437708',
				'secret' => 'fdbf44db3c5f8d9726d592472ad4cf9d',
			));

			$facebook = $this->facebook_api;
			// Get User ID
			$user = $facebook->getUser();

			if(!$user) {

				$this->view->assign("fb_login_root", $facebook->getLoginUrl(array('scope'=>"publish_stream, status_update, email")));
				
			}else {

				$fb_access_token = $this->_request->getParam("code");

				if(isset($fb_access_token)) {

					$user_profile = $facebook->api('/me');

					$email = $user_profile["email"];

					if(!$email) {

						$this->view->assign("fb_login_error",1);
						return;
					}

					$model = new Model_Usuarios();
					$user_data = $model->getUserByMail($email);

					$fb_access_token = $this->_request->getParam("code");
					$fb_avatar = "https://graph.facebook.com/".$user_profile['id']."/picture?type=large"; 
					// si existe un usuario con ese email actualizo el token
					if($user_data) {

						$model->updateFBToken($fb_access_token, $email);
					}
					// si no existe un usuario con ese email registro el usuario
					else {

						if(!isset($user_profile["username"])) {
							$aux_username = $user_profile["email"];
							$aux_username = explode("@", $aux_username);
							$aux_username = $aux_username[0];
							$user_profile["username"] = $aux_username;
						}

						$user = array(
							"user"     => $user_profile["username"], 
							"nombre"   => $user_profile["first_name"],
							"apellido" => $user_profile["last_name"], 
							"mail"     => $user_profile["email"], 
							"sexo"     => $user_profile["gender"] == "male" ? "m" : "f",
							"localidad"   => 1,
							"descripcion" => isset($user_profile["quotes"]) ? $user_profile["quotes"] : "",
							"website"     => $user_profile["link"],
							"fb_avatar"   => $fb_avatar,
							"fb_pic"      => 1,
							"fb_uid"      => $user_profile["id"],
							"fb_access_token" => $fb_access_token,
							"tipo_cel" => "facebook web login",
							"puntos" => 60
						);

						$response = $this->createFBUser($user);

						$comunidad = new Model_Comunidad();
						$comunidad->newUser(array('id' => $response["id"],'user' => $response['user']));

						$data = $user;
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
							<p>Bienvenid'.$letra.' a Kuesty! Has conectado Kuesty con tu cuenta de Facebook, podes utilizar Kuesty desde facebook la web o tu smartphone. Kuesty, Sabes a donde ir!</p>

							<p>Tu usuario en Kuesty es: <b> '.$data['user'].'</b></p>

							<p>Segu&iacute; Kuestyando! </p>
							<p>El equipo de Kuesty</p>
							</div>
							</div>
							</body>
							</html>';


						mail($data['mail'],"Activa tu cuenta de Kuesty",$mailContent,$cabecera);


					}

					Zend_Session::start();
					$session = new Zend_Session_Namespace("usuario");
					$session->user_id = $user_data["id"];
					$session->user    = $user_data["user"];
					$session->avatar  = $fb_avatar;

					$uri = $_SERVER["REQUEST_URI"];
					//$uri = preg_replace("/code/","",$uri);
					//$uri = preg_replace("/state/","",$uri);
					//
					$this->_redirect($uri);			

				}
			}
		}
		
	}

	protected function createFBUser($user) {

		$model = new Model_Usuarios;
		$aux_username = $user["user"];

		if($model->userExists($aux_username)) {

			$i = 1;
			$aux_username = $user["user"].$i;
			while($model->userExists($aux_username)) {
				$i++;
				$aux_username = $user["user"].$i;
			}
			$user["user"] = $aux_username;
		}

		$uid = $model->addUser($user);

		$user_data = array(
			"id"   => $uid, 
			"user" => $user["user"]
		);

		return $user_data;
	}

	protected function hasCoords() {
		
		$session = new Zend_Session_Namespace("geo");
		return !$session->lat || $session->lat == 0 ? false : true;
	}

	protected function getVisitorCoords(){
	
		$session = new Zend_Session_Namespace("geo");
		return array("lat"=>$session->lat, "lng"=>$session->lng);
	}

	protected function mayoresContribuidores( $cantContribuidores = 4 ){

//	Devuelve el contenido con los mayores contribuidores

		$usuariosModel = new Model_Usuarios();
		$mejores = $usuariosModel->MejoresContribuidores( $cantContribuidores );
		$this->view->mejores = $mejores;

	}

	protected function getNotifCount($id_user){

		if(!(int) $id_user) return;

		$model = new Model_Notificaciones();
		$notif = $model->getUnreadCount($id_user);
		$this->view->assign("notif_unread",$notif);
	}

	protected function redimensionar_imagen($imagen, $carpeta, $ancho, $nombre_nuevo=NULL, $nuevoAL = NULL )

    {
        $nuevoAN     = $ancho;

        //indicamos el directorio donde se van a colgar las imágenes
        $directorio = $carpeta ;

        if ( !is_dir( $directorio ) ){
        	//Si no existe el directorio lo crea
        	mkdir( $directorio , 0777 );
        	chmod( $directorio , 0777 );
        }

        //establecemos los límites de ancho y alto
        $nuevo_ancho = $nuevoAN ;

        if ( !is_null( $nuevoAL ) )$nuevo_alto = $nuevoAL ;

        //Recojo información de la imágen
		$info_imagen = getimagesize($imagen);
		
        $alto = $info_imagen[1];
        $ancho = $info_imagen[0];
        $tipo_imagen = $info_imagen[2];

   	 	//Calculo y redimensiono para mantener el aspecto
        $ratio =  $ancho / $alto;
   	    $nuevo_alto = ceil($nuevo_ancho / $ratio);
    
        //Si no lo paso, armo el nuevo nombre del archivo
        $nombre_nuevo = (is_null($nombre_nuevo)) ? 'preview.jpg' : $nombre_nuevo;    
    
        // dependiendo del tipo de imagen tengo que usar diferentes funciones

        switch ($tipo_imagen) {

            case 1: //si es gif
                $imagen_nueva = imagecreate($nuevo_ancho, $nuevo_alto);
                $imagen_vieja = imagecreatefromgif($imagen);

                //cambio de tamaño
                imagecopyresampled($imagen_nueva, $imagen_vieja, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
                if (!imagegif($imagen_nueva, $directorio . $nombre_nuevo)) return false;

            break;

            case 2: //si es jpeg
                $imagen_nueva = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
                $imagen_vieja = imagecreatefromjpeg($imagen);

                //cambio de tamaño
                imagecopyresampled($imagen_nueva, $imagen_vieja, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
                if (!imagejpeg($imagen_nueva, $directorio . $nombre_nuevo)) return false;

             break;

            case 3: //si es png
                $imagen_nueva = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
                $imagen_vieja = imagecreatefrompng($imagen);

                //cambio de tamaño
                imagecopyresampled($imagen_nueva, $imagen_vieja, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
                if (!imagepng($imagen_nueva, $directorio . $nombre_nuevo)) return false;
                
            break;

        }
        
        chmod( $directorio . $nombre_nuevo , 0777 );

        return $nombre_nuevo;

    }


    	protected function paginar( $pages, $itemsPerPage = 5, $page = null ){

		if( is_null( $page ) )
			$page = $this->_getParam( 'page' );

		Zend_View_Helper_PaginationControl::setDefaultViewPartial('modules/paginador_frontend.phtml');
		
//		echo "route => " . Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName();

		$data = range( 1, $pages );
		$paginator = Zend_Paginator::factory( $data );
		
		$paginator->setDefaultScrollingStyle('Sliding');
		
		$paginator->setCurrentPageNumber( $page );
		$paginator->setDefaultItemCountPerPage( $itemsPerPage );

		// Assign the Paginator object to the view
		$this->view->paginator = $paginator;
		return $paginator->render();

		
	}

	protected function busquedaAvanzada(){

		$categoriasModel = new Model_Categorias();
		$categorias = $categoriasModel->listar();

		$this->view->categorias = $categorias;


	}
	

/*
        protected function setTitle( $title ){

            $this->view->assign( 'title', $title );

        }
	
	private function setTranslate(){
		
	  $this->lang           =   $this->getRequest()->getParam( 'language' );
	  $this->translate	=   Zend_Registry::get('Zend_Translate');

	  if ( !$this->lang )
	  		$this->lang	=	'es';
	  
	  switch ( $this->lang ){
	  	
	  	case 'es':
                                $locale = 'es_ES';
                                break;
	  	case 'en':
	  	case 'uk':
	  			$locale = 'en_GB';
	  			break;
	  	case 'pt':
	  			$locale = 'pt_PT';
	  			break;
	  	default:
	  			$locale = 'es_ES';
	  	
	  }

	  $this->translate->setLocale( $locale );

	}

*/	
}
