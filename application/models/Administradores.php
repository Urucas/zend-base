<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Administradores extends Model_Db {
    protected $_name	=	'administradores';
    protected $_primary	=	'id';
    protected $_order	=	'usuario';
    protected $_limit	=	0;
    protected $_offset	=	5;
    
    public function validar( $user, $pass ){

    	$rs	=	$this->fetchAll( $this->select()->where( "usuario = ? ", $user )->where( 'password = ? ', md5( $pass ) ) );
    	return ( !is_null( $rs ) )	?	$rs->toArray()	:	null;
    	
    }
    
    public function add( $data ){

    	$data['password']	=	md5( $data['password'] );
    	parent::add( $data );
    	
    }

    public function guardar( $data, $where ){

        if( isset( $data['password'] ) && !empty ( $data['password'] ) )
            $data['password']	=	md5( $data['password'] );
        else
            unset( $data['password'] );

    	parent::guardar($data, $where);

    }
	
}

?>