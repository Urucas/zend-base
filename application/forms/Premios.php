<?php
class Form_Premios extends Zend_Form {

  public function init(){
 
	  $this->setEnctype( 'UTF-8' );
//                $this->setEnctype( Zend_Form::ENCTYPE_MULTIPART );
  	
  		$this->loadDefaultDecorators();	
//		$this->addDecorator('HtmlTag',array('tag'=>'div','class'=>'contenido_registro'));
		
		$obj = $this->createElement( 'hidden', 'id' );
		$obj->addDecorator('HtmlTag',array('tag'=>'div'));
		$obj->removeDecorator('Label');

		$this->addElement( $obj );
	
		// titulo element
		$obj = $this->createElement('text','titulo');
		$obj->setLabel('* Titulo');
		$this->addElement($obj);

  		//descripcion element
		$obj = $this->createElement('textarea','descripcion',array( "cols" => 50, "rows" => 10 ) );
		$obj->setLabel('* Descripcion');

//		$obj->addFilter('HtmlEntities');
//		$obj->addDecorator('Label',array('tag'=>'div','class'=>'labels'));
		$this->addElement($obj);

		$obj = $this->createElement('text','stock');
		$obj->setLabel('* Stock');
		$this->addElement($obj);

		$obj = $this->createElement('text','puntos');
		$obj->setLabel('* Puntos necesarios');
		$this->addElement($obj);

		$obj = $this->createElement('text','link');
		$obj->setLabel('* Link');
		$this->addElement($obj);

		$obj = $this->createElement('text','fecha_ini');
		$obj->setLabel('* Fecha Inicio');
		$this->addElement($obj);

		$obj = $this->createElement('text','fecha_fin');
		$obj->setLabel('* Fecha Fin');
		$this->addElement($obj);

		$obj = $this->createElement('file','imagen' );
		$obj->setdestination("./resources/premios");
		$obj->setIgnore(true);
		$obj->setLabel('* Imagen');
		$this->addElement($obj);

		/*
		 * cambiar por localidad 
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
		 */

    	//submit
    	$submit = $this->createElement('submit','Guardar', array( 'ignore' => true ) );
    	$submit->setValue( 'Guardar' );
    
//		$submit->addDecorator('HtmlTag',array('tag'=>'div','class'=>'inputs'));
		$submit->removeDecorator('Label');

    	$this->addElement($submit);

  }

}
?>
