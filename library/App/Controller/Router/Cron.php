<?php

class App_Controller_Router_Cron extends Zend_Controller_Router_Abstract
{
    /**
     *
     * @param Zend_Controller_Request_Abstract $dispatcher
     * @return Zend_Controller_Request_Abstract 
     */
    public function route( Zend_Controller_Request_Abstract $dispatcher )
    {
	$getopt = new Zend_Console_Getopt( array() );
	$arguments = $getopt->getRemainingArgs();
	
	$controller = null;
	$action = null;
	$params = array();
	
	if ( $arguments ) {

	    foreach ( $arguments as $index => $command ) {

		if ( preg_match( '/([a-z0-9]+)=([a-z0-9]+)/i', trim( $command ), $match ) ) {
		    
		    switch ( $match[1] ) {
			case 'controller':
			    $controller = $match[2];
			    break;
			case 'action':
			    $action = $match[2];
			    break;
			default: 
			    $params[$match[1]] = $match[2];
		    }
		}
	    }
	    
	    $action = empty( $action ) ? 'index' : $action;
	    $controller = empty( $controller ) ? 'index' : $controller;

	    $dispatcher->setControllerName( $controller );
	    $dispatcher->setActionName( $action );
	    $dispatcher->setParams( $params );

	    return $dispatcher;
	}
	
	echo "Invalid command.\n";
	echo "No command given.\n", exit;
    }

    /**
     *
     * @param type $userParams
     * @param type $name
     * @param type $reset
     * @param type $encode 
     */
    public function assemble( $userParams, $name = null, $reset = false, $encode = true )
    {
	throw new Exception( "Assemble isnt implemented ", print_r( $userParams, true ) );
    }

}