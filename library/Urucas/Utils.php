<?php 
abstract class Urucas_Utils {

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


}
