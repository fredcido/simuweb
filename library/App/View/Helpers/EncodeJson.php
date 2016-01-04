<?php

class App_View_Helper_EncodeJson extends Zend_View_Helper_Abstract
{
    
    /**
     *
     * @param array $data
     * @return string
     */
    public function encodeJson( array $data )
    {
	$this->_encodeArray( $data );
	
        return json_encode( $data );
    }
    
    /**
     *
     * @param array $data 
     */
    protected function _encodeArray( &$data )
    {
	foreach ( $data as &$value ) {
	    
	    if ( is_array( $value ) )
		$this->_encodeArray ( $value );
	    else
		$value = htmlentities( $value, ENT_QUOTES, 'UTF-8' );
	}
    }
}