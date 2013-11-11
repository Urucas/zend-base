<?php

require( APPLICATION_PATH . '/controllers/ComunidadController.php' );

class FacebookappController extends ComunidadController
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
		//$this->mayoresContribuidores()
		//;
		//
		
		$session = new Zend_Session_Namespace("usuario");

		if($session->user_id) {
			$model   = new Model_Usuarios();
			$usuario = $model->getUserById($session->user_id);
			$this->view->usuario = $usuario[0];
		}

		/*
		require_once("../library/Facebook/src/facebook.php");
	
		$facebook = new Facebook(array(
			'appId'  => '106941089437708',
			'secret' => 'fdbf44db3c5f8d9726d592472ad4cf9d',
		));
		
		$user = $facebook->getUser();
		$likes = $facebook->api("/$user/likes/145597152184192");
		$this->view->fb_like = (int) sizeof($likes);
		 */
    }

	public function phiphiAction() {
	
		$this->_helper->layout->disableLayout();

		require_once("../library/Facebook/src/facebook.php");
	
		$facebook = new Facebook(array(
			'appId'  => '537035679675226',
			'secret' => 'eeb0a634699dbef9015ef60d161b7d88',
		));
		
		$error = array();
	
		$t = $facebook->getLoginUrl(array('scope'=>"publish_stream, status_update, email, user_likes"));
		$this->view->assign("fb_login",$t);

		$fb_access_token = $this->_request->getParam("code");

		if(!isset($fb_access_token)) {
			return;
		}	
		
		$user = $facebook->getUser();
		
		$likes = $facebook->api("/$user/likes/173860265966926");
		if(!sizeof($likes["data"])) {
			$error[] = "Quieres participar del sorteo ? Dale Me gusta a Phi Phi Beach - Parador Viejo Parana!!!";
		}

		$likes = $facebook->api("/$user/likes/145597152184192");
		if(!sizeof($likes["data"])) {
			$error[] = "Quieres participar del sorteo ? Dale Me gusta a Kuesty!!!";
		}

		if(sizeof($error)) {
			$this->view->assign("error",$error);
			return;		
		}
		
		try {
		$data = array(
			'link' => 'http://kuesty.com/facebookapp/phiphi/?r='.mt_rand(),
			'picture' => 'http://kuesty.com/resources/images/promo-phiphi.fw.png',
			'message' => 'Despedí el verano en Phi Phi Beach, Kuesty te safa de pagar la pizza!!! Participá en el sorteo y este finde despedí el verano en Phi Phi Beach, junto al rio y una pizza de rúcula y 4 latas de warsteiner!!!',
			'descripction' => 'Participa del Sorteo Kuesty + Phi Phi Beach! Y esta finde safa de pagar la pizza!!!',
		);
		$id = $facebook->api("/me/feed/", "POST", $data);

		$u = $facebook->api("/me/");
		$table = new Zend_Db_Table("sorteo-phiphibeach");
		$table->insert(array(
			"fb_id" => $u["id"],
			"fb_name" => $u["name"],
			"fb_email" => $u["email"]
		));	

		$this->view->assign("fbpostid", 1);
		$this->view->assign("user", $u);

		}catch(Exception $e) {
			
			$error[] = "Ha ocurrido un error al anotarte en el sorteo! Intentalo nuevamente!";
			// var_dump($e);
			$this->view->assign("error",$error);	
		}
 	
	
	}

	public function milochoveinteAction() {

		$this->_helper->layout->disableLayout();

		require_once("../library/Facebook/src/facebook.php");
	
		$facebook = new Facebook(array(
			'appId'  => '537035679675226',
			'secret' => 'eeb0a634699dbef9015ef60d161b7d88',
		));
		
		$error = array();
	
		$t = $facebook->getLoginUrl(array('scope'=>"publish_stream, status_update, email, user_likes"));
		$this->view->assign("fb_login",$t);

		$fb_access_token = $this->_request->getParam("code");

		if(!isset($fb_access_token)) {
			return;
		}	
		
		$user = $facebook->getUser();
		
		$likes = $facebook->api("/$user/likes/175799812448749");
		if(!sizeof($likes["data"])) {
			$error[] = "Quieres participar del sorteo ? Dale Me gusta a Milochoveinte!!!";
		}

		$likes = $facebook->api("/$user/likes/145597152184192");
		if(!sizeof($likes["data"])) {
			$error[] = "Quieres participar del sorteo ? Dale Me gusta a Kuesty!!!";
		}

		if(sizeof($error)) {
			$this->view->assign("error",$error);
			return;		
		}
		
		try {
		$data = array(
			'link' => 'http://kuesty.com/facebookapp/milochoveinte/',
			'picture' => 'http://kuesty.com/resources/images/milochoveinte.png',
			'message' => 'Estoy participando en el sorteo de Kuesty + Milochoveinte!!! Participa vos tambien!!!',
			'descripction' => 'Queres ganarte un cool boxer Milochoveinte, participa en el sorteo de Kuesty + Milochoveinte! Es muy facil!',
		);
		// $id = $facebook->api("/me/feed/", "POST", $data);

		$u = $facebook->api("/me/");
		$table = new Zend_Db_Table("sorteo-milochoveinte");
		$table->insert(array(
			"fb_id" => $u["id"],
			"fb_name" => $u["name"],
			"fb_email" => $u["email"]
		));	

		$this->view->assign("fbpostid", 1);
		$this->view->assign("user", $u);

		}catch(Exception $e) {
			
			$error[] = "Ha ocurrido un error al anotarte en el sorteo! Intentalo nuevamente!";
			var_dump($e);
			$this->view->assign("error",$error);	
		}
 		
	}

	public function createmobilepassAction() {

		$session_id = Zend_Session::getId();
		$session = new Zend_Session_Namespace("usuario");
		$this->view->assign("username", $session->user);

		$this->_helper->layout->disableLayout();

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

		$redirect = $referer->url;
		$referer->url = "";

		if($referer->url != "") {
			$this->_redirect($redirect);			
		}else {
			$this->_redirect("/facebookapp/destacados/");
		}
		
	}

    public function destacadosAction()
	{

		
		$referer = new Zend_Session_Namespace("referer");
		$referer->url = "/facebookapp/destacados/";			


		$this->_helper->layout->disableLayout();

		$locales = new Model_Locales();

		$q = trim($this->_request->getParam("q"));
		if($q== "Busca un lugar") $q="";

		$cat = (int)$this->_request->getParam("cat");
		$k   = trim($this->_request->getParam("keywords"));

		$this->view->q   = $q;
		$this->view->cat = $cat;

		$patron = array(
			"patron" => $q,
			"keywords" => $k,
			"itemsPerPage" => 20,
			"page" => 0
		);
		if($cat) {
			$patron['categorias'] = $cat;
		}

		if($q != "" || $cat || $k != "") {
			
			$locales = $locales->searchLocales($patron);
			$locales = $locales['locales'];
		}else {
		
			$locales = $locales->getHighlights();
		} 

		$modelCategorias = new Model_Categorias();
		for($i=0; $i<sizeof($locales); $i++) {
			$locales[$i]["categorias"] = $modelCategorias->getCategoriasByLocal( $locales[$i]['id']);
		}

		$this->view->locales = $locales;

		if($q == "" && !$cat) {
		
			$this->view->titulo = "Listado de Restaurantes";
			$this->view->headTitle("Kuesty - Listado de Restaurantes");
		}else {

			$cats = $this->view->categorias;
			foreach($cats as $ct) {
				if($ct["id"] == $cat) {
					$cat_dsc = $ct["nombre"];	
				}
			}

			$this->view->titulo = "Resultados de la busqueda: ".$q. " - ".$cat_dsc;
			$this->view->headTitle("Kuesty - Resultados de la busqueda: ".$q);
		}

	}

	public function calificacionesAction() {
	
		$this->_helper->layout->disableLayout();

		$page_id = $this->_request->getParam("page_id");
		
		$modelLocales = new Model_Locales();
		$local = $modelLocales->getLocalByFBID($page_id);
		$local = $local[0];

		$modelCategorias = new Model_Categorias();
		$local["categorias"] = $modelCategorias->getCategoriasByLocal( $local['id'] );

		$modelCheckins = new Model_Checkins();
		$tips = $modelCheckins->getCheckinsByLocal($local['id'], 0, 10);
		$this->view->tips = $tips;

		$modelReviews = new Model_Reviews();
		$reviews = $modelReviews->getReviewsByLocal( $local['id'],10);
		$this->view->reviews = $reviews;

		$this->view->assign("local", $local);
	
	}

	public function comunidadAction() {

		$referer = new Zend_Session_Namespace("referer");
		$referer->url = "/facebookapp/comunidad/";			

		$this->_helper->layout->disableLayout();
	
		$filter  = $this->_request->getParam("filter");
		
		$filters = array("all", "nearby", "imfan", "friends", "me", "local","hoy");
		$key     = array_search($filter, $filters); 
		$filter  = $key == -1 ? $filters[6] : $filters[$key];

		$this->view->assign("filter", $filter);

		$page = (int)$this->_request->getParam("page") ? (int)$this->_request->getParam("page") : 1;

		$comunidad = $this->{"get".$filter}($page);

		$this->view->assign("comunidad", $comunidad);

	}

	protected function gethoy($page) {
	
		return $this->comunidad->getLocalTodayEvents($page);
	}


	public function mobileAction() {
	
		$this->_helper->layout->disableLayout();
	}

}
