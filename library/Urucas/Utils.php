<?php 
abstract class My_Utils {

	public static function ago($time)
	{
		$periods = array("segundo", "minuto", "hora", "dia", "semana", "mes", "año", "decada");
		$lengths = array("60","60","24","7","4.35","12","10");

		$now = time();

		$time = strtotime($time);


		$difference     = $now - $time;

		$tense         = "hace ";

		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}

		$difference = round($difference);

		if($difference != 1) {
			$periods[$j].= "s";
		}

		return " $tense $difference $periods[$j]";
	}

	public function localAbierto($horarios) {
		
		$now  = date("G:i:s"); 
		$now  = date('G:i:s', strtotime($now . ' + 4 hours'));	
			
		foreach($horarios as $horario) {
		
			$ini  = strtotime($horario["hora_ini"]);
			$fin  = strtotime($horario["hora_fin"]);
			$now  = strtotime($now);

			// esto para el caso en q se ponga por ej. como en el almacen de pizzas, de 09:00 a 01:00
			if($fin == $ini) {
				return 1;
			}

			if($fin < $ini) {
						
				if($now <= $fin && $now <= $ini) {
					return 1;
				}elseif($now >= $fin && $now >= $ini) {
					return 1;
				}	

			} else {
				if($now >= $ini && $now <= $fin) {
					return 1;
				}
			}
		}
		return 0;
	}

}
