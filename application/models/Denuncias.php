<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Denuncias extends Model_Db {
	
	protected $_name 	= 'denuncias';
	protected $_primary = 'id';
//    protected $_order	= 'nombre';
	
    public function getDenuncias(){

        $table = new Zend_Db_Table('denuncias');

        $sql = $table->select()
                        ->from($table,array('*'));

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getOneDenuncia($id){

		if(is_null($id))
			return false;

		$where = "id = ".$id;

        $table = new Zend_Db_Table('categorias');

        $sql = $table->select()
                        ->from($table,array('*'))
						->where($where);

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

	public function addDenuncia($data){
	
		return $this->insert($data);

	}
}
