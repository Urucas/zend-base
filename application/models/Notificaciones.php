<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Notificaciones extends Model_Db {
	
	protected $_name = 'notificaciones';
	protected $_primary = 'id';
    protected $_order	= 'fecha';

	public function shareLocal($emisor, $local, $receptor, $extramessage) {
	
		$mensaje = "<button class=\"%_estado\" id_notif = \"%_idnotificacion\" onclick=\"Notifications.markAsRead(this);Local.load($local->id)\">".$emisor->nombre." ".substr($emisor->apellido, 0, 1).". te recomienda el local ".$local->nombre."<br />".$extramessage."</button>";
		
		//$mensaje_web = "<button class=\"%_estado\" id_notif = \"%_idnotificacion\" onclick=\"kuesty.notifMarkAsRead(this);kuesty.loadLocal($local->id)\">".$emisor->usuario." te recomienda el local ".$local->nombre."</button>";
		
		$mensaje_web = "<a href=\"/user/?id=$emisor->id\">$emisor->user</a> te recomienda el local <a href=\"/restaurants/local/?id=$local->id\">$local->nombre</a>";

		return $this->insert(array(
			'id_user'           => $receptor->id, 
			'mensaje'           => $mensaje, 
			'mensaje_web'       => $mensaje_web, 
			'tipo_notificacion' => 'COMPARTIR' 
		));
	}

	public function localStatusNotificacion($receptor, $local, $status) {	

	
		$mensaje     = '<button class="%_estado" id_notif = "%_idnotificacion" onclick="Notifications.markAsRead(this);Local.load('.$local["id"].')">'.$local["nombre"].' ha actualizado su estado: <br />'.$status.'</button>';

		//		$mensaje_web = "<button class=\"%_estado\" id_notif = \"%_idnotificacion\" onclick=\"kuesty.notifMarkAsRead(this);kuesty.loadFriend($emisor->id)\">".$emisor->user." te ha aceptado como amigo. Hurra!</button>";

		$mensaje_web = '<a class="notificacion_web" href="/restaurants/local/id/'.$local["id"].'/name/'.$local["nombre"].'">'.$local['nombre'].'</a> ha actualizado su estado: <br />'. $status.' ...'; 
		
		//$mensaje_web = "<a href=\"/user/?id=$emisor->id\">$emisor->user</a> te ha aceptado como amigo. Hurra!";

		return $this->insert(array(
			'id_user'     => $receptor["id"], 
			'id_local'    => $local["id"],
			'tipo_notificacion' => "LOCAL_STATUS_UPDATE", 
			'mensaje'     => $mensaje,
			'mensaje_web' => $mensaje_web
		));

	}

	public function acceptFriend($emisor, $receptor) {

		$mensaje     = "<button class=\"%_estado\" id_notif = \"%_idnotificacion\" onclick=\"Notifications.markAsRead(this);User.load($emisor->id)\">".$emisor->user." te ha aceptado como amigo. Hurra!</button>";
		
		//		$mensaje_web = "<button class=\"%_estado\" id_notif = \"%_idnotificacion\" onclick=\"kuesty.notifMarkAsRead(this);kuesty.loadFriend($emisor->id)\">".$emisor->user." te ha aceptado como amigo. Hurra!</button>";
		
		$mensaje_web = "<a href=\"/user/?id=$emisor->id\">$emisor->user</a> te ha aceptado como amigo. Hurra!";

		return $this->insert(array(
			'id_user'     => $receptor["id"], 
			'mensaje'     => $mensaje,
			'mensaje_web' => $mensaje_web
		));
	}

	public function signupValidation($usuario_id) {
	
		$mensaje     = "<button class=\"%_estado\" id_notif = \"%_idnotificacion\">Tu cuenta se autodestruira en 2 dias si no validas el email q Don Kuesty te ha enviado!</button>";
			
		return $this->insert(array(
			'id_user'           => $usuario_id, 
			'mensaje'           => $mensaje, 
			'mensaje_web'       => "", 
			'tipo_notificacion' => 'VALIDACION MAIL' 
		));
	}

	public function friendshipRequest($receptor, $emisor) {
		$mensaje     = "<button class=\"%_estado\" id_notif = \"%_idnotificacion\" onclick=\"Notifications.markAsRead(this);User.load($emisor->id)\">".$emisor->user." quiere ser tu amigo</button>";

		// $mensaje_web = "<button class=\"%_estado\" id_notif = \"%_idnotificacion\" onclick=\"kuesty.notifMarkAsRead(this);kuesty.loadFriend($emisor->id)\">".$emisor->user." quiere ser tu amigo</button>";

		$mensaje_web = 	"<a href=\"/user/?id=$emisor->id\">$emisor->user</a> quiere ser tu amigo";

		return $this->insert(array(
			'id_user'           => $receptor->id, 
			'mensaje'           => $mensaje, 
			'mensaje_web'       => $mensaje_web, 
			'tipo_notificacion' => 'SOLICITUD' 
		));	
	}

	public function generatePromo($users, $local, $promo) {
		// esta notificacion la va a generar el restaurant x backend	
		for($i = 0; $i < sizeof($users); $i++) {
			
			$mensaje     = "<button class=\"%_estado\" id_notif=\"%_idnotificacion\"  onclick=\"Notifications.markAsRead(this);Promo.load($promo->id)\">".$local->nombre." quiere que veas una promo</button>";
			$mensaje_web = "";
			
			$this->insert(array(
				'id_user'     => $user[i]->id, 
				'mensaje'     => $mensaje,
				'mensaje_web' => ""
			));
		}
	}

	public function newMessage($emisor, $receptor, $mensaje_id) {
		
		$mensaje     = "<button class=\"%_estado\" id_notif=\"%_idnotificacion\"  onclick=\"Notifications.markAsRead(this);Mensajes.load($mensaje_id);\">".$emisor->nombre." ".substr($emisor->apellido,0,1).". te ha enviando un mensaje.</button>";
		$mensaje_web = "";

		return $this->insert(array(
			'id_user'     => $receptor, 
			'mensaje'     => $mensaje,
			'mensaje_web' => ""
		));
	}

	public function getNotifications($id_user) {
	
		$sql = $this->select()->where('id_user = '.$id_user)->order('fecha DESC');
		return $this->fetchAll($sql)->toArray();
	}

	public function getUnreadCount($id_user) {
	
		$sql = $this->select()->from("notificaciones","COUNT(*) as unread_count")->where('id_user = '.$id_user. ' AND estado = 0');
		$res = $this->fetchAll($sql)->toArray();
		return (int) $res[0]["unread_count"];
	}

	public function delActivacion($id_user) {
	
		$this->delete("id_user = ".$id_user." AND tipo_notificacion = 'VALIDACION MAIL'");
	}

	public function markAsRead($id_notif, $id_user) {
		
		return $this->update(array('estado' => 1), "id = ".(int)$id_notif." AND id_user =".(int)$id_user);	
	}

	public function markAllAsRead($id_user) {
	
		return $this->update(array('estado' => 1), " id_user =".(int)$id_user);	
	}
}
