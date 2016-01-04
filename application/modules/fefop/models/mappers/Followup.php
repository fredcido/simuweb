<?php

class Fefop_Model_Mapper_Followup extends App_Model_Abstract
{   
    /**
     * 
     * @var Model_DbTable_FEFOPFollowup
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEFOPFollowup();

	return $this->_dbTable;
    }
  
    /**
     * 
     * @return int|bool
     */
    public function saveFollowUp( $contract, $description )
    {
	$this->_data = array(
	    'fk_id_fefop_contract' => $contract,
	    'fk_id_sysuser'	   => Zend_Auth::getInstance()->getIdentity()->id_sysuser,
	    'description'	   => $description
	);
	
	return parent::_simpleSave( $this->_dbTable, false );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function save()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dataForm = $this->_data;
	    
	    $id = $this->saveFollowUp( $this->_data['fk_id_fefop_contract'], $this->_data['description'] );
	    
	    // If the status was changed
	    if ( !empty( $dataForm['fk_id_fefop_status'] ) ) {
		
		$dataStatus = array(
		    'contract'		=> $dataForm['fk_id_fefop_contract'],
		    'status'		=> $dataForm['fk_id_fefop_status'],
		    'id_fefop_followup' => $id
		);
		
		// Save the new Status
		$mapperStatus = new Fefop_Model_Mapper_Status();
		$mapperStatus->setData( $dataStatus )->save();
	    }
	    
	    $dbAdapter->commit();
	    
	    return true;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listFollowups( $id )
    {
	$dbFollowup = App_Model_DbTable_Factory::get( 'FEFOPFollowup' );
	$dbContractStatus = App_Model_DbTable_Factory::get( 'FEFOPContractStatus' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$select = $dbFollowup->select()
			     ->from( array( 'fu' => $dbFollowup ) )
			     ->setIntegrityCheck( false )
			     ->join(
				array( 'u' => $dbUser ),
				'u.id_sysuser = fu.fk_id_sysuser',
				array( 'user' => 'name' )
			    )
			     ->joinLeft(
				array( 'cs' => $dbContractStatus ),
				'cs.fk_id_fefop_followup = fu.id_fefop_followup',
				array( 'fk_id_fefop_status' )
			     )
			     ->where( 'fu.fk_id_fefop_contract = ?', $id )
			     ->order( array( 'id_fefop_followup DESC' ) );
	
	return $dbFollowup->fetchAll( $select );
    }
}