<?php

class Register_Model_Mapper_ScholarityArea extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_ScholarityArea
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_ScholarityArea();

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

	    $row = $this->_checkScholarityArea( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Area Kursu iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( !empty( $this->_data['acronym'] ) ) {
		
		$row = $this->_checkScholarityAreaAcronym( $this->_data );

		if ( !empty( $row ) ) {
		    $this->_message->addMessage( 'Area Kursu nia sigla iha tiha ona.', App_Message::ERROR );
		    return false;
		}
	    }
	    
	    if ( empty( $this->_data['id_scholarity_area'] ) )
		$history = 'INSERE AREA KURSU: %s DADUS PRINCIPAL - INSERE NOVA AREA KURSU';
	    else
		$history = 'ALTERA AREA KURSU: %s DADUS PRINCIPAL - ALTERA AREA KURSU';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['scholarity_area'] );
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
    protected function _checkScholarityArea()
    {
	$select = $this->_dbTable->select()->where( 'scholarity_area = ?', $this->_data['scholarity_area'] );

	if ( !empty( $this->_data['id_scholarity_area'] ) )
	    $select->where( 'id_scholarity_area <> ?', $this->_data['id_scholarity_area'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @param array $data
     * @return App_Model_DbTable_Row_Abstract
     */
    protected function _checkScholarityAreaAcronym()
    {
	$select = $this->_dbTable->select()->where( 'acronym = ?', $this->_data['acronym'] );

	if ( !empty( $this->_data['id_scholarity_area'] ) )
	    $select->where( 'id_scholarity_area <> ?', $this->_data['id_scholarity_area'] );

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
	    'fk_id_sysform'	    => Register_Form_AreaScholarity::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}