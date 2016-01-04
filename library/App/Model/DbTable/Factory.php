<?php

/** 
 * 
 */
class App_Model_DbTable_Factory
{
	/**
	 * 
	 * @staticvar array
	 */
	protected static $_dbTable = array();
	
	/**
	 * 
	 * @access 	public
	 * @static
	 * @param 	string $class
	 * @param 	string $module
	 * @return 	Zend_Db_Table_Abstract
	 */
	public static function get ( $class, $module = null )
	{
		$className 	= '';
		
		if ( !is_null($module) )
			$className .= ucfirst( $module ) . '_';
		
		$className .= 'Model_DbTable_' . implode('', array_map('ucfirst', explode('_', $class)));
		
		if ( !class_exists( $className ) ) 
			throw new Exception( 'class not found: ' . $class );
						
		if ( !in_array( $className, array_keys( self::$_dbTable ) ) )
			self::$_dbTable[$className] = new $className();
			
		return self::$_dbTable[$className];
	}
}