<?php
class Form_Locales extends Zend_Form {

  public function init(){
        $this->setEnctype( 'UTF-8' );
//                $this->setEnctype( Zend_Form::ENCTYPE_MULTIPART );
  	
  		$this->loadDefaultDecorators();	
//		$this->addDecorator('HtmlTag',array('tag'=>'div','class'=>'contenido_registro'));
		
		$obj = $this->createElement( 'hidden', 'id' );
		$obj->addDecorator('HtmlTag',array('tag'=>'div'));
		$obj->removeDecorator('Label');

		$this->addElement( $obj );
		
  		//nombre element
		$obj = $this->createElement('text','nombre', array( 'maxlength'=>'999' ));
		$obj->setLabel('* Nombre')
			->setRequired( true );
//		$obj->addDecorator('HtmlTag',array('tag'=>'div','class'=>'inputs'));

//		$obj->addDecorator('Label',array('tag'=>'div','class'=>'labels'));

		$this->addElement($obj);

  		//descripcion element
		$obj = $this->createElement('textarea','descripcion',array( "cols" => 50, "rows" => 10 ) );
		$obj->setLabel('* Descripcion');

//		$obj->addFilter('HtmlEntities');
//		$obj->addDecorator('Label',array('tag'=>'div','class'=>'labels'));
		$this->addElement($obj);

  		//lat element
		$obj = $this->createElement('text','latitud' );
		$obj->setLabel('* Latitud');

//		$obj->addDecorator('HtmlTag',array('tag'=>'div','class'=>'inputs'));
//		$obj->addDecorator('Label',array('tag'=>'div','class'=>'labels'));

		$this->addElement($obj);
  		
		//long element
		$obj = $this->createElement('text','longitud' );
		$obj->setLabel('* Longitud');

//		$obj->addDecorator('HtmlTag',array('tag'=>'div','class'=>'inputs'));
//		$obj->addDecorator('Label',array('tag'=>'div','class'=>'labels'));ç

		$this->addElement($obj);

	
  		//web element
		$obj = $this->createElement('text','web' );
		$obj->setLabel('* Web');
		$this->addElement($obj);

		//fb element
		$obj = $this->createElement('text','facebook' );
		$obj->setLabel('* Facebook');
		$this->addElement($obj);

		$obj = $this->createElement('text','elmetre' );
		$obj->setLabel('* El Metre');
		$this->addElement($obj);

  		//usuario propietario element
/*		$obj = $this->createElement('text','id_user_propietario_hlp');
		$obj->setLabel('Propietario');
		$this->addElement($obj);
/*
		$obj = $this->createElement('hidden','id_user_propietario');
		$this->addElement($obj);
*/
		//tel element
		$obj = $this->createElement('text','telefono' );
		$obj->setLabel('* Tel');

//		$obj->addDecorator('HtmlTag',array('tag'=>'div','class'=>'inputs'));
//		$obj->addDecorator('Label',array('tag'=>'div','class'=>'labels'));

		$this->addElement($obj);

		//dir element
		$obj = $this->createElement('text','direccion' );
		$obj->setLabel('* Direccion');

//		$obj->addDecorator('HtmlTag',array('tag'=>'div','class'=>'inputs'));
//		$obj->addDecorator('Label',array('tag'=>'div','class'=>'labels'));

		$this->addElement($obj);



  		//localidad element
		$obj = $this->createElement('select','localidad' );
		$obj->setMultiOptions( array( '1' => 'Rosario', '2' => 'Santa Fe', '3' => 'Punta del Este' ) );
		$obj->setValue( '1' );
		$obj->setLabel('Localidad');

//		$obj->addDecorator('HtmlTag',array('tag'=>'div','class'=>'inputs'));
//		$obj->addDecorator('Label',array('tag'=>'div','class'=>'labels'));

		$this->addElement( $obj );
		
  		//keywords element
		$obj = $this->createElement('text','keywords' );
		$obj->setLabel('* Keywords');
		$this->addElement($obj);


  		//descripcion element
		$obj = $this->createElement('textarea','horario',array( "cols" => 50, "rows" => 10 ) );
		$obj->setLabel('* Horario');

//		$obj->addFilter('HtmlEntities');
//		$obj->addDecorator('Label',array('tag'=>'div','class'=>'labels'));
		$this->addElement($obj);

		//thumb element
		$obj = $this->createElement('file','imagen' );
		$obj->setdestination("./resources/locales");
		$obj->setIgnore(true);
		$obj->setLabel('* Imagen');

//		$obj->addDecorator('HtmlTag',array('tag'=>'div','class'=>'inputs'));
//		$obj->addDecorator('Label',array('tag'=>'div','class'=>'labels'));

		$this->addElement($obj);


		$model = new Model_Categorias();
		$cats = $model->getCategorias();
		foreach ($cats as $cat){
        	$categorias[$cat['id']] = utf8_encode($cat['nombre']);
        }

		$obj = $this->CreateElement('MultiCheckbox', 'categorias')
              		 ->setLabel('Categorias')
					 ->setRequired(false)
					 ->setAttrib('escape', false)
                     ->setMultiOptions($categorias);

		$obj->getDecorator('Label')->setOption('escape',false);
		$this->addElement($obj);


    	//submit
    	$submit = $this->createElement('submit','Guardar', array( 'ignore' => true ) );
    	$submit->setValue( 'Guardar' );
    
//		$submit->addDecorator('HtmlTag',array('tag'=>'div','class'=>'inputs'));
		$submit->removeDecorator('Label');

    	$this->addElement($submit);

  }

}
?>
