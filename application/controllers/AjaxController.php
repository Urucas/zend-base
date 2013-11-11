<?php

//require( APPLICATION_PATH . '/controllers/ExtensionBackController.php' );
require( APPLICATION_PATH . '/controllers/ExtensionFrontController.php' );

//class AjaxController extends ExtensionBackController 
class AjaxController extends ExtensionFrontController 
{
	public $provincias,$localidades,$usuarios,$notificaciones,$likes,$reviews,$comunidad,$denunciascheckins,$friends;

    public function init()
    {
        /* Initialize action controller here */
		$this->provincias 	= new Model_Provincias();
		$this->localidades 	= new Model_Localidades();
		$this->usuarios 	= new Model_Usuarios();
		$this->notificaciones 	= new Model_Notificaciones();
		$this->likes          	= new Model_Likes();
		$this->reviews  	    = new Model_Reviews();
		$this->comunidad        = new Model_Comunidad();
		$this->friends	        = new Model_Friends();
		$this->denunciascheckins = new Model_Denunciascheckins();
    }

	public function visitorcoordsAction() {
	
		$lat = (float) $this->_request->getParam("lat");
		$lng = (float) $this->_request->getParam("lng");

		$session = new Zend_Session_Namespace("geo");
		$session->lat = $lat;
		$session->lng = $lng;

		die(json_encode(array("lat"=>$lat, "lng"=>$lng)));
	}

	public function nearbyAction() {
	
		$locales = new Model_Locales();

		$coords = $this->getVisitorCoords();
		
		$nearby = $locales->getNearby($coords["lat"], $coords["lng"], 5, null, (int)$this->_request->getParam("page"));

		$modelo = new Model_CheckinEspecial();
		foreach($nearby as $i => $local) {
			$checkin = $modelo->hasCheckinEspecial($local["id"]);
			if($checkin){
				$nearby[$i]["has_checkinespecial"] = 1;
				$nearby[$i]["desc_checkin"]        = $modelo->getCheckinEspecialLongDesc($checkin, $local);
			}					
		}
		$this->view->assign('nearby',$nearby);

		$html   = $this->view->render("/restaurants/nearby.phtml");
	   	die(json_encode(array("html"=>$html)));	
	}
	public function moretopAction() {
	
		$model = new Model_Locales();

		$tops = $model->TopRated(5,	(int)$this->_request->getParam("page"));

		$modelo = new Model_CheckinEspecial();
		foreach($tops as $i => $local) {
			$checkin = $modelo->hasCheckinEspecial($local["id"]);
			if($checkin){
				$tops[$i]["has_checkinespecial"] = 1;
				$tops[$i]["desc_checkin"]        = $modelo->getCheckinEspecialLongDesc($checkin, $local);
			}					
		}

		$this->view->assign('tops',$tops);
		$html   = $this->view->render("/restaurants/top.phtml");
	   	die(json_encode(array("html"=>$html)));	
	}

    public function getnearbyAction() {

		
		//$this->checkOAuth();
		$this->locales = new Model_Locales();

        $lat  = (float) $this->_request->getParam('lat');
		$long = (float) $this->_request->getParam('long');
		
		$localidades = $this->localidades->getAll();

        if($lat == 0 || $long == 0) {
            die(json_encode(
                array(
                    "error"=>true,
					"message"=>"No se ha podido obtener las coordenadas. Por favor intente mas tarde nuevamente!"
				)
                )
            );
		}

        $cant = (int) $this->_request->getParam('cant');
		$cant = $cant == 0 ? 25 : $cant;
		$page = (int) $this->_request->getParam("page");
		$comodidades = $this->_request->getParam("comodidades");

        $categoria = (int) $this->_request->getParam('categoria');
        $locales  = $this->locales->getNearby($lat, $long, 15, $categoria, $page, $comodidades);

		$modelo = new Model_CheckinEspecial();
		foreach($locales as $k=>$v){
			$id = $v["id"];
			$checkin = $modelo->hasCheckinEspecial($id);	
			if($checkin){
				$locales[$k]["has_checkinespecial"] = 1;
				$locales[$k]["desc_checkin"] = $modelo->getCheckinEspecialLongDesc($checkin, $locales[$k]);
			}
		}

		$this->view->assign("nearby", $locales);
		$html = $this->view->render("/restaurants/nearby.phtml");

		$metrics = new My_Mixpanel("147cf3e28614fd0afe0e77286906f8fe");
		$i = $metrics->track("nearby", array(
			"latLng" => $lat.",".$long
		));

		die(json_encode(
			array(
				"locales"=>$locales,
				"html" => $html
            )
        ));

	}

	public function morecheckinsAction() {
	
		$model = new Model_Checkins();
		
		$id = (int)$this->_request->getParam("id");
		$data = $model->getCheckinsByUser($id, 4, (int)$this->_request->getParam("page"));
		$this->view->assign("checkins",$data);
		$html = $this->view->render("/user/checkins.phtml");
		$total = $model->getCantCheckinsByUser($id);

	   	die(json_encode(array("total"=>$total,"html"=>$html)));	
	}

    public function indexAction()
    {
        // action body
    }

	public function getprovinciasAction(){
	
		$id_pais = $this->_request->getParam("pais");
		$provincias = $this->provincias->getProvByPais($id_pais);
		die(json_encode($provincias));

	}

	public function saveuserfieldAction(){
		$id = $this->_request->getParam("userid");
		$campo = $this->_request->getParam("campo");
		$valor = $this->_request->getParam("valor");

		$data[$campo] = $valor;
		$up = $this->usuarios->update($data,"id=".$id);
		die(json_encode($up));
	}
    
	public function getlocalidadesAction(){
	
		$id_prov = $this->_request->getParam("prov");
		$localidades = $this->localidades->getLocalidadesByProv($id_prov);
		die(json_encode($localidades));

	}

	public function addfanAction() {
	
		$id_local = $this->_request->getPost("id_local");

		$session  = new Zend_Session_Namespace("usuario");

		$usuario  = new stdClass();
		$usuario->id   = $id_user  = $session->user_id;
		$usuario->user = $session->user;

		if(!(int)$id_user) {
			die(json_encode(array("error"=>1, "msg" => "user error")));
		}

		$this->locales = new Model_Locales();
		
		$local    = $this->locales->getLocalById($id_local);
		if(!sizeof($local)) {
			die(json_encode(array("error"=>1, "msg" => "local_error")));
		}
		
		$this->fans = new Model_Fans();
		$cant_fans = $aux_cant_fans = $local['cant_fans'];

		if($this->fans->userIsFan($id_user, $local['id'])) 
		{
			$affected = $this->fans->deleteFan($id_user, $local['id']);
			if($affected) {
				--$cant_fans;
				$this->usuarios->restFan($id_user);
				$this->locales->restFan($local['id']);
				
			}
			$this->comunidad->delFan($id_user, $local["id"]);
		} 
		else 
		{
			$affected = $this->fans->addFan($id_user, $local['id']);
			if($affected) {
				++$cant_fans;
				$this->usuarios->sumFan($id_user);
				$this->locales->addFan($local['id']);
			}
			$this->comunidad->newFan($usuario, $local);

			try {	
				if($this->facebook_api == null) {

					require_once("../library/Facebook/src/facebook.php");

					$this->facebook_api = new Facebook(array(
						'appId'  => '106941089437708',
						'secret' => 'fdbf44db3c5f8d9726d592472ad4cf9d',
					));
				}

				$response = $this->facebook_api->api(
					'/me/kuestyapp:fan',
					'POST',
					array(
						'restaurant' => "http://kuesty.com/restaurants/local/id/".$local["id"]."/name/".$local["nombre"]
					)
				);

			}catch(Exception $e) { }

			/*	
			if($id_local > 615 && $id_local < 651) {
				$tw = new Model_Twitter();
				$message = $local["nombre"]." tiene un nuevo FAN en la feria de las #colectividades #rosario #kuesty http://www.kuesty.com/restaurants/local/id/".$local["id"]. " http://www.kuesty.com/user/id/".$id_user;

				$tw->tweet($message, $local["latitud"], $local["longitud"]);
			}
			 */

		}
		die(json_encode(array("error"=>!$affected, "cant_fans" => $cant_fans, "aux"=>(int)$aux_cant_fans)));
	}

	public function haztefanAction(){

		$idRestaurant = $this->_request->getParam( 'restaurant' );

		if( !$idRestaurant )
			die(json_encode(array( 'error' => 1, 'description' => 'No existe el restaurant' )));

		$session = new Zend_Session_Namespace("usuario");

		if( !$session->usuario )
			die(json_encode(array( 'error' => 2, 'description' => 'El usuario debe estar logueado.' )));

		$user = $session->usuario;

		if( $idRestaurant ){

			$modelFans = new Model_Fans();
			$result = $modelFans->addFan( $session->usuario, $idRestaurant );

		}

		die(json_encode(array( 'fan' => 1 )));

	}

	public function dejardeserfanAction(){

		$idRestaurant = $this->_request->getParam( 'restaurant' );

		if( !$idRestaurant )
			die(json_encode(array( 'error' => 1, 'description' => 'No existe el restaurant' )));

		$session = new Zend_Session_Namespace("usuario");

		if( !$session->usuario )
			die(json_encode(array( 'error' => 2, 'description' => 'El usuario debe estar logueado.' )));

		$user = $session->usuario;

		if( $idRestaurant ){

			$modelFans = new Model_Fans();
			$result = $modelFans->deleteFan( $session->usuario, $idRestaurant );

		}

		die(json_encode(array( 'fan' => 1 )));

	}

	public function claimAction() {
	
		$id_local  = $this->_request->getParam("id_local");
	//	$localname = $this->_request->getParam("restaurant") 

		$session  = new Zend_Session_Namespace("usuario");
		$id_user  = $session->user_id;
		$username = $session->user;

		if(!$id_user) {
		
			die(json_encode(array("error"=>1, "msg"=>"Se ha perdido la sesion de usuario")));
		}

		$model = new Model_Usuarios();
		$user  = $model->getUserById($id_user);
	   	if(!sizeof($user)) {
		
			die(json_encode(array("error"=>1, "msg"=>"Ha ocurrido un error al buscar su usurios, por favor intente nuevamente!")));
		}	
		$user = $user[0];

		$model = new Model_Locales();
		$local = $model->getLocalById($id_local);
		if(!$local) {
		
			die(json_encode(array("error"=>1, "msg"=>"Ha ocurrido un error al buscar su usurios, por favor intente nuevamente!")));
		}

		$cabecera = "Content-type: text/html\r\n";
		// Additional headers

		$cabecera .= 'From: Equipo de Kuesty <info@kuesty.com>' . "\r\n";
		$cabecera .= 'Reply-To: info@kuesty.com';

		$mailContent = '<html>
<head>
<title>Han reclamado un local</title>
</head>
<body>
	<div style="width:700px;height:400px;background:#E6E6E6 url(http://www.kuesty.com/resources/images/new-background.png)">
		<div >
			<img style="margin:30px 0 6px 100px;" src="http://www.kuesty.com/resources/images/logo-mail.png" />
		</div>
		<div style="width:500px;height:220px;background-color:white;border-radius:15px;margin-left:90px;padding:20px;font-family:\'Arial\';font-size:10pt;">
			<p><br />
			
			Hola <b>'.$user["nombre"].'</b>, <br /><br /> 

Primero que nada gracias por utilizar Kuesty. <br /><br />

Vemos que has reclamado el local: "'.$local["nombre"].'", para poder validar que este sea tu local te vamos a enviar un código de activacion a tu local, que te va a permitir ingresar a nuestro sistema y poder utilizar herramientas más avanzadas.
<br /><br />
Una vez que verifiquemos que este local es tuyo vas a poder acceder a los beneficios que ofrece Kuesty; podrás modificar los datos del local para que quede completo el perfil del mismo, cambiar los estados para la comunidad y crear promociones para tus fans de Kuesty o para todos los usuarios de la aplicación. Estas promos especiales le van a llegar a los usuarios como notificación en el celu por lo que es mucho más fácil llegar a potenciales clientes. Esta y muchas herramientas mas, están al alcance de tu mano a traves de Kuesty.
<br /><br />
Cualquier consulta que tengas, no dudes en consultarnos.
<br /><br />

Saludos!
<br /><br />
Equipo de Kuesty.

		</div>
	</div>
</body>
</html>';

		@mail($user["mail"].",info@kuesty.com","Tu local en Kuesty",$mailContent,$cabecera);
		
		die(json_encode(array("claim"=>1)));
	}

	public function addfriendAction(){

		// Agrega un nuevo amigo a un usuario
        $idFriend = $this->_request->getParam('friend');
		$modelUsuarios = new Model_Usuarios();
		$friend = $modelUsuarios->getUserById($idFriend);
		// valido que exista el amigo
		if(sizeof($friend) == 0) {
			die(json_encode(array("error"=>1)));	
		}
		
		$modelFriends = new Model_Friends();
		$session = new Zend_Session_Namespace("usuario");
		if( !$session->user_id )
			die(json_encode(array( 'error' => 2, 'description' => 'El usuario debe estar logueado.' )));

       	$affected = $modelFriends->addNewFriend( $session->user_id, $idFriend);
		// valido que se haya podido agregar en base la relacion
		if(!$affected) {
			die(json_encode(array("error"=>1)));
		}

		$modelNotificaciones = new Model_Notificaciones();
		$objFriend = new stdClass();
		$objFriend->id = $friend[0]['id'];
		$objFriend->user = $friend[0]['user'];

		$session2 = $session;
		$session2->id = $session->user_id;

		$modelNotificaciones->friendshipRequest($objFriend, $session2 );
        
		die(json_encode(array("error" => 0)));
	}
	public function acceptfriendAction(){
		$session = new Zend_Session_Namespace("usuario");
		$userId	  =	$session->user_id;
		$idFriend = (int) $this->_request->getParam( 'idfriend' );
		$modelUsuarios = new Model_Usuarios();
		$friend   = $modelUsuarios->find($idFriend);

		// valido que exista el amigo
		if( !$friend ) {
			// eh amigo no existi
			die(json_encode(array("error"=>1)));	
		}
		$modelFriends = new Model_Friends();
		$affected = $modelFriends->changeStateFriend( $userId, $idFriend );       
		// valido que se haya cambiado el estado de amistad
		if(!$affected) {
			die(json_encode(array("error"=>2)));	
		}

		$modelUsuarios->updateCantidadAmigos($userId, $idFriend);

		$user   = $modelUsuarios->find($userId);
		$modelNotificaciones = new Model_Notificaciones();
		$modelNotificaciones->acceptFriend($user[0], $friend[0]);
		// mas adelante hay q ver de eliminar la notificacion de solicitud 
		// de amistad una vez q el amigo acepta o declina(declina.. ja)
		$modelComunidad = new Model_Comunidad();
		$modelComunidad->newFriendship($user[0], $friend[0]);

		die(json_encode(array("error"=>0)));	
	}
	public function rejectfriendAction(){
		$session = new Zend_Session_Namespace("usuario");
		$userId	  =	$session->user_id;
		$idFriend = (int) $this->_request->getParam( 'idfriend' );
		$modelUsuarios = new Model_Usuarios();
		$friend   = $modelUsuarios->find($idFriend);

		// valido que exista el amigo
		if( !$friend ) {
			// eh amigo no existi
			die(json_encode(array("error"=>"El usuario no existe")));	
		}
		$modelFriends = new Model_Friends();
		$affected = $modelFriends->changeStateFriend( $userId, $idFriend, "D" );       
		// valido que se haya cambiado el estado de amistad
		if(!$affected) {
			die(json_encode(array("error"=>"Algo anda mal")));	
		}

		die(json_encode(array("success"=>1)));	
	}

	public function addlocalstatusAction() {
	
		$id_local = $this->_request->getPost("id_local");
		
		$session  = new Zend_Session_Namespace("usuario");
		$id_user  = $session->user_id;

		if(!$id_user) {
			die(json_encode(array(
				"error" => 1, "msg" => "Debes estar logueado para realizar esta acci&oacute;n" 
			)));
		}

		$locales = new Model_Locales();

		$local = $locales->getLocalById($id_local);
		if(!$local) {
			die(json_encode(array(
				"error" => 1, "msg" => "Ha ocurrido un error, intente nuevamente mas tarde" 
			)));
		}

		
		if($local["id_user_propietario"] != $id_user) {
			if($id_user != 1 && $id_user !=12) {
				die(json_encode(array(
					"error" => 1, "msg" => "No tienes permiso para realizar esta accion",
					"id_user" => $id_user
				)));
			}
		}

		$post = $this->_request->getPost("post");
		$post = str_replace("“","", $post);
		$post = str_replace("”","", $post);

		/*
		if(trim($post) == "" || strlen($post) < 50) {
			die(json_encode(array(
				"error" => 1, "msg" => "Debes ingresar un texto de al menos 50 caracteres" 
			)));	
		}
		 */
		$comunidad = new Model_Comunidad();
		$sid = $comunidad->addLocalStatus($local, $post);

		$tw = new Model_Twitter();
		$post = substr($post, 0, 80);

		$message = "$post... #rosario http://kuesty.com/restaurants/local/id/".$local["id"]."/sid/".$sid;
		$tw->tweet($message, $local["latitud"], $local["longitud"]);

		// busco los usuarios que son fans de ese local
		// y agrego una notificacion en cada uno

		$fans = $locales->getLocalFans($local["id"]);
		if(sizeof($fans)) {
			foreach($fans as $fan) {
				$ids[] = $fan["id_user"];
			}
			$this->usuarios = new Model_Usuarios();
			$fans = $this->usuarios->getUsersInArray($ids);


			$modelNotif = new Model_Notificaciones();
			$modelNotif->delete("id_local =".(int) $local["id"]);
			
			foreach($fans as $fan) {
				$modelNotif->localStatusNotificacion($fan, $local, $post);
			}
		}

		die(json_encode(array(
			"msg" => "Tu estado ha sido actualizado con exito" 
		)));
		
	}

	public function removelocalstatusAction() {

		$id_local = $this->_request->getPost("id_local");
		
		$session  = new Zend_Session_Namespace("usuario");
		$id_user  = $session->id;

		if(!$id_user) {
			die(json_encode(array(
				"error" => 1, "msg" => "Debes estar logueado para realizar esta acci&oacute;n" 
			)));
		}

		$locales = new Model_Locales();

		$local = $locales->getLocalById($id_local);
		if(!$local) {
			die(json_encode(array(
				"error" => 1, "msg" => "Ha ocurrido un error, intente nuevamente mas tarde" 
			)));
		}
		if($local["id_user_propietario"] != $id_user) {
			die(json_encode(array(
				"error" => 1, "msg" => "No tienes permiso para realizar esta accion" 
			)));
		}

		$id_evento = $this->_reqeuest->getPost($id_evento);

		$comunidad = new Model_Comunidad();
		$comunidad->delete("id = ".(int)$id_evento);
	
		die(json_encode(array(
			"msg" => "Tu estado ha sido eliminado" 
		)));

	}

	public function getreviewsAction(){

		$idLastReview = $this->_request->getParam( 'lastreview' );
		$local = $this->_request->getParam( 'local' );
		$modelReviews = new Model_Reviews();
		$reviews = $modelReviews->getReviewsByLocal( $local, 3, $idLastReview );	
		if( !$reviews )
			die(json_encode(array("error" => 1, "description"  => "No se encontraron Reviews")));

		die(json_encode(array("reviews" => $reviews)));


	}
	 
	public function addreviewAction(){
	
		$data["price"] = (int)$this->_request->getParam("price");
		$data["comentario"] = trim($this->_request->getParam("comentario"));
		$data["rating"] = (int) $this->_request->getParam("rating");
		$data["id_local"] = $id_local = $this->_request->getParam("id_local");

		$session = new Zend_Session_Namespace("usuario");
		if( !$session->user_id ) {
			die(json_encode(array( 'error' => 2, 'description' => 'El usuario debe estar logueado.' )));
		}
		$data["id_user"] = $session->user_id;

		$modelReviews = new Model_Reviews();
		$val = $modelReviews->getDiffLastReview($data["id_user"],$id_local);
		if($val[0]['dif'] != null && $val[0]['dif'] < 1){
			die(json_encode(array("error"=>1, 'description'=>'Hace muy poco escribiste una reseña de este local. Intenta nuevamente en unos dias',"dif"=>$val[0]['dif'])));
		}
	
		$len = strlen($data["comentario"]);
		if(strlen($data['comentario']) < 90) {
			die(json_encode(array(
				'error' => 1, 
				'description' => 'Debes escribir al menos 90 caracteres para que la reseña sea valida. Hasta ahora has escrito'.$len. ", vamos, tu puedes!", 
				'cant'=>$len
				))
			);
		}
		 
		if($data["rating"] < 1) {
			die(json_encode(array(
				'error' => 1, 
				'description' => 'Debes seleccionar al menos una estrella' 
				))
			);
		}

		if($data["price"] < 1) {
			die(json_encode(array(
				'error' => 1, 
				'description' => 'Debes seleccionar al menos un $' 
				))
			);
		}

		
		$locales = new Model_Locales();
		$local =  $locales->getLocalById($id_local);
		if( !$local ) {
			die(json_encode(array( 'error' => 1, 'description' => 'Ha ocurrido un error. Intente nuevamente mas tarde!' )));
		}

		$id = $modelReviews->insert($data);	

		// actualizo los datos del usuario, sumandole puntos por escribir la review, cant reviews y guradando el id de la ultima review
		//
		// se le da al usuario 0.7 cada 10 caracteres q escriba
		$puntos = round((0.7*$len) / 10);
		$this->usuarios->addReview($data["id_user"], $id, $puntos);

		$model = new Model_Locales();
		$model->addReview($id, $data["id_local"], $data["rating"], $data["price"]);

		$comunidad = new Model_Comunidad();

		$user = new stdClass();
		$user->id = $session->user_id;
		$user->user = $session->user;

		$data["id"] = $id;
		$comunidad->newReview($user, $data, $local);

		if($this->_request->getParam("shareFB")) {

			try {	
				if($this->facebook_api == null) {

					require_once("../library/Facebook/src/facebook.php");

					$this->facebook_api = new Facebook(array(
						'appId'  => '106941089437708',
						'secret' => 'fdbf44db3c5f8d9726d592472ad4cf9d',
					));
				}

				$response = $this->facebook_api->api(
					'me/kuestyapp:review',
					'POST',
					array(
						'restaurant' => "http://kuesty.com/restaurants/local/id/".$local["id"]."/name/".$local["nombre"]+"/rid/"+ $data["id"],
						'stars' => $data["rating"],
						'pesos' => $data["price"], 
						'tip'   => "He escrito una reseña sobre " + $local["nombre"] + " en Kuesty. " + $data["comentario"],
					)
				);

			}catch(Exception $e) { 
			
				var_dump($e);
			}
	
			
		}


		$metrics = new My_Mixpanel("147cf3e28614fd0afe0e77286906f8fe");
		$i = $metrics->track("review", array(
			"id_local" => $local["id"],
			"nombre_local" => $local["nombre"],
			"id_user" => $user->id,
			"nombre_user" => $user->user,
			"id_review" => $data["id"]
//			"referer" => $_SERVER["HTTP_REFERER"]
		));		


		die(json_encode(array("msj"=>"Ya tenemos tu review! Has ganado $puntos puntos")));
	}

	public function searchrestaurantAction(){

		$patron = $this->_request->getParam( 'patron' );
		$pais = $this->_request->getParam( 'pais' );
		$provincia = $this->_request->getParam( 'provincia' );
		$localidad = $this->_request->getParam( 'localidad' );
		$categorias = $this->_request->getParam( 'categorias' );
		$page = $this->_request->getParam( 'page' );
		$order = $this->_request->getParam( 'order' );

		$params['patron'] = $patron;
		$params['page'] = $page;
		$params['order'] = $order;

		$itemsPerPage = 7;
		$params['itemsPerPage'] = $itemsPerPage;


		if( $pais > 0 && $provincia > 0 &&  $localidad > 0 )
			$params['localidad']	= $localidad;

		if( $categorias )
			$params['categorias'] = substr( $categorias, 0, strlen( $categorias )-1  );
		
		$localesModel = new Model_Locales();
		$locales = $localesModel->searchLocales( $params );
		$totalPages = $locales[ 'total' ];
		$locales = $locales[ 'locales' ];

		$totalPages =   ( $totalPages > 0 ) ?   $totalPages :   0;
		$paginador = '';
		if ( $totalPages > $itemsPerPage )
			$paginador = $this->paginar( $totalPages, $itemsPerPage, ++$page );

		die(json_encode(array( 'locales' =>  $locales, 'paginador' => $paginador )));
//		die(json_encode(array( 'locales' =>  $locales, 'paginador' => $paginador, 'totalPages' => $totalPages, 'itemsPerPage' => $itemsPerPage )));

	}


	public function likereviewAction() {

		$idreview = $this->_request->getParam('idreview');
		$like     = (int) $this->_request->getParam('like');		
		$like     = $like ? 1 : 0;

		$sesion = new Zend_Session_Namespace("usuario");
		if(!$sesion->usuario){
			$mensaje = json_encode(array("error"=>"1","mensaje"=>"Debes iniciar sesi&oacute;n para poder calificar una rese&ntilde;a"));
			die($mensaje);
		}

		$review = $this->reviews->find($idreview);
		// manejar exception si no existe la review
		if(sizeof($review) == 0){
			$mensaje = json_encode(array("error"=>"1","mensaje"=>"Esa rese&ntilde;a no esta disponible"));
			die($mensaje);
		}
		$review = $review[0];
		$likes   = (int)$review["likes"];
		$unlikes = (int)$review["unlikes"];
			
		// verifico si el usuario ya voto esta review anteriormente
		$vote = $this->likes->userLikeReview($sesion->usuario, $idreview);
		if(sizeof($vote)) {				
			// si ya habia votado por: Le Guto y quiere votar nuevamente Le Guto, termino la accion
			if($vote[0]['like'] == 1 && $like == 1)
			{
				die(json_encode(array(
					"id_review" => $idreview,
					"likes" => $likes, 
					"unlikes" => $unlikes,
					"user_review_like" => $like
				)));
			}
			// si ya habia votado por No Le Guto y quiere votar nuevamente No Le Guto, termino la accion
			elseif($vote[0]['like'] == 0 && $like == 0)
			{
				die(json_encode(array(
					"id_review" => $idreview,
					"likes" => $likes, 
					"unlikes" => $unlikes,
					"user_review_like" => $like
				)));
			}			
			// en caso de que este cambiando su estado actualizo el estado, Le Gutoooo o no le guto ?
			$this->likes->update(
				array("like" => $like), 
				"id = ".$vote[0]['id']
			);			
			if($like) --$unlikes; else --$likes;
		}else{
			// en caso de que no haya votado nunca la review, ingreseo el voto en base
			$this->likes->insert(array("like"=>$like, "id_user"=>$sesion->usuario, "id_review" => $idreview));
		}
		// incremento la cantidad de votos para actualizar la review
		if($like) ++$likes;	else ++$unlikes;
		$this->reviews->update(array(
			"likes"=>$likes, "unlikes"=> $unlikes), 
			"id = ".$idreview
		);

		$usuario = new stdClass();
		$usuario->id = $sesion->usuario;
		$usuario->user = $sesion->user;
		$this->comunidad->likeReview($usuario, $review);

		die(json_encode(array(
			"id_review" => $idreview,
			"likes" => $likes, 
			"unlikes" => $unlikes,
			"user_review_like" => $like
		)));
	}

	public function uploadprofileimageAction(){

		$valid_formats = array("jpg", "png", "gif", "bmp","jpeg","JPG");
		if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST")
		{
			$name = $_FILES['photoimg']['name'];
			$size = $_FILES['photoimg']['size'];
			if(strlen($name))
			{
				list($txt, $ext) = explode(".", $name);
				if(in_array($ext,$valid_formats))
				{
					if($size<(1024*1024)) // Image size max 1 MB
					{
						$actual_image_name = "avatar.jpg";
						$tmp = $_FILES['photoimg']['tmp_name'];

						$sesion = new Zend_Session_Namespace("usuario");
						$id = $sesion->user_id;
						//move_uploaded_file($tmp,"resources/avatars/".$id."/".$_FILES["file"]["name"]);

						if (move_uploaded_file($tmp,"resources/avatars/".$id."/".$actual_image_name))
						{
							//la redimensiono
							//$url_imagen = "resources/avatars/".$id."/".$_FILES["file"]["name"];
							//$nueva = $this->redimensionar_imagen($url_imagen,"resources/avatars/".$id."/",500);

							$table = new Zend_Db_Table("usuarios");
							$table->update(array("fb_pic"=>"0"),$id);
							//mysql_query("UPDATE users SET profile_image='$actual_image_name' WHERE uid='$session_id'");
							die( json_encode(array("html"=>"<img src='/user/avatar/id/".$id."/?r=".rand(8)."' class='avatar' onmouseover=\"$('#change-img').show()\" onmouseout=\"$('#change-img').hide()\">")));
						}
						else
							die( json_encode(array("error"=>"Falló la subida de la imagen, prueba de nuevo")));
					}
					else
						die(json_encode(array("error"=>"La imagen es muy grande, tiene que ser menor a 1 MB"))); 
				}
				else
					die (json_encode(array("error"=>"Formato de imagen invalido.."))); 
			}
			else
				die(json_encode(array("error"=>"Selecciona una foto..!")));
			exit;
		}

	}
	
	public function flagcheckinAction(){
	

		$sesion = new Zend_Session_Namespace("usuario");
		if(!$sesion->usuario){
			$mensaje = json_encode(array("error"=>"1","mensaje"=>"Debes iniciar sesi&oacute;n para poder calificar una rese&ntilde;a"));
			die($mensaje);
		}

		//denuncia de un local
        $data['id_usuario']			=	$sesion->usuario;
        $data['id_checkin']			=   $this->_request->getParam('idcheckin');
											     
		try{
			$inserted = $this->denunciascheckins->addDenuncia($data);
			if($inserted){
				mail('info@urucas.com','Checkin denunciado','El usuario '.$sesion->usuario.' ha denunciado el checkin '.$data['id_checkin']);
			}
		}catch(Exception $e){
			die(json_encode(array("mensaje"=>"ya denunciaste")));	
		}


		die( json_encode( array("mensaje"=>"Su denuncia ha sido enviada, gracias!","id"=>$inserted)));
	
	}

	public function signupAction(){

		$response = array();

        include_once('../library/Validate/User.php');
        $validator = new Validate_User();
		$nombre = trim($this->_request->getParam('nombre'));
		if($nombre == "") {
            $response["nombre"][] = "El campo Nombre es obligatorio";
        }
/*        elseif(!$validator->isValid($nombre)) {
            $response["nombre"][] = "El campo Nombre es invalido, debe tener entre 6 y 10 caracteres y solo puede contener caracteres alfanumericos y _";
        }
*/
		$user = trim($this->_request->getParam('user'));
        if($user == "") {
            $response["user"][] = "El campo Usuario es obligatorio";
        }
        elseif(!$validator->isValid($user)) {
            $response["user"][] = "El campo Usuario es invalido, solo puede contener caracteres alfanumericos y _";
        }
        elseif($this->usuarios->userExists($user)) {
            $response["user"][] = "El nombre de usuario ya se encuentra registrado por otra persona";
        }


        $pass = trim($this->_request->getParam('pass'));
        $repeatpass = trim($this->_request->getParam('repeatpass'));

//       include_once('../library/Validate/Password.php');
//        $validator = new Validate_Password();
        if($pass == "") {
            $response["pass"][] = "El campo Password es obligatorio";
        }
        elseif(!$validator->isValid($pass)) {
            $response["pass"][] = "El campo Password debe contener de 6 a 10 caracteres alfanumericos";
        }
        elseif($pass !== $repeatpass) {
           $response["pass"][] = "El campo Password/Repeat no coinciden";
        }

        $email = trim($this->_request->getParam('email'));
        $validator = new Zend_Validate_EmailAddress();
        if($email == "") {
            $response["email"][] = "El campo Email es obligatorio";
        }
        elseif(!$validator->isValid($email)) {
            $response["email"][] = "El campo Email es invalido";
        }
        elseif($this->usuarios->emailExists($email)) {
            $response["email"][] = "El email ya se encuentra registrado por otra persona";
        }

        if(sizeof($response)) {
             die(json_encode(
                array(
                    "error"=>true,
                    "message"=>$response
                )
            ));
        }

        $sexo = $this->_request->getParam('sexo');
        $sexo = $sexo == 'M' ? 'M' : 'F';
        $pais = (int) $this->_request->getParam("pais");
        $localidad = (int) $this->_request->getParam("localidad");
		$provincia = (int) $this->_request->getParam("provincia");
		$nacim 		= $this->_request->getParam("fecha_nacimiento");
		$apellido 	= $this->_request->getParam("apellido");

        $id = $this->usuarios->addUser(
            array(
				'nombre'   => $nombre,
				'apellido' => $apellido,
                'user'     => $user,
                'pass'     => md5($pass),
                'mail'     => $email,
                'sexo'     => $sexo,
				'fecha_nacimiento'     => $nacim,
                'pais'      => $pais,
				'provincia' => $provincia,
				'localidad' => $localidad
             )
        );

		$this->notificaciones->signupValidation($id);
// mandar mail de bienvenida


        die(json_encode(
                array(
                    "error"=>false,
                    "message"=>"El usuario se ha registrado con exito",
                    "user_id" => $id
                )
            )
        );

	}
    
}
