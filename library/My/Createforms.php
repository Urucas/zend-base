<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of createForms
 *
 * @author gato
 */
class My_Createforms {
    //put your code here

    private $form;

    public function  __construct( $table ) {

        $this->form = new Zend_Form();

        foreach( $table as $fieldName => $field ){

            $type   =   $field['DATA_TYPE'];
            switch( $type ){

                case 'varchar':

                    if( $field['COLUMN_NAME']   !=  'password' ){

                        $this->createTextBoxElement( $field );

                    }   else    {

                        $this->createPasswordElement( $field );

                    }
                    break;

                case 'text':

                        $this->createTextAreaElement( $field );

                        break;

                case 'timestamp':
                        $this->createDateElement( $field );

                    break;

                 case 'int':

                        $pos    = strpos( $field['COLUMN_NAME'], "d_" );

                        if( $pos > 0 ){
                            
                            $this->createSelectElement( $field );
                            
                        }   elseif( $field['COLUMN_NAME'] == 'id' ){

                            $this->createHiddenElement( $field );

                        }   else    {

                            $this->createTextBoxElement( $field );

                        }

                     break;

            }

        }

    }

    private function createTextBoxElement( $field ){
//        Textbox Element

        $element = new Zend_Form_Element_Text( $field['COLUMN_NAME'],  array( 'maxlength'=> $field['LENGTH'] ) );
        $element->setLabel( ucfirst( $field['COLUMN_NAME'] ) ) ;
        $this->form->addElement( $element );

    }

    private function createTextAreaElement( $field ){
//        Textbox Element

        $element = new Zend_Form_Element_Textarea( $field['COLUMN_NAME'],  array( 'maxlength'=> $field['LENGTH'], 'cols'=>'80', 'rows' => '10' ) );
        $element->setLabel( ucfirst( $field['COLUMN_NAME'] ) ) ;
        $this->form->addElement( $element );

    }

    private function createPasswordElement( $field ){
//        Password Element

        $element = new Zend_Form_Element_Password( $field['COLUMN_NAME'],  array( 'maxlength'=> $field['LENGTH'] ) );
        $element->setLabel( ucfirst( $field['COLUMN_NAME'] ) ) ;
        $element->addValidator( 'regex',true,array('/[\w]+/') )
                ->addValidator('stringLength', true, array(5, $field['LENGTH'] ));
        $this->form->addElement( $element );

    }

    private function createDateElement( $field ){
//        DatePicker Element

        $id =   strtolower( $field['COLUMN_NAME'] );
        echo '<script>$(function() {$( "#' . $id . '" ).datepicker();});</script>';

        $element = new Zend_Form_Element_Text( $field['COLUMN_NAME'],  array( 'id'=> $id ) );
        $element->setLabel( ucfirst( $field['COLUMN_NAME'] ) ) ;
        $this->form->addElement( $element );

    }

    private function createFileElement( $field ){
//            File Element

//                $obj = $this->createElement( 'file', 'imgurl' );
//		$obj->setdestination( $config->categorias );
//		$obj->addValidator('Extension', false, 'jpeg,jpg,png,gif');
//		$obj->addValidator('ImageSize', false,
//                      array('minwidth' => 100,
//                            'maxwidth' => 140,
//                            'minheight' => 50,
//                            'maxheight' => 100)
//                      );
//		$obj->setLabel( "Imagen: ");

        $element = new Zend_Form_Element_File( $field['COLUMN_NAME'], $options);
        $element->setLabel( ucfirst( $field['COLUMN_NAME'] ) ) ;
        $element->addValidator( 'regex',true,array('/[\w]+/') )
                ->addValidator('stringLength', true, array(5, $field['LENGTH'] ));
        $this->form->addElement( $element );

    }

    private function createHiddenElement( $field ){
//        Hidden Element

        $element = new Zend_Form_Element_Hidden( $field['COLUMN_NAME'] );
        $this->form->addElement( $element );

    }

    private function createSelectElement( $field ){
//        Select Element

        $element = new Zend_Form_Element_Select( $field['COLUMN_NAME'] );
        $relationTable  =   explode( '_', $field['COLUMN_NAME'] );
        $element->setLabel( ucfirst( $relationTable[1] ) ) ;
        $model = "Model_".ucfirst( $relationTable[1] );
        $model = new $model;
        $record =   $model->listar();

        if( $record ){
            foreach ( $record as $key => $row )
                 $records[ $row['id'] ] =   $row[ $model->getOrder() ];

            $element->setMultiOptions( $records );
        }

        $this->form->addElement( $element );

    }

    private function createSubmitElement(){

//            Button Element
        $element    =   new Zend_Form_Element_Submit('Guardar', array( 'ignore' => true ) );
    	$this->form->addElement( $element );

    }

    public function getForm(){

        $this->createSubmitElement();
        return $this->form;

    }
}
?>
