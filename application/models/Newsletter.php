<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Newsletter extends Model_Db {
	
	protected $_name = 'newsletter';
	protected $_primary = 'id';
	protected $_order = 'email';
}
