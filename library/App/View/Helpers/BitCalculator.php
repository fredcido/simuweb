<?php

/**
 * 
 */
class App_View_Helper_BitCalculator extends Zend_View_Helper_Abstract
{
	/**
	 * @var array
	 */
	protected $_unit = array( 'bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
	
	/**
	 * 
	 * @access 	public
	 * @param 	number 	$bytes
	 * @return 	number
	 */
	public function bitCalculator ( $bytes )
	{
	    for ( $i = 0; $bytes > 1024; $i++ )
		    $bytes /= 1024;

	    return number_format( $bytes, 2, ',', '' ) . ' ' . $this->_unit[$i];
	}
}

?>