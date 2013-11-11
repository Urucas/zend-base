<?php

class Model_Kuesty extends Zend_Db_Table_Abstract {
	
	protected $_name = 'usuarios';
	//protected $_primary = array('id_usuario');
	
    public function validaUser($user,$pass,$seccion) {
    
        $table = new Zend_Db_Table('usuarios');
        $pass = md5($pass);
        $sql = $table->select()
                ->where("user = '{$user}'")
                ->where("pass = '{$pass}'");
						
        $s = $table->fetchAll($sql);
        $res = $s->toArray();
        return $res;		
    }

    public function userExists($user) {

        $user = $this->_db->quote($user);
        $table = new Zend_Db_Table('usuarios');
        $sql = $table->select()->where("user = $user");
        $res = $table->fetchAll($sql)->toArray();
        return sizeof($res);
    }

    public function getNearby($x0, $y0, $cant, $subrubro) {

        // formula de la circunferencia del circulo
        // (lat - x0)^2 + (long - y0)^2 = r^2
        //
        // tomo la latitud y longitud actual del usuario
        // como centro de la circunferencia
        // con la posicion de cada restaurant calculo la
        // ec. del circulo y los q esten mas cerca son
        // aquellos q caen dentro de este area
        // este comentario no lo entiendo ni yo!

        $radius = 0.005;
        // este lo puse a manopla, probe hasta q me tiro
        // algo mas o menos cerca, hay q mejorarlo bastante

        $where  = "(((latitud - $x0)*(latitud - $x0) + (longitud - $y0)*(longitud - $y0)) < $radius*$radius)";
        $where .= " AND rubro = 2";
        if((int)$subrubro) {
            $where .= " AND subrubro = $subrubro";
        }

        $table = new Zend_Db_Table('locales');
        $sql   = $table->select()->where($where)->limit($cant);
        $res   = $table->fetchAll($sql)->toArray();       
        return $res;
    }

    public function latnlongAlreadyUp($lat, $long) {

        $table = new Zend_Db_Table('locales');
        $sql   = $table->select()->where("latitud = $lat AND longitud = $long");
        $res   = $table->fetchAll($sql)->toArray();
        return sizeof($res) ? true : false;
    }

    public function userIsActivo($id_user) {

        $table = new Zend_Db_Table('usuarios');
        $sql   = $table->select()->where("id = $id_user AND estado = 'Activo'");
        $res   = $table->fetchAll($sql)->toArray();
        return sizeof($res) ? true : false;
    }

    public function addLocal($localData) {

        $table = new Zend_Db_Table('locales');
        return $table->insert($localData);
    }

    public function emailExists($email) {

        $email = $this->_db->quote($email);

        $table = new Zend_Db_Table('usuarios');
        $sql = $table->select()->where("mail = $email");
        $res = $table->fetchAll($sql)->toArray();
        return sizeof($res);
    }

    public function addUser($userData) {

        $table = new Zend_Db_Table('usuarios');
        return $table->insert($userData);
    }

    public function getsticky($cant) {
        
        $table = new Zend_Db_Table('locales');
        $sql   = $table->select()
                ->where('rubro = 2')
                ->order("rating DESC")
                ->limit((int) $cant);
        
        return $table->fetchAll($sql)->toArray();
    }

    public function getRubros(){

        $table = new Zend_Db_Table('rubros');

        $sql = $table->select()
                        ->from($table,array('*'));

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getRubrosBySubrubro($subrubro){

        $table = new Zend_Db_Table('rubros');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('id_subrubro = '.$subrubro);

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getPaises(){

        $table = new Zend_Db_Table('paises');

        $sql = $table->select()
                        ->from($table,array('*'));

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getProvByPais($pais){

        $table = new Zend_Db_Table('provincias');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('id_pais = '.$pais);

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getLocalidadesByProv($prov){

        $table = new Zend_Db_Table('localidades');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('id_prov = '.$prov);

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getLocalById( $id = null ){

        if(is_null( $id ))
           return false;

        $table  =   new Zend_Db_Table( 'locales' );

        $where  =   "id = $id ";
        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where( $where );

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getLocales($params){

        $where = '';
        $p = 0;

        if(isset($params['idlocalidad'])){
            $where .= ' localidad = '.$params['idlocalidad'];
            $p = 1;
        }
        if(isset($params['rubro'])){
            if($p==1){
                $where .= ' AND ';
            }else{ $p=1; }
            $where .= ' rubro = '.$params['rubro'];
        }
        if(isset($params['subrubro'])){
            if($p==1){
                $where .= ' AND ';
            }else{ $p=1; }
            $where .= ' subrubro = '.$params['subrubro'];
        }
        if(isset($params['nombre'])){
            if($p==1){
                $where .= ' AND ';
            }else{ $p=1; }
            $where .= ' nombre  LIKE  "%'.$params['nombre'].'%"';
        }

        $table = new Zend_Db_Table('locales');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where($where);

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getUserByName($name){

        $table = new Zend_Db_Table('usuarios');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('user LIKE "%'.$name.'%" OR nombre LIKE "%'.$name.'%" ');

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getFriends($iduser){

        $table = new Zend_Db_Table('friends');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('id_user1 = '.$iduser .' OR id_user2 = '.$iduser);

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getCheckinsByUser($iduser){

        $table = new Zend_Db_Table('checkins');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('id_user = '.$iduser );

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getReviewsByUser($iduser){

        $table = new Zend_Db_Table('reviews');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('id_user = '.$iduser );

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getReviewsByLocal($idlocal){

        $table = new Zend_Db_Table('reviews');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('id_local = '.$idlocal);

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function getUserById( $id = null ){

        if(is_null( $id ))
           return false;

        $table  =   new Zend_Db_Table( 'usuarios' );

        $where  =   "id = $id ";
        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where( $where );

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

    public function addNewFriend( $idUser, $idFriend ){

//        Queda que definir cuales van a ser los estados de los usuarios

        $table = new Zend_Db_Table('friends');

        $data[ 'id_user1' ] =   $idUser;
        $data[ 'id_user2' ] =   $idFriend;
        $data[ 'fecha' ]    =   mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );
//        $data[ 'estado' ]    =   NULL;

        return $table->insert( $data );

    }

    public function addReview( $idUser, $idLocal, $stars, $comentario ){

        $table  =   new Zend_Db_Table( 'reviews' );

        $data[ 'id_user' ]      =   $idUser;
        $data[ 'id_local' ]     =   $idLocal;
        $data[ 'stars' ]        =   $stars;
        $data[ 'comentario' ]   =   $comentario;
        $data[ 'fecha' ]        =   mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );

        return $table->insert( $data );
        
    }

    public function addCheckin( $idUser, $idLocal ){

        $table  =   new Zend_Db_Table( 'checkins' );

        $data[ 'id_user' ]      =   $idUser;
        $data[ 'id_local' ]     =   $idLocal;
        $data[ 'fecha' ]        =   mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );

        return $table->insert( $data );

    }
	
}
