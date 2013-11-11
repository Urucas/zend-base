<?php
class Form_Extension extends Zend_Form {

  public function processAjax(array $values)
  {
  	$errors = array();
  	$messages = array();
  	$labels = array();
  	foreach($this->getElements() as $element)
  	{
  		$id = $element->getId();

//  		if(!array_key_exists($id,$values))
//  			continue;
  		
  		if($element->isValid($values[$id]))
  			continue;
  		
  		foreach($element->getErrors() as $message)

  			$messages[] = $this->getTranslator()->translate($message);
               
//                $this->getTranslator()->translate( 'Nombre' )
//                $this->translate->_( $this->servicios[ rand(0,11) ]  )
  			$labels[] = $element->getLabel();
  				
			$errors[] = array(
				'element'  => $id,
				'messages' => $messages,
				'labels' => $labels
			);
			
			$messages = array();
			$labels = array();
  	} 
  	 	
  	return !sizeof($errors) ? true : $errors;
  } 

}
?>