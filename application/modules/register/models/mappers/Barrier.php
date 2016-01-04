<?php

class Register_Model_Mapper_Barrier extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_Barrier
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_BarrierName();

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

	    $row = $this->_checkBarrier( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Barreira iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_barrier_name'] ) )
		$history = 'REJISTRU Naran Barreira %s - ID: %s';
	    else
		$history = 'ALTERA Naran Barreira %s - ID: %s';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['barrier_name'], $id );
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
    protected function _checkBarrier()
    {
	$select = $this->_dbTable->select()->where( 'barrier_name = ?', $this->_data['barrier_name'] );

	if ( !empty( $this->_data['id_barrier_name'] ) )
	    $select->where( 'id_barrier_name <> ?', $this->_data['id_barrier_name'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @param int $type
     * @return Zend_Db_Table_Rowset
     */
    public function listAll( $type = null )
    {
	$dbBarrier = App_Model_DbTable_Factory::get( 'BarrierName' );
	$dbBarrierType = App_Model_DbTable_Factory::get( 'BarrierType' );
	
	$select = $dbBarrier->select()
			    ->from( array( 'b' => $dbBarrier  ) )
			    ->setIntegrityCheck( false )
			    ->join(
				array( 'bt' => $dbBarrierType ),
				'b.fk_id_barrier_type = bt.id_barrier_type',
				array( 'barrier_type_name' )
			    )
			    ->order( array( 'barrier_type_name', 'barrier_name' ) );
	
	if ( !empty( $type ) )
	    $select->where( 'b.fk_id_barrier_type = ?', $type );
	
	return $dbBarrier->fetchAll( $select );
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
	    'fk_id_sysform'	    => Register_Form_Barrier::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}