<?php

class Register_Model_Mapper_AppointmentObjective extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_AppointmentObjective
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_AppointmentObjective();

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

	    $row = $this->_checkAppointmentObjective( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Intensaun ba Audiensia iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_appointment_objective'] ) )
		$history = 'REJISTRU INTENSAUN BA AUDIENSIA %s - ID: %s';
	    else
		$history = 'ALTERA INTENSAUN BA AUDIENSIA %s - ID: %s';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['objective_desc'], $id );
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
    protected function _checkAppointmentObjective()
    {
	$select = $this->_dbTable->select()->where( 'objective_desc = ?', $this->_data['objective_desc'] );

	if ( !empty( $this->_data['id_appointment_objective'] ) )
	    $select->where( 'id_appointment_objective <> ?', $this->_data['id_appointment_objective'] );

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
	    'fk_id_sysform'	    => Register_Form_AppointmentObjective::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}