<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Comunidad extends Model_Db {
	
	protected $_name = 'comunidad';
	protected $_primary = 'id';
    protected $_order	= 'fecha';

	public function newLocalByUser($local, $usuario) {

		$mensaje     = "<button onclick=\"Friend.load(".$usuario->id.")\">".$usuario->user."</button> ha agregado el local <button onclick=\"Local.load(".$local['id'].")\">".$local['nombre']." a kuesty</button>";

		$mensaje_web = '<a class="user_logo" onclick="kuesty.loadFriend('.$usuario->id.')"><img width="75px" src="/user/avatar/id/'.$usuario->id.'" /></a><a onclick="kuesty.loadFriend('.$usuario->id.')">'.$usuario->user.'</a> ha agregado el local <a onclick="kuesty.loadLocal('.$local["id"].')">'.$local["nombre"].'</a> a kuesty<br /><a class="local_logo" onclick="kuesty.loadLocal('.$local["id"].')"><img width="200px" src="/restaurants/avatar/id/'.$local["id"].'" /></a>';
	//	$mensaje_web = "<a onclick=\"kuesty.loadFriend(".$usuario->id.")\">".$usuario->user."</a> ha agregado el local <a onclick=\"kuesty.loadLocal(".$local['id'].")\">".$local['nombre']." a kuesty</a>";

		return $this->insert(array(
			'id_user' => $usuario->id,
		   	'nom_user' => $usuario->user,
			'id_local' => $local["id"],	
			'nom_local' => $local["nombre"],	
			'latitud' => $local["latitud"],
			'longitud' => $local["longitud"],
			'mensaje' => $mensaje,
			'tipo' => 'newlocalbyuser',
			'mensaje_web' => $mensaje_web
		));
	}

	public function addLocalStatus($local, $status) {

		return $this->insert(array(
			"id_local"  => $local["id"],
			"nom_local" => $local["nombre"],
			"post"      => trim($status),
			"latitud"   => $local["latitud"],
			"longitud"  => $local["longitud"],
			"tipo"      => "newlocalstatus"
		));
	}

	public function getLocalStatus($local) {
	
		$sql    = $this->select()->where("id_local = ".(int)$local." AND tipo = 'newlocalstatus'")->order("fecha DESC")->limit(1);
		$result = $this->fetchAll($sql)->toArray();
		return sizeof($result) ? $result[0] : false;
	}

	public function newLocal($local) {

		// falta estilizar
		$mensaje     = "<button onclick=\"Local.load(".$local['id'].")\">El local ".$local['nombre']." se ha unido a kuesty</button>";
		$mensaje_web = "<a onclick=\"kuesty.loadLocal(".$local['id'].")\">El local ".$local['nombre']." se ha unido a kuesty</a>";
		return $this->insert(array(
			'id_user' => 0,
			'mensaje' => $mensaje,
			'mensaje_web' => $mensaje_web
		));
	}

	public function newUser($usuario) {

		$mensaje     = "<button onclick=\"Friend.load(".$usuario['id'].")\">".$usuario['user']." se ha unido a kuesty</button>";
			
		$mensaje_web = '<a onclick="kuesty.loadFriend('.$usuario["id"].')">'.$usuario["user"].'</a> se ha unido a kuesty<br /><a class="friend_logo" onclick="kuesty.loadLocal('.$usuario["id"].')"><img width="100px" src="/user/avatar/id/'.$usuario["id"].'" /></a>';

		//$mensaje_web = "<a onclick=\"kuesty.loadFriend(".$usuario['id'].")\">".$usuario['user']." se ha unido a kuesty</a>";
		$mensaje_web = $mensaje = "";

		return $this->insert(array(
			'id_user' => $usuario['id'],
		   	'nom_user' => $usuario['user'],
			'mensaje' => $mensaje,
			'tipo' => 'newuser',
			'mensaje_web' => $mensaje_web
		));
	}

	public function newCheckinEspecialUnlock($user, $local, $cespecial_desc) {

		return $this->insert(array(
			"id_user" => $user->id,
			"nom_user" => $user->user,
			'tipo' => 'promo_unlock',
			"id_local" => $local["id"],
			"nom_local" => $local["nombre"],
			"latitud" => $local["latitud"],
			"longitud" => $local["longitud"],
			"post" => $cespecial_desc			
		));
	}

	public function newReview($usuario, $review, $local) {

		// falta estilizar
		$mensaje     = "<button onclick=\"Friend.load($usuario->id)\">".$usuario->user."</button> ha escrito una <button onclick=\"Review.load(".$review['id'].")\">rese&ntilde;a </button> sobre <button onclick=\"Local.load(".$local['id'].")\">".$local['nombre']." </button>";
		$mensaje_web = "<a onclick=\"kuesty.loadFriend($usuario->id)\">".$usuario->user."</a> ha escrito una <a onclick=\"kuesty.loadReview(".$local['id'].",".$review['id'].")\">rese&ntilde;a </a> sobre <a onclick=\"kuesty.loadLocal(".$local['id'].")\">".$local['nombre']." </a>";
		
		return $this->insert(array(
			'id_user' => $usuario->id,
		   	'nom_user' => $usuario->user,
			'id_local' => $local["id"],
			'nom_local' => $local["nombre"],
			'latitud'   => $local["latitud"],	
			'longitud'  => $local["longitud"],
			'id_review' => $review["id"],
			'post'	    => substr($review["comentario"], 0, 139)."...",
			'stars'     => $review["rating"],
			'pesos'     => $review["price"],
			'mensaje' => $mensaje,
			'tipo' => "newreview",
			'mensaje_web' => $mensaje_web
		));
	}

	public function likeReview($usuario, $review) {

		// falta estilizar
		$mensaje = "<button onclick=\"Friend.load($usuario->id)\">".$usuario->user."</button> ha calificado la rese&ntilde;a: <button onclick=\"Review.load(".$review['id'].")\">".$review['titulo']."</button> ";

		$mensaje_web = "<a onclick=\"kuesty.loadFriend($usuario->id)\">".$usuario->user."</a> ha calificado la rese&ntilde;a: <a onclick=\"kuesty.loadReview(".$review['id_local'].",".$review['id'].")\">".$review['titulo']."</a> ";
		return $this->insert(array(
			'id_user' => $usuario->id, 
			'mensaje' => $mensaje,
			'mensaje_web' => $mensaje_web
		));
	}

	public function newPromo($local, $promo) {

		// falta estilizar
		$mensaje = "<button onclick=\"Local.load(".$local['id'].")\">".$local['nombre']."</button> ha agregado una nueva promo: <button onclick=\"Promo.load(".$promo['id'].")\">".$promo['titulo']."</button>";

		$mensaje_web = "";

		return $this->insert(array(
			'id_user' => 0, 
			'mensaje' => $mensaje,
			'mensaje_web' => $mensaje_web
		));
	}

	public function newFan($usuario, $local) {


		$mensaje     = "<button onclick=\"Friend.load($usuario->id)\">".$usuario->user."</button> se ha hecho fan de <button onclick=\"Local.load(".$local['id'].")\">".$local['nombre']." </button>";

		$mensaje_web = '<a class="user_logo" onclick="kuesty.loadFriend('.$usuario->id.')">';
		$mensaje_web.= '<img width="75px" src="/user/avatar/id/'.$usuario->id.'" />';
		$mensaje_web.= '</a>';
		$mensaje_web.= '<a onclick="kuesty.loadFriend('.$usuario->id.')">'.$usuario->user.'</a>';
		$mensaje_web.= ' se ha hecho fan de ';
		$mensaje_web.= '<a onclick="kuesty.loadLocal('.$local["id"].')">'.$local["nombre"].'</a><br />';
		$mensaje_web.= '<a class="local_logo" onclick="kuesty.loadLocal('.$local["id"].')">';
		$mensaje_web.= '<img width="200px" src="/restaurants/avatar/id/'.$local["id"].'" /></a>';

		$mensaje = $mensaje_web = "";
		//$mensaje_web = "<a class=\"user_logo\"onclick=\"kuesty.loadFriend($usuario->id)\">".$usuario->user."</a> se ha hecho fan de <a onclick=\"kuesty.loadLocal(".$local['id'].")\">".$local['nombre']." </a>";

		return $this->insert(array(
			'id_user' => $usuario->id,
			'nom_user' => $usuario->user,
			'id_local' => $local['id'],
			'nom_local' => $local['nombre'],
			'latitud' => $local['latitud'],	
			'longitud' => $local['longitud'],	
			'mensaje' => $mensaje,
			'tipo' => 'newfan',
			'mensaje_web' => $mensaje_web
		));
	}

	public function delFan($id_user, $id_local) {
	
		$this->delete("id_user = ".(int)$id_user." AND id_local = ".(int)$id_local." AND tipo = 'newfan'");
	}

	public function newCheckin($usuario, $local, $checkin) {

		$mensaje     = "<button onclick=\"Friend.load($usuario->id)\">".$usuario->user."</button> ha hecho checkin en <button onclick=\"Local.load(".$local['id'].")\">".$local['nombre']." </button>";
		
		$mensaje_web = '<a class="user_logo" onclick="kuesty.loadFriend('.$usuario->id.')"><img width="75px" src="/user/avatar/id/'.$usuario->id.'" /></a><a onclick="kuesty.loadFriend('.$usuario->id.')">'.$usuario->user.'</a> ha hecho checkin en <a onclick="kuesty.loadLocal('.$local["id"].')">'.$local["nombre"].'</a><br /><a class="local_logo" onclick="kuesty.loadLocal('.$local["id"].')"><img width="200px" src="/restaurants/avatar/id/'.$local["id"].'" /></a>';
		//$mensaje_web = "<a onclick=\"kuesty.loadFriend($usuario->id)\">".$usuario->user."</a> ha hecho checkin en <a onclick=\"kuesty.loadLocal(".$local['id'].")\">".$local['nombre']." </a>";
		
		return $this->insert(array(
			'id_user' => $usuario->id, 
			'nom_user' => $usuario->user,
			'mensaje' => $mensaje,	
			'id_local' => $local['id'],
			'nom_local' => $local['nombre'],
			'latitud' => $local['latitud'],	
			'longitud' => $local['longitud'],	
			'id_checkin' => $checkin["id"],
			'post' => $checkin["tip"],
			'stars' => $checkin["rating"],
			"pesos" => $checkin["price"],
			'tipo'    => "newcheckin" ,
 			'mensaje_web' => $mensaje_web
		));
	}

	public function newFriendship($usuario, $friend) {
		
		$mensaje = "<button onclick=\"Friend.load($usuario->id)\">".$usuario->user."</button> ahora es amigo de <button onclick=\"Friend.load(".$friend['id'].")\">".$friend['user']." </button>";
		
		$mensaje_web = '<a class="user_logo" onclick="kuesty.loadFriend('.$usuario->id.')"><img width="75px" src="/user/avatar/id/'.$usuario->id.'" /></a><a onclick="kuesty.loadFriend('.$usuario->id.')">'.$usuario->user.'</a> ahora es amigo de <a onclick="kuesty.loadFriend('.$friend["id"].')">'.$friend["user"].'</a><br /><a class="friend_logo" onclick="kuesty.loadFriend('.$friend["id"].')"><img width="100px" src="/user/avatar/id/'.$friend["id"].'" /></a>';

//		$mensaje_web = "<a onclick=\"kuesty.loadFriend($usuario->id)\">".$usuario->user."</a> ahora es amigo de <a onclick=\"kuesty.loadFriend(".$friend['id'].")\">".$friend['user']." </a>";

		$mensaje = $mensaje_web = "";

		return $this->insert(array(
			'id_user' => $usuario->id, 
			'nom_user' => $usuario->user, 
			'id_friend' => $friend["id"], 
			'nom_friend' => $friend["user"],
		   	'tipo' => 'friendship',	
			'mensaje' => $mensaje,
			'mensaje_web' => $mensaje_web
		));
	}
	/*
	public function getAllEvents($id_user, $friends = null) {
	
		$sql = $this->select()->from($this);
		if($friends != null) {
			$friends[] = 0;
			$friends = join($friends, ",");
			$sql->where("id_user IN ($friends)");
		}else {
			$sql->where("(id_user = 0 OR public = 1)");
		}
		$sql->where(" id_user <> ".$id_user);
		$sql->order("fecha ASC")->limit(30,0);
//		echo $sql;
		$r = $this->fetchAll($sql)->toArray();
		return $r;
	}
	 */

	public function obtenerTodos($page, $cant = 10) {

		$sql = $this->select()
			->from("comunidad", array("*", "fecha_server"=>"fecha"))
			->order("fecha DESC")
			->limitPage($page,20)
			->where(" fixed = 1 ")
			->where(" public = 1");
		//->where("tipo = 'newlocalstatus'");
		return $this->fetchAll($sql)->toArray();
	}

	public function getMeEvents($id_user,$page, $cant = 20) {
	
		$sql = $this->select()
			->from("comunidad", array("*", "fecha_server"=>"fecha"))
			->where("id_user = ".$id_user." OR id_friend = ".$id_user)
			->order("fecha DESC")
			->limitPage($page,$cant)
			->where(" fixed = 1 ");

		return $this->fetchAll($sql)->toArray();
	}

	public function getPeoplesOpinion($page, $cant = 20) {
	
		$sql = $this->select()
			->from("comunidad", array("*", "fecha_server"=>"fecha"))
			->where("tipo = 'newreview' OR tipo = 'newcheckin'")
			->order("fecha DESC")
			->limitPage($page, $cant)
			->where("fixed = 1");

		return $this->fetchAll($sql)->toArray();
	}

	public function getUserIsFanEvents($id_user, $id_locales, $id_friends,$page = 1, $cant = 20) {

		$id_friends[] = $id_user;

		$ids = implode($id_locales, ",");
		$fids = implode($id_friends, ","); 
		$sql = $this->select()
				//->where(" id_local IN(".$ids.")")
				//->where(" (id_user = ".$id_user." OR id_friend = ".$id_user." OR id_user = 0) AND id_local IN(".$ids.")")
				->where(" ( id_user IN(".$fids.") OR id_friend IN (".$fids.") OR id_user = 0) AND id_local IN(".$ids.")")
				->where(" fixed = 1 ")
				->where(" ( tipo = 'newcheckin' OR tipo = 'newlocalstatus' )") 
				->from("comunidad", array("*", "fecha_server"=>"fecha"))
				->order("fecha DESC")
				->limitPage($page,$cant);

		return $this->fetchAll($sql)->toArray();
	}

	public function getNearbyEvents($lat, $lng, $page = 1, $cant = 10) {

        $radius = 0.015;

		--$page;
		$ini = $cant * $page;
		$fin = $ini + $cant;

		$sql = " SELECT c.*, ";
		$sql.= " ((c.latitud - ({$lat})) * ( c.latitud - ({$lat}) ) + ( c.longitud - ({$lng}) ) * ( c.longitud - ({$lng}))) AS dist ";
		$sql.= " FROM comunidad as c";
		$sql.= " WHERE latitud <> 0 AND longitud <> 0 AND tipo IN ('newcheckin', 'newlocalstatus', 'newreview', 'newlocalbyuser')";
		$sql.= " HAVING dist < ({$radius} * {$radius}) ORDER BY fecha DESC, dist ASC LIMIT $ini, $fin;";

		$res = $this->_db->query($sql)->fetchAll();
        return $res;
	}

	public function getLocalEvents($page = 1, $cant = 20) {
		
		$sql = $this->select()
			->where("tipo = 'newlocalstatus'")
			->where(" fixed = 1 ")
			->order("fecha DESC")
			//->joinLeft(array( 'checkin_especial' => 'esp'), 'comunidad.id_local = esp.id_local', array( 'nombre as nombre_localidad') )
			->limitPage($page,$cant)
			->from("comunidad", array("*", "fecha_server"=>"fecha"));
		return $this->fetchAll($sql)->toArray();
	}

	public function getLocalTodayEvents($page = 1, $cant = 20) {
		
		$sql = $this->select()
			->where("tipo = 'newlocalstatus'")
			->where(" fixed = 1 ")
			->where(" DATE(fecha) = DATE(NOW()) ")
			->order("fecha DESC")
			//->joinLeft(array( 'checkin_especial' => 'esp'), 'comunidad.id_local = esp.id_local', array( 'nombre as nombre_localidad') )
			->limitPage($page,$cant)
			->from("comunidad", array("*", "fecha_server"=>"fecha"));
		return $this->fetchAll($sql)->toArray();
	
	}

	public function getColectividadesEvents($page, $cant = 20) {

		$sql = $this->select()
			->where("id_local <= 650 AND id_local >= 616")
			->where(" fixed = 1 ")
			->order("fecha DESC")
			->limitPage($page,$cant)
			->from("comunidad", array("*", "fecha_server"=>"fecha"));
//		die($sql);
		return $this->fetchAll($sql)->toArray();
	}

	public function getLocalesWithPromos($page = 1, $cant = 20) {

		$offset = ($page - 1) * $cant;
		$table = new Zend_Db_Table("checkin_especial");
		$sql = $table->select()
			->from(array("ce"=>"checkin_especial"))
			->where("ce.fecha_ini <= NOW() AND ce.fecha_fin >= NOW() AND ce.estado = 'A'")->order("ce.fecha_creacion DESC")
			->joinLeft(array("l"=>'locales'), 'ce.id_local = l.id',array("l.id as id_local","l.nombre as nombre_local", "l.latitud as latitud", "l.longitud as longitud", "l.logo as logo"))
			->setIntegrityCheck(false)
			->limit($cant, $offset);

	//	die($sql);

		$res = $this->fetchAll($sql)->toArray();

		$model = new Model_CheckinEspecial();
		foreach($res as $key => $ev) {
			$res[$key]["descripcion"] = $model->getCheckinEspecialLongDesc($ev, array("nombre"=>$ev["nombre_local"]));
			$res[$key]["tipo"] = "checkin_especial";
		}	
		return $res;
	}

	public function getMyFriendsEvents($id_friends,$page = 1, $cant = 20) {
	
		$fids = implode($id_friends, ","); 
		$sql = $this->select()
			->where(" ( id_user IN(".$fids.") OR id_friend IN (".$fids.") )")
			->where(" fixed = 1 ")
			->from("comunidad", array("*", "fecha_server"=>"fecha"))
			->order("fecha DESC")->limitPage($page,$cant);

		return $this->fetchAll($sql)->toArray();
	}

}
