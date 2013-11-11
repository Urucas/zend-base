<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Usuarios extends Model_Db {
	
	protected $_name = 'usuarios';
	protected $_order = 'user';
	//protected $_primary = array('id_usuario');
	
    public function validaUser($user,$pass,$seccion = "") {

        $table = new Zend_Db_Table('usuarios');
       	$pass = md5($pass);
		
		$user = $this->_db->quote($user);
		$pass = $this->_db->quote($pass);

		$sql = $table->select()->where("pass = $pass");

		if(preg_match("/@/",$user)) {
			$sql->where("mail = $user");
		}else{
			$sql->where("user = $user");
		}
        $s = $table->fetchAll($sql);
        $res = $s->toArray();
        return $res;		
    }

	public function addReview($id_user, $id_review, $puntos) {

		$puntos = (int) $puntos;
		$this->_db->query("update usuarios set cantidad_reviews = cantidad_reviews + 1, puntos = puntos + $puntos, ultimo_review = ".(int)$id_review." where id = ".(int)$id_user);
	}

    public function userExists($user) {

        $user = $this->_db->quote($user);
        $table = new Zend_Db_Table('usuarios');
        $sql = $table->select()->where("user = $user");
        $res = $table->fetchAll($sql)->toArray();
        return sizeof($res);
    }

    public function userIsActivo($id_user) {

        $table = new Zend_Db_Table('usuarios');
        $sql   = $table->select()->where("id = $id_user AND estado = 'A'");
        $res   = $table->fetchAll($sql)->toArray();
        return sizeof($res) ? true : false;
    }

	public function updateFBToken($token, $email, $avatar = null) {
		
		$data = array("fb_access_token"=>$token);
		if($avatar) {
			$data["fb_avatar"] = $avatar;
		}
		$this->update($data, "mail = ".$this->_db->quote($email));

		$table = new Zend_Db_Table('usuarios');
        $sql   = $table->select()->where("mail = ".$this->_db->quote($email));
        $res   = $table->fetchAll($sql)->toArray();
		return $res[0]["id"];
	}

	public function getUserByMail($email) {
	
		$table = new Zend_Db_Table('usuarios');
        $sql   = $table->select()->where("mail = ".$this->_db->quote($email));
        $res   = $table->fetchAll($sql)->toArray();
		return sizeof($res) ? $res[0] : false;
	}

	public function activateUser($id_user, $actkey) {

		return $this->update(array("estado" => "A", "activationkey" => ""), "id = ".$id_user." AND activationkey = ".$this->_db->quote($actkey));
	}

	public function setRecoverUser($id,$key){
	
		$this->update(array("changepasskey"=>$key),"id = ".$id);
	}

	public function checkPassKey($mail,$key=''){
	
		$table = new Zend_Db_Table('usuarios');
        $sql   = $table->select()->where("mail = ".$this->_db->quote($mail)." AND changepasskey = '".$key."'");
        $res   = $table->fetchAll($sql)->toArray();
		return sizeof($res) ? $res[0] : false;

	}

	public function getInactiveUsers(){

		//$sql = $this->select()->where("id IN (119,122,130,133,135,165,147,150)");
		$sql = $this->select()->where("estado = 'P'");
		return $this->fetchAll($sql)->toArray();
	
	}
	public function getUs(){

		//trae bruno y pam
		$sql = $this->select()->where("id IN (1,12)");
		return $this->fetchAll($sql)->toArray();
	
	}

	public function topPoints($cant) {
		
		$sql = $this->select()->order("puntos DESC")->limit($cant);
		return $this->fetchAll($sql)->toArray();

	}

	public function addTip($idUser) {
	
		$sql = "UPDATE `usuarios` set cantidad_tips = cantidad_tips+1 WHERE id=".$idUser;
		$r = $this->_db->query($sql);
	}

	public function addPassChange($idUser,$pass) {
	
		$sql = "UPDATE `usuarios` set pass = '".md5($pass)."',changepasskey='',passchanges=passchanges+1 WHERE id=".$idUser;
		$r = $this->_db->query($sql);
	}

	public function changePrivacy($user_id, $public) {
		
		$table = new Zend_Db_Table('usuario');
		$table->update(array('public'=> (int) $privacy ? 1 :0 ), 'id = '.(int)$user_id);

		$table = new Zend_Db_Table('comunidad');
		$table->update(array('public'=>  (int) $privacy ? 1 : 0), 'id_user = '.(int)$user_id);
	}

    public function emailExists($email) {

        $email = $this->_db->quote($email);

        $table = new Zend_Db_Table('usuarios');
        $sql = $table->select()->where("mail = $email");
        $res = $table->fetchAll($sql)->toArray();
        return sizeof($res) ? (int) $res[0]["id"] : false;
    }

    public function addUser($userData) {

        $table = new Zend_Db_Table('usuarios');

        return $table->insert($userData);
    }


	public function getOauthUser() {
		$sql = $this->select()->where("id = ".(int)$id);
		return $this->fetchRow($sql);
	}

    public function getUserByName($name){

        $table = new Zend_Db_Table('usuarios');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('user LIKE "%'.$name.'%" OR nombre LIKE "%'.$name.'%" ');

        $r = $table->fetchAll($sql);

        return $r->toArray();

    }

	public function findUser($q, $page) {

//		$q = $this->_db->quote($q);
		$sql = $this->select()->where('user LIKE "%'.$q.'%" OR nombre LIKE "%'.$q.'%" OR mail LIKE "%'.$q.'%"')->limit(30);
		return $this->fetchAll($sql)->toArray();
	}

	public function generatePassword() {
	
		$psw = date("Y-m-d G:i:s");
		$psw = md5($psw);
		$psw = substr($psw, 0, 7);
		return $psw;
	}

	public function resetPassword($id, $pass) {
	
		return $this->update(array("pass"=>md5($pass)), "id = ".(int)$id);
	}

	public function getUserByNameExactly($name) {
	
	    $table = new Zend_Db_Table('usuarios');

        $sql = $table->select()
                        ->from($table,array('*'))
                        ->where('user = "'.$name.'"');

        $r = $table->fetchAll($sql)->toArray();
		
        return sizeof($r) ? $r[0] : false;
	}

    public function getUserById( $id = null, $resume = false ){

        if(is_null( $id ))
           return false;

        $table  =   new Zend_Db_Table( 'usuarios' );

        $where  =   "usuarios.id IN ( $id ) ";
        $sql = $table->select()
			->from($table,array('*'));

		if(!$resume) {
			$sql->joinLeft( array( 'local' => 'localidades'), 'local.id = usuarios.localidad', array( 'nombre as nombre_localidad') )
				->joinLeft( array( 'prov' => 'provincias'), 'prov.id = usuarios.provincia', array("nombre as nombre_prov") )
				->joinLeft( array( 'pais' => 'paises'), 'pais.id = usuarios.pais', array("pais as nombre_pais") )
				->setIntegrityCheck(false);
		}
        $sql->where( $where );

        $r = $table->fetchAll($sql);
        return $r->toArray();

    }

	public function getUsersInArray($uid_list) {

		$list = implode(",", $uid_list);
		$sql = $this->select()->where("id IN (".$list.")");
		return $this->fetchAll($sql)->toArray();
	}

	public function addCheckin($id_user,$id_checkin, $puntos){
		
		$sql = "UPDATE `usuarios` set cantidad_checkins = cantidad_checkins+1 , ultimo_checkin = ".$id_checkin.", puntos = puntos + ".$puntos." WHERE id=".$id_user;
		$r = $this->_db->query($sql);

		return 1;

	}

	public function sumarPuntos($id_user,$cant){
		
		$sql = "UPDATE `usuarios` set puntos = puntos+".$cant." WHERE id=".$id_user;
		
		$r = $this->_db->query($sql);

		return 1;
	
	}

	public function updateCantidadAmigos($user1, $user2) {

		$sql = "UPDATE usuarios SET cantidad_amigos = cantidad_amigos + 1 WHERE id IN (".$user1.",".$user2.");";
		return $this->_db->query($sql);
	}

	public function restFan($userid) {
	
		$sql = "UPDATE usuarios SET cantidad_fan = cantidad_fan - 1 WHERE id =".$userid.";";
		return $this->_db->query($sql);	
	}	

	public function sumFan($userid) {
	
		$sql = "UPDATE usuarios SET cantidad_fan = cantidad_fan + 1 WHERE id =".$userid.";";
		return $this->_db->query($sql);	
	}	

	public function searchUser( $patron, $user ){

		$patrones = explode( ' ', $patron );
		$orWhere = $orWhere1 = $orWhere2 = '';

		$where = "usuarios.id NOT IN ($user) AND usuarios.estado = 'A' ";
	
		foreach( $patrones as $key => $patron ){

			$orWhere .= " usuarios.user LIKE '%$patron%' OR ";
			$orWhere1 .= " usuarios.nombre LIKE '%$patron%' OR ";
			$orWhere2 .= " usuarios.apellido LIKE '%$patron%' OR ";

		}

		$andWhere = $orWhere . $orWhere1 . $orWhere2;
		$andWhere = substr_replace( $andWhere, '', strlen( $andWhere ) -3 );
		$sql = $this->select()->from( $this, array( 'id', 'user', 'sexo', 'nombre', 'apellido', 'avatar'))
			->joinLeft( array( 'pais' => 'paises'), 'pais.id = usuarios.pais', array('pais') )
			->joinLeft( array( 'local' => 'localidades'), 'local.id = usuarios.localidad', array( 'localidad' => 'nombre') )
			->joinLeft( array( 'fr' => 'friends'), " (fr.id_user1 = usuarios.id AND fr.id_user2 = $user )  OR ( fr.id_user2 = usuarios.id AND fr.id_user1 = $user)", array('estado', 'id_user1' ) )
			->setIntegrityCheck( false )
			->where( $where )
			->where( $andWhere )
			->order($this->_order );

	        $r = $this->fetchAll($sql);
		return $r->toArray();

	}

	public function MejoresContribuidores($cant){
	
		$sql = $this->select()
					->order('cantidad_checkins desc')
					->limit($cant);
		$data = $this->fetchAll($sql)->toArray();
		
		return $data;
	
	}

	public function CantUsuarios(){

		$datos = array();

		//todos los usuarios
		$sql = $this->select()->from($this,array("count(*) as cant"));
		$data = $this->fetchAll($sql)->toArray();
		$datos["cantidad"] = $data[0]["cant"];

		//usuarios activos
		$sql1 = $this->select()->from($this,array("count(*) as cant"))->where("estado = 'P'");
		$data1 = $this->fetchAll($sql1)->toArray();
		$datos["activos"] = $data1[0]["cant"];
					
		return $datos;
	
	}

}
