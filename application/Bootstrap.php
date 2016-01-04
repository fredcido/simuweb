<?php

/**
 * 
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    /**
     * 
     */
    protected function _initConfig()
    {
	$config = new Zend_Config_Ini( APPLICATION_PATH . '/configs/config.ini' );

	Zend_Registry::set( 'config', $config );
    }

    /**
     * 
     */
    protected function _initLocale()
    {
	$locale = new Zend_Locale( 'pt_BR' );
	Zend_Registry::set( 'Zend_Locale', $locale );
	
	$currency = new Zend_Currency( 'en_US' );
	Zend_Registry::set( 'Zend_Currency', $currency );
    }
    
    /**
     * 
     */
    protected function _initCacheDir ()
    {
	$frontendOptions = array(
		'lifetime' => 86400, 
		'automatic_serialization' => true, 
		'automatic_cleaning_factor' => 1
	);

	$backendOptions = array( 'cache_dir' => APPLICATION_PATH . '/cache' );

	$cache = Zend_Cache::factory( 'Core', 'File', $frontendOptions, $backendOptions );

	Zend_Db_Table_Abstract::setDefaultMetadataCache( $cache );
	Zend_Date::setOptions( array('cache' => $cache) );
    }
    
    /**
    * 
    */
    protected function _initCli() 
    {
	if ( PHP_SAPI == 'cli' ) {

	    $this->bootstrap( 'frontcontroller' );
	    $front = $this->getResource( 'frontcontroller' );
	    
	    $front->unregisterPlugin( 'App_Plugins_Auth' );
	    $front->unregisterPlugin( 'App_Plugins_Layout' );

	    $front->setDefaultModule( 'cron' );
	    $front->setRouter( new App_Controller_Router_Cron() );
	    $front->setRequest( new Zend_Controller_Request_Simple() );
	}
    }
}
