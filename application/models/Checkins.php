<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Checkins extends Model_Db{
	
	protected $_name = 'checkins';
	protected $_primary = array('id');
	protected $_order = 'id';
	
    public function getCheckinsByUser($iduser,$cant=10,$page=1){

		// paginador
		$ini = $cant * $page;
		$fin = $ini + $cant;

        //$table = new Zend_Db_Table('checkins');

        $sql = $this->select()
                        ->from(array("c"=>"checkins"),array('*'))
						->joinLeft(array('l'=>'locales'),'l.id = c.id_local',array("l.logo as logo","l.nombre as nombre_local"))
						->where('id_user = '.$iduser.' AND tip <> ""' )
						->limit($cant,$ini)
						->order('fecha desc');

		$sql->setIntegrityCheck(false);
        $r = $this->fetchAll($sql);

        return $r->toArray();

    }

    public function getCheckinsByLocal($idlocal,$inicio = 0,$cant){

        //$table = new Zend_Db_Table('checkins');

        $sql = $this->select()
                        ->from(array("c"=>"checkins"),array('*'))
						->joinLeft(array('u'=>'usuarios'), 'u.id = c.id_user',array("avatar", "nombre as nombre_usr", "apellido as apellido_usr", "user"))
                        ->where('id_local = '.$idlocal.' AND tip <> ""' );
		$sql->setIntegrityCheck(false);

		if($cant)
			$sql->limit($cant,$inicio);

		$sql->setIntegrityCheck(false);

        $r = $this->fetchAll($sql);

        return $r->toArray();

    }
    public function addCheckin( $idUser, $idLocal, $rating, $tip, $price ){

        //$table  =   new Zend_Db_Table( 'checkins' );

        $data[ 'id_user' ]      =   $idUser;
        $data[ 'id_local' ]     =   $idLocal;
        $data[ 'rating' ]     	=   $rating;
        $data[ 'tip' ]     		=   $tip;
        $data[ 'price' ]     	=   $price;

        return $this->insert( $data );

    }

    public function deleteCheckin($idCheckin){

        $this->delete("id = ".$idCheckin);

        return true;

    }

    public function getLastCheckin( $idUser ){
//	Devuelve el ultimo checkin de un usuario

        $table = new Zend_Db_Table('checkins');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('id_user = '.$idUser )
			->limit( 1 )
			->order( 'fecha DESC' );

        $r = $table->fetchAll($sql);

        return $r->toArray();

	}

	public function getLastTip() {
	
		 $table = new Zend_Db_Table('checkins');

		 $sql = $table->select()

			 ->from(array('r'=>'checkins'),array('*'))
			 ->where('tip <> ""')
			 ->joinLeft(array('u'=>'usuarios'), 'u.id = r.id_user',array("r.id as id_review",'u.id as id_user', "u.nombre as user_nombre", "u.apellido as user_apellido", "u.user as user"))
			 ->joinLeft(array('l'=>'locales'), 'l.id = r.id_local',array("l.nombre as nombre_local", "l.direccion as direccion_local","l.logo_mobile as logo_local","l.cantidad_checkins","l.cantidad_reviews","l.cantidad_tips","l.cant_fans"))

			 ->setIntegrityCheck(false)

			 ->limit( 1 )
			 ->order( 'fecha DESC' );

        $r = $table->fetchAll($sql);

        return $r->toArray();
	}

	public function getCheckin($id_checkin) {
	
		$table = new Zend_Db_Table('checkins');
        $sql = $table->select()
					 ->from($table,array('*'))
					 ->setIntegrityCheck(false)
					 ->joinLeft(array('l'=>'locales'),'l.id = id_local',array("nombre as nombre_resto","logo"))
					 ->where('checkins.id = '.$id_checkin)
					 ->limit(1);

        $r = $table->fetchAll($sql)->toArray();
		
        return sizeof($r) ? $r[0] : array();
	}

    public function getDiffLastCheckinByLocal( $idUser,$idLocal ){
//	Devuelve el ultimo checkin de un usuario

        $table = new Zend_Db_Table('checkins');
/*
		SELECT fecha, DATEDIFF( NOW( ) , fecha ) 
			FROM  `checkins` 
			WHERE id_user =1
			AND id_local =244
*/
        $sql = $table->select()
                        ->from($table,array('dif'=>'DATEDIFF(NOW(),fecha)'))
                        ->where('id_user = '.$idUser .' AND id_local = '.$idLocal)
			->limit( 1 )
			->order( 'fecha DESC' );

        $r = $table->fetchAll($sql);

        return $r->toArray();

   }


	public function LastCheckins($cant){
	
		$sql = $this->select()
					->from(array('c'=>'checkins'),array('*'))
					->joinLeft(array('l'=>'locales'),'l.id = id_local',array("nombre as nombre_resto","logo"))
					->joinLeft(array('u'=>'usuarios'),'u.id = id_user',array("nombre as nombre_usr"))
					->where('tip <> ""')
					->order("fecha DESC")
					->limit($cant)
					->setIntegrityCheck(false);
		
		$data = $this->fetchAll($sql)->toArray();
		return $data;
	
	}

	public function getCantCheckinsByUser($iduser){

		$sql = $this->select()
                    ->from(array("c"=>"checkins"),array('count(*)'))
					->where('id_user = '.$iduser.' AND tip <> ""' );

        $r = $this->fetchAll($sql);

		$r->toArray();

		return $r[0]["count(*)"];
	}
}
