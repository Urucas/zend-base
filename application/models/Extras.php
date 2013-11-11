<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Extras extends Model_Db {
	
	protected $_name = 'extras';
	protected $_primary = 'id';
    protected $_order	=	'nombre';

	public function getAll() {
	
		return $this->fetchAll()->toArray();
	}	
    
}
