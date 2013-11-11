<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Configuraciones extends Model_Db {
	
	protected $_name 	= 'configuraciones';
	protected $_primary = 'id';
	
    public function getHelp(){

        $sql = $this->select()
                    ->from($this,array('*'))
					->where("tipo = 'ayuda'");

        $r = $this->fetchAll($sql);

        return $r->toArray();

    }

    public function getWebHelp(){

        $sql = $this->select()
                    ->from($this,array('*'))
					->where("tipo = 'ayuda_web'");

        $r = $this->fetchAll($sql);
        return $r->toArray();

    }

    public function getTerminos(){

        $sql = $this->select()
                    ->from($this,array('*'))
					->where("tipo = 'terminos'");

        $r = $this->fetchAll($sql);

        return $r->toArray();

    }

}
