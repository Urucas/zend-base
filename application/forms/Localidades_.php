<?php
class Form_Localidades extends Zend_Form {

  public function init(){

                $this->setEnctype( 'UTF-8' );
//                $this->setEnctype( Zend_Form::ENCTYPE_MULTIPART );
  	
  		$this->loadDefaultDecorators();	
		$this->addDecorator('HtmlTag',array('tag'=>'div','class'=>'contenido_registro'));
		
		$obj = $this->createElement( 'hidden', 'id' );
		$this->addElement( $obj );
		
  		//nombre element
		$obj = $this->createElement('text','nombre', array( 'maxlength'=>'999' ));
		$obj->setLabel('* Titulo')
			->setRequired( true );

		$this->addElement($obj);

  		//pais element
		$obj = $this->createElement('select','idpais' );
//		$obj->setMultiOptions( array( 'es' => 'Espanol', 'en' => 'Ingles', 'pt' => 'Portugues' ) );
//		$obj->setValue( 'es' );
		$obj->setLabel('Pais');

		$this->addElement( $obj );

  		//provincia element
		$obj = $this->createElement('select','idprov' );
//		$obj->setMultiOptions( array( 'es' => 'Espanol', 'en' => 'Ingles', 'pt' => 'Portugues' ) );
//		$obj->setValue( 'es' );
		$obj->setLabel('Provincia');

		$this->addElement( $obj );
		
		//latitud element
		$obj = $this->createElement('text','latitud' );
		$obj->setLabel('* Latitud');

		$this->addElement($obj);

		//longitud element
		$obj = $this->createElement('text','longitud' );
		$obj->setLabel('* Longitud');

		$this->addElement($obj);
		
    	//submit
    	$submit = $this->createElement('submit','Guardar', array( 'ignore' => true ) );
    	$submit->setValue( 'Guardar' );
    
    	$this->addElement($submit);

  }

}
?>