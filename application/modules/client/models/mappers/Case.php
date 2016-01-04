<?php

class Client_Model_Mapper_Case extends App_Model_Abstract
{
    /**
     *
     * @var Client_Model_Mapper_Client
     */
    protected $_mapperClient;
    
    const BARRIER_PENDING = 'P';
    const BARRIER_COMPLETED = 'C';
    const BARRIER_NOT_COMPLETED = 'N';
    
    /**
     * 
     * @var Model_DbTable_ActionPlan
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_ActionPlan();

	return $this->_dbTable;
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveActionPlan()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    if ( !$this->_validateActionPlan() )
		return false;
	    
	    $id = $this->saveCase();
	    
	    $this->_data['fk_id_action_plan'] = $id;
	    
	    // Save the barriers
	    $this->saveBarriers();
	    
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
     * @return int 
     */
    public function saveCase()
    {
	if ( empty( $this->_data['id_action_plan'] ) ) {
		
	    $this->_data['active'] = 1;
	    $this->_data['fk_id_dec'] = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;

	    $history = 'JESTAUN KAZU: %s HAKAT 1 - REJISTRU DADOS PLANO ASAUN';

	    // Prepare the data to the history
	    $dataHistory = array(
		'action'	    => 'REJISTU KAZU BA KLIENTE - PLANO ASAUN',
		'description'   => 'REJISTU KAZU BA KLIENTE - PLANO ASAUN',
	    );

	} else {

	    unset( $this->_data['fk_id_counselor'] );

	    $history = 'ATUALIZA JESTAUN KAZU: %s - PLANO ASAUN';

	    // Prepare the data to the history
	    $dataHistory = array(
		'action'		=> 'ATUALIZA KAZU BA KLIENTE - PLANO ASAUN',
		'description'	=> 'ATUALIZA KAZU BA KLIENTE - PLANO ASAUN',
	    );
	}

	// Save the Client
	$id = parent::_simpleSave();
	
	// Set the missing data to the history
	$dataHistory['fk_id_perdata'] = $this->_data['fk_id_perdata'];
	$dataHistory['fk_id_dec'] = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;

	// Save the client history
	$this->_saveHistory( $dataHistory );

	// Save the audit
	$history = sprintf( $history, $id );
	$this->_sysAudit( $history );

	return $id;
    }
    
     /**
     * 
     * @return int|bool
     */
    public function saveCaseDevelopment()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    $history = 'ATUALIZA JESTAUN KAZU: %s - PLANO ASAUN';
	    
	    // Save the barriers
	    $this->saveBarriers();
	    
	    // Save the audit
	    $history = sprintf( $history, $this->_data['fk_id_action_plan'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    return $this->_data['fk_id_action_plan'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     */
    public function saveBarriers()
    {
	$dbBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	
	foreach ( $this->_data['fk_id_barrier_type'] as $pos => $type ) {
	    
	    $intervention = $this->_data['fk_id_barrier_intervention'][$pos];
	    
	    if ( empty( $this->_data['id_action_barrier'][$pos] ) ) {
		
		$row = $dbBarrier->createRow();
		$row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$row->status = self::BARRIER_PENDING;
		
	    } else {
		
		$row = $dbBarrier->fetchRow( array( 'id_action_barrier = ?' => $this->_data['id_action_barrier'][$pos] ) );
		
		if ( !empty( $this->_data['status'][$intervention] ) ) {
		    
		    $row->status = $this->_data['status'][$intervention];
		
		    if ( $row->status == self::BARRIER_COMPLETED )
			$row->date_finish = Zend_Date::now()->toString( 'yyyy-MM-dd' );
		}
	    }
	    
	    if ( !empty( $this->_data['date_finish'][$intervention] ) ) {
		
		$dateFinish = new Zend_Date($this->_data['date_finish'][$intervention]);
		$row->date_finish = $dateFinish->toString( 'yyyy-MM-dd' );
	    }
	    
	    $row->fk_id_action_plan = $this->_data['fk_id_action_plan'];
	    $row->fk_id_barrier_type = $type;
	    $row->fk_id_barrier_name = $this->_data['fk_id_barrier_name'][$pos];
	    $row->fk_id_barrier_intervention = $intervention;
	    $row->save();
	}
    }
    
    /**
     *
     * @return boolean 
     */
    protected function _validateActionPlan()
    {
	if ( !empty( $this->_data['id_action_plan'] ) )
	    return true;
	
	$dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	$actionPlan = $dbActionPlan->fetchRow( array( 'fk_id_perdata = ?' => $this->_data['fk_id_perdata'], 'active = ?' => 1 ) );
	
	if ( !empty( $actionPlan ) ) {
	    
	    $this->_message->addMessage( 'Kliente iha ne\'e iha kazu loket tiha one. La bele rejistu fila fali.', App_Message::ERROR );
	    return false;
	}
	
	return true;
    }
    
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $form = Client_Form_ActionPlan::ID, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::CLIENT,
	    'fk_id_sysform'	    => $form,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
    
    /**
    * 
    * @param int $id_client
    * @param string $action
    */
   protected function _saveHistory( $data )
   {
       $data += array(
	   'fk_id_sysuser'   => Zend_Auth::getInstance()->getIdentity()->id_sysuser,
	   'date'	     => Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm:ss' )
       );
       
       $dbClientHistory = App_Model_DbTable_Factory::get( 'Person_History' );
       $dbClientHistory->createRow( $data )->save();
   }
   
    /**
     * 
     * @return int|bool
     */
    public function deleteBarrier()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	    $dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	    
	    $where = array( 
			$dbAdapter->quoteInto( 'fk_id_action_barrier = ?', $this->_data['id_barrier'] ),
			$dbAdapter->quoteInto( 'fk_id_action_plan = ?', $this->_data['id'] ) 
		    );
	    
	    $dbActionPlanReferences->delete( $where );
	    
	    $where = array( 
			$dbAdapter->quoteInto( 'id_action_barrier = ?', $this->_data['id_barrier'] ),
			$dbAdapter->quoteInto( 'fk_id_action_plan = ?', $this->_data['id'] ) 
		    );
	    
	    $dbBarrier->delete( $where );
	    
	    // Save the auditing
	    $history = 'HAMOS BARREIRA %s BA JESTAUN KAZU: %s';
	    $history = sprintf( $history, $this->_data['id_barrier'], $this->_data['id'] );
	    $this->_sysAudit( $history, Client_Form_ActionPlan::ID  );
	    
	    $dbAdapter->commit();
	    
	    return true;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
   
   /**
    *
    * @param Zend_Db_Table_Row $client
    * @return boolean 
    */
   public function checkClientData( $client )
   {
       $return = array(
	    'valid'  => false,
	    'itens'  => array()
	);
	
	try {
	    
	    $this->_data = $client;
	    $this->_mapperClient = new Client_Model_Mapper_Client();
	    
	    $validators = array(
		'_validateMainData',
		'_validateDocumentData',
		'_validateAddressData',
		'_validatePurposeData'
	    );
	    
	    $validGeneral = true;
	    foreach ( $validators as $validator ) {
		
		$valid = call_user_func( array( $this, $validator ) );
		if ( empty( $valid ) )
		    continue;
		
		$return['itens'][] = $valid;
		
		$validGeneral = empty( $valid['valid'] ) || empty( $validGeneral ) ? false : true;
	    } 
	    
	    $return['valid'] = $validGeneral;
	    
	    return $return;
	
	} catch ( Exception $e ) {
	    
	    return $return;
	}
   }
   
   /**
     *
     * @return array
     */
    protected function _validateMainData()
    {
	$return = array(
	   'msg'    => 'DADUS PRINCIPAIS',
	   'valid'  => true
	);
	
	return $return;
    }
    
    /**
     *
     * @return array
     */
    protected function _validateDocumentData()
    {
	$return = array(
	   'msg'    => 'DOKUMENTU',
	   'valid'  => true
	);
	
	$documents = $this->_mapperClient->listDocument( $this->_data->id_perdata );
	if ( $documents->count() < 1 )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @return array
     */
    protected function _validateAddressData()
    {
	$return = array(
	   'msg'    => 'HELA FATIN',
	   'valid'  => true
	);
	
	$address = $this->_mapperClient->listAddress( $this->_data->id_perdata );
	if ( $address->count() < 1 )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @return array
     */
    protected function _validatePurposeData()
    {
	$return = array(
	   'msg'    => 'OBJETIVO VIZITA',
	   'valid'  => true
	);
	
	$visit = $this->_mapperClient->listVisit( $this->_data->id_perdata );
	if ( $visit->count() < 1 )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listBarriers( $id )
    {
	$dbActionPlanBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	$dbBarrierType = App_Model_DbTable_Factory::get( 'Barrier_Type' );
	$dbBarrierName = App_Model_DbTable_Factory::get( 'Barrier_Name' );
	$dbBarrierIntervention = App_Model_DbTable_Factory::get( 'Barrier_Intervention' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$select = $dbActionPlanBarrier->select()
					->from( array( 'ab' => $dbActionPlanBarrier ) )
					->setIntegrityCheck( false )
					->join(
					    array( 'bi' => $dbBarrierIntervention ),
					    'bi.id_barrier_intervention = ab.fk_id_barrier_intervention',
					    array( 
						'barrier_Intervention_name',
						'date_registration_format'  => new Zend_Db_Expr( 'DATE_FORMAT( ab.date_registration, "%d/%m/%Y" )' ),
						'date_finish_format'	    => new Zend_Db_Expr( 'DATE_FORMAT( ab.date_finish, "%d/%m/%Y" )' ),
					    )
					)
					->join(
					    array( 'bn' => $dbBarrierName ),
					    'bn.id_barrier_name = bi.fk_id_barrier_name',
					    array( 'barrier_name' )
					)
					->join(
					    array( 'bt' => $dbBarrierType ),
					    'bt.id_barrier_type = bn.fk_id_barrier_type',
					    array( 'barrier_type_name' )
					)
					->join(
					    array( 'u' => $dbUser ),
					    'u.id_sysuser = ab.fk_id_sysuser',
					    array( 'user' => 'name' )
					)
					->where( 'ab.fk_id_action_plan = ?', $id )
					->order( array( 'barrier_type_name', 'barrier_name' ) );
	
	return $dbActionPlanBarrier->fetchAll( $select );
    }
    
    /**
     *
     * @return array 
     */
    public function getOptionsStatus()
    {
	return array(
	    self::BARRIER_PENDING	=> 'Seidauk hotu',
	    self::BARRIER_NOT_COMPLETED => 'Laos hotu',
	    self::BARRIER_COMPLETED	=> 'Kompletu'
	);
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detailCase( $id )
    {
	$dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	$dbOcupation = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	
	$select = $dbActionPlan->select()
				->from( array( 'ap' => $dbActionPlan ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'o' => $dbOcupation ),
				    'o.id_profocupationtimor = ap.fk_id_profocupationtimor',
				    array( 'ocupation_name_timor' )
				)
				->join(
				    array( 'u' => $dbUser ),
				    'u.id_sysuser = ap.fk_id_counselor',
				    array( 'name' )
				)
				->join(
				    array( 'd' => $dbDec ),
				    'd.id_dec = ap.fk_id_dec',
				    array( 'name_dec' )
				)
				->where( 'ap.id_action_plan = ?', $id );
	
	return $dbActionPlan->fetchRow( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function casesByClient( $id )
    {
	$dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	$dbOcupation = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$select = $dbActionPlan->select()
				->from( array( 'ap' => $dbActionPlan ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'o' => $dbOcupation ),
				    'o.id_profocupationtimor = ap.fk_id_profocupationtimor',
				    array( 'ocupation_name_timor' )
				)
				->join(
				    array( 'u' => $dbUser ),
				    'u.id_sysuser = ap.fk_id_counselor',
				    array( 'name' )
				)
				->where( 'ap.fk_id_perdata = ?', $id );
	
	return $dbActionPlan->fetchAll( $select );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function finishCase()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dataValid = $this->_data;
	    $valid = $this->checkFinishCase( $this->_data['id'] );
	    
	    if ( empty( $valid['valid'] ) ) {
		
		$this->_message->addMessage( 'Erro: La bele remata kazu! Haree kriterio sira.', App_Message::ERROR );
		return false;
	    }
	    
	    $this->_data = $dataValid;
	    
	    $case = $this->fetchRow( $this->_data['id'] );
	    $case->active = 0;
	    $case->date_end = Zend_Date::now()->toString( 'yyyy-MM-dd' );
	    $case->save();
	    
	    // Save the auditing
	    $history = 'REMATA KAZU NUMERU: %s';
	    $history = sprintf( $history, $this->_data['id'] );
	    $this->_sysAudit( $history, Client_Form_ActionPlan::ID  );
	    
	    $dbAdapter->commit();
	    return $this->_data['id'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @return array
     */
    public function checkFinishCase( $id )
    {
	$return = array(
	    'valid'  => false,
	    'itens'  => array()
	);
	
	try {
	    
	    $this->_data = $this->fetchRow( $id );
	    
	    $validators = array(
		'_validateBarriers',
		'_validateBarriersStatus',
		'_validateCaseNote',
		'_validateAppointment'
	    );
	    
	    $validGeneral = ( 1 == $this->_data->active );
	    foreach ( $validators as $validator ) {
		
		$valid = call_user_func( array( $this, $validator ) );
		if ( empty( $valid ) )
		    continue;
		
		$return['itens'][] = $valid;
		
		$validGeneral = empty( $valid['valid'] ) || empty( $validGeneral ) ? false : true;
	    } 
	    
	    $return['valid'] = $validGeneral;
	    
	    return $return;
	
	} catch ( Exception $e ) {
	    
	    return $return;
	}
    }
    
    /**
     *
     * @return array
     */
    protected function _validateBarriers()
    {
	$barriers = $this->listBarriers( $this->_data->id_action_plan );
	
	$return = array(
	   'msg'    => 'IHA BARRERIA IHA PLANU ASAUN ?',
	   'valid'  => true
	);
	
	if ( $barriers->count() < 1 )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @return array
     */
    protected function _validateBarriersStatus()
    {
	$barriers = $this->listBarriers( $this->_data->id_action_plan );
	
	$return = array(
	   'msg'    => 'BARRERIA STATUS HOTU ONA KA SEIDAUK ?',
	   'valid'  => true
	);
	
	if ( $barriers->count() < 1 )
	    $return['valid'] = false;
	else {
	    
	    foreach ( $barriers as $barrier )
		if ( $barrier->status  == self::BARRIER_PENDING )
		    $return['valid'] = false;
	}
	
	return $return;
    }
    
    /**
     *
     * @return array
     */
    protected function _validateCaseNote()
    {
	$mapperCaseNote = new Client_Model_Mapper_CaseNote();
	$caseNotes = $mapperCaseNote->listNotes( $this->_data->id_action_plan );
	
	$return = array(
	   'msg'    => 'IHA NOTA KAZU IHA KAZU ?',
	   'valid'  => true
	);
	
	if ( $caseNotes->count() < 1 )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @return array
     */
    protected function _validateAppointment()
    {
	$mapperAppointment = new Client_Model_Mapper_Appointment();
	$appointments = $mapperAppointment->listAppointments( $this->_data->id_action_plan );
	
	$return = array(
	   'msg'    => 'IHA AUDIENSIA ?',
	   'valid'  => true
	);
	
	if ( $appointments->count() < 1 )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listJobBarriers( $id )
    {
	$mapperVacancy = new Job_Model_Mapper_JobVacancy();
	$selectVacancy = $mapperVacancy->getSelectVacancy();
	
	$dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	$dbHired = App_Model_DbTable_Factory::get( 'Hired' );
	
	$selectVacancy->join(
			    array( 'apr' => $dbActionPlanReferences ),
			    'apr.fk_id_jobvacancy = jv.id_jobvacancy',
			    array()
			)
			->joinLeft(
			    array( 'h' => $dbHired ),
			    'h.fk_id_jobvacancy = jv.id_jobvacancy
			    AND h.fk_id_perdata = apr.fk_id_perdata',
			    array( 'hired' => 'id_relationship' )
			)
			->where( 'apr.fk_id_action_barrier = ?', $id );
	
	return $dbActionPlanReferences->fetchAll( $selectVacancy );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function clientVacancy()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbJobCandidates = App_Model_DbTable_Factory::get( 'JOBVacancy_Candidates' );
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    $dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	    
	    // Fetch the case
	    $case = $this->fetchRow( $this->_data['id'] );
	    
	    // Fetch the vacancy
	    $mapperVacancy = new Job_Model_Mapper_JobVacancy();
	    $vacancy = $mapperVacancy->fetchRow( $this->_data['vacancy'] );
	    
	    if ( $vacancy->active != 1 ) {
		
		$this->_message->addMessage( 'Vaga ida ne\'e taka tiha ona, la bele tau kliente iha lista kandidatu.', App_Message::ERROR );
		return false;
	    }
	    
	    $where = array( 
		'fk_id_jobvacancy = ?'	=> $this->_data['vacancy'],
		'fk_id_perdata = ?'	=> $case->fk_id_perdata
	    );
	    
	    $hasCandidateList = $dbJobCandidates->fetchRow( $where );
	    
	    // If the candidate is already in candidate list
	    if ( !empty( $hasCandidateList ) ) {
		
		$this->_message->addMessage( 'Kliente iha Lista Kandidatu tiha ona ba vaga empregu iha ne\'e.', App_Message::ERROR );
		return false;
	    }
	  
	    // Add the client to the shortlist
	    $row = $dbJobCandidates->createRow();
	    $row->fk_id_jobvacancy = $this->_data['vacancy'];
	    $row->fk_id_perdata = $case->fk_id_perdata;
	    $row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $row->source = 'C';
	    $row->save();
		
	    // Save history to client
	    $rowHistory = $dbPersonHistory->createRow();
	    $rowHistory->fk_id_perdata = $case->fk_id_perdata;
	    $rowHistory->fk_id_jobvacancy = $this->_data['vacancy'];
	    $rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
	    $rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
	    $rowHistory->action = sprintf( 'KLIENTE SELECIONADO BA LISTA KANDIDATU VAGA EMPREGU NUMERO:%s ', $this->_data['vacancy'] );
	    $rowHistory->description = 'KLIENTE SELECIONADO BA LISTA KANDIDATU BA VAGA EMPREGU';
	    $rowHistory->save();

	    // Save the auditing
	    $history = sprintf( 'KLIENTE BA LISTA KANDIDATU: %s - BA VAGA EMPREGU NUMERU: %s ', $case->fk_id_perdata, $this->_data['vacancy'] );
	    $this->_sysAudit( $history );
	    
	    // Insert the reference to the action barrier
	    $rowReference = $dbActionPlanReferences->createRow();
	    $rowReference->fk_id_perdata = $case->fk_id_perdata;
	    $rowReference->fk_id_action_plan = $this->_data['id'];
	    $rowReference->fk_id_action_barrier = $this->_data['barrier'];
	    $rowReference->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $rowReference->fk_id_jobvacancy = $this->_data['vacancy'];
	    $rowReference->save();
		
	    $dbAdapter->commit();
	    
	    return $this->_data['id'];
	    
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
    public function listClassBarriers( $id )
    {
	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	$selectStudentClass = $mapperStudentClass->getSelectClass();
	
	$dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	$dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	
	$selectStudentClass->join(
			    array( 'apr' => $dbActionPlanReferences ),
			    'apr.fk_id_fefpstudentclass = sc.id_fefpstudentclass',
			    array()
			)
			->joinLeft(
			    array( 'scp' => $dbStudentClassPerData ),
			    'scp.fk_id_fefpstudentclass = sc.id_fefpstudentclass
			    AND scp.fk_id_perdata = apr.fk_id_perdata',
			    array( 'in_class' => 'id_relationship' )
			)
			->where( 'apr.fk_id_action_barrier = ?', $id );
	
	return $dbActionPlanReferences->fetchAll( $selectStudentClass );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listJobTrainingBarriers( $id )
    {
	$mapperJobTraining = new StudentClass_Model_Mapper_JobTraining();
	$selectJobTraining = $mapperJobTraining->getSelectJobTraining();
	
	$dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	$dbJobTrainingTrainee = App_Model_DbTable_Factory::get( 'JOBTraining_Trainee' );
	
	$selectJobTraining->join(
			    array( 'apr' => $dbActionPlanReferences ),
			    'apr.fk_id_jobtraining = jt.id_jobtraining',
			    array()
			)
			->joinLeft(
			    array( 'jtt' => $dbJobTrainingTrainee ),
			    'jtt.fk_id_jobtraining = jt.id_jobtraining
			    AND jtt.fk_id_perdata = apr.fk_id_perdata',
			    array( 'trainee' => 'id_trainee' )
			)
			->where( 'apr.fk_id_action_barrier = ?', $id );
	
	return $dbActionPlanReferences->fetchAll( $selectJobTraining );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function clientClass()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbStudentClassCandidates = App_Model_DbTable_Factory::get( 'StudentClass_Candidates' );
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    $dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	    
	    // Fetch the case
	    $case = $this->fetchRow( $this->_data['id'] );
	    
	    // Fetch the class
	    $mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	    $studentClass = $mapperStudentClass->fetchRow( $this->_data['idClass'] );
	    
	    if ( $studentClass->active != 1 ) {
		
		$this->_message->addMessage( 'Klase Formasaun ida ne\'e taka tiha ona, la bele tau kliente iha lista kandidatu.', App_Message::ERROR );
		return false;
	    }
	    
	    $where = array( 
			'fk_id_fefpstudentclass = ?' => $this->_data['idClass'],
			'fk_id_perdata = ?'	     => $case->fk_id_perdata
		     );
	    
	    $hasCandidateList = $dbStudentClassCandidates->fetchRow( $where );
	    
	    // If the candidate is already in candidate list
	    if ( !empty( $hasCandidateList ) ) {
		
		$this->_message->addMessage( 'Kliente iha Lista Kandidatu tiha ona ba klase formasaun ida ne\'e.', App_Message::ERROR );
		return false;
	    }
	    
	    // Add the client to the list candidates
	    $row = $dbStudentClassCandidates->createRow();
	    $row->fk_id_fefpstudentclass = $this->_data['idClass'];
	    $row->fk_id_perdata = $case->fk_id_perdata;
	    $row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $row->shortlisted = 0;
	    $row->source = 'C';
	    $row->save();

	    // Save history to client
	    $rowHistory = $dbPersonHistory->createRow();
	    $rowHistory->fk_id_perdata = $case->fk_id_perdata;
	    $rowHistory->fk_id_student_class = $this->_data['idClass'];
	    $rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
	    $rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
	    $rowHistory->action = sprintf( 'KLIENTE SELECIONADO BA LISTA KANDIDATU TURMA TREINAMENTU NUMERO: %s ', $this->_data['idClass'] );
	    $rowHistory->description = 'KLIENTE SELECIONADO BA LISTA KANDIDATU TURMA TREINAMENTU NUMERO';
	    $rowHistory->save();

	    // Save the auditing
	    $history = sprintf( 'KLIENTE BA LISTA KANDIDATU: %s - BA TURMA TREINAMENTU NUMERU: %s ', $case->fk_id_perdata, $this->_data['idClass'] );
	    $this->_sysAudit( $history );
	    
	    // Insert the reference to the action barrier
	    $rowReference = $dbActionPlanReferences->createRow();
	    $rowReference->fk_id_perdata = $case->fk_id_perdata;
	    $rowReference->fk_id_action_plan = $this->_data['id'];
	    $rowReference->fk_id_action_barrier = $this->_data['barrier'];
	    $rowReference->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $rowReference->fk_id_fefpstudentclass = $this->_data['idClass'];
	    $rowReference->save();
	    
	    $dbAdapter->commit();
	    
	    return $this->_data['id'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @return int|bool
     */
    public function clientJobTraining()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbJobTrainingCandidates = App_Model_DbTable_Factory::get( 'JOBTraining_Candidates' );
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    $dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	    
	    // Fetch the case
	    $case = $this->fetchRow( $this->_data['id'] );
	    
	    // Fetch the job training
	    $mapperJobTraining = new StudentClass_Model_Mapper_JobTraining();
	    $jobTraining = $mapperJobTraining->fetchRow( $this->_data['idJobTraining'] );
	    
	    if ( $jobTraining->status != 1 ) {
		
		$this->_message->addMessage( 'Job Training ida ne\'e taka tiha ona, la bele tau kliente iha lista kandidatu.', App_Message::ERROR );
		return false;
	    }
	    
	    $where = array( 
			'fk_id_jobtraining = ?' => $this->_data['idJobTraining'],
			'fk_id_perdata = ?'	=> $case->fk_id_perdata
		     );
	    
	    $hasCandidateList = $dbJobTrainingCandidates->fetchRow( $where );
	    
	    // If the candidate is already in candidate list
	    if ( !empty( $hasCandidateList ) ) {
		
		$this->_message->addMessage( 'Kliente iha Lista Kandidatu tiha ona ba job training ida ne\'e.', App_Message::ERROR );
		return false;
	    }
	    
	    // Add the client to the list candidates
	    $row = $dbJobTrainingCandidates->createRow();
	    $row->fk_id_jobtraining = $this->_data['idJobTraining'];
	    $row->fk_id_perdata = $case->fk_id_perdata;
	    $row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $row->selected = 0;
	    $row->save();

	    // Save history to client
	    $rowHistory = $dbPersonHistory->createRow();
	    $rowHistory->fk_id_perdata = $case->fk_id_perdata;
	    $rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
	    $rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
	    $rowHistory->action = sprintf( 'KLIENTE SELECIONADO BA LISTA KANDIDATU JOB TRAINING NUMERO: %s ', $this->_data['idJobTraining'] );
	    $rowHistory->description = 'KLIENTE SELECIONADO BA LISTA KANDIDATU JOB TRAINING NUMERO';
	    $rowHistory->save();

	    // Save the auditing
	    $history = sprintf( 'KLIENTE BA LISTA KANDIDATU: %s - BA JOB TRAINING NUMERU: %s ', $case->fk_id_perdata, $this->_data['idJobTraining'] );
	    $this->_sysAudit( $history );
	    
	    // Insert the reference to the action barrier
	    $rowReference = $dbActionPlanReferences->createRow();
	    $rowReference->fk_id_perdata = $case->fk_id_perdata;
	    $rowReference->fk_id_action_plan = $this->_data['id'];
	    $rowReference->fk_id_action_barrier = $this->_data['barrier'];
	    $rowReference->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $rowReference->fk_id_jobtraining = $this->_data['idJobTraining'];
	    $rowReference->save();
	    
	    $dbAdapter->commit();
	    
	    return $this->_data['id'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveCaseCancel()
    {
	
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	    
	    // Cancel case
	    $where = $dbAdapter->quoteInto( 'id_action_plan = ?', $this->_data['fk_id_action_plan'] );
	    $dataUpdate = array(
		'active'		=>  2,
		'date_end'		=>  Zend_Date::now()->toString( 'yyyy-MM-dd' ),
		'cancel_justification'	=>  $this->_data['cancel_justification']
	    );
	    
	    $dbActionPlan->update( $dataUpdate, $where );
	    
	    // Save auditing
	    $history = 'KANSELA KAZU: %s - JUSTIFIKASAUN: %s';
	    
	    $history = sprintf( $history, $this->_data['fk_id_action_plan'], $this->_data['cancel_justification'] );
	    $this->_sysAudit( $history, Client_Form_CaseCancel::ID );
	    
	    $dbAdapter->commit();
	    return $this->_data['fk_id_action_plan'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listByFilters( $filters = array() )
    {
	$dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	$dbActionPlanGroup = App_Model_DbTable_Factory::get( 'Action_Plan_Group' );
	$dbActionPlanHasGroup = App_Model_DbTable_Factory::get( 'Action_Plan_has_Group' );
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$selectCase = $dbActionPlan->select()
				   ->from(
					array( 'ap' => $dbActionPlan ),
					array(
					    'id'			=> 'id_action_plan',
					    'status'			=> 'active',
					    'type'			=> new Zend_Db_Expr( '"S"' ),
					    'fk_id_counselor',
					    'fk_id_fefpeduinstitution'  => new Zend_Db_Expr( 'NULL' ),
					    'fk_id_addcountry'		=> new Zend_Db_Expr( 'NULL' ),
					    'date_start'		=> new Zend_Db_Expr( 'DATE_FORMAT( ap.date_insert, "%d/%m/%Y" )' ),
					    'fk_id_profocupationtimor',
					)
				    )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 'pd' => $dbPerData ),
					'pd.id_perdata = ap.fk_id_perdata',
					array(
					    'title' => new Zend_Db_Expr( 'CONCAT( pd.first_name, " ", IFNULL(pd.medium_name, ""), " ", pd.last_name )' ),
					    'client' => 'id_perdata'
					)
				    )
				    ->where( 'ap.type = ?', 'S' );
	
	$selectGroup = $dbActionPlanGroup->select()
					 ->from(
					    array( 'apg' => $dbActionPlanGroup ),
					    array(
						'id'	     => 'id_action_plan_group',
						'status',
						'type'	     => new Zend_Db_Expr( '"G"' ),
						'fk_id_counselor',
						'fk_id_fefpeduinstitution',
						'fk_id_addcountry',
						'date_start' => new Zend_Db_Expr( 'DATE_FORMAT( apg.date_registration, "%d/%m/%Y" )' ),
					    )
					 )
					 ->setIntegrityCheck( false )
					 ->join(
					    array( 'aphg' => $dbActionPlanHasGroup ),
					    'aphg.fk_id_action_plan_group = apg.id_action_plan_group',
					    array()
					 )
					 ->join(
					    array( 'ap' => $dbActionPlan ),
					    'aphg.fk_id_action_plan = ap.id_action_plan',
					    array(
						'fk_id_profocupationtimor',
						'title' => 'apg.name',
						'client' => new Zend_Db_Expr( 'NULL' ) 
					    )
					 );
	
	$selectUnion = $dbActionPlan->select()
				    ->union( array( $selectCase, $selectGroup ) )
				    ->setIntegrityCheck( false );
	
	$lastSelect = $dbActionPlan->select()
			     ->setIntegrityCheck( false )
			     ->from( array( 't' => new Zend_Db_Expr( '(' . $selectUnion . ')' ) ) )
			     ->join(
				array( 'c' => $dbUser ),
				'c.id_sysuser = t.fk_id_counselor',
				array( 'counselor' => 'name' )
			     )
			     ->order( array( 't.date_start DESC' ) )
			     ->group( array( 't.id', 't.type' ) );
	
	if ( !empty( $filters['fk_id_counselor'] ) )
	    $lastSelect->where( 't.fk_id_counselor = ?', $filters['fk_id_counselor'] );
	
	if ( !empty( $filters['fk_id_profocupationtimor'] ) )
	    $lastSelect->where( 't.fk_id_profocupationtimor = ?', $filters['fk_id_profocupationtimor'] );
	
	if ( !empty( $filters['fk_id_fefpeduinstitution'] ) )
	    $lastSelect->where( 't.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution'] );
	
	if ( !empty( $filters['fk_id_addcountry'] ) )
	    $lastSelect->where( 't.fk_id_addcountry = ?', $filters['fk_id_addcountry'] );
	
	if ( array_key_exists( 'status', $filters ) && $filters['status'] != '' )
	    $lastSelect->where( 't.status = ?', (int)$filters['status'] );
	
	if ( !empty( $filters['type'] ) )
	    $lastSelect->where( 't.type = ?', $filters['type'] );
	
	return $dbActionPlan->fetchAll( $lastSelect );
    }
}