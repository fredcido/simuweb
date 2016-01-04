<?php

/** 
 * 
 */
class App_Util_Session
{
	/**
	 * 
	 * @var Zend_Session_Namespace
	 */
	protected static $_session;
	
	/**
	 * 
	 * @access public
	 * @static
	 * @return Zend_Session_Namespace
	 */
	public static function get ()
	{
		if ( is_null(self::$_session) ) {
			$config = Zend_Registry::get( 'config' );
			self::$_session = new Zend_Session_Namespace( $config->geral->appid );
		}
		
		return self::$_session;
	} 
}
