<?php

class App_Plugins_External_Config extends Zend_Controller_Plugin_Abstract
{

    /**
     * 
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;
    

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Plugin_Abstract::dispatchLoopStartup()
     */
    public function dispatchLoopStartup( Zend_Controller_Request_Abstract $request )
    {
	$auth = Zend_Auth::getInstance();
	$config = Zend_Registry::get( 'config' );
	$session = new Zend_Session_Namespace( $config->general->appid );
	
	if ( !$auth->hasIdentity() || empty( $session->client ) ) {
	 
	    $request->setModuleName('default')
		    ->setControllerName('auth')
		    ->setActionName( 'index' );
	    
	    return false;
	}
	
	$layout = Zend_Layout::getMvcInstance();
	$layout->setLayout('external')->getView()->session = $session;
	
	return true;
    }
}