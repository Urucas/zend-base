<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Premios extends Model_Db {
	
	protected $_name = 'premios';
	protected $_primary = 'id';

	public function getAllInDate() {
	
		$sql = $this->select()->order("order ASC")->where("estado = 1");
		return $this->fetchAll($sql)->toArray();

	}	
}
