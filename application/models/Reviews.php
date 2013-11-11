<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Reviews extends Model_Db {
	
	protected $_name = 'reviews';
	protected $_primary = 'id';
	protected $_order = 'rating';
	
    public function getReviewsByUser($iduser,$cant = 10,$page = 1){

		// paginador
		$ini = $cant * $page;
		$fin = $ini + $cant;

        $table = new Zend_Db_Table('reviews');

        $sql = $table->select()
                        ->from(array('r'=>'reviews'),array('*'))
						->joinLeft(array('l'=>'locales'),'l.id = r.id_local',array("l.logo as logo","l.nombre as nombre_local"))
                        ->where('id_user = '.$iduser )
						->limit($cant,$ini)
						->order("fecha desc");

		$sql->setIntegrityCheck(false);
        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

	public function getDiffLastReview($id_user, $id_local) {

		$table = new Zend_Db_Table("reviews");
		$sql = $table->select()
			    ->from($table,array('dif'=>'DATEDIFF(NOW(),fecha)'))
                ->where('id_user = '.$id_user .' AND id_local = '.$id_local)
				->limit( 1 )
				->order( 'fecha DESC' );

		$r = $table->fetchAll($sql);
        return $r->toArray();

	}

	public function getReviewPreview($id) {
	
		$table = new Zend_Db_Table('reviews');

        $sql = $table->select()
                        ->from(array('r'=>'reviews'),array('*'))
						->joinLeft(array('l'=>'locales'), 'l.id = r.id_local',array("l.nombre as nombre_local","l.logo as logo_local","l.direccion as dir_local"))
                        ->where('r.id = '.$id );

		$sql->setIntegrityCheck(false);
        $r = $table->fetchAll($sql)->toArray();

        return sizeof($r) ? $r[0] : false;

	}

	public function getLastReview() {
	

		 $table = new Zend_Db_Table('reviews');

        $sql = $table->select()
                      ->from(array('r'=>'reviews'),array('*'))
					  ->joinLeft(array('u'=>'usuarios'), 'u.id = r.id_user',array("r.id as id_review",'u.id as id_user', "u.nombre as user_nombre", "u.apellido as user_apellido", "u.user as user"))
					  ->joinLeft(array('l'=>'locales'), 'l.id = r.id_local',array("l.nombre as nombre_local", "l.direccion as direccion_local","l.logo_mobile as logo_local","l.cantidad_checkins","l.cantidad_reviews","l.cantidad_tips","l.cant_fans"))
					  ->order('r.fecha DESC')
					  ->limit(1);

		$sql->setIntegrityCheck(false);
        $r = $table->fetchAll($sql);

		$r = $r->toArray();
		
		return $r;
	}

    public function getReview($id){

        $table = new Zend_Db_Table('reviews');

        $sql = $table->select()
                      ->from(array('r'=>'reviews'),array('*'))
					  ->joinLeft(array('u'=>'usuarios'), 'u.id = r.id_user',array("r.id as id_review",'u.id as id_user', "u.nombre as user_nombre", "u.apellido as user_apellido", "u.user as user"))
					  ->joinLeft(array('l'=>'locales'), 'l.id = r.id_local',array("l.nombre as nombre_local", "l.direccion as direccion_local","l.logo_mobile as logo_local","l.cantidad_checkins","l.cantidad_reviews","l.cantidad_tips","l.cant_fans"))
                      ->where('r.id = '.$id );

		$sql->setIntegrityCheck(false);
        $r = $table->fetchAll($sql);

		$r = $r->toArray();
        return $r;

    }

    public function getLastReviewByUser($iduser){

        $table = new Zend_Db_Table('reviews');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('id_user = '.$iduser )
			->limit( 1 )
			->order( 'fecha DESC' );

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getReviewsByLocal($idlocal,$cant, $lastId = null){

        $table = new Zend_Db_Table('reviews');
        $sql = $table->select()
                        ->from(array('r'=>'reviews'),array('*'))
						->joinLeft(array('u'=>'usuarios'), 'u.id = r.id_user',array("nombre as nombre_usr", "apellido as apellido_usr", "user" ))
                        ->where('id_local = '.$idlocal);

	if( !is_null( $lastId) )
		$sql->where( "r.id > $lastId" );

	if($cant)
		$sql->limit($cant);

	$sql->order( 'id ASC' );

	$sql->setIntegrityCheck(false);
//echo $sql;die();

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function addReview( $idUser, $idLocal, $stars, $comentario ){

        $table  =   new Zend_Db_Table( 'reviews' );

        $data[ 'id_user' ]      =   $idUser;
        $data[ 'id_local' ]     =   $idLocal;
        $data[ 'stars' ]        =   $stars;
        $data[ 'comentario' ]   =   $comentario;
//        $data[ 'fecha' ]        =   mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );

        return $table->insert( $data );
        
    }


}
