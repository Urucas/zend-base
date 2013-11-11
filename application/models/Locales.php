<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Locales extends Model_Db {
	
	protected $_name = 'locales';
	protected $_primary = 'id';
	protected $_order = 'nombre';
	protected $_limit = 10;
	
    public function getNearby($x0, $y0, $cant, $categoria, $page = 0, $comodidades = "") {

        // formula de la circunferencia del circulo
        // (lat - x0)^2 + (long - y0)^2 = r^2
        //
        // tomo la latitud y longitud actual del usuario
        // como centro de la circunferencia
        // con la posicion de cada restaurant calculo la
        // ec. del circulo y los q esten mas cerca son
        // aquellos q caen dentro de este area
        // este comentario no lo entiendo ni yo!

        $radius = 0.009;
        $radius = 0.015;
        // este lo puse a manopla, probe hasta q me tiro
        // algo mas o menos cerca, hay q mejorarlo bastante

		// paginador
		$ini = $cant * $page;
		$fin = $ini + $cant;

		$fields = "l.latitud, l.longitud, l.id, l.nombre, l.direccion, l.logo_mobile, l.stars, l.pesos, l.logo, l.cantidad_reviews, l.cantidad_checkins, ";
		$fields.= " (l.cantidad_reviews + l.cantidad_checkins) AS cantidad_calificaciones,";
		$fields.= " ((l.latitud - ({$x0})) * ( l.latitud - ({$x0}) ) + ( l.longitud - ({$y0}) ) * ( l.longitud - ({$y0}))) AS dist ";
		//$fields.= " esp.id as has_checkinespecial, esp.dia as dia_checkin, esp.descripcion as desc_checkin, esp.hora_ini as horaini_checkin, ";
		//$fields.= " esp.hora_fin as horafin_checkin, esp.fecha_ini as fechaini_checkin, esp.fecha_fin as fechafin_checkin ";

		//$join  = " LEFT JOIN checkin_especial as esp on esp.id_local = l.id ";
		$where = " WHERE l.estado=1 ";
		$join  = "";
		if((int)$categoria) {
			$join.= " LEFT JOIN local_categoria as lctg on lctg.id_local = l.id ";
			$where.= " AND lctg.id_categoria = $categoria ";			
//			$fields.= " , lctg.id_categoria as local_categoria ";
		}
		
		if($comodidades !="") {
			$join.= " LEFT JOIN extra_local as lext on lext.id_local = l.id ";
			$where.= " AND lext.id_extra IN (".$comodidades.") ";
		}

		$sql = "SELECT ";
		$sql.= $fields;
		$sql.= " FROM locales as l ";
		$sql.= $join;
		$sql.= $where;
		$sql.= " GROUP BY l.id";
		$sql.= " HAVING dist < ({$radius} * {$radius}) ORDER BY dist ASC LIMIT $ini, $fin;";

		$res = $this->_db->query($sql)->fetchAll();
        return $res;
    }

    public function getLocalByFBID($fb_id) {
	
		$sql = $this->select()->where("fb_pageid =".(int)$fb_id);
		return $this->fetchAll($sql)->toArray();
	}

	public function guardarCategorias($id_local, $categorias) {
	
		$table = new Zend_Db_Table("local_categoria");
		$table->delete("id_local = ".(int)$id_local);
		for($i=0; $i<sizeof($categorias); $i++) {
			$table->insert(array(
				"id_local" => $id_local,
				"id_categoria" => (int)$categorias[$i]
			));
		}
	}

	public function addReview($id_review, $id_local, $stars, $pesos) {
	
		$this->_db->query("update locales set ultimo_review = ". $id_review .", cantidad_reviews = cantidad_reviews + 1 where id =".(int)$id_local);
		$this->addCalificacion($id_local, $stars, $pesos);		
		$this->calcularPromedios($id_local);

	}

    public function latnlongAlreadyUp($lat, $long) {

        $sql   = $this->select()->where("latitud = $lat AND longitud = $long");
        $res   = $this->fetchAll($sql)->toArray();
        return sizeof($res) ? true : false;
    }

	public function getHighlights($localidad = 1, $cant = 25){


		$sql = "SELECT id, nombre, direccion, logo_mobile, stars, pesos, logo, latitud, longitud, cantidad_checkins, cantidad_reviews, ";
		$sql.= " (cantidad_reviews + cantidad_checkins) AS cantidad_calificaciones";
		$sql.= " FROM locales";
		$sql.= " WHERE estado=1 AND localidad=".$localidad;
		$sql.= " ORDER BY puntaje_total_estrellas DESC";
		$sql.= " LIMIT ".$cant;

		$res = $this->_db->query($sql)->fetchAll();
        return $res;
	}

    public function addLocal($localData, $categorias) {

        $id_local = $this->insert($localData);
		for($i = 0; $i < sizeof($categorias); $i++) {
			$r = $this->_db->query("INSERT INTO local_categoria SET id_local = ".(int)$id_local." , id_categoria = ".(int)$categorias[$i]);
		}
		return $id_local;
    }

    public function getLocalById( $id = null ){

        if(is_null( $id ))
           return false;

        $where  =   "id = $id ";
        $sql = $this->select()
                        ->from($this,array('*'))
//						->joinLeft(array('r'=>'reviews'), 'locales.id = r.id_local',array("id as id_review, rating, titulo, comentario"))
                        ->where( $where );

        $r = $this->fetchAll($sql)->toArray();

        return sizeof($r) ? $r[0] : false;

    }

	public function getHorariosLocal($id_local) {
		
		$table = new Zend_Db_Table("horarios");
		$sql = $table->select()->where("id_local = ".$id_local);

		return $table->fetchAll($sql)->toArray();
	}

    public function getLocales($params){


		/*
		 * SELECT *, ((latitud - (-32.944886)) * ( latitud - (-32.944886) ) + ( longitud - (-60.653558) ) * ( longitud - (-60.653558))) AS dist FROM locales where nombre like "%pizza%" ORDER BY dist ASC LIMIT 15;
		 *
		 */

		$radius = 0.015;

		if($params["lat"]) {
			$latitud  = (float) $params['lat'];
			$longitud = (float) $params['lng'];
		}else{
			$latitud = 0;
		}

		$this->_db->query("INSERT INTO search set q = ".$this->_db->quote($params["q"]));


		$fields = "l.latitud, l.longitud, l.id, l.nombre, l.direccion, l.logo_mobile, l.stars, l.pesos, l.cantidad_reviews, l.cantidad_checkins, l.logo, SUBSTRING(l.descripcion, 1, 140) as descripcion ";
		$fields.= " ,(l.cantidad_reviews + l.cantidad_checkins) AS cantidad_calificaciones";
		if($latitud) {
			$fields.= " , ((latitud - ({$latitud})) * ( latitud - ({$latitud}) ) + ( longitud - ({$longitud}) ) * ( longitud - ({$longitud}))) AS dist";
		}
	
		$sql = "SELECT ";
		$sql.= $fields;
		$sql.= " FROM locales as l ";

		$q = urldecode($params['q']);

		$where = " WHERE (l.nombre LIKE '%{$q}%' OR l.keywords LIKE '%{$q}%') AND estado=1 ";

		$cat = isset($params['categoria']) && (int) $params["categoria"] ? $params['categoria'] : false;

		if($cat) {

			$where.= " AND lc.id_categoria = {$cat}";
			$sql.= " LEFT JOIN local_categoria lc ON lc.id_local = l.id";
		}

		$sql .= $where;
		$sql .= " GROUP by l.id";

		if($latitud) {

			$sql.= " HAVING dist < ({$radius} * {$radius})";
			$sql.= " ORDER BY dist ASC";

		}else{
			$sql.= " ORDER BY puntaje_total_estrellas DESC";
		}
		
		if($q != 'colectividades'){
			$sql.= " LIMIT 25";
		}

		$res = $this->_db->query($sql)->fetchAll();
        return $res;

		/*
        $where = '';
        $p = 0;

        if(isset($params['idlocalidad'])){
            $where .= ' locales.localidad = '.$params['idlocalidad'];
            $p = 1;
        }
	
        if(isset($params['nombre'])){
            if($p==1){
                $where .= ' AND ';
            } else{ $p=1; }

			$params['nombre'] = urldecode($params['nombre']);
            
			$where .= ' locales.nombre  LIKE  "%'.$params['nombre'].'%"';
        }

        $sql = $this->select()->from($this,array('*'));

		if(isset($params['categoria']) && (int)$params['categoria']){
           	if($p==1){
                	$where .= ' AND ';
           	}else{ $p=1; }			
           	$where .= ' lc.id_categoria = '.$params['categoria'];
			
			$sql->setIntegrityCheck(false)->joinLeft(array('lc'=>'local_categoria'), 'lc.id_local = locales.id');
        }

		*/

   		//$sql->where($where)->order("locales.stars DESC")->limit(15);
        //$r = $this->fetchAll($sql);
        //return $r->toArray();
    }

	public function addCheckin($id_local){
		$sql = "UPDATE `locales` set cantidad_checkins = cantidad_checkins+1 WHERE id=".$id_local;
		$r = $this->_db->query($sql);
		return 1;
//		return $r->toArray();
	}

	public function addTip($id_local){
		$sql = "UPDATE `locales` set cantidad_tips = cantidad_tips+1 WHERE id=".$id_local;
		$r = $this->_db->query($sql);
		     
		return 1;
	}

	public function addFan($id_local){
		$sql = "UPDATE `locales` set cant_fans = cant_fans+1 WHERE id=".$id_local;
		$r = $this->_db->query($sql);
		     
		return 1;
	}
	public function restFan($id_local){
		$sql = "UPDATE `locales` set cant_fans = cant_fans-1 WHERE id=".$id_local;
		$r = $this->_db->query($sql);
	     
		return 1;
	}

	public function getLocalFanByUserId($userId) {
	
		$sql = "SELECT * FROM fans left join locales on fans.id_local = locales.id WHERE fans.id_user = $userId AND locales.estado=1";
		$r   = $this->_db->query($sql);
		return $r->fetchAll();
	}

	public function getLocalFans($id_local, $cant = null) {
	
		$sql = "SELECT * FROM fans WHERE fans.id_local = ".(int)$id_local;
		if($cant != null) {
			$sql .= " LIMIT ".(int) $cant;
		}
		$r   = $this->_db->query($sql);
		return $r->fetchAll();
	}

	public function addCalificacion($id_local,$puntaje_estrellas,$puntaje_pesos){

//		$data = array('cantidad_tips=>'cantidad_tips+1','puntaje_total_estrellas'=>'puntaje_total_estrellas+'.$puntaje_estrellas,'puntaje_total_pesos'=>'puntaje_total_pesos+'.$puntaje_pesos);
//		$sql = $this->update($data,'id='.$id_local);

//		$r = $this->fetchAll($sql);
		     
		$sql = "UPDATE `locales` set puntaje_total_estrellas = puntaje_total_estrellas + ".$puntaje_estrellas.",puntaje_total_pesos = puntaje_total_pesos+".$puntaje_pesos." WHERE id=".$id_local;

		$r = $this->_db->query($sql);
		return $r;
	}

	public function calcularPromedios($id_local){
		
//		$data = array('rating'=>'puntaje_total_estrellas/cantidad_checkins','stars'=>'puntaje_total_estrellas/cantidad_checkins');

//		$sql = $this->update($data,'id='.$id_local);

//		$r = $this->fetchAll($sql);
		$sql = "UPDATE locales SET stars = puntaje_total_estrellas/( cantidad_checkins + cantidad_reviews), pesos = puntaje_total_pesos/ ( cantidad_checkins + cantidad_reviews ) WHERE id=".$id_local;
		$r = $this->_db->query($sql); 
		return $r;
	}

	public function TopRated($cant,$page = 0){
		// paginador
		$ini = $cant * $page;
		$fin = $ini + $cant;
		
		$sql = $this->select()
					->order("puntaje_total_estrellas DESC")
					->limit($cant,$ini);
		$res = $this->fetchAll($sql)->toArray();
		return $res;
	}

	public function LastCreated($cant){
	
		$sql = $this->select()
					->where("estado=1")
					->order("fecha_creacion DESC")
					->limit($cant);
		$res = $this->fetchAll($sql)->toArray();
		return $res;
	}

	public function searchLocales($params){

//		if( !$params['patron'] )
//			return null;

		$patron = $params['patron'];
		$page = $params['page'];
		$itemsPerPage = $params['itemsPerPage'];
		$where = null;
		$keywords = $params['keywords'];

		if($keywords != "") {
			$keywords = preg_replace("/\s+/","%", $keywords);
			$where = "keywords LIKE '%$keywords%'";
		}
		else if( $params['patron'] )
		        $where = " nombre LIKE '%$patron%' OR descripcion LIKE '%$patron%' OR keywords LIKE '%$patron%'";

        	$sql = $this->select()
//                    ->from($this,array( 'id', 'nombre', 'descripcion', 'logo', 'localidad', 'direccion', 'telefono', 'telefono2', 'telefono3', 'stars', 'web', 'pesos'  ))
                    ->from($this,new Zend_Db_Expr( 'SQL_CALC_FOUND_ROWS *' ));
			
		if( !is_null( $where ) )
                    $sql->where($where);

		if( isset( $params['localidad'] ) )
			$sql->where( 'localidad = ? ', $params['localidad'] );

		if( isset( $params['categorias'] ) ){
			$sql->joinLeft( array( 'lc' => 'local_categoria' ), 'lc.id_local=locales.id' ) ;
			$sql->setIntegrityCheck( false );
			$sql->where(  "lc.id_categoria IN ( {$params['categorias']}  )"   );

		}

		$order = ( isset( $params['order'] ) ) ? strtoupper( $params['order'] ) : 'DESC';

		$sql->order( "stars $order");
		$sql->order( "nombre ASC" )->limit( $itemsPerPage, $page * $itemsPerPage );

//		die($sql);
//			echo $sql;
		$sql->where("estado=1");
        	$r = $this->fetchAll($sql);

		if( !$r )
			return null;

	    	$sql = "SELECT FOUND_ROWS() AS total";
    		$rs = $this->_db->fetchAll( $sql );

		$data['locales'] = $r->toArray();
		$data['total'] = $rs[0]['total'];

		return $data;

    	}
		
	public function CantLocales(){

		$datos = array();

		//todos los locales
		$sql = $this->select()->from($this,array("count(*) as cant"));
		$data = $this->fetchAll($sql)->toArray();
		$datos["cantidad"] = $data[0]["cant"];
		
		return $datos;
	}

	public function getAllExtras() {

		$table = new Zend_Db_Table("extras");
		return $table->fetchAll()->toArray();
	}

	public function actualizarLocal($data, $id, $extras, $categorias, $horarios) {


		// actualizo info del local
		$this->update($data, "id = ".(int)$id);
		
		// actualizo las comodidades y extras del local
		$sql = "DELETE FROM extra_local WHERE id_local = ".(int)$id;
		$this->_db->query($sql);

		if(sizeof($extras)) {	
			$sql = "INSERT INTO extra_local(id_local, id_extra, yesno) VALUES ";
			foreach($extras as $x) {
				$sql.= "(".$id.",".$x.",1),";
			}
			$sql = substr($sql, 0, -1);
			$this->_db->query($sql);
		}

		// actualizo las categorias
		$sql = "DELETE FROM local_categoria WHERE id_local = ".(int)$id;
		$this->_db->query($sql);

		if(sizeof($categorias)) {
			$sql = "INSERT INTO local_categoria(id_local, id_categoria) VALUES ";
			foreach($categorias as $x) {
				$sql.= "(".$id.",".$x."),";
			}
			$sql = substr($sql, 0, -1);
			$this->_db->query($sql);
		}

		$sql = "DELETE FROM horarios WHERE id_local = ".(int)$id;
		$this->_db->query($sql);
	
		$table = new Zend_Db_Table("horarios");
		foreach($horarios as $h) {
			$table->insert(array(
				"id_local" => (int) $id,
				"dia" => $h["dia"],
				"hora_ini" => $h["hora_ini"],
				"hora_fin" => $h["hora_fin"]
			));
			/*
			if($k<1 || $k>7) continue;

			if($h["cerrado"]) {
				$hora = "";
			}else{
				$hora = $h["ini"]."-".$h["fin"];
			}
			$sql .= ", `".(int)$k. "` = ".$this->_db->quote($hora);
			 */
		}

	}

	public function getLocalByActivationKey($act_url) {

		$sql = $this->select()
				->from(array("l"=>"locales"))
				->where("l.activation_url = ".$this->_db->quote($act_url));
//				->joinLeft(array('u'=>'usuarios'), 'u.id=l.activation_userid')
//				->setIntegrityCheck(false);
		
		//		die($sql);
		//
		$res = $this->fetchAll($sql)->toArray();
		return sizeof($res) ? $res[0] : false;
	}

	public function updateUserPropietario($id_local, $act_key, $id_user) {

		if(trim($act_key) == "") return false;

		$sql = "UPDATE locales SET ";
		$sql.= "id_user_propietario = ".(int)$id_user.", ";
		$sql.= "activation_key      = '' ";
		$sql.= "WHERE id = ".(int)$id_local." ";
		$sql.= "AND activation_key = ".$this->_db->quote($act_key).";";

		return $this->_db->query($sql);
	}

	public function getExtrasByLocal($id) {
		
		$extras = $this->getAllExtras();
		
		$table = new Zend_Db_Table("extra_local");
		$sql = $table->select()->where("id_local = ".(int)$id);
		$extras_local = $table->fetchAll($sql)->toArray();

		foreach($extras as $key => $value) {

			foreach($extras_local as $k => $v) {
				if($v["id_extra"] == $value["id"]) {
					$value["yesno"] = $v["yesno"];
				   	$extras[$key] = $value;	
				}			
			}

		}
		return $extras;
	}

}
