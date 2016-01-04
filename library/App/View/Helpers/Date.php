<?php

class App_View_Helper_Date extends Zend_View_Helper_Abstract
{
    protected $_objDate;

    public function date( $date = null, $format = 'dd/MM/yyyy')
    {
	if ( null == $date ) {

	    $this->_objDate = new Zend_Date();
	} else {

	    if ( is_null( $this->_objDate ) )
		$this->_objDate = new Zend_Date( $date );
	    else
		$this->_objDate->set( $date );
	}

        return $this->_objDate->toString( $format );
    }
}