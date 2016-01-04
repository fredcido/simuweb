<?php

class Register_Model_Mapper_Dec extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_Dec
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_Dec();

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

	    $row = $this->_checkCeop( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'CEOP iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_dec'] ) ) {
		
		$history = 'INSERE CEOP-DEC: %s- INSERIDO NOVO CEOP-DEC COM SUCESSO';
		
		$this->_data['fk_id_sysuser'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$this->_data['registry_date'] = Zend_Date::now()->toString( 'yyyy-MM-dd' );
		
	    } else
		$history = 'ALTERA CEOP-DEC: %s DADUS PRINCIPAL - ALTERA CEOP-DEC';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $id );
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
    protected function _checkCeop()
    {
	$select = $this->_dbTable->select()->where( 'name_dec = ?', $this->_data['name_dec'] );

	if ( !empty( $this->_data['id_dec'] ) )
	    $select->where( 'id_dec <> ?', $this->_data['id_dec'] );

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
	    'fk_id_sysform'	    => Register_Form_Ceop::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}