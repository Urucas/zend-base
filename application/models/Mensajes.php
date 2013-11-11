<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Mensajes extends Model_Db {
	
	protected $_name = 'mensajes';
	protected $_primary = 'id';
	protected $_order	=	'id_receptor';
	
    public function getMessagesByUser( $user, $page = 0, $sent = false ){

//        $table = new Zend_Db_Table('categorias');
		if( !$user ) return array();

/*		if($sent) {
		
			$where = "id_emisor = $idUser";
		}else {
		
			$where = "id_receptor = $idUser";
		}
*/

		/* SQL 
		   select ue.nombre as nombre_emisor, ue.apellido as apellido_emisor, ue.user as user_emisor , u.nombre as nombre_receptor, u.apellido as apellido_receptor, u.user as user_receptor, mensajes.* from mensajes left join usuarios ue on ue.id = id_emisor left join usuarios u on u.id = id_receptor where id_emisor = 1 or id_receptor = 1 order by fecha_hora DESC;
		*/ 
		$sql = "select ue.nombre as nombre_emisor, ue.apellido as apellido_emisor, ue.user as user_emisor, ue.sexo as sexo_emisor,ue.avatar as avatar_emisor, u.avatar as avatar_receptor, u.sexo as sexo_receptor , u.nombre as nombre_receptor, u.apellido as apellido_receptor, u.user as user_receptor, mensajes.id_emisor, mensajes.id_receptor, mensajes.fecha_hora, LEFT(mensajes.mensaje, 70) as mensaje, mensajes.id from mensajes left join usuarios ue on ue.id = id_emisor left join usuarios u on u.id = id_receptor where id_emisor = $user or id_receptor = $user order by fecha_hora DESC;";

		$mensajes = $this->_db->query($sql)->fetchAll();
		return sizeof($mensajes) ? $mensajes : array();
    }

	public function getConversationsByUser($user) {
	
		$sql =  "select conversation_id, ue.nombre as nombre_emisor, ue.apellido as apellido_emisor, ue.user as user_emisor, ue.sexo as sexo_emisor,ue.avatar as avatar_emisor, u.avatar as avatar_receptor, u.sexo as sexo_receptor , u.nombre as nombre_receptor, u.apellido as apellido_receptor, u.user as user_receptor, mensajes.id_emisor, mensajes.id_receptor, mensajes.fecha_hora, LEFT(mensajes.mensaje, 70) as mensaje, mensajes.id from mensajes left join usuarios ue on ue.id = id_emisor left join usuarios u on u.id = id_receptor where id_emisor = $user or id_receptor = $user group by conversation_id order by fecha_hora DESC;";

//		die($sql);
		$mensajes = $this->_db->query($sql)->fetchAll();
		return sizeof($mensajes) ? $mensajes : array();

	}

	public function getConversation($cid, $user) {
	
		$sql = "select conversation_id, ue.nombre as nombre_emisor, ue.apellido as apellido_emisor, ue.user as user_emisor, ue.sexo as sexo_emisor,ue.avatar as avatar_emisor, u.avatar as avatar_receptor, u.sexo as sexo_receptor , u.nombre as nombre_receptor, u.apellido as apellido_receptor, u.user as user_receptor, mensajes.id_emisor, mensajes.id_receptor, mensajes.fecha_hora, mensajes.mensaje as mensaje, mensajes.id from mensajes left join usuarios ue on ue.id = id_emisor left join usuarios u on u.id = id_receptor where conversation_id = \"$cid\" AND (id_emisor = $user OR id_receptor = $user) order by fecha_hora ASC;";

		$mensajes = $this->_db->query($sql)->fetchAll();
		return sizeof($mensajes) ? $mensajes : array();

	}

	public function getMessageById($userid, $msgid) {
		
		$sql = "select ue.nombre as nombre_emisor, ue.apellido as apellido_emisor, ue.user as user_emisor, ue.sexo as sexo_emisor, u.sexo as sexo_receptor , u.nombre as nombre_receptor, u.apellido as apellido_receptor, u.user as user_receptor, mensajes.id_emisor, mensajes.id_receptor, mensajes.fecha_hora, mensajes.mensaje, mensajes.id from mensajes left join usuarios ue on ue.id = id_emisor left join usuarios u on u.id = id_receptor where ( id_emisor = $userid or id_receptor = $userid ) and mensajes.id = $msgid;";
		$mensajes = $this->_db->query($sql)->fetchAll();
		return sizeof($mensajes) ? $mensajes[0] : array();
	}

    public function sendMessage( $emisor, $receptor, $mensaje ){

//        $table = new Zend_Db_Table('categorias');
		if( !$emisor || !$receptor || !$mensaje ) return null;

		//		foreach( $receptores as $key => $receptor ){ 
		$conversation_id = $emisor < $receptor ? (string)$emisor.":".(string)$receptor : (string)$receptor.":".(string)$emisor; 

		$data = array(
	    	'id_emisor'   => $emisor,
			'id_receptor' => $receptor,
			'conversation_id' => $conversation_id,
	    	'mensaje'     => $mensaje
		);

		$idMessage = $this->insert($data);
			// agrego notif al usuario
//		}
		return $idMessage;
    }

    public function deleteMessage( $idMessage, $id_user ){

		if( !$idMessage ) return null;

		$where	= "id = $idMessage AND id_emisor = $id_user";
		$this->delete($where);
    }

}
