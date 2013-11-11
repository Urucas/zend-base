<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Localidades extends Model_Db {
	
	protected $_name = 'localidades';
	protected $_primary = 'id';
        protected $_order	=	'nombre';
	
    public function getLocalidadesByProv($prov){

        $sql = $this->select()
                    ->from($this,array('*'))
                    ->where('idprov = '.$prov.' AND estado = 1');

        $r = $this->fetchAll($sql);

        return $r->toArray();

    }

	public function getAll(){

		$sql   = $this->select()->where("estado = 1");
        $res   = $this->fetchAll($sql)->toArray();
       	return $res;
	}

}
