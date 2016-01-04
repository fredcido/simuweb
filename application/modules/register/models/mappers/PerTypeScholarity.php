<?php

class Register_Model_Mapper_PerTypeScholarity extends App_Model_Abstract
{
    const FORMAL = 1;
    
    const NON_FORMAL = 2;
    
    /**
     * 
     * @var Model_DbTable_PerTypeScholarity
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_PerTypeScholarity();

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

	    $row = $this->_checkTypeScholarity( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Tipu Kursu iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_pertypescholarity'] ) )
		$history = 'INSERE TIPU KURSU: %s DADUS PRINCIPAL - INSERE NOVO TIPU KURSU';
	    else
		$history = 'ALTERA TIPU KURSU: %s DADUS PRINCIPAL - ALTERA TIPU KURSU';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['type_scholarity'] );
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
    protected function _checkTypeScholarity()
    {
	$select = $this->_dbTable->select()->where( 'type_scholarity = ?', $this->_data['type_scholarity'] );

	if ( !empty( $this->_data['id_pertypescholarity'] ) )
	    $select->where( 'id_pertypescholarity <> ?', $this->_data['id_pertypescholarity'] );

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
	    'fk_id_sysform'	    => Register_Form_TypeScholarity::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}