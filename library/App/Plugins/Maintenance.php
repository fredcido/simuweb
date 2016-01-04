<?php

class App_Plugins_Maintenance extends Zend_Controller_Plugin_Abstract
{

    /**
     * 
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * 
     * @var Zend_Auth
     */
    protected $_auth;
    
    /**
     *
     * @var Zend_Config
     */
    protected $_config;
    
    /**
     * 
     */
    public function __construct()
    {
	$this->_auth = Zend_Auth::getInstance();
	$this->_config = Zend_Registry::get( 'config' );

	//Namespace de autenticacao da aplicacao
	$namespace = 'Auth_Admin_' . ucfirst( $this->_config->general->appid );

	//Define storage da aplicacao
	$this->_auth->setStorage( new Zend_Auth_Storage_Session( $namespace ) );
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Plugin_Abstract::dispatchLoopStartup()
     */
    public function dispatchLoopStartup( Zend_Controller_Request_Abstract $request )
    {
	$maintenance = $this->_config->maintenance->toArray();
	if ( empty( $maintenance['enabled'] ) )
	    return true;
	
	$module = $request->getModuleName();
	if ( !empty( $maintenance['modules'] ) && !in_array( $module, $maintenance['modules'] ) )
	    return true;
	
	if ( Zend_Auth::getInstance()->hasIdentity() ) {
	    
	    $user = strtolower( Zend_Auth::getInstance()->getIdentity()->login );
	    if ( !empty( $maintenance['user'] ) && in_array( $user, $maintenance['user'] ) )
		return true;
	}
	
	$request->setModuleName( 'default' )->setControllerName( 'error' )->setActionName( 'maintenance' );
    }
}