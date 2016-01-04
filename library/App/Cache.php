<?php

abstract class App_Cache
{
    /**
     *
     * @var Zend_Cache_Core 
     */
    protected static $_cache;
    
    
    /**
     * 
     */
    private static function initCache()
    {
	if ( null == self::$_cache ) {
	    
	    $frontendOptions = array(
		'lifetime' 			=> 86400,
		'automatic_serialization' 	=> true
	    );

	    $backendOptions = array( 'cache_dir' => APPLICATION_PATH . '/cache' );

	    self::$_cache = Zend_Cache::factory( 'Core', 'File', $frontendOptions, $backendOptions );
	
	}
    }
    
    /**
     * 
     */
    public static function save( $data, $id = null, $tags = array(), $specificLifetime = false, $priority = 8 )
    {
	self::initCache();
	
	return self::$_cache->save( $data, $id, $tags, $specificLifetime, $priority );
    }
    
    /**
     * 
     */
    public static function load( $id )
    {
	self::initCache();
	
	return self::$_cache->load( $id );
    }
    
    /**
     * 
     */
    public static function remove( $id )
    {
	self::initCache();
	
	return self::$_cache->remove( $id );
    }
    
    /**
     * 
     */
    public static function clean( $mode = 'all', $tags = array() )
    {
	self::initCache();
	
	return self::$_cache->clean( $mode, $tags );
    }
    
}