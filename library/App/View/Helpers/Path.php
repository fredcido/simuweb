<?php

class App_View_Helper_Path extends Zend_View_Helper_Abstract
{
    public function path( $action = 'index', $controller = null, $module = null, $params = array() )
    {
        $frontController = Zend_Controller_Front::getInstance();
        $baseUrl = $frontController->getBaseUrl();
        
        $url = array();

        if ( empty( $module ) ) {

            $moduleName = $frontController->getRequest()->getModuleName();
            
	    if ( $frontController->getDefaultModule() != $moduleName )
		$url[] = $moduleName;

        } else $url[] = $module;
	
        if ( empty( $controller ) ) {

            $controllerName = $frontController->getRequest()->getControllerName();
            $url[] = $controllerName;
        } else $url[] = $controller;
	
        $url[] = $action;
	
	$url = $baseUrl . '/' . implode( '/', $url );

        foreach ( $params as $key => $value )
            $url .= '/' . $key . '/' . $value;
	
        return $url;
    }

    public function direct( $action = 'index', $controller = null, $module = null, $params = array() )
    {
        return $this->url( $action, $controller, $module, $params );
    }

}