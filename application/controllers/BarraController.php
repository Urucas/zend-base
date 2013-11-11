<?php

require( APPLICATION_PATH . '/controllers/ExtensionBackController.php' );

class BarraController extends ExtensionBackController 
{
	protected $model;

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->getHelper('Layout')->setLayout( 'intranet' );
        $this->checksession();
        
      	if ($this->_hasParam('module') ){
		    $this->module = $this->_getParam('module');
		    $this->view->assign( 'module', $this->module );
		    
		    $model = "Model_".ucfirst( $this->module );

//		    if ( is_file( APPLICATION_PATH . "/models/$model.php" ))
    			$this->model = new $model;
		} 
        
    }

    public function indexAction()
    {
        // action body
		$modelUsuarios = new Model_Usuarios();
		$usuarios = $modelUsuarios->CantUsuarios();

		$this->view->assign('cant_usuarios',$usuarios["cantidad"]);
		$this->view->assign('usuarios_activos',$usuarios["activos"]);

		$modelLocales = new Model_Locales();
		$locales = $modelLocales->CantLocales();
		$this->view->assign('cant_locales',$locales["cantidad"]);
    }
    
    public function loginAction(){
    	
		$this->_helper->layout->disableLayout();
    	
    	$form = new Form_Login();
    	$this->view->assign( 'form', $form );
    	
    	if($this->getRequest()->isPost()) {

			if (!$form->isValid($_POST)) {
				// failed validation
				$json = $form->processAjax($_POST);
			}else{
				//validation success
				$user = trim($_POST["usuario"]);
				$pass = trim($_POST["password"]);
				
				$adminModel	=	new Model_Administradores();
				$rs		=	$adminModel->validar( $user, $pass );

//				if( $user == 'admin' && md5( $pass ) == '21232f297a57a5a743894a0e4a801fc3' ) {
				if( $rs ) {
					
					//Inicio la session
					Zend_Session::start();
					$this->auth = new Zend_Session_Namespace('Zend_Auth');
					$this->auth->id 		= $rs[0]["id"];
					$this->auth->usuario 	= $rs[0]["usuario"];
					$this->auth->logged = true;
					$this->_redirect( '/barra/index' );

				}else {

					$this->view->assign('msg', 'El usuario ingresado no es valido. Verifique.');

				}
					
			}
		}
    	
    }
    
    public function listarAction(){

		$eliminar = $this->getRequest()->getParam( 'eliminar' );

		if ( $eliminar ){
			//Si desea eliminar

			$rs	 =	$this->model->deleteByPrimaryKey( $eliminar );
			if ( $rs )$this->view->assign( 'msg', "El registro se elimino con exito." );

		}

		$page = ( $this->getRequest()->getParam( 'page' ) ) ? $this->getRequest()->getParam( 'page' ) -1 : 0 ;
		$this->view->page = $page;

		$config = new Zend_Config_Ini( APPLICATION_PATH . '/configs/application.ini', 'totales' );
		$itemsPerPage	=	$config->itemsPerPageDefault;

		// traigo los campos que quiero mostrar en el listado
		$config_campos = new Zend_Config_Ini( APPLICATION_PATH . '/configs/application.ini', 'campos' );
		$campos = $config_campos->{$this->module};
		if(!$campos){
			$campos = "*";
			$fields =   $this->model->getTableFields( $this->module );
		}else{
			$fields = explode(',',$campos);
		}

		$q = $this->getRequest()->getParam('q')     ? $this->getRequest()->getParam('q') : null;
		$f = $this->getRequest()->getParam('field') ? $this->getRequest()->getParam('field') : null;
		$where = null;
		if($q && $f) {
			if(in_array($f, $fields)) {
				$where = $f." LIKE '%".$q."%'";		
			}
		}

		$this->view->f = $f ? $f : "";
		$this->view->q = $q ? $q : "";

        $this->view->fields =   $fields;

		$this->model->setLimit( $itemsPerPage );
		$rs = $this->model->listar($page, $campos, $where);
		$this->view->records = $rs;

		$totalPages = $this->model->getCalcFoundRows();
		
		$this->view->totalPages =   ( $totalPages > 0 ) ?   $totalPages :   0;
		if ( $totalPages > $itemsPerPage )
			$this->paginar( $totalPages, $itemsPerPage );

    }

	public function facebookfeedsAction() {
	
		
		require_once("../library/Facebook/src/facebook.php");
	
		$facebook = new Facebook(array(
			'appId'  => '106941089437708',
			'secret' => 'fdbf44db3c5f8d9726d592472ad4cf9d',
		));
		
		$r = $facebook->api('kuestyapp/feed');	
		die(json_encode($r));
	
	}

    public function editAction(){
//    	Agrega 


        if(class_exists( "Form_".ucfirst($this->module) )){

			$form = "Form_".ucfirst($this->module);
			$form = new $form;

        } else {

			/*
                $tableDescription   =   $this->model->getFieldsDescription( $this->module );

                $form  =   new My_Createforms( $tableDescription );
                $form   =   $form->getForm();
			 */
			die("el form no existe, crear el form de ".$this->module); 
        }

	

	
//		if ( method_exists( $this, "edit" . ucfirst( $this->module ) ) ){
//			$method = "edit" . ucfirst( $this->module );
//			$this->{$method}(	$form	);
//		}

		if ( $this->getRequest()->ispost() ){
//			Si esta guardando

		

			if ( !$form->isValid( $this->getRequest()->getpost() )){

				$json = $form->processAjax( $this->getRequest()->getpost() );

			} else {

				if($this->module == "locales") {

					$categorias = $form->getElement("categorias")->getValue();	
					$form->removeElement("categorias");

				}

				$id = $this->model->guardar( $form->getValues(), null );
			
				if($this->module == "locales") {

					$this->model->guardarCategorias($id, $categorias);	
				}


				if ( $_FILES ){
					
					$fileName = key( $_FILES );
					$fileInfo = $form->getElement( $fileName )->getFileInfo();

					$fileInfo = $fileInfo[ "$fileName" ];

					if ( $fileInfo['error'] == 0 ){
						/* Si se esta subiendo un archivo */

//						list( $type, $ext ) = explode( '/', $fileInfo['type'] );
						$ext	=	strtolower( substr( $fileInfo['name'], strrpos( $fileInfo['name'], '.') + 1 ) );
						if ( in_array( $ext, array( 'jpeg','jpg','png','gif' ) ) )
							$ext = 'png';

						//genero el directorio
						mkdir($fileInfo['destination']."/".$id."/",0777,true);

						$nombre_archivo = "/thumb_" . $id . "_o.$ext";
						$form->getElement( "$fileName" )->addFilter( 'Rename',   array('target' => $fileInfo['destination'] . '/'.$id. '/thumb_' . $id . "_o.$ext", 'overwrite' => true));
						$form->getElement( "$fileName" )->receive();
						
						//si le quiero hacer alguna modif a la imagen una vez subida
						if ( method_exists( $this, "upload" . ucfirst( $this->module ) ) ){
							$method = "upload" . ucfirst( $this->module );
							$this->{$method}($nombre_archivo,$id);
						}
						$values = array('logo'=>'http://www.kuesty.com/resources/locales/'.$id.'/thumb_'.$id.'_o.png','logo_mobile'=>'http://www.kuesty.com/resources/locales/'.$id.'/thumb_'.$id.'_s.png');
						//$values['logo'] = '/resources/locales/'.$id.'/thumb_'.$id.'_o.png';
						//$values['logo_mobile'] = '/resources/locales/'.$id.'/thumb_'.$id.'_s.png';
						$this->model->update($values,'id = '.$id);
						
					} else {

						$form->getElement( "$fileName" )->setIgnore( true );

					}



				}

				$form->reset();

				$this->view->msg = "El registro se guardo con exito.";

			}

		}
		
		 if ( method_exists( $this, "prerender" . ucfirst( $this->module ) ) ){
			$method = "prerender" . ucfirst( $this->module );
			$this->{$method}( null );
		}
		
		$this->view->form = $form;
		
    }

    public function modificarAction(){


//        $l = $this->getResource('layout');
//        $view = $l->getView();

//        $this->view->setEncoding('UTF-8');


    	$id = $this->getRequest()->getParam( 'id' );
    	
        if(class_exists( "Form_".ucfirst($this->module) )){

			$form = "Form_".ucfirst($this->module);
			$form = new $form();

        } else {

                $tableDescription   =   $this->model->getFieldsDescription( $this->module );

                $form  =   new My_Createforms( $tableDescription );
                $form   =   $form->getForm();

        }

		if($this->module == "locales") {
		
			$categorias = new Model_Categorias();
			$categorias = $categorias->getCategoriasxLocal($id);

			for($i=0; $i < sizeof($categorias); $i++) {
				$cats[] = $categorias[$i]["id_categoria"];
			}

			$form->getElement("categorias")->setValue($cats);
		}

		if ( method_exists( $this, "edit" . ucfirst( $this->module ) ) ){
			$method = "edit" . ucfirst( $this->module );
			$this->{$method}(	$form	);
		}

    	if ( $this->getRequest()->ispost() ){
//			Si esta guardando

			if ( !$form->isValid( $this->getRequest()->getpost() )){

		  			$json = $form->processAjax( $this->getRequest()->getpost() );

			} else {

				

				$id		=	$form->getValue( 'id' );

				if ( $_FILES ){

					$fileName = key( $_FILES );
					$fileInfo = $form->getElement( $fileName )->getFileInfo();
					$fileInfo = $fileInfo[ "$fileName" ];

					if ( $fileInfo['error'] == 0 ){
	//					Si se esta subiendo un archivo

						$ext	=	strtolower( substr( $fileInfo['name'], strrpos( $fileInfo['name'], '.') + 1 ) );
						if ( in_array( $ext, array( 'jpeg','jpg','png','gif' ) ) )
							$ext = 'png';

						//genero el directorio
						mkdir($fileInfo['destination']."/".$id."/",0777,true);
						
						$nombre_archivo = "/thumb_".$id."_o.$ext";
						$form->getElement( "$fileName" )->addFilter( 'Rename',   array('target' => $fileInfo['destination'] . '/'.$id.'/thumb_' . $id . "_o.$ext", 'overwrite' => true));
						$form->getElement( "$fileName" )->receive();
						
						//si le quiero hacer alguna modif a la imagen una vez subida
						if ( method_exists( $this, "upload" . ucfirst( $this->module ) ) ){
							$method = "upload" . ucfirst( $this->module );
							$this->{$method}($nombre_archivo,$id);
						}

					} else {

						$form->getElement( "$fileName" )->setIgnore( true );

					}

				}
				if($this->module == "locales") {

					$categorias = $form->getElement("categorias")->getValue();	
					$form->removeElement("categorias");

				}
				$values = $form->getValues();
				foreach( $values as $key => $val) {
					$values[$key] = My_HTMLDecoder::encode($val);
				}

				if(isset($nombre_archivo)){

					if($this->module == "locales") {	

						$values['logo'] = 'http://www.kuesty.com/resources/locales/'.$id.'/thumb_'.$id.'_o.png';
						$values['logo_mobile'] = 'http://www.kuesty.com/resources/locales/'.$id.'/thumb_'.$id.'_s.png';
					}
					elseif($this->module == "premios") {
						$values["img"] =  'http://www.kuesty.com/resources/premios/'.$id.'/thumb_'.$id.'_o.png';
					}

				}

				unset( $values['id'] );
				$this->model->guardar( $values, $id );

				if($this->module == "locales") {

					$this->model->guardarCategorias($id, $categorias);	
				}

				$form->reset();

				$this->view->msg = "El registro se actualizo con exito.";
				$this->_redirect("/barra/".$this->module);
			}

		}
    	
    	if ( !$id ) $this->_redirect( '/barra/index');

		$rs = $this->model->getRecordById( $id );
		
    	if ( !$rs ) $this->_redirect( '/barra/index');

        foreach( $rs as $key => $row ){
		
			$cadena = $row;
			$cadena = My_HTMLDecoder::decode($cadena);

        	$rs[ $key ] = $cadena;
//            funca
        }
    	
    	$form->setDefaults( $rs );
    	
    	$this->view->form = $form;

    	$scriptPaths =  $this->view->getScriptPaths();
    	$view	=	$scriptPaths[0] . "barra/edit{$this->module}.phtml";
    	
    	if ( method_exists( $this, "prerender" . ucfirst( $this->module ) ) ){
			$method = "prerender" . ucfirst( $this->module );
			$this->{$method}( $form );
		}

        $this->renderScript( 'barra/edit.phtml' );
    		
    }

    public function disponibilidadAction(){

        $modelEstados = new Model_Estados();

        $estados    =   $modelEstados->listar();

        $modelReservas  =   new Model_Reservas();
        $fecha  =   date( "m/d/Y" );
        $habitaciones = $modelReservas->getHabitaciones( $fecha );

        $this->view->habitaciones   =   $habitaciones;
        $this->view->estados    =   $estados;
        
    }
    
    public function logoutAction(){

            $session_id = Zend_Session::getId();
            if($session_id){
                    unset( $this->auth );
                    Zend_Session::destroy();
            }

            $this->_redirect('/barra/login');

    }

	private function uploadLocales($filename,$id){
	
		chmod("./resources/locales/".$id.$filename,0777);

		//genero una imagen grande
		$this->redimensionar_imagen("./resources/locales/".$id.$filename,"./resources/locales/".$id."/",500,"thumb_".$id."_l.png");
	
		//genero una imagen mediana
		$this->redimensionar_imagen("./resources/locales/".$id.$filename,"./resources/locales/".$id."/",300,"thumb_".$id."_m.png");
		
		//genero una imagen chica
		$this->redimensionar_imagen("./resources/locales/".$id.$filename,"./resources/locales/".$id."/",130,"thumb_".$id."_s.png");
	}
    
    private function checksession(){

		//Si el usuario no esta logueado lo envia al metodo loginAction
		$this->auth = new Zend_Session_Namespace('Zend_Auth');

		if ( !$this->auth->logged && $this->getRequest()->getActionName() != 'login' ){

//			$this->loginAction();
			$this->_redirect( '/barra/login' );

		}
		
		$this->view->username	=	$this->auth->usuario;

	}
	
}
