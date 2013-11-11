<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Fans extends Model_Db {
	
	protected $_name = 'fans';
	protected $_primary = 'id';
	protected $_order = 'id';
	
    public function userIsFan($user_id, $local_id){

        $sql = $this->select()->where("id_user = ".(int) $user_id." AND id_local = ".(int)$local_id);
       	$r = $this->fetchAll($sql)->toArray();
        return sizeof($r) ? true : false;
    }

	public function deleteFan($id_user, $id_local) {
		
		return $this->delete('id_user = '.(int)$id_user." AND id_local = ".(int)$id_local);
	}
	public function addFan($id_user, $id_local) {

		if(!(int)$id_user || !(int)$id_local) return false;

		return $this->insert(array("id_user"=>(int)$id_user, "id_local"=>(int)$id_local));
	}

	public function getlocalsfromuser($id_user) {
		$sql = $this->select()->from($this)->where("id_user = ".(int)$id_user)->setIntegrityCheck(false)->joinLeft('locales', "locales.id = fans.id_local");
		return $this->fetchAll($sql)->toArray();
	}
	
	public function getIdLocalUserIsFanAsArray($id_user) {

		$sql = $this->select()->where("id_user =".(int)$id_user);
		$locales = $this->fetchAll($sql)->toArray();
		$l = array();
		foreach($locales as $local){
			$l[] = $local["id_local"];
		}
		return $l;
	}

}
