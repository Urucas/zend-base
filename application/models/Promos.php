<?php

class Model_Promos extends Zend_Db_Table_Abstract {
	
	protected $_name = 'promos';
	protected $_primary = array('id');
	
    
    public function getPromos($localidad){

		$today = date("Y-m-d G:i:s");

        $sql = $this->select()
					->setIntegrityCheck(false)
					->from($this,array('*'))
					->where("fecha_inicio <=".$this->_db->quote($today)." AND fecha_fin >=".$this->_db->quote($today)." AND promos.localidad =".(int)$localidad)
					->order("fecha_inicio")
					->joinLeft('locales', 'locales.id = promos.id_local', array('locales.nombre as nombre_local'));
				
		$r = $this->fetchAll($sql);
        return $r->toArray();
	}	
}
