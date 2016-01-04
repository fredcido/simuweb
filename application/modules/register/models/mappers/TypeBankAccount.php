<?php

class Register_Model_Mapper_TypeBankAccount extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_TypeBankAccount
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_TypeBankAccount();

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

	    $row = $this->_checkTypeBankAccount( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Tipu Konta Banku iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_bank'] ) )
		$history = 'INSERE TIPU KONTA BANKU: %s - INSERIDO NOVO TIPU KONTA BANKU';
	    else
		$history = 'ALTERA TIPU KONTA BANKU: %s - ALTERADO TIPU KONTA BANKU';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['type_bankaccount'] );
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
    protected function _checkTypeBankAccount()
    {
	$select = $this->_dbTable->select()->where( 'type_bankaccount = ?', $this->_data['type_bankaccount'] );

	if ( !empty( $this->_data['id_typebankaccount'] ) )
	    $select->where( 'id_typebankaccount <> ?', $this->_data['id_typebankaccount'] );

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
	    'fk_id_sysform'	    => Register_Form_TypeBankAccount::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}