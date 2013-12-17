<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected function _initAutoload()
	{
	//	date_default_timezone_set('America/Argentina/Buenos_Aires');

		$autoloader = new Zend_Application_Module_Autoloader(array(
			'namespace' => '',
			'basePath'  => APPLICATION_PATH, //dirname(__FILE__),
			'resourceTypes' => array (
				'model' => array(
					'path' => 'models',
					'namespace' => 'Model',
				),
				'mylibs' => array(
					'path' => '../library/Urucas',
					'namespace' => 'Urucas',
				),
				'filters' => array(
					'path' => 'filters',
					'namespace' => 'Filter',
				)


			),
		));

		return $autoloader;

	}

//	protected function _initTranslate() {
//
//	  // Set up and load the translations (all of them!)
//	  $translate = new Zend_Translate('csv',
//                                APPLICATION_PATH . DIRECTORY_SEPARATOR .'languages/',
//								null,
//                                array('scan' =>
//                                      Zend_Translate::LOCALE_FILENAME  ));
//
////	   Save it for later
//	  $registry = Zend_Registry::getInstance();
//	  $registry->set('Zend_Translate', $translate);
//
//	}

}

