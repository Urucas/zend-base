<?php
class Form_Local extends Zend_Form {

  public function init(){
        $this->setEnctype( 'UTF-8' );
//                $this->setEnctype( Zend_Form::ENCTYPE_MULTIPART );
  	
  		$this->loadDefaultDecorators();	
		
		$obj = $this->createElement( 'hidden', 'id' );
		$obj->addDecorator('HtmlTag',array('tag'=>'div'));
		$obj->removeDecorator('Label');

		$this->addElement( $obj );
		
  		//nombre element
		$obj = $this->createElement('text','nombre', array( 'maxlength'=>'999' ));
		$obj->setLabel('* Nombre')
			->setRequired( true );

		$this->addElement($obj);

  		//descripcion element
		$obj = $this->createElement('textarea','descripcion',array( "cols" => 50, "rows" => 10 ) );
		$obj->setLabel('* Descripcion');
		$this->addElement($obj);

  		//web element
		$obj = $this->createElement('text','web' );
		$obj->setLabel('Web');
		$this->addElement($obj);

		//fb element
		$obj = $this->createElement('text','facebook' );
		$obj->setLabel('Facebook');
		$this->addElement($obj);

		$obj = $this->createElement('text','twitter' );
		$obj->setLabel('Twitter');
		$this->addElement($obj);

		//tel element
		$obj = $this->createElement('text','telefono' );
		$obj->setLabel('* Tel');

		$this->addElement($obj);

		//dir element
		$obj = $this->createElement('text','direccion' );
		$obj->setLabel('* Direccion');

		$this->addElement($obj);

  		//localidad element
		$obj = $this->createElement('select','localidad' );
		$obj->setMultiOptions( array( '1' => 'Rosario') );
		$obj->setValue( '1' );
		$obj->setLabel('Localidad');
		$obj->setAttribs(array("class"=>"selects-lindos"));
		//$obj->addDecorator('HtmlTag',array('class'=>'selects-lindos'));
		$this->addElement( $obj );
		
  		//keywords element
		$obj = $this->createElement('text','keywords' );
		$obj->setLabel('* Keywords');
		$this->addElement($obj);


		//descripcion element
		/*
		$obj = $this->createElement('textarea','horario',array( "cols" => 50, "rows" => 10 ) );
		$obj->setLabel('* Horario');
		$this->addElement($obj);
		 */

		//thumb element
		$obj = $this->createElement('file','imagen' );
		$obj->setdestination("./resources/locales");
		$obj->setIgnore(true);
		$obj->setLabel('* Imagen');

		$this->addElement($obj);


		$obj = new My_Horarios("horarios");
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
		//			->setAttribs(array("class"=>"selects-lindos"))
		//			->setAttribs(array("style"=>"height:180px;"))
                    ->setMultiOptions($categorias);
			
		/*$obj = $this->CreateElement('MultiCheckbox', 'categorias')
              		 ->setLabel('Categorias')
					 ->setRequired(false)
					 ->setAttrib('escape', false)
                     ->setMultiOptions($categorias);
		 */
		$obj->getDecorator('Label')->setOption('escape',false);
		$this->addElement($obj);

		$obj = $this->CreateElement('MultiCheckbox', 'extras')
              		 ->setLabel('Comodidades, extras')
					 ->setRequired(false)
					 ->setAttrib('escape', false);

		$this->addElement($obj);

    	//submit
    	$submit = $this->createElement('submit','Guardar', array( 'ignore' => true ) );
    	$submit->setValue( 'Guardar' );
    
		$submit->removeDecorator('Label');

    	$this->addElement($submit);

  }

}
?>
