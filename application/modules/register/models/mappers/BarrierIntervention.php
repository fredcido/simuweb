<?php

class Register_Model_Mapper_BarrierIntervention extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_BarrierIntervention
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_BarrierIntervention();

	return $this->_dbTable;
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

	    $row = $this->_checkBarrierIntervention( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Intervensaun iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_barrier_intervention'] ) )
		$history = 'REJISTRU Intervensaun %s - ID: %s';
	    else
		$history = 'ALTERA Intervensaun %s - ID: %s';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['barrier_Intervention_name'], $id );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return $id;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
   
    /**
     *
     * @param array $data
     * @return App_Model_DbTable_Row_Abstract
     */
    protected function _checkBarrierIntervention()
    {
	$select = $this->_dbTable->select()->where( 'barrier_Intervention_name = ?', $this->_data['barrier_Intervention_name'] )
					  ->where( 'fk_id_barrier_name = ?', $this->_data['fk_id_barrier_name'] );

	if ( !empty( $this->_data['id_barrier_intervention'] ) )
	    $select->where( 'id_barrier_intervention <> ?', $this->_data['id_barrier_intervention'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @param int $type
     * @return Zend_Db_Table_Rowset
     */
    public function listAll()
    {
	$dbBarrierIntervention = App_Model_DbTable_Factory::get( 'BarrierIntervention' );
	$dbBarrierName = App_Model_DbTable_Factory::get( 'BarrierName' );
	$dbBarrierType = App_Model_DbTable_Factory::get( 'BarrierType' );
	
	$select = $dbBarrierIntervention->select()
			    ->from( array( 'bi' => $dbBarrierIntervention  ) )
			    ->setIntegrityCheck( false )
			    ->join(
				array( 'bt' => $dbBarrierType ),
				'bi.fk_id_barrier_type = bt.id_barrier_type',
				array( 'barrier_type_name' )
			    )
			    ->join(
				array( 'bn' => $dbBarrierName ),
				'bi.fk_id_barrier_name = bn.id_barrier_name',
				array( 'barrier_name' )
			    )
			    ->order( array( 'barrier_type_name', 'barrier_name', 'barrier_Intervention_name' ) );
	
	return $dbBarrierIntervention->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row 
     */
    public function detail( $id )
    {
	$dbBarrierIntervention = App_Model_DbTable_Factory::get( 'BarrierIntervention' );
	$dbBarrierName = App_Model_DbTable_Factory::get( 'BarrierName' );
	$dbBarrierType = App_Model_DbTable_Factory::get( 'BarrierType' );
	
	$select = $dbBarrierIntervention->select()
			    ->from( array( 'bi' => $dbBarrierIntervention  ) )
			    ->setIntegrityCheck( false )
			    ->join(
				array( 'bt' => $dbBarrierType ),
				'bi.fk_id_barrier_type = bt.id_barrier_type',
				array( 'barrier_type_name' )
			    )
			    ->join(
				array( 'bn' => $dbBarrierName ),
				'bi.fk_id_barrier_name = bn.id_barrier_name',
				array( 'barrier_name' )
			    )
			    ->where( 'bi.id_barrier_intervention = ?', $id );
	
	return $dbBarrierIntervention->fetchRow( $select );
    }
    
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::REGISTER,
	    'fk_id_sysform'	    => Register_Form_BarrierIntervention::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}