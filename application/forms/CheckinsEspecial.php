<?php
class Form_CheckinsEspecial extends Zend_Form {

  public function init(){
        $this->setEnctype( 'UTF-8' );
//                $this->setEnctype( Zend_Form::ENCTYPE_MULTIPART );
  	
  		$this->loadDefaultDecorators();	
		
	
		$obj = $this->createElement('text','fecha_ini');
		$obj->setLabel('* Fecha Inicio')
			->setRequired( true );

		$this->addElement($obj);


		$obj = $this->createElement('text','fecha_fin');
		$obj->setLabel('* Fecha Inicio')
			->setRequired( true );

		$this->addElement($obj);

  		//descripcion element
		$obj = $this->createElement('textarea','descripcion',array( "cols" => 50, "rows" => 10 ) );
		$obj->setLabel('* Descripcion');
		$this->addElement($obj);

  		//dia element
		$obj = $this->createElement('select','dia' );
		$obj->setMultiOptions( array( 
			'0' => 'Todos los dias',
			'1' => 'Lunes',
			'2' => 'Martes',
			'3' => 'Miercoles',
			'4' => 'Jueves',
			'5' => 'Viernes',
			'6' => 'Sabados',
			'7' => 'Domingo'
		));
		$obj->setValue( '0' );
		$obj->setLabel('Dia');
		$obj->setAttribs(array("class"=>"selects-lindos"));
		$this->addElement( $obj );

		

    	//submit
    	$submit = $this->createElement('submit','Guardar', array( 'ignore' => true ) );
    	$submit->setValue( 'Guardar' );
    
		$submit->removeDecorator('Label');

    	$this->addElement($submit);

  }

}
?>
