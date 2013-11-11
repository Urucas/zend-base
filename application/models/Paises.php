<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Paises extends Model_Db{
	
	protected $_name = 'paises';
	protected $_primary = 'id';
	protected $_order = 'pais';
	
    public function getPaises(){

        $table = new Zend_Db_Table('paises');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('estado = 1');

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

}
