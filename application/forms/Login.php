<?php
class Form_Login extends Zend_Form {

  public function init(){
  	
//  		$this->loadDefaultDecorators();	
//		$this->addDecorator('HtmlTag',array('tag'=>'div','class'=>'contenido_registro'));
//		$this->addDecorator('Description',array('tag'=>'div','class'=>'rotulos_reg'));

  		//usuario element
		$obj = $this->createElement('text','usuario', array( 'maxlength'=>'40' ));
		$obj->setRequired(true)
//			->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'input_t_reg auto-height'))
//			->addDecorator('Label',array('tag'=>'div','class'=>'rotulos_reg'))
//			->addvalidator('regex',true,array('/[\w]+/'))
//			->addValidator('stringLength', true, array(5,40))
			->setLabel('*Usuario')
			->setRequired(true);

		$this->addElement($obj);

		//password element
		$obj = $this->createElement('password','password', array( 'maxlength'=>'40' ));
		$obj->setRequired(true)
//			->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'input_t_reg auto-height'))
//			->addDecorator('Label',array('tag'=>'div','class'=>'rotulos_reg'))
//			->addValidator('stringLength', true, array(5,40))
//			->addvalidator('regex',true,array('/[\w]+/'))
			->setLabel('*Password')
			->setRequired(true);

		$this->addElement($obj);

    	//submit
    	$submit = $this->createElement('submit','Log in', array( 'value' => 'Acceder' ));
//    	$submit->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'continuar'));
    			
    	$this->addElement($submit);

  }

}
?>
