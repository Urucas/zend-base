<?php
class Form_Administradores extends Zend_Form {

  public function init(){
  	
  		$this->loadDefaultDecorators();
//  		$this->addAttribs( array( 'onsubmit'	=>	'return jsform.validarpass();' ));
		$this->addDecorator('HtmlTag',array('tag'=>'div','class'=>'contenido_registro'));
		
		$obj = $this->createElement( 'hidden', 'id' );
		$this->addElement( $obj );
		
  		//nombre element
		$obj = $this->createElement('text','nombre', array( 'maxlength'=>'199' ));
		$obj->setLabel('* Nombre')
			->setRequired( true );

		$this->addElement($obj);

		//apellido element
		$obj = $this->createElement('text','apellido', array( 'maxlength'=>'199' ));
		$obj->setLabel('* Apellido')
			->setRequired( true );

		$this->addElement($obj);
		
  		//usuario element
		$obj = $this->createElement('text','usuario', array( 'maxlength' => '99' ));
		$obj->setRequired(	true )
			->addvalidator('regex',true,array('/[\w]+/'))
			->addValidator('stringLength', true, array(5,40))
			->setLabel('* Usuario');
		
		$this->addElement( $obj );
		
//		require( APPLICATION_PATH . '/filters/Md5.php');
//		$md5Filter	= new Filter_Md5();
//		$md5Filter	= new Zend_Filter_Encrypt();
		
  		//password element
		$obj = $this->createElement('password','password', array( 'maxlength'=>'99' ));
		$obj->addvalidator('regex',true,array('/[\w]+/'))
			->addValidator('stringLength', true, array(5,40))
//			->addFilter( $md5Filter )
			->setLabel('* Password');
		
		$this->addElement( $obj );

		//repetir password element
		$obj = $this->createElement('password','repassword', array( 'maxlength'=>'99' ));
		$obj->addvalidator('regex',true,array('/[\w]+/'))
			->addValidator('stringLength', true, array(5,40))
			->setLabel('* Repetir Password')
			->setIgnore(true);
		
		$this->addElement( $obj );

    	//submit
    	$submit = $this->createElement('button','Guardar', array( 'ignore' => true, 'onclick' => 'javascript:jsform.validarpass();' ) );
    	$submit->setValue( 'Guardar' );
    
    	$this->addElement($submit);

  }

}
?>