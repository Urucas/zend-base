<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Seguidores extends Model_Db {
	
	protected $_name 	= 'seguidores';
	protected $_primary = 'id';
//    protected $_order	= 'nombre';
	
    public function getSeguidores(){

        $table = new Zend_Db_Table('seguidores');

        $sql = $table->select()
                        ->from($table,array('*'));

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }
    public function getCantSeguidores(){

        $table = new Zend_Db_Table('seguidores');

        $sql = $table->select()
                        ->from($table,'count(*)');

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }
	public function addSeguidor($data){
	
		return $this->insert($data);

	}
	public function deleteSeguidor($data){
	
		return $this->delete('id_user = '.$data['id_user'].' and id_local = '.$data['id_local']);

	}
}
