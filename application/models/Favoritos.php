<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Favoritos extends Model_Db {
	
	protected $_name = 'favoritos';
	protected $_primary = 'id';
	protected $_order = 'id';
	
    public function getFavoritos($user_id){

        $sql = $this->select()
	     	->setIntegrityCheck(false)
            ->from($this, array('favoritos.id as id_fav'))
            ->where("favoritos.id_user = ".(int) $user_id)
	     	->joinLeft("locales", "locales.id = favoritos.id_local");
//        echo $sql;die();
        $r = $this->fetchAll($sql);
        return $r->toArray();

    }

	public function deleteFavoritos($id_fav, $id_user) {
		
		return $this->delete('id = '.(int)$id_fav.' AND id_user = '.(int)$id_user);
	}
	public function deleteFavorito($id_user, $id_local) {
		
		return $this->delete('id_local = '.(int)$id_local. ' AND id_user = '.(int)$id_user);
	}

	public function userHasFavorito($id_user, $id_local) {
		
		$sql = $this->select()->from($this)->where("id_user = ".(int) $id_user. ' AND id_local = '.(int) $id_local);	     	
       	$r = $this->fetchAll($sql)->toArray();
        return sizeof($r) ? true : false;
	}

	public function addFavorito($id_user, $id_local) {
		
		return $this->insert(array("id_user"=>(int)$id_user, "id_local"=>(int)$id_local));
	}

}
