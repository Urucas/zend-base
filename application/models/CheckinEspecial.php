<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_CheckinEspecial extends Model_Db {
	
	protected $_name = 'checkin_especial';
	protected $_primary = 'id';
	protected $_order = 'id';

	public function getCheckinEspecial($id_local) {
	
		$sql = $this->select()->where("id_local = ".(int)$id_local);
		$res = $this->fetchAll($sql)->toArray();

		return sizeof($res) ? $res[0] : false;
	}

	public function getCheckinEspecialById($pid) {
	
		$sql = $this->select()->where("id = ".(int)$pid);
		$res = $this->fetchAll($sql)->toArray();

		return sizeof($res) ? $res[0] : false;
	}

	public function delCheckinEspecial($id_local) {
		return $this->update(array("estado" =>"D", "id_local = ".(int)$id_local));
	}	

	public function getCheckinEspecialFromLocales($ids) {
	
		$sql = $this->select()->where("id_local IN(".$ids.")");
		$res = $this->fetchAll($sql)->toArray();

		return sizeof($res) ? $res : false;
		
	}

	public function addCheckinEspecial($id_local, $checkin) {

		if(!(int)$id_local) return false;

		$ocheckin = $this->getCheckinEspecial((int)$id_local);
		if($ocheckin) {
			
			unset($ocheckin["id"]);

			$table = new Zend_Db_Table("checkin_especial_historicos");
			$table->insert($ocheckin);

			$this->delete("id_local = ".(int)$id_local);
		}

		$checkin["id_local"] = (int) $id_local;

		$cid = $this->insert($checkin);
		return $cid;
	}

	public function hasCheckinEspecial($id_local) {

		// si tiene un checkin especial activo ahora
		$sql = $this->select()->where("id_local = ".(int)$id_local. " AND fecha_ini <= NOW() AND fecha_fin >= NOW() AND estado = 'A'");
		$res = $this->fetchAll($sql)->toArray();

		return sizeof($res) ? $res[0] : false;
	}

	public function checkinAvailableNow($pid) {
		$sql = $this->select()->where("id = ".(int)$pid. "");
	}

	public function userAlreadyUseCheckinEspecialToday($uid, $ceid) {

		$date  = date("Y-m-d");

		$table = new Zend_Db_Table("checkin_especial_desbloqueados");
		$sql   = $table->select()->where("id_checkin_especial = ".(int)$ceid)
			->where("id_usuario = ".(int)$uid)
			->where("fecha = ".$this->_db->quote($date))
			->limit(1);

		$res = $table->fetchAll($sql)->toArray();
		return sizeof($res) ? true : false;
	}

	public function isCheckinActiveNow($cespecial) {

		// checqueo la fecha en que el checkin esta activo
		$fecha_ini = strtotime($cespecial["fecha_ini"]);
		$fecha_fin = strtotime($cespecial["fecha_fin"]);
		$today     = strtotime(date("Y-m-d"));
		if($today < $fecha_ini || $today > $fecha_fin) {
			return false;
		}

		// chequeo el dia de la semana
		$today = date("N");
		if($cespecial["dia"] != $today) {
			return false;		
		}

		// si el checkin es con horario valido la hora
		if($cespecial["sin_horario"] == 0) {

			$now      = (int) str_replace(":", "", date("G:i:s")); 
			$hora_ini = (int) str_replace($cespecial["hora_ini"]);
			$hora_fin = (int) str_replace($cespecial["hora_fin"]);

			if($now < $hora_ini || $now > $hora_fin) {			
				return false;
			}
		}
		
		return true;		
	}

	public function unlockCheckinEspecial($cespecial, $uid) {
	
		$table = new Zend_Db_Table("checkin_especial_desbloqueados");
		return $table->insert(array(
			"id_usuario" => (int) $uid,
			"id_local"   => (int) $cespecial["id_local"],
			"id_checkin_especial" => (int) $cespecial["id"],
			"fecha" => date("Y-m-d")
		));
	}

	public function getCheckinEspecialTimesUnlocked($ceid) {
	
		$table = new Zend_Db_Table("checkin_especial_desbloqueados");
		$sql = $table->select()->where("id_checkin_especial = ".(int)$ceid);

		$res = $table->fetchAll($sql)->toArray();
		return sizeof($res);		
	}

	public function getCheckinEspecialLongDesc($cespecial, $local) {
	
		// agrego la descripcion del checkin especial
		$dias = array(
			"1"=>"lunes", 
			"2"=>"martes", 
			"3"=>"miercoles", 
			"4"=>"jueves", 
			"5"=>"viernes", 
			"6"=>"sabados", 
			"7"=>"domingos" 
		);
		$cespecial_desc = "Todos los ".$dias[$cespecial["dia"]]." ";

		if(!$cespecial["sin_horario"]) {

			$cespecial_desc .= substr($cespecial["hora_ini"],0,5)." a ".substr($cespecial["hora_fin"],0,5); 
		} 
		$cespecial_desc .= " haciendo checkin en ".$local["nombre"]." ".$cespecial["descripcion"];

		return $cespecial_desc;
	}
}
