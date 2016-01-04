<?php

class Fefop_Model_Mapper_Status extends App_Model_Abstract
{   
    const ANALYSIS = 1;
    
    const REJECTED = 2;
    
    const PROGRESS = 3;
    
    const CANCELLED = 4;
    
    const FINALIZED = 5;
    
    const CEASED = 6;
    
    const SEMI = 7;
    
    const INITIAL = 8;
    
    const REVIEWED = 9;
    
    
    /**
     * 
     * @var Model_DbTable_FEFOPContractStatus
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEFOPContractStatus();

	return $this->_dbTable;
    }
    
    /**
     * 
     * @return array
     */
    protected function _getValidStatuses()
    {
	return array(
	    self::ANALYSIS,
	    self::CANCELLED,
	    self::CEASED,
	    self::FINALIZED,
	    self::INITIAL,
	    self::PROGRESS,
	    self::SEMI,
	    self::REJECTED,
	    self::REVIEWED
	);
    }

    /**
     * 
     * @return int|bool
     */
    public function save()
    {
	$validStatuses = $this->_getValidStatuses();
	
	if ( !in_array( $this->_data['status'], $validStatuses ) )
	    throw new Exception( $this->_data['status'] . ' is not a valid status' );
	
	// Inactivate the last status
	$dataUpdate = array(
	   'status'	    => 0,
	   'date_finished'  => Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm:ss' )
	);
	
	$whereUpdate = array(
	    'fk_id_fefop_contract = ?'	=> $this->_data['contract'],
	    'status = ?'		=> 1
	);
	
	$this->_dbTable->update( $dataUpdate, $whereUpdate );
	
	$data = array(
	    'fk_id_fefop_contract'  => $this->_data['contract'],
	    'fk_id_fefop_status'    => $this->_data['status'],
	    'fk_id_sysuser'	    => Zend_Auth::getInstance()->getIdentity()->id_sysuser,
	    'status'		    => 1
	);
	
	// If there is description to be inserted
	if ( !empty( $this->_data['description'] ) ) {
	    
	    $mapperFollowUp = new Fefop_Model_Mapper_Followup();
	    $data['fk_id_fefop_followup'] = $mapperFollowUp->saveFollowUp( $this->_data['contract'], $this->_data['description'] );
	    
	} else if ( !empty( $this->_data['id_fefop_followup'] ) )
	    $data['fk_id_fefop_followup'] = $this->_data['id_fefop_followup'];
	
	return $this->_dbTable->createRow( $data )->save();
    }
    
    /**
     * 
     */
    public function getStatuses()
    {
	$dbStatus = App_Model_DbTable_Factory::get( 'FEFOPStatus' );
	return $dbStatus->fetchAll( array(), array( 'order' ) );
    }
}