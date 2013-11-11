<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Tipodenuncias extends Model_Db {
	
	protected $_name 	= 'tipo_denuncias';
	protected $_primary = 'id';
//    protected $_order	= 'nombre';
	
    public function getTipos(){

        $table = new Zend_Db_Table('tipo_denuncias');

        $sql = $table->select()
                        ->from($table,array('*'));

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getOneTipo($id){

		if(is_null($id))
			return false;

		$where = "id = ".$id;

        $table = new Zend_Db_Table('tipo_denuncias');

        $sql = $table->select()
                        ->from($table,array('*'))
						->where($where);

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

	public function addTipo($data){
	
		return $this->insert($data);

	}
}
