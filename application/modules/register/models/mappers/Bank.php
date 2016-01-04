<?php

class Register_Model_Mapper_Bank extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_Bank
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_Bank();

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

	    $row = $this->_checkBank( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Banku iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_bank'] ) )
		$history = 'INSERE BANKU: %s - INSERIDO NOVO BANKU';
	    else
		$history = 'ALTERA BANKU: %s - ALTERADO BANKU';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['name_bank'] );
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
    protected function _checkBank()
    {
	$select = $this->_dbTable->select()->where( 'name_bank = ?', $this->_data['name_bank'] );

	if ( !empty( $this->_data['id_bank'] ) )
	    $select->where( 'id_bank <> ?', $this->_data['id_bank'] );

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
	    'fk_id_sysform'	    => Register_Form_Bank::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}