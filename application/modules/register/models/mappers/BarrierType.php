<?php

class Register_Model_Mapper_BarrierType extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_BarrierType
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_BarrierType();

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

	    $row = $this->_checkBarrierType( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Tipu Barreira iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_barrier_type'] ) )
		$history = 'REJISTRU Tipu Barreira %s - ID: %s';
	    else
		$history = 'ALTERA Tipu Barreira %s - ID: %s';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['barrier_type_name'], $id );
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
    protected function _checkBarrierType()
    {
	$select = $this->_dbTable->select()->where( 'barrier_type_name = ?', $this->_data['barrier_type_name'] );

	if ( !empty( $this->_data['id_barrier_type'] ) )
	    $select->where( 'id_barrier_type <> ?', $this->_data['id_barrier_type'] );

	return $this->_dbTable->fetchRow( $select );
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
	    'fk_id_sysform'	    => Register_Form_BarrierType::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}