<?php

class Register_Model_Mapper_AddCountry extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_AddCountry
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_AddCountry();

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

	    $row = $this->_checkNation( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Nasaun iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_addcountry'] ) )
		$history = 'INSERE PAIS ID: %s - INSERE NOVO PAIS';
	    else
		$history = 'ALTERA PAIS NUMERU: %s - ALTERA PAIS';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['country'] );
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
    protected function _checkNation()
    {
	$select = $this->_dbTable->select()->where( 'country = ?', $this->_data['country'] );

	if ( !empty( $this->_data['id_addcountry'] ) )
	    $select->where( 'id_addcountry <> ?', $this->_data['id_addcountry'] );

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
	    'fk_id_sysform'	    => Register_Form_Nation::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}