<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Denunciascheckins extends Model_Db {
	
	protected $_name 	= 'denuncias_checkins';
	protected $_primary = 'id';
//    protected $_order	= 'nombre';
	
    public function getDenuncias(){

        $table = new Zend_Db_Table('denuncias_checkins');

        $sql = $table->select()
                        ->from($table,array('*'));

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getOneDenuncia($id){

		if(is_null($id))
			return false;

		$where = "id = ".$id;

        $table = new Zend_Db_Table('denuncias_checkins');

        $sql = $table->select()
                        ->from($table,array('*'))
						->where($where);

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

	public function addDenuncia($data){
	
		//ver si ya tiene una el usuario

		return $this->insert($data);

	}
}
