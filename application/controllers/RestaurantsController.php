<?php

require( APPLICATION_PATH . '/controllers/ExtensionFrontController.php' );

class RestaurantsController extends ExtensionFrontController
{
	private $model;

    public function init()
    {
        /* Initialize action controller here */
        $this->view->seccion  =   'restaurants';

        parent::init();

		$categorias = new Model_Categorias();
		$this->view->categorias = $categorias->fetchAll();

		//$this->busquedaAvanzada();
		//$this->mayoresContribuidores();
        
    }

    public function indexAction()
    {
		$locales = new Model_Locales();
		$search = $this->_request->getParam("search");

		$q = trim($this->_request->getParam("q"));
		if($q== "Busca un lugar") $q="";


		$cat = (int)$this->_request->getParam("cat");
		$k   = trim($this->_request->getParam("keywords"));

		$lat = (float) $this->_request->getParam("lat");
		if($lat) {
			$lat = (float) $this->_request->getParam("lat");
			$lng = (float) $this->_request->getParam("lng");
		}else {
			$lat = $lng = 0;
		}

		$this->view->q   = $q;
		$this->view->cat = $cat;

		$session = new Zend_Session_Namespace("usuario");
		$logged = (int) $session->user_id ? true : false;

		$metrics = new My_Mixpanel("147cf3e28614fd0afe0e77286906f8fe");
		$i = $metrics->track("search", array(
			"q" => $q,
			"cat" => $cat,
			"k" => $k,
			"logged" => $logged
		));

		if(preg_match("/colectividades/",$q)) {
			$limit = 35;
		}else {
			$limit = 30;
		}

		$patron = array(
			"q" => $q,
			"keywords" => $k,
			"lat" => $lat,
			"lng" => $lng,
			"categoria" => $cat,
			"itemsPerPage" => $limit,
			"page" => 0
		);


//		if($q != "" || $cat || $k != "") {
			
		$locales = $locales->getLocales($patron);
//			$locales = $locales['locales'];
//		}else {
//		
//			$locales = $locales->getHighlights();
//		} 

		$modelCategorias = new Model_Categorias();
		$modelo = new Model_CheckinEspecial();
		
		for($i=0; $i<sizeof($locales); $i++) {
			// busco las categorias
			$locales[$i]["categorias"] = $modelCategorias->getCategoriasByLocal($locales[$i]['id']);

			// busco los checkins especiales
			// 	
			$checkin = $modelo->hasCheckinEspecial($locales[$i]["id"]);
			if($checkin){
				$locales[$i]["has_checkinespecial"] = 1;
				$locales[$i]["desc_checkin"] = $modelo->getCheckinEspecialLongDesc($checkin, $locales[$i]);
			}	
		}

	
		$this->view->locales = $locales;

		if($q == "") {
		
			$this->view->titulo = "Listado de Restaurantes";
			$this->view->headTitle("Guia movil de bares y restaurantes de Rosario - Kuesty - Listado de Restaurantes");
		}else {
			
			$this->view->titulo = "Resultados de la busqueda: ".$q;
			$this->view->headTitle("Guia movil de bares y restaurantes de Rosario - Kuesty - Resultados de la busqueda: ".$q);
		}
    }

	public function avatarAction() {
	
		$id      = $this->_request->getParam("id");
		$model   = new Model_Locales();
		$local = $model->getLocalById($id);

		header('Content-type: image/png'); 

		if($this->_request->getParam("mobile")) {
			$path = $local["logo_mobile"];
		}else{
			$path = $local["logo"];
		}
		if($path == "") {
			$path = "http://kuesty.com/resources/images/no_image.png";
		}
		$this->_redirect($path);
//		$data = file_get_contents($path);		
//		die($data);
	}

	public function fansAction() {
	
		$id = $this->getRequest()->getParam('id');
		
		$locales = new Model_Locales();
		$local = $locales->getLocalById($id);

		$modelCategorias = new Model_Categorias();
		$local["categorias"] = $modelCategorias->getCategoriasByLocal( $local['id'] );


		$this->view->local = $local;

		$fans = $locales->getLocalFans($local["id"]);
		if(sizeof($fans)) {
			foreach($fans as $fan) {
				$ids[] = $fan["id_user"];
			}
			
			$this->usuarios = new Model_Usuarios();
			$fans = $this->usuarios->getUsersInArray($ids);

		}
		$this->view->fans = $fans;

		$tops = $locales->TopRated(8);
		$this->view->assign('tops',$tops);

		
		$comunidad = new Model_Comunidad();
		$status    = $comunidad->getLocalStatus($local['id']);

		$this->view->status = $status;

	}

    public function localAction(){
		

		$id = $this->getRequest()->getParam('id');

		$locales = new Model_Locales();
		$local = $locales->getLocalById($id);

		$this->view->facebookURL = $furl = $this->getRequest()->getScheme().'://'.$this->getRequest()->getHttpHost().$this->getRequest()->getRequestUri();

		$twitterMeta = '<meta name="twitter:card" content="summary">';
		$twitterMeta.= '<meta name="twitter:site" content="@kuestyapp">';
		$twitterMeta.= '<meta name="twitter:creator" content="@kuestyapp">';
		$twitterMeta.= '<meta name="twitter:url" content="http://kuesty.com/restaurants/local/id/'.$local["id"].'">';
	 	$twitterMeta.= '<meta name="twitter:title" content="'.$local["nombre"].'">';
		$twitterMeta.= '<meta name="twitter:image" content="'.$local["logo"].'">';

		$twitterMeta.= '<meta name="twitter:app:name:googleplay" content="Kuesty"/>';
		$twitterMeta.= '<meta name="twitter:app:id:googleplay" content="com.Kuesty"/>';

		$twitterMeta.= '<meta name="twitter:app:name:iphone" content="Kuesty"/>';
		$twitterMeta.= '<meta name="twitter:app:id:iphone" content="514762136"/>';
	
		$modelReviews = new Model_Reviews();
		$reviews = $modelReviews->getReviewsByLocal( $local['id'],10);
		$this->view->reviews = $reviews;

		$modelCategorias = new Model_Categorias();
		$local["categorias"] = $modelCategorias->getCategoriasByLocal( $local['id'] );
		$cats_ids = array();
		foreach($local["categorias"] as $cat) {
			$cats_ids[] = $cat["id"];
		}
		$cats_ids = implode(",", $cats_ids);

		$modelCheckins = new Model_Checkins();
		$tips = $modelCheckins->getCheckinsByLocal($local['id'], 0, 10);
		$this->view->tips = $tips;

		$comunidad = new Model_Comunidad();
		$status    = $comunidad->getLocalStatus($local['id']);
		$this->view->status = $status;

		$modelCS = new Model_CheckinEspecial();

		if((int)$this->_request->getParam("sid")) {
			$twitterMeta.= '<meta name="twitter:description" content="'.$status["post"].'">';

		}elseif((int) $this->_request->getParam("ceid")) { 

			$cespecial = $modelCS->getCheckinEspecialById($this->_request->getParam("ceid"));
			$dias = array(
				"1"=>"lunes", 
				"2"=>"martes", 
				"3"=>"miercoles", 
				"4"=>"jueves", 
				"5"=>"viernes", 
				"6"=>"sabados", 
				"7"=>"domingos" 
			);
			$cespecial_desc = "Todos los ".$dias[$cespecial["dia"]]." ";

			if(!$cespecial["sin_horario"]) { 
				$cespecial_desc .= $cespecial["hora_ini"]." a ".$cespecial["hora_fin"]; 
		   } 
			$cespecial_desc .= "haciendo checkin en ".$local["nombre"]." ".$cespecial["descripcion"];
			$cespecial["long_descripcion"] = $cespecial_desc;
			$twitterMeta.= '<meta name="twitter:description" content="'.$cespecial_desc.'">';
		}
		else {
			$twitterMeta.= '<meta name="twitter:description" content="'.$local["descripcion"].'">';
		}
		
		$this->view->twitterMeta = $twitterMeta;

		$fans = $locales->getLocalFans($local["id"], 4);
		if(sizeof($fans)) {
			foreach($fans as $fan) {
				$ids[] = $fan["id_user"];
			}
			shuffle($ids);
			
			$this->usuarios = new Model_Usuarios();

			if(sizeof($ids) <= 3) {
				$fans = $this->usuarios->getUsersInArray($ids);
			}else{
				$fans = $this->usuarios->getUsersInArray(array_slice($ids,0,3));
			}
		}
		$this->view->fans = $fans;

		$horarios = $locales->getHorariosLocal($local["id"]);
		
		if(sizeof($horarios)) {

			$day  = date("N") - 1;
		
			foreach($horarios as $horario) {
				if($day == $horario["dia"]) {
					$horarios_dia[] = $horario;
				}
			}
			if(!sizeof($horarios_dia)){

				$this->view->assign("open", 0);

			}else{	
				$is_open = My_Utils::localAbierto($horarios_dia);
				$this->view->assign("open", $is_open);	
			}
		}

		$this->view->assign("horarios", $horarios);

		// busco los extras de los locales
		$extras = $locales->getExtrasByLocal($local["id"]);
		$this->view->assign("extras", $extras);

		$this->view->local = $local;

		$locales = new Model_Locales();
		$tops = $locales->TopRated(8);
		$this->view->assign('tops',$tops);

		$this->view->headTitle("Guia movil de bares y restaurantes de Rosario - Kuesty - ". $local["nombre"]);

		$checkin = $modelCS->getCheckinEspecial($local["id"]);

		if($checkin && $checkin["estado"] == "A") {

			$checkin["times_unlocked"] = $modelCS->getCheckinEspecialTimesUnlocked($checkin["id"]);
			$this->view->assign("cespecial", $checkin);
		}

		$metrics = new My_Mixpanel("147cf3e28614fd0afe0e77286906f8fe");
		$i = $metrics->track("local", array(
			"id_local" => $id,
			"nombre_local" => $local["nombre"],
			"categorias" => $cats_ids, 
			"logged" => $logged
		));


		// TODO og del unlockpromo

		// facebook opengraph
		// if pid is set then use og:type kuestyapp:promo
		if($pid = $this->_request->getParam("ceid")) {

		
			$og["og:title"] = $local["nombre"];
			$og["og:type"]  = "kuestyapp:promo";
			$og["og:image"] = $local["logo"];
			$og["og:latitude"] = $local["latitude"];
			$og["og:longitude"] = $local["longitude"];
			$og["og:street-address"] = $local["direccion"];
			$og["og:description"] = $cespecial["long_descripcion"];
			$og["og:url"] = "http://kuesty.com/restaurants/local/id/".$local["id"]."/ceid/".$pid;
			$og["kuestyapp:restaurant"] = "http://kuesty.com/restaurants/local/id/".$local["id"];

		}
		// use og:type kuesty:restaurant
		else {
			$og["og:title"] = $local["nombre"];
			$og["og:type"]  = "kuestyapp:restaurant";
			$og["og:image"] = $local["logo"];
			$og["og:latitude"] = $local["latitude"];
			$og["og:longitude"] = $local["longitude"];
			$og["og:street-address"] = $local["direccion"];
			$og["og:description"] = $local["descripcion"];	
			$og["og:url"] = "http://kuesty.com/restaurants/local/id/".$local["id"];
		}

		$facebookMeta = "";
		foreach($og as $og_key => $og_value) {
			$facebookMeta .= '<meta property="'.$og_key.'" content="'.$og_value.'" />';
		}
		$facebookMeta.= '<meta property="og:description" content="'.$og["descripcion"].'" />';

		$this->view->facebookMeta = $facebookMeta;

		$session = new Zend_Session_Namespace("usuario");
		if($session->user_id) {
			$model = new Model_Fans();
			$userisfan = $model->userIsFan($session->user_id, $local["id"]);
			$this->view->assign("userisfan", $userisfan);
		}

		$rid = $this->_request->getParam("rid");
		if((int)$rid){
			$review = $modelReviews->getReview($rid);
			$this->view->assign("rid", $rid);
			$this->view->assign("review", $review[0]); 
		}
	}

	public function editAction(){

		$idLocal = $this->_request->getParam("id");
		$model = new Model_Locales();
		$local = $model->getLocalById($idLocal);

		$keywords = $local["keywords"];
		$keywords = preg_replace("/\s+/", ",", $keywords);
		$local["keywords"] = $keywords;

		foreach( $local as $key => $val) {
			$local[$key] = My_HTMLDecoder::decode($val);
		}	

		// si el local no existe
		if(sizeof($local) == 0){
			$this->_redirect("/restaurants");
		}

		//valido si este user tiene permiso para editarlo
		$idUser = $this->session_user["id"];
		if(!$local["id_user_propietario"] == $idUser){
			$this->_redirect("/restaurants/local/id/".$idLocal);
		}

		$this->view->headScript()->appendScript("$(document).ready(function(){ $('#keywords').tagsInput({defaultText:'Agregar',maxChars:254}); });");

		$form = new Form_Local();

		$categorias = new Model_Categorias();
		$categorias = $categorias->getCategoriasxLocal($idLocal);

		for($i=0; $i < sizeof($categorias); $i++) {
			$cats[] = $categorias[$i]["id_categoria"];
		}

		$extras = $model->getExtrasByLocal($local["id"]);

		for($i=0; $i < sizeof($extras); $i++) {
			
			$exts[$extras[$i]["id"]] = $extras[$i]["nombre"];
			if($extras[$i]["yesno"] == 1){
				$vals[] = $extras[$i]["id"];
			}
		}
		$form->getElement("extras")->setMultiOptions($exts);

		$horarios = $model->getHorariosLocal($local["id"]);
		
		//    	$this->view->assign('form',$form);

		if(!$this->_request->ispost()) {

			$form->setDefaults($local);

			$form->getElement("categorias")->setValue($cats);
			$form->getElement("horarios")->setValue($horarios);
			$form->getElement("extras")->setValue($vals);
		
			$this->view->form = $form;
			return;
		}

		if(!$form->isValid($this->getRequest()->getPost())) {

			$this->view->form = $form;
			return;
		}
		
		// guardo los datos del local
		$id	= $form->getValue('id');
		$values = $form->getValues();

		$cats = $this->_request->getPost("categorias");

		$categorias = $form->getElement("categorias")->getValue();	
		$form->removeElement("categorias");
		unset( $values['categorias'] );

		$extras = $form->getElement("extras")->getValue();
		$form->removeElement("extras");
		unset($values["extras"]);

		unset( $values['id'] );

		$horarios = $_POST["horarios"];
		$form->removeElement("horarios");
		unset($values["horarios"]);

		foreach( $values as $key => $val) {
			$values[$key] = My_HTMLDecoder::encode($val);
		}
		
		if($_FILES ){

			$fileName = key( $_FILES );
			$fileInfo = $form->getElement( $fileName )->getFileInfo();

			$fileInfo = $fileInfo[ "$fileName" ];

			if($fileInfo['error'] == 0 ){

				$ext	=	strtolower( substr( $fileInfo['name'], strrpos( $fileInfo['name'], '.') + 1 ) );
				if ( in_array( $ext, array( 'jpeg','jpg','png','gif' ) ) )
					$ext = 'png';

				//genero el directorio
				mkdir($fileInfo['destination']."/".$id."/",0777,true);

				$nombre_archivo = "/thumb_" . $id . "_o.$ext";
				$form->getElement( "$fileName" )->addFilter( 'Rename',   array('target' => $fileInfo['destination'] . '/'.$id. '/thumb_' . $id . "_o.$ext", 'overwrite' => true));
				$form->getElement( "$fileName" )->receive();

				chmod("./resources/locales/".$id.$nombre_archivo,0777);

				//genero una imagen grande
				$this->redimensionar_imagen("./resources/locales/".$id.$nombre_archivo,"./resources/locales/".$id."/",500,"thumb_".$id."_l.png");
				//genero una imagen mediana
				$this->redimensionar_imagen("./resources/locales/".$id.$nombre_archivo,"./resources/locales/".$id."/",300,"thumb_".$id."_m.png");

				//genero una imagen chica
				$this->redimensionar_imagen("./resources/locales/".$id.$nombre_archivo,"./resources/locales/".$id."/",130,"thumb_".$id."_s.png");

			} 

			$values["logo"] = "http://www.kuesty.com/resources/locales/".$id."/thumb_".$id."_o.png";
			$values["logo_mobile"] = "http://www.kuesty.com/resources/locales/".$id."/thumb_".$id."_s.png";
		}
		
		unset($values["imagen"]);

		$model->actualizarLocal($values, $id, $extras, $categorias, $horarios);

		$this->_redirect("/restaurants/local/id/".$id);

	}

	public function uploadPicture() {
	
		$idLocal = $this->_request->getParam("id");
		$model = new Model_Locales();
		$local = $model->getLocalById($idLocal);

		// si el local no existe
		if(sizeof($local) == 0){
			die(json_encode(array("error"=>1,"message"=>"Wops! Ha ocurrido un error al intentar subir la foto! Intenta nuevamente en unos segudnso")));
			$this->_redirect("/restaurants");
		}

		$idUser = $this->session_user["id"];
		if(!$local["id_user_propietario"] == $idUser){
			die(json_encode(array("error"=>1,"message"=>"Wops! Ha ocurrido un error al intentar subir la foto! Intenta nuevamente en unos segudnso")));
		}

		$imgID = $this->_request->getParam("iid");

		if(!sizeof($_FILES)) {
		
			
		}

	}



	public function addcheckinAction() {
	
		$idLocal = $this->_request->getParam("id");
		$model = new Model_Locales();
		$local = $model->getLocalById($idLocal);

		// si el local no existe
		if(sizeof($local) == 0){
			$this->_redirect("/restaurants");
		}

		$this->view->assign("local",$local);
		//valido si este user tiene permiso para editarlo
		$idUser = $this->session_user["id"];
		if(!$local["id_user_propietario"] == $idUser){
			$this->_redirect("/restaurants/local/id/".$idLocal);
		}

		$modelCheckin = new Model_CheckinEspecial();
		$checkin = $modelCheckin->getCheckinEspecial($idLocal);
		
		if($checkin) {
			
			$checkin["fecha_ini"] = date("d-m-Y", strtotime($checkin["fecha_ini"]));
			$checkin["fecha_fin"] = date("d-m-Y", strtotime($checkin["fecha_fin"]));
			$this->view->assign("checkin",$checkin);

			$this->view->assign("ocheckin", $checkin);
		}


		if(!$this->_request->isPost()){
			return;
		}

		$dia = (int) $this->_request->getPost("dia");
		// valido el dia
		if($dia < 0 || $dia > 7) {
			$error[] = "Ha ocurrido un error al tomar el dia del checkin especial, por favor complete el formulario nuevamente";
		}else {
			$checkin["dia"] = $dia;
		}

		$sin_hora = $this->_request->getPost("sin_hora");
		$sin_hora = $sin_hora == "on" ? true : false;
		$checkin["sin_horario"] = (int) $sin_hora;

		if(!$sin_hora) {

			// valido la hora de inicio del checkin especial
			$hora_ini = $this->_request->getPost("hora_ini");
			$d = strtotime($hora_ini);
			if($d) {
				$checkin["hora_ini"] = $hora_ini;
			}else {
				$error[] = "Ha ocurrido un error al tomar la hora inicial del checkin especial, por favor complete el formulario nuevamente (Hora ini)";
			}
			
			// valido la hora de fin del checkin especial
			$hora_fin = $this->_request->getPost("hora_fin");
			$d1 = strtotime($hora_fin);
			if($d1) {
				$checkin["hora_fin"] = $hora_fin;
			}else {
				$error[] = "Ha ocurrido un error al tomar la hora final del checkin especial, por favor complete el formulario nuevamente (Hora fin)";
			}

			// valido q la hora inicial sea menor a la hora final del checkin
			if($d >= $d1) {
				$error[] = "La hora final del checkin especial debe ser mayor a la hora inicial";
			}	

		}

		// valido que la descripcion del descuento no sea nula
		$descrip = $this->_request->getPost("descripcion");
		if(trim($descrip) == "") {
			$error[] = "La descripcion de la promo o descuento ha quedado vacia, por favor complete el formulario nuevamente";
		}
		$checkin["descripcion"] = $descrip;

		// valido las fechas
		$fecha_ini = $this->_request->getPost("fecha_ini");
		$f_ini = strtotime($fecha_ini);
		if(!$f_ini) {
			$error[] = "La fecha inicial del checkin especial es incorrecta, por favor complete el formulario nuevamente";
		}
		$checkin["fecha_ini"] = $fecha_ini;
	

		$fecha_fin = $this->_request->getPost("fecha_fin");
		$f_fin = strtotime($fecha_fin);
		if(!$f_fin) {
			$error[] = "La fecha de finalizacion del checkin especial es incorrecta, por favor complete el formulario nuevamente";
		}
		
		$checkin["fecha_fin"] = $fecha_fin;
				
		if($f_ini > $f_fin) {
			$error[] = "La fecha de inicio del checkin especial debe ser mayor a la fecha de finalizacion, por favor complete el formulario nuevamente";
		}

		$terms = $this->_request->getPost("terminos");
		$terms = $terms == "on" ? true : false;
		if(!$terms) {
			$error[] = "Debes aceptar los terminos y condiciones impuesto por Kuesty";
		}

		$this->view->assign("checkin",$checkin);

		if(sizeof($error)) {

			// var_dump($error);
			$this->view->assign("error", $error);
			return;

		}

		$checkin["fecha_ini"] = date("Y-m-d", strtotime($checkin["fecha_ini"]));
		$checkin["fecha_fin"] = date("Y-m-d", strtotime($checkin["fecha_fin"]));
		$checkin["estado"]    = "P";
		unset($checkin["id"]);

		$id = $modelCheckin->addCheckinEspecial($local["id"], $checkin);
		if($id) {
			$this->view->assign("checkin", $checkin);
			$this->view->assign("checkin_created", 1);
		}else {
			$error[] = "Ha ocurrido un error al intentar guardar el checkin especial, por favor intente nuevamente";
			$this->view->assign("error", $error);
		}
	}


	public function activateAction() {
		
		$this->_helper->layout->disableLayout();
		
		$session = new Zend_Session_Namespace("usuario");

		if(!$session->user_id) {
			$this->view->assign("logged",false);
			return;
		}

		$local_id = $this->_request->getParam("id");

		$modelLocales = new Model_Locales();
		$local = $modelLocales->getLocalById($local_id);
		$this->view->assign("local", $local);

		$modelUsuarios = new Model_Usuarios();
		$usuario = $modelUsuarios->getUserById($session->user_id);
		$this->view->assign("usuario",$usuario[0]);

		$recaptcha = new Zend_Service_ReCaptcha("6Lcol88SAAAAAB5Hs_2Z7jozhijhK6wb_20pu0m3","6Lcol88SAAAAAA-rXsSiQNse3y6sMD0zBlhz82Hk");
		$this->view->assign("recaptcha", $recaptcha->getHTML());

		if(!$this->_request->ispost()) {
			return;
		}

		try {
			$result = $recaptcha->verify(
			  	$_POST['recaptcha_challenge_field'],
		    	$_POST['recaptcha_response_field']
			);
		}catch(Exception $e) {
			$this->view->assign("error", "El codigo de verificaci&oacute;n es incorrecto");
			return;
		}
		if (!$result->isValid()) {
			$this->view->assign("error", "El codigo de verificaci&oacute;n es incorrecto");
			return;
		}

		$key = $this->_request->getPost("activation_key");
		$key = strtolower($key);
		$key = str_replace("-","", $key);
		
		if($local["activation_key"]!=$key) {
			$this->view->assign("error", "El codigo de activacion incorrecto");
			return;
		}

		$modelLocales->updateUserPropietario($local["id"], $key, $usuario[0]["id"]);
		$this->view->assign("changed", 1);
	}

}
