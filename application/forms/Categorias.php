<?php
class Form_Categorias extends Zend_Form {

  public function init(){
  	
  		$this->loadDefaultDecorators();	
		$this->addDecorator('HtmlTag',array('tag'=>'div','class'=>'contenido_registro'));
		
		$obj = $this->createElement( 'hidden', 'id' );
		$this->addElement( $obj );
		
  		//nombre element
		$obj = $this->createElement('text','nombre', array( 'maxlength'=>'999' ));
		$obj->setLabel('* Titulo')
			->setRequired( true );

		$this->addElement($obj);
		
		//descripcion element
		$obj = $this->createElement('textarea','descripcion', array( 'cols'=>'80', 'rows' => '10' ));
		$obj->setLabel('* Descripcion');

		$this->addElement($obj);

		$config = new Zend_Config_Ini( APPLICATION_PATH . '/configs/application.ini', 'paths' );

		//imgurl element
		$obj = $this->createElement( 'file', 'imgurl' );
		$obj->setdestination( $config->categorias );
		$obj->addValidator('Extension', false, 'jpeg,jpg,png,gif');
//		$obj->addValidator('ImageSize', false,
//                      array('minwidth' => 100,
//                            'maxwidth' => 140,
//                            'minheight' => 50,
//                            'maxheight' => 100)
//                      );
		$obj->setLabel( "Imagen: ");
		$this->addElement( $obj );
		
    	//submit
    	$submit = $this->createElement('submit','Guardar', array( 'ignore' => true ) );
    	$submit->setValue( 'Guardar' );
    
    	$this->addElement($submit);

  }

}
?>