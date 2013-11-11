<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Friends extends Model_Db {
	
	protected $_name = 'friends';
	protected $_primary = array('id');
	
    
    public function getFriends($iduser, $limit = null){

        $sql = $this->select()->from($this,array('*'))->where('id_user1 = '.$iduser .' OR id_user2 = '.$iduser)->where( 'estado = ?', 'A');
		if((int) $limit) {
			$sql->limit($limit);
		}
        $r = $this->fetchAll($sql);
        return $r->toArray();

    }

	public function getFriendsByFuid($fids_list) {

		$f_uids = implode($fids_list, ",");
		$table = new Zend_Db_Table("usuarios");
		$sql = $table->select()->where("fb_uid IN($f_uids)");
//		die($sql);
		return $table->fetchAll($sql)->toArray();
	}

	public function getFriendsAsIdArray($iduser) {
	
		$friends = $this->getFriends($iduser);
		$ids     = array();
		foreach($friends as $friend) {
			$id_user1 = $friend['id_user1'];
			$id_user2 = $friend['id_user2'];
			$ids[] = $id_user1 == $iduser ? $id_user2 : $id_user1;
		}
		return $ids;
	}

    public function addNewFriend( $idUser, $idFriend ){

		// Agrega un nuevo amigo a la tabla friends con estado pending

		// si ya le mande entonces no la vuelvo a insertar
		$friendship = $this->isFriend($idUser,$idFriend);

		if($friendship['estado'] == "D"){

			return $this->changeStateFriend( $idUser, $idFriend, 'P' );       
		}

		if($friendship['estado'] != "NN"){
			return null;
		}

        $data[ 'id_user1' ] = $idUser;
        $data[ 'id_user2' ] = $idFriend;

        return $this->insert($data);

    }

    public function changeStateFriend( $userId, $idFriend, $state = 'A' ){

//	Acepta a un nuevo usuario como amigo

		$data	=	array( 'estado' => $state );
		$where = "id_user1 = $userId AND id_user2 = $idFriend ";
		$where .= "OR id_user1 = $idFriend AND id_user2 = $userId ";

		return $this->update( $data, $where );
    }


    public function deleteFriend( $userId, $idFriend){

		$where = "id_user1 = $userId AND id_user2 = $idFriend ";
		$where .= "OR id_user1 = $idFriend AND id_user2 = $userId ";

        return $this->delete($where);
    }

	public function isFriend($userId, $idFriend) {
		
		$where = "id_user1 = $userId AND id_user2 = $idFriend ";
		$where .= "OR id_user1 = $idFriend AND id_user2 = $userId ";

		$sql = $this->select()->where($where);
		$r   = $this->fetchAll($sql)->toArray();
		return sizeof($r) ? $r[0] : array("estado" => "NN");
	}	
}
