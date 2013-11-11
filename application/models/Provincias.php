<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Provincias extends Model_Db{
	
	protected $_name = 'provincias';
	protected $_primary = 'id';
	protected $_order = 'nombre';
	
    public function getProvByPais($pais){

        $table = new Zend_Db_Table('provincias');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('idpais = '.$pais. ' AND estado = 1');

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }
	
}
