<?php

class Fefop_Model_Mapper_Program extends App_Model_Abstract
{   
    const PCE = 1;
    
    const PFPCI = 2;
    
    const PISE = 3;
    
    const PER = 4;
    
    
    /**
     * 
     * @var Model_DbTable_FEFOPPrograms
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEFOPPrograms();

	return $this->_dbTable;
    }
}