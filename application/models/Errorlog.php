<?php

class Model_Errorlog extends Zend_Db_Table_Abstract {
	
	protected $_name = 'errorlog';
	protected $_primary = array('id');

	public function addNewError( $error, $type, $user, $controller, $action, $file, $line, $url ){

//	Agrega un nuevo error al log
		$data[ 'error' ]	=	$error;
		$data['type']		=	$type;
		$data['user']		=	$user;
		$data['controller']	=	$controller;
		$data['action']		=	$action;
		$data['file']		=	$file;
		$data['line']		=	$line;
		$data['url']		=	$url;

		$this->insert( $data );

	}
		
}
