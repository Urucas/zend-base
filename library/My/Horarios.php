<?php 

class My_Horarios extends Zend_Form_Element {


	public function render() {

		for($i = 0; $i<24;$i++) {
			$horas[] = $i <10 ? "0".$i.":00" : $i.":00";
			$horas[] = $i <10 ? "0".$i.":30" : $i.":30";
		}

		$horarios = $this->getValue();

		$dias = array("lunes", "martes", "miercoles","jueves","viernes", "sabados", "domingos", "feriados");

				
		$html = '<div id="form_horario_local">';
		$html.= '<label for="categorias" class="optional">Horario</label>';
		$html.= '<div id="horarios_list">';

		foreach($horarios as $horario) {

			$fid = md5($horario["dia"]."-".$horario["hora_ini"]."-".$horario["hora_fin"]);
			$html .= '<div id="horario_'.$fid.'" class="unhorario">';
			$html .= '<input type="hidden" name="horarios['.$fid.'][dia]" value="'.$horario["dia"].'" />';
			$html .= '<input type="hidden" name="horarios['.$fid.'][hora_ini]" value="'.$horario["hora_ini"].'" />';
			$html .= '<input type="hidden" name="horarios['.$fid.'][hora_fin]" value="'.$horario["hora_fin"].'" />';
			$html .= '<p>'.$dias[$horario["dia"]].' de '.$horario["hora_ini"].' a '.$horario["hora_fin"];
			$html .= ' <a onclick="$(\'#horario_'.$fid.'\').remove()">eliminar</a>';
			$html .= '</p>';
			$html .= '</div>';
		}
		
		$html.= '</div>';
		$html.= '<div class="subform_horario">';
		$html.= '<select id="horario_dia">';
		foreach($dias as $i => $dia) {
			$html.= '<option value="'.$i.'">'.$dia.'</option>';
		}
		$html.= "</select>";
		$html.= "<span> de </span>";

		$html.= '<select id="horario_hora_ini">';
		foreach($horas as $hora) {
			$html.= '<option value="'.$hora.'">'.$hora.'</option>';
		}
		$html.= "</select>";
		$html.= "<span> a </span>";
		$html.= '<select id="horario_hora_fin">';
		foreach($horas as $hora) {
			$html.= '<option value="'.$hora.'">'.$hora.'</option>';
		}
		$html.= "</select>";
		$html.= '<a onclick="kuesty.addhorariolocal()" class="lindo-boton">Agregar horario</a>';
		
		$html.= '</div>';
		$html.= '</div>';
		
		return $html;
		
	}
}
?>
