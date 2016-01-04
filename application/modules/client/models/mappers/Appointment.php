<?php

class Client_Model_Mapper_Appointment extends App_Model_Abstract
{
    
    /**
     * 
     * @var Model_DbTable_Appointment
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_Appointment();

	return $this->_dbTable;
    }
   
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset 
     */
    public function listAppointments( $id )
    {
	$dbAppointment = App_Model_DbTable_Factory::get( 'Appointment' );
	$dbAppointmentHasObjective = App_Model_DbTable_Factory::get( 'Appointment_has_Objective' );
	$dbAppointmentObjective = App_Model_DbTable_Factory::get( 'Appointment_Objective' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	
	$select = $dbAppointment->select()
				->from( array( 'a' => $dbAppointment ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'u' => $dbUser ),
				    'u.id_sysuser = a.fk_id_counselor',
				    array(
					'name_counselor' => 'name',
					'date_appointment_formated'  => new Zend_Db_Expr( 'DATE_FORMAT( a.date_appointment, "%d/%m/%Y %H:%i" )' ),
				    )
				)
				->join(
				    array( 'd' => $dbDec ),
				    'd.id_dec = a.fk_id_dec',
				    array( 'name_dec' )
				)
				->join(
				    array( 'aho' => $dbAppointmentHasObjective ),
				    'aho.fk_id_appointment = a.id_appointment',
				    array()
				)
				->join(
				    array( 'ao' => $dbAppointmentObjective ),
				    'aho.fk_id_appointment_objective = ao.id_appointment_objective',
				    array(
					'objective' => new Zend_Db_Expr( "GROUP_CONCAT(objective_desc SEPARATOR ' - ')" )
				    )
				)
				->where( 'a.fk_id_action_plan = ?', $id )
				->group( array( 'id_appointment' ) )
				->order( array( 'date_insert DESC' ) );
	
	return $dbAppointment->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row 
     */
    public function detailAppointment( $id )
    {
	$dbAppointment = App_Model_DbTable_Factory::get( 'Appointment' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	
	$select = $dbAppointment->select()
				->from( array( 'a' => $dbAppointment ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'u' => $dbUser ),
				    'u.id_sysuser = a.fk_id_counselor',
				    array(
					'name_counselor' => 'name',
					'date_appointment_formated'  => new Zend_Db_Expr( 'DATE_FORMAT( a.date_appointment, "%d/%m/%Y %H:%i" )' ),
				    )
				)
				->join(
				    array( 'u2' => $dbUser ),
				    'u2.id_sysuser = a.fk_id_sysuser',
				    array( 'responsible' => 'name' )
				)
				->join(
				    array( 'd' => $dbDec ),
				    'd.id_dec = a.fk_id_dec',
				    array( 'name_dec' )
				)
				->where( 'a.id_appointment = ?', $id );
	
	return $dbAppointment->fetchRow( $select );
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
	    
	    $date = new Zend_Date( $this->_data['date_appointment'] );
	    $date->set( $this->_data['time_appointment'], Zend_Date::TIME_SHORT );
	    
	    $dataForm = $this->_data;
	    
	    $today = new Zend_Date();
	    
	    $this->_data['date_appointment'] = $date->toString( 'yyyy-MM-dd HH:mm');
	    
	    if ( empty( $this->_data['id_appointment'] ) ) {
		
		
		if ( $date->isEarlier( $today ) ) {

		    $this->_message->addMessage( 'Erro: Data no Oras ba audiensia uluk data ohin.', App_Message::ERROR );
		    $this->addFieldError( 'date_appointment' )->addFieldError( 'time_appointment' );
		    return false;
		}

		$mapperCase = new Client_Model_Mapper_Case();
		$case = $mapperCase->detailCase( $this->_data['fk_id_action_plan'] );

		$this->_data['fk_id_perdata'] = $case->fk_id_perdata;
		$this->_data['appointment_active'] = 1;
		$this->_data['appointment_filled'] = 0;
	    }
	    
	    // Save the Note
	    $id = parent::_simpleSave();
	    
	    // Delete and insert all objetives
	    $dbAppointmentObjective = App_Model_DbTable_Factory::get( 'Appointment_has_Objective' );
	    $where = array( $dbAdapter->quoteInto( 'fk_id_appointment = ?', $id ) );
	    
	    $dbAppointmentObjective->delete( $where );
	    
	    // Insert the news objectives
	    foreach ( $dataForm['objective'] as $objective ) {
		
		$row = $dbAppointmentObjective->createRow();
		$row->fk_id_appointment = $id;
		$row->fk_id_appointment_objective = $objective;
		$row->save();
	    }
	    
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
     * @param int $id
     * @return Zend_Db_Table_Rowset 
     */
    public function listObjectives( $id )
    {
	$dbAppointmentHasObjective = App_Model_DbTable_Factory::get( 'Appointment_has_Objective' );
	$dbAppointmentObjective = App_Model_DbTable_Factory::get( 'Appointment_Objective' );
	
	$select = $dbAppointmentObjective->select()
					->from( array( 'ao' => $dbAppointmentObjective ) )
					->setIntegrityCheck( false )
					->join(
					    array( 'aho' => $dbAppointmentHasObjective ),
					    'aho.fk_id_appointment_objective = ao.id_appointment_objective',
					    array()
					)
					->where( 'aho.fk_id_appointment = ?', $id )
					->order( array( 'objective_desc' ) );
	
	return $dbAppointmentObjective->fetchAll( $select );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function deleteAppointmentObjective()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbAppointmentHasObjective = App_Model_DbTable_Factory::get( 'Appointment_has_Objective' );
	    
	    $where = array(
		$dbAdapter->quoteInto( 'fk_id_appointment = ?', $this->_data['id'] ),
		$dbAdapter->quoteInto( 'fk_id_appointment_objective = ?', $this->_data['id_objective'] )
	    );
	    
	    $dbAppointmentHasObjective->delete( $where );
	    $dbAdapter->commit();
	    
	    return true;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
}