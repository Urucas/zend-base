<?php

require( APPLICATION_PATH . '/controllers/ExtensionFrontController.php' );

class ComunidadController extends ExtensionFrontController
{
	private $model;

    public function init()
    {
        /* Initialize action controller here */
        $this->view->seccion  =   'comunidad';

		$this->comunidad = new Model_Comunidad();

		$this->session   = new Zend_Session_Namespace("usuario");
        parent::init();

		$categorias = new Model_Categorias();
		$this->view->categorias = $categorias->fetchAll();

		
		$this->view->headTitle("Kuesty - Comunidad");
	}

    public function indexAction()
	{
		$filter  = $this->_request->getParam("filter");
		$page    = $this->_request->getParam("page") ? $this->_request->getParam("page") : 1 ;
		
		$filters = array("all", "nearby", "imfan", "friends", "me", "local","hoy","promos","people");
		$key     = array_search($filter, $filters); 
		$filter  = $key == -1 ? $filters[0] : $filters[$key];
		
		$this->view->assign("filter", $filter);

		$comunidad = $this->{"get".$filter}($page);

		$this->view->assign("comunidad", $comunidad);

		if($this->session->user_id){
			$metrics = new My_Mixpanel("147cf3e28614fd0afe0e77286906f8fe");
			$i = $metrics->track("comunidad", array(
				"filter" => $filter,
				"logged"=>1
			));
		}else{
			$metrics = new My_Mixpanel("147cf3e28614fd0afe0e77286906f8fe");
			$i = $metrics->track("comunidad", array(
				"filter" => $filter,
				"logged"=>0
			));
		}

 
		if($this->_request->getParam("ajax")){
			$html = $this->view->render("/comunidad/evento.phtml");
			die(json_encode(array("html"=>$html, "len"=>sizeof($comunidad))));
		}
		
	}

	protected function getcolectividades($page) {
	
		return $this->comunidad->getColectividadesEvents($page);
	}

	protected function getpeople($page) {
		return $this->comunidad->getPeoplesOpinion($page);
	}

	protected function getall($page) {
	
		return $this->comunidad->obtenerTodos($page);
	}

	protected function getnearby($page) {

		return $this->comunidad->obtenerTodos($page);
	}

	protected function getlocal($page) {
	
		return $this->comunidad->getLocalEvents($page);
	}
	
	protected function gethoy($page) {
	
		return $this->comunidad->getLocalTodayEvents($page);
	}

	protected function getpromos($page) {
	
		return $this->comunidad->getLocalesWithPromos($page);	
	}

	protected function getfriends($page) {

		$user_id = $this->session->user_id;
		if(!$user_id) {
			$this->view->assign("error", "Debes estar logueado para ver esta seccion");
			return false;
		}
		$model_friends  = new Model_Friends();
	   	$friends   = $model_friends->getFriendsAsIdArray($user_id);	

		$this->view->assign("mid", $user_id);
		$model =  new Model_Comunidad();
		return $model->getMyFriendsEvents($friends,$page);

	}

	protected function getimfan($page) {

		$user_id = $this->session->user_id;
		if(!$user_id) {
			$this->view->assign("error", "Debes estar logueado para ver esta seccion");
			return false;
		}

		$fans     = new Model_Fans();
		$locales  = $fans->getIdLocalUserIsFanAsArray($user_id);	

		$model_friends  = new Model_Friends();
	   	$friends   = $model_friends->getFriendsAsIdArray($user_id);	

		$model =  new Model_Comunidad();
		return $model->getUserIsFanEvents($user_id, $locales, $friends,$page);
	}

	public function getme($page) {
		
		$user_id = $this->session->user_id;
		if(!$user_id) {
			$this->view->assign("error", "Debes estar logueado para ver esta seccion");
			return false;
		}
		$this->view->assign("mid", $user_id);
		$model =  new Model_Comunidad();
		return $model->getMeEvents($user_id,$page);

	}

	public function fixAction() {
	
		$model = new Model_Comunidad();
		$comunidad = $model->fetchAll();

		$locales  = new Model_Locales();
		$usuarios = new Model_Usuarios();

		foreach($comunidad as $evento) {

			/*
			if($evento["tipo"] == "friendship") {
				
				$e = preg_match_all("/kuesty\.loadFriend\(\d+\)/", $evento["mensaje_web"], $matches);
				if(!$e) continue;

				preg_match("/\d+/", $matches[0][0],  $user_id);
				$user_id = $user_id[0];
				if(!(int)$user_id) continue;

				preg_match("/\d+/", $matches[0][1],  $friend_id);
				$friend_id = $friend_id[0];
				if(!(int)$friend_id) continue;

				$usuario = $usuarios->getUserById($user_id);
				$friend  = $usuarios->getUserById($friend_id);

				if(!$friend || !$usuario) {

					$model->delete("id = ".$evento["id"]);
					continue;
				}

				$usuario = $usuario[0];
				$friend  = $friend[0];
				/*
				$mensaje_web = '<a class="user_logo" onclick="kuesty.loadFriend('.$usuario["id"].')"><img width="75px" src="/user/avatar/id/'.$usuario["id"].'" /></a><a onclick="kuesty.loadFriend('.$usuario["id"].')">'.$usuario["user"].'</a> ahora es amigo de <a onclick="kuesty.loadFriend('.$friend["id"].')">'.$friend["user"].'</a><br /><a class="friend_logo" onclick="kuesty.loadFriend('.$friend["id"].')"><img width="100px" src="/user/avatar/id/'.$friend["id"].'" /></a>';

			//	echo $mensaje_web; 
			//	continue;
				 */
				/*
				var_dump(array(
					"mensaje_web" => "",
					"nom_user"    => $usuario["user"],
					"id_friend"   => $friend["id"],
					"nom_friend"  => $friend["user"],
				));
				 *
				$model->update(array(
					"mensaje_web" => "",
					"nom_user"    => $usuario["user"],
					"id_friend"   => $friend["id"],
					"nom_friend"  => $friend["user"],
				), "id = ".$evento["id"]);
				 
				continue;
			}
			 */
			/*
			
			if($evento["tipo"] == "newlocalbyuser") {

				$e = preg_match("/kuesty\.loadFriend\(\d+\)/", $evento["mensaje_web"], $matches);
				if(!$e) continue;


				preg_match("/\d+/", $matches[0],  $user_id);
				$user_id = $user_id[0];
				if(!(int)$user_id) continue;

				$e = preg_match("/kuesty\.loadLocal\(\d+\)/", $evento["mensaje_web"], $matches);
				if(!$e) continue;
			
				preg_match("/\d+/", $matches[0],  $local_id);
				$local_id = $local_id[0];
				if(!(int)$local_id) continue;

				$local   = $locales->getLocalById($local_id);
				$usuario = $usuarios->getUserById($user_id);

				if(!$local || !$usuario) {

					$model->delete("id = ".$evento["id"]);
					continue;
				}

				$usuario = $usuario[0];

				$mensaje_web = '<a class="user_logo" onclick="kuesty.loadFriend('.$usuario["id"].')"><img width="75px" src="/user/avatar/id/'.$usuario["id"].'" /></a><a onclick="kuesty.loadFriend('.$usuario["id"].')">'.$usuario["user"].'</a> ha agregado el local <a onclick="kuesty.loadLocal('.$local["id"].')">'.$local["nombre"].'</a> a kuesty<br /><a class="local_logo" onclick="kuesty.loadLocal('.$local["id"].')"><img width="200px" src="/restaurants/avatar/id/'.$local["id"].'" /></a>';

				// echo $evento["mensaje_web"];
				// echo $mensaje_web;
				$model->update(array(
					"mensaje_web" => $mensaje_web,
					"id_local"    => $local["id"]
				), "id = ".$evento["id"]);
			
			}
			 
			
			if($evento["tipo"] == "newuser") {
			
				$e = preg_match("/kuesty\.loadFriend\(\d+\)/", $evento["mensaje_web"], $matches);
				if(!$e) continue;

				preg_match("/\d+/", $matches[0], $user_id);
				$user_id = $user_id[0];
				if(!(int)$user_id) continue;

				$usuario = $usuarios->getUserById($user_id);

				if(!$usuario) {	
					$model->delete("id = ".$evento["id"]);
					continue;
				}

				$usuario = $usuario[0];

				$mensaje_web = '<a onclick="kuesty.loadFriend('.$usuario["id"].')">'.$usuario["user"].'</a> se ha unido a kuesty<br /><a class="friend_logo" onclick="kuesty.loadLocal('.$usuario["id"].')"><img width="100px" src="/user/avatar/id/'.$usuario["id"].'" /></a>';

				$model->update(array(
					"mensaje_web" => $mensaje_web,
				), "id = ".$evento["id"]);

			}
			 

			
			if($evento["tipo"] == "newfan"){
			
				$e = preg_match("/kuesty\.loadFriend\(\d+\)/", $evento["mensaje_web"], $matches);
				if(!$e) continue;

				preg_match("/\d+/", $matches[0],  $user_id);
				$user_id = $user_id[0];
				if(!(int)$user_id) continue;


				$e = preg_match("/kuesty\.loadLocal\(\d+\)/", $evento["mensaje_web"], $matches);
				if(!$e) continue;
			
				preg_match("/\d+/", $matches[0],  $local_id);
				$local_id = $local_id[0];
				if(!(int)$local_id) continue;

				$local   = $locales->getLocalById($local_id);
				$usuario = $usuarios->getUserById($user_id);

				if(!$local || !$usuario) {
					$model->delete("id = ".$evento["id"]);
					continue;
				}
				$usuario = $usuario[0];

				$mensaje_web = '<a class="user_logo" onclick="kuesty.loadFriend('.$usuario["id"].')">';
				$mensaje_web.= '<img width="75px" src="/user/avatar/id/'.$usuario["id"].'" />';
				$mensaje_web.= '</a>';
				$mensaje_web.= '<a onclick="kuesty.loadFriend('.$usuario["id"].')">'.$usuario["user"].'</a>';
				$mensaje_web.= ' se ha hecho fan de ';
				$mensaje_web.= '<a onclick="kuesty.loadLocal('.$local["id"].')">'.$local["nombre"].'</a><br />';
				$mensaje_web.= '<a class="local_logo" onclick="kuesty.loadLocal('.$local["id"].')">';
				$mensaje_web.= '<img width="200px" src="/restaurants/avatar/id/'.$local["id"].'" /></a>';

				// echo $evento["mensaje_web"];
				// echo $mensaje_web;
				$model->update(array(
					"mensaje_web" => $mensaje_web,
					"id_local"    => $local["id"]
				), "id = ".$evento["id"]);
					
			}
			 

			
			if($evento["tipo"] == "newcheckin")	{

				$e = preg_match("/kuesty\.loadFriend\(\d+\)/", $evento["mensaje_web"], $matches);
				if(!$e) continue;

				preg_match("/\d+/", $matches[0],  $user_id);
				$user_id = $user_id[0];
				if(!(int)$user_id) continue;


				$e = preg_match("/kuesty\.loadLocal\(\d+\)/", $evento["mensaje_web"], $matches);
				if(!$e) continue;
			
				preg_match("/\d+/", $matches[0],  $local_id);
				$local_id = $local_id[0];
				if(!(int)$local_id) continue;

				$local   = $locales->getLocalById($local_id);
				$usuario = $usuarios->getUserById($user_id);

				if(!$local || !$usuario) {
				
					$model->delete("id = ".$evento["id"]);
					continue;
				}

				$usuario = $usuario[0];

				$mensaje_web = '<a class="user_logo" onclick="kuesty.loadFriend('.$usuario["id"].')"><img width="75px" src="/user/avatar/id/'.$usuario["id"].'" /></a><a onclick="kuesty.loadFriend('.$usuario["id"].')">'.$usuario["user"].'</a> ha hecho checkin en <a onclick="kuesty.loadLocal('.$local["id"].')">'.$local["nombre"].'</a><br /><a class="local_logo" onclick="kuesty.loadLocal('.$local["id"].')"><img width="200px" src="/restaurants/avatar/id/'.$local["id"].'" /></a>';

				// echo $evento["mensaje_web"];
				// echo $mensaje_web;
				$model->update(array(
					"mensaje_web" => $mensaje_web,
					"id_local"    => $local["id"]
				), "id = ".$evento["id"]);
					
			} 
			 */
			
		}
		die();
	
	}

}
