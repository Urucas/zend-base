<?php
//class Form_Contacto extends Zend_Form {

require( APPLICATION_PATH . '/forms/Extension.php' );

class Form_Contacto extends Form_Extension {

  public function init(){
//  	 echo $this->getTranslator()->translate("error404");die();
		//nombre element
		$obj = $this->createElement('text','nombre', array( 'maxlength'=>'50' ));
		$obj->setLabel('* ' .  $this->getTranslator()->translate( 'Nombre' ) )
			->setRequired( true );

		$this->addElement($obj);
		
  		//email element
		$obj = $this->createElement('text','email', array( 'maxlength'=>'50' ));
		$obj->setLabel('* Email')
			->addValidator('EmailAddress')
			->setRequired( true );

		$this->addElement($obj);

  		//asunto element
		$obj = $this->createElement('text','asunto', array( 'maxlength'=>'100' ));
		$obj->setLabel('Asunto');
		
		$this->addElement( $obj );
		
  		//consulta element
		$obj = $this->createElement('textarea','consulta', array( "cols" => 50, "rows" => 10 ) );
		$obj->setRequired(true)
			->setLabel('* ' .  $this->getTranslator()->translate( 'Consulta' ));
		
		$this->addElement( $obj );
			
    	//submit
    	$submit = $this->createElement('submit','Guardar', array( 'ignore' => true ) );
        $label  =   $this->getTranslator()->translate( 'Enviar consulta' );
    	$submit->setLabel(  $label );
    
    	$this->addElement($submit);

  }

}
?>