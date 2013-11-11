<?php

class ExtensionBackController extends Zend_Controller_Action
{
	
    protected $provincias	=	array() ;

    protected function editLocalidades( $form ){

    	$form->idprov->options	=	$this->getProvincias();
    	
    }


    protected function getProvincias(){
    	
		$this->provincias	=	array(	
                                                        '1' =>	'Buenos Aires',
                                                        '2'	=>	'Catamarca',
                                                        '3'	=>	'Chaco',
                                                        '4'	=>	'Chubut',
                                                        '5'	=>	'Cordoba',
                                                        '6'	=>	'Corrientes',
                                                        '7'	=>	'Entre Rios',
                                                        '8'	=>	'Santa Fe',
                                                        '9'	=>	'Formosa',
                                                        '10'	=>	'Jujuy',
                                                        '11'	=>	'La Pampa',
                                                        '12'	=>	'La Rioja',
                                                        '13'	=>	'Mendoza',
                                                        '14'	=>	'Misiones',
                                                        '15'	=>	'Neuquen',
                                                        '16'	=>	'Rio Negro',
                                                        '17'	=>	'Salta',
                                                        '18'	=>	'San Juan',
                                                        '19'	=>	'San Luis',
                                                        '20'	=>	'Santa Cruz',
                                                        '21'	=>	'Santiago del Estero',
                                                        '22'	=>	'Tierra del Fuego',
                                                        '23'	=>	'Tucuman'
                                                ) ;
			
		asort( $this->provincias, SORT_STRING);
		return $this->provincias;
    	
    }

    protected function paginar( $pages, $itemsPerPage = 5 ){

		Zend_View_Helper_PaginationControl::setDefaultViewPartial('modules/paginador.phtml');
		
//		echo "route => " . Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName();

		$data = range( 1, $pages );
		$paginator = Zend_Paginator::factory( $data );
		
		$paginator->setDefaultScrollingStyle('Sliding');
		
		$paginator->setCurrentPageNumber( $this->_getParam('page' ) );
		$paginator->setDefaultItemCountPerPage( $itemsPerPage );

		// Assign the Paginator object to the view
		$this->view->paginator = $paginator;
		
	}
	
	protected function redimensionar_imagen($imagen, $carpeta, $ancho, $nombre_nuevo=NULL, $nuevoAL = NULL )

    {
        $nuevoAN     = $ancho;

        //indicamos el directorio donde se van a colgar las imágenes
        $directorio = $carpeta ;

        if ( !is_dir( $directorio ) ){
        	//Si no existe el directorio lo crea
        	mkdir( $directorio , 0777 );
        	chmod( $directorio , 0777 );
        }

        //establecemos los límites de ancho y alto
        $nuevo_ancho = $nuevoAN ;

        if ( !is_null( $nuevoAL ) )$nuevo_alto = $nuevoAL ;

        //Recojo información de la imágen
        $info_imagen = getimagesize($imagen);
        $alto = $info_imagen[1];
        $ancho = $info_imagen[0];
        $tipo_imagen = $info_imagen[2];

   	 	//Calculo y redimensiono para mantener el aspecto
        $ratio =  $ancho / $alto;
   	    $nuevo_alto = ceil($nuevo_ancho / $ratio);
    
        //Si no lo paso, armo el nuevo nombre del archivo
        $nombre_nuevo = (is_null($nombre_nuevo)) ? 'preview.jpg' : $nombre_nuevo;    
    
        // dependiendo del tipo de imagen tengo que usar diferentes funciones

        switch ($tipo_imagen) {

            case 1: //si es gif
                $imagen_nueva = imagecreate($nuevo_ancho, $nuevo_alto);
                $imagen_vieja = imagecreatefromgif($imagen);

                //cambio de tamaño
                imagecopyresampled($imagen_nueva, $imagen_vieja, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
                if (!imagegif($imagen_nueva, $directorio . $nombre_nuevo)) return false;

            break;

            case 2: //si es jpeg
                $imagen_nueva = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
                $imagen_vieja = imagecreatefromjpeg($imagen);

                //cambio de tamaño
                imagecopyresampled($imagen_nueva, $imagen_vieja, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
                if (!imagejpeg($imagen_nueva, $directorio . $nombre_nuevo)) return false;

             break;

            case 3: //si es png
                $imagen_nueva = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
                $imagen_vieja = imagecreatefrompng($imagen);

                //cambio de tamaño
                imagecopyresampled($imagen_nueva, $imagen_vieja, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
                if (!imagepng($imagen_nueva, $directorio . $nombre_nuevo)) return false;
                
            break;

        }
        
        chmod( $directorio . $nombre_nuevo , 0777 );

        return $nombre_nuevo;

    }

}
