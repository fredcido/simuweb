<?php

class Zend_View_Helper_Access extends Zend_View_Helper_Abstract
{
    
    /**
     * 
     */
    public function access( $form, $operation = null )
    {
	$config = Zend_Registry::get( 'config' );
	$session = new Zend_Session_Namespace( $config->general->appid );
	
	if ( empty( $session->permissions ) )
	    return false;
	
	if ( empty( $session->permissions[$form] ) )
	    return false;
	
	$formAccess = $session->permissions[$form];
	
	return !$operation ? true : in_array( $operation, $formAccess );
    }
}