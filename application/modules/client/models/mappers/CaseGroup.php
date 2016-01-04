<?php

class Client_Model_Mapper_CaseGroup extends App_Model_Abstract
{
    
     /**
     * 
     * @var Model_DbTable_ActionPlanGroup
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_ActionPlanGroup();

	return $this->_dbTable;
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveCaseGroup()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    if ( empty( $this->_data['id_action_plan_group'] ) ) {
		
		$this->_data['active'] = 1;
		$this->_data['fk_id_dec'] = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
		
		$history = 'JESTAUN KAZU GRUPU: %s HAKAT 1 - REJISTRU DADUS PRINSIPAL';
		
	    } else {
		
		unset( $this->_data['fk_id_counselor'] );
		
		$history = 'ATUALIZA JESTAUN KAZU GRUPU: %s - PLANO DADUS PRINSIPAL';
	    }
	    
	    // Save the Case Group
	    $id = parent::_simpleSave();
	    
	    // Save the audit
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
     * @return int|bool
     */
    public function saveActionPlanGroup()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $mapperCase = new Client_Model_Mapper_Case();
	    $cases = $this->listCasesInGroup( $this->_data['fk_id_action_plan_group'] );
	    
	    foreach ( $cases as $case ) {
		
		$dataBarrier = $this->_data;
		$dataBarrier['fk_id_action_plan'] = $case->id_action_plan;
		$mapperCase->setData( $dataBarrier )->saveBarriers();
	    }
	    
	    $history = 'INSERE BARREIRAS IHA KAZU GRUPU: %s';
	    
	    // Save the audit
	    $history = sprintf( $history, $this->_data['fk_id_action_plan_group'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    return $this->_data['fk_id_action_plan_group'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detailCase( $id )
    {
	$dbActionPlanGroup = App_Model_DbTable_Factory::get( 'Action_Plan_Group' );
	$dbEduInsitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbAddCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$select = $dbActionPlanGroup->select()
				->from( array( 'apg' => $dbActionPlanGroup ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'u' => $dbUser ),
				    'u.id_sysuser = apg.fk_id_counselor',
				    array( 'name' )
				)
				->joinLeft(
				    array( 'e' => $dbEduInsitution ),
				    'e.id_fefpeduinstitution = apg.fk_id_fefpeduinstitution',
				    array( 'institution' )
				)
				->joinLeft(
				    array( 'c' => $dbAddCountry ),
				    'c.id_addcountry = apg.fk_id_addcountry',
				    array( 'country' )
				)
				->where( 'apg.id_action_plan_group = ?', $id );
	
	return $dbActionPlanGroup->fetchRow( $select );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function addClient()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $rows = $this->listCasesInGroup( $this->_data['case_group'] );
	    
	    $clientsInGroup = array();
	    foreach ( $rows as $row )
		$clientsInGroup[] = $row->fk_id_perdata;
	    
	    $newClients = array_diff( $this->_data['clients'], $clientsInGroup );
	    // If there is now new clients
	    if ( empty( $newClients ) ) {
		
		$this->_message->addMessage( 'Kliente hotu iha grupu tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    // Search for the case group
	    $caseGroup = $this->fetchRow( $this->_data['case_group'] );
	    
	    $dbActionPlanHasGroup = App_Model_DbTable_Factory::get( 'Action_Plan_has_Group' );
	    $dbActionPlanBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	    
	    $mapperCase = new Client_Model_Mapper_Case();
	    
	    // Insert the barriers to the new clients
	    $barriers = $this->listBarriers( $this->_data['case_group'] );
	    
	    // Generate cases for all the clients
	    foreach ( $this->_data['clients'] as $client ) {
		
		$dataCase = array(
		    'fk_id_perdata'		=> $client,
		    'fk_id_profocupationtimor'	=> $this->_data['occupation'],
		    'fk_id_counselor'		=> $caseGroup->fk_id_counselor,
		    'type'			=> 'G'
		);
		
		// Save the CAse
		$idCase = $mapperCase->setData( $dataCase )->saveCase();
		
		$rowCase = $dbActionPlanHasGroup->createRow();
		$rowCase->fk_id_action_plan_group = $this->_data['case_group'];
		$rowCase->fk_id_action_plan = $idCase;
		$rowCase->save();
		
		// Insert the reamining barriers
		foreach ( $barriers as $barrier ) {
		    
		    $data = $barrier->toArray();
		    unset( $data['id_action_barrier'] );
		    
		    $rowBarrier = $dbActionPlanBarrier->createRow( $data );
		    $rowBarrier->fk_id_action_plan = $idCase;
		    $rowBarrier->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		    $rowBarrier->status = Client_Model_Mapper_Case::BARRIER_PENDING;
		    $rowBarrier->save();
		}
	    }
	    
	    $history = 'INSERE KLIENTE IHA KAZU GRUPU: %s';
	    
	    // Save the audit
	    $history = sprintf( $history, $this->_data['case_group'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    return $this->_data['case_group'];
	    
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
    public function saveCaseGroupResult()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbActionPlanBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	    $dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	    
	    $today = Zend_Date::now()->toString( 'yyyy-MM-dd' );
	    foreach ( $this->_data['status'] as $case => $result ) {
		
		if ( $result == Client_Model_Mapper_Case::BARRIER_PENDING )
		    continue;
		
		$update = array( 'status' => $result );
		$where = array(
		    'fk_id_barrier_intervention = ?' => $this->_data['fk_id_barrier_intervention'],
		    'fk_id_action_plan = ?'	     => $case
		);
		
		if ( $result == Client_Model_Mapper_Case::BARRIER_COMPLETED )
		    $update['date_finish'] = $today;
		
		// If one intervention is failed, cancel all the others intervention and finishes the case
		if ( $result == Client_Model_Mapper_Case::BARRIER_NOT_COMPLETED ) {
		    
		    $whereCancel = array(
			'fk_id_action_plan = ?' => $case,
			'status = ?'		=> Client_Model_Mapper_Case::BARRIER_PENDING
		    );
		    
		    $dbActionPlanBarrier->update( $update, $whereCancel );
		    
		    // Cancel the case if one intervention is failed
		    $updateCase = array(
			'active'		=> 2,
			'date_end'		=> $today,
			'cancel_justification'	=> 'INTERVENSAUN LAOS HOTU IHA KAZU GRUPU'
		    );
		    
		    $whereCase = array( 'id_action_plan = ?' => $case );
		    $dbActionPlan->update( $updateCase, $whereCase );
		}
		
		// Updates the intervention
		$dbActionPlanBarrier->update( $update, $where );
	    }
	    
	    $history = 'ATUALIZA STATUS INTERVENSAUN %s IHA KAZU GRUPU: %s';
	    
	    // Save the audit
	    $history = sprintf( $history, $this->_data['fk_id_barrier_intervention'], $this->_data['fk_id_action_plan_group'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    return $this->_data['fk_id_action_plan_group'];
	    
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
    public function clientToVacancy()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	    $dbActionPlanBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	    $dbJobCandidates = App_Model_DbTable_Factory::get( 'JOBVacancy_Candidates' );
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    $dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	    
	    $clients = array();
	    $casesClient = array();
	    
	    // Fetch the clients related to the case
	    $rows = $dbActionPlan->fetchAll( array( 'id_action_plan IN (?)' => $this->_data['cases'] ) );
	    foreach ( $rows as $row ) {
		
		$clients[] = $row->fk_id_perdata;
		$casesClient[$row->fk_id_perdata] = $row->id_action_plan;
	    }
	    
	    // Fetch the vacancy
	    $mapperVacancy = new Job_Model_Mapper_JobVacancy();
	    $vacancy = $mapperVacancy->fetchRow( $this->_data['vacancy'] );
	    
	    if ( $vacancy->active != 1 ) {
		
		$this->_message->addMessage( 'Vaga ida ne\'e taka tiha ona, la bele tau kliente iha lista kandidatu.', App_Message::ERROR );
		return false;
	    }
	    
	    $where = array( 'fk_id_jobvacancy = ?' => $this->_data['vacancy'] );
	    $rows = $dbJobCandidates->fetchAll( $where );
	    
	    $clientsCandidates = array();
	    foreach ( $rows as $row )
		$clientsCandidates[] = $row->fk_id_perdata;
	    
	    $newClients = array_diff( $clients, $clientsCandidates );
	    
	    // If all the candidates are already in candidate list
	    if ( empty( $newClients ) ) {
		
		$this->_message->addMessage( 'Kliente hotu iha Lista Kandidatu tiha ona ba vaga empregu iha ne\'e.', App_Message::ERROR );
		return false;
	    }
	    
	    // Insert all the clients in the candidate list
	    foreach ( $newClients as $client ) {
	  
		// Add the client to the shortlist
		$row = $dbJobCandidates->createRow();
		$row->fk_id_jobvacancy = $this->_data['vacancy'];
		$row->fk_id_perdata = $client;
		$row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$row->source = 'C';
		$row->save();

		// Save history to client
		$rowHistory = $dbPersonHistory->createRow();
		$rowHistory->fk_id_perdata = $client;
		$rowHistory->fk_id_jobvacancy = $this->_data['vacancy'];
		$rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
		$rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
		$rowHistory->action = sprintf( 'KLIENTE SELECIONADO BA LISTA KANDIDATU VAGA EMPREGU NUMERO:%s ', $this->_data['vacancy'] );
		$rowHistory->description = 'KLIENTE SELECIONADO BA LISTA KANDIDATU BA VAGA EMPREGU';
		$rowHistory->save();

		// Save the auditing
		$history = sprintf( 'KLIENTE BA LISTA KANDIDATU: %s - BA VAGA EMPREGU NUMERU: %s ', $client, $this->_data['vacancy'] );
		$this->_sysAudit( $history );
		
		
		$whereBarrier = array(
		    'fk_id_action_plan = ?'	     => $casesClient[$client],
		    'fk_id_barrier_intervention = ?' => $this->_data['intervention']
		);
		
		$barrier = $dbActionPlanBarrier->fetchRow( $whereBarrier );

		// Insert the reference to the action barrier
		$rowReference = $dbActionPlanReferences->createRow();
		$rowReference->fk_id_perdata = $client;
		$rowReference->fk_id_action_plan = $casesClient[$client];
		$rowReference->fk_id_action_barrier = $barrier->id_action_barrier;
		$rowReference->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$rowReference->fk_id_jobvacancy = $this->_data['vacancy'];
		$rowReference->save();
	    }
		
	    $dbAdapter->commit();
	    
	    return $this->_data['case_id'];
	    
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
    public function clientToClass()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	    $dbActionPlanBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	    $dbStudentClassCandidates = App_Model_DbTable_Factory::get( 'StudentClass_Candidates' );
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    $dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	    
	    $clients = array();
	    $casesClient = array();
	    
	    // Fetch the clients related to the case
	    $rows = $dbActionPlan->fetchAll( array( 'id_action_plan IN (?)' => $this->_data['cases'] ) );
	    foreach ( $rows as $row ) {
		
		$clients[] = $row->fk_id_perdata;
		$casesClient[$row->fk_id_perdata] = $row->id_action_plan;
	    }
	    
	    // Fetch the class
	    $mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	    $studentClass = $mapperStudentClass->fetchRow( $this->_data['idClass'] );
	    
	    if ( $studentClass->active != 1 ) {
		
		$this->_message->addMessage( 'Klase Formasaun ida ne\'e taka tiha ona, la bele tau kliente iha lista kandidatu.', App_Message::ERROR );
		return false;
	    }
	    
	    $where = array( 'fk_id_fefpstudentclass = ?' => $this->_data['idClass'] );
	    $rows = $dbStudentClassCandidates->fetchAll( $where );
	    
	    $clientsCandidates = array();
	    foreach ( $rows as $row )
		$clientsCandidates[] = $row->fk_id_perdata;
	    
	    $newClients = array_diff( $clients, $clientsCandidates );
	    
	    // If all the candidates are already in candidate list
	    if ( empty( $newClients ) ) {
		
		$this->_message->addMessage( 'Kliente hotu iha Lista Kandidatu tiha ona ba klase formasaun ida ne\'e.', App_Message::ERROR );
		return false;
	    }
	    
	    // Insert all the clients in the candidate list
	    foreach ( $newClients as $client ) {
	  
		// Add the client to the list candidates
		$row = $dbStudentClassCandidates->createRow();
		$row->fk_id_fefpstudentclass = $this->_data['idClass'];
		$row->fk_id_perdata = $client;
		$row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$row->shortlisted = 0;
		$row->source = 'C';
		$row->save();

		// Save history to client
		$rowHistory = $dbPersonHistory->createRow();
		$rowHistory->fk_id_perdata = $client;
		$rowHistory->fk_id_student_class = $this->_data['idClass'];
		$rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
		$rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
		$rowHistory->action = sprintf( 'KLIENTE SELECIONADO BA LISTA KANDIDATU TURMA TREINAMENTU NUMERO: %s ', $this->_data['idClass'] );
		$rowHistory->description = 'KLIENTE SELECIONADO BA LISTA KANDIDATU TURMA TREINAMENTU NUMERO';
		$rowHistory->save();

		// Save the auditing
		$history = sprintf( 'KLIENTE BA LISTA KANDIDATU: %s - BA TURMA TREINAMENTU NUMERU: %s ', $client, $this->_data['idClass'] );
		$this->_sysAudit( $history );

		$whereBarrier = array(
		    'fk_id_action_plan = ?'	     => $casesClient[$client],
		    'fk_id_barrier_intervention = ?' => $this->_data['intervention']
		);
		
		$barrier = $dbActionPlanBarrier->fetchRow( $whereBarrier );

		// Insert the reference to the action barrier
		$rowReference = $dbActionPlanReferences->createRow();
		$rowReference->fk_id_perdata = $client;
		$rowReference->fk_id_action_plan = $casesClient[$client];
		$rowReference->fk_id_action_barrier = $barrier->id_action_barrier;
		$rowReference->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$rowReference->fk_id_fefpstudentclass = $this->_data['idClass'];
		$rowReference->save();
	    }
		
	    $dbAdapter->commit();
	    
	    return $this->_data['case_id'];
	    
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
    public function clientToJobTraining()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	    $dbActionPlanBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	    $dbJobTrainingCandidates = App_Model_DbTable_Factory::get( 'JOBTraining_Candidates' );
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    $dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	    
	    $clients = array();
	    $casesClient = array();
	    
	    // Fetch the clients related to the case
	    $rows = $dbActionPlan->fetchAll( array( 'id_action_plan IN (?)' => $this->_data['cases'] ) );
	    foreach ( $rows as $row ) {
		
		$clients[] = $row->fk_id_perdata;
		$casesClient[$row->fk_id_perdata] = $row->id_action_plan;
	    }
	    
	    // Fetch the job training
	    $mapperJobTraining = new StudentClass_Model_Mapper_JobTraining();
	    $jobTraining = $mapperJobTraining->fetchRow( $this->_data['idJobTraining'] );
	    
	    if ( $jobTraining->status != 1 ) {
		
		$this->_message->addMessage( 'Job Training ida ne\'e taka tiha ona, la bele tau kliente iha lista kandidatu.', App_Message::ERROR );
		return false;
	    }
	    
	    $where = array( 'fk_id_jobtraining = ?' => $this->_data['idJobTraining'] );
	    $rows = $dbJobTrainingCandidates->fetchAll( $where );
	    
	    $clientsCandidates = array();
	    foreach ( $rows as $row )
		$clientsCandidates[] = $row->fk_id_perdata;
	    
	    $newClients = array_diff( $clients, $clientsCandidates );
	    
	    // If all the candidates are already in candidate list
	    if ( empty( $newClients ) ) {
		
		$this->_message->addMessage( 'Kliente hotu iha Lista Kandidatu tiha ona ba job training ida ne\'e.', App_Message::ERROR );
		return false;
	    }
	    
	    // Insert all the clients in the candidate list
	    foreach ( $newClients as $client ) {
	  
		// Add the client to the list candidates
		$row = $dbJobTrainingCandidates->createRow();
		$row->fk_id_jobtraining = $this->_data['idJobTraining'];
		$row->fk_id_perdata = $client;
		$row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$row->selected = 0;
		$row->save();

		// Save history to client
		$rowHistory = $dbPersonHistory->createRow();
		$rowHistory->fk_id_perdata = $client;
		$rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
		$rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
		$rowHistory->action = sprintf( 'KLIENTE SELECIONADO BA LISTA KANDIDATU JOB TRAINING NUMERO: %s ', $this->_data['idJobTraining'] );
		$rowHistory->description = 'KLIENTE SELECIONADO BA LISTA KANDIDATU JOB TRAININGU NUMERO';
		$rowHistory->save();

		// Save the auditing
		$history = sprintf( 'KLIENTE BA LISTA KANDIDATU: %s - BA JOB TRAINING NUMERU: %s ', $client, $this->_data['idJobTraining'] );
		$this->_sysAudit( $history );

		$whereBarrier = array(
		    'fk_id_action_plan = ?'	     => $casesClient[$client],
		    'fk_id_barrier_intervention = ?' => $this->_data['intervention']
		);
		
		$barrier = $dbActionPlanBarrier->fetchRow( $whereBarrier );

		// Insert the reference to the action barrier
		$rowReference = $dbActionPlanReferences->createRow();
		$rowReference->fk_id_perdata = $client;
		$rowReference->fk_id_action_plan = $casesClient[$client];
		$rowReference->fk_id_action_barrier = $barrier->id_action_barrier;
		$rowReference->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$rowReference->fk_id_jobtraining = $this->_data['idJobTraining'];
		$rowReference->save();
	    }
		
	    $dbAdapter->commit();
	    
	    return $this->_data['case_id'];
	    
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
    public function listCasesInGroup( $id )
    {
	$dbActionPlanGroup = App_Model_DbTable_Factory::get( 'Action_Plan_has_Group' );
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
				    array( 'apg' => $dbActionPlanGroup ),
				    'apg.fk_id_action_plan = ap.id_action_plan',
				    array()
				)
				->join(
				    array( 'u' => $dbUser ),
				    'u.id_sysuser = ap.fk_id_counselor',
				    array( 'name' )
				)
				->where( 'apg.fk_id_action_plan_group = ?', $id );
	
	return $dbActionPlan->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset 
     */
    public function listClientGroup( $id )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$selectClient = $mapperClient->selectClient();
	
	$dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	$dbActionPlanHasGroup = App_Model_DbTable_Factory::get( 'Action_Plan_has_Group' );
	$dbOcupation = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$selectClient->join(
			array( 'ap1' => $dbActionPlan ),
			'ap1.fk_id_perdata = c.id_perdata',
			array( 
			    'id_action_plan',
			    'status_case' => 'active'
			)
		    )
		    ->join(
			array( 'apg' => $dbActionPlanHasGroup ),
			'apg.fk_id_action_plan = ap1.id_action_plan',
			array()
		    )
		    ->join(
			array( 'o' => $dbOcupation ),
			'o.id_profocupationtimor = ap1.fk_id_profocupationtimor',
			array( 'ocupation_name_timor' )
		    )
		    ->join(
			array( 'u' => $dbUser ),
			'u.id_sysuser = ap1.fk_id_counselor',
			array( 'name' )
		    )
		    ->where( 'apg.fk_id_action_plan_group = ?', $id )
		    ->where( 'ap1.type = ?', 'G' );
	
	return $dbActionPlanHasGroup->fetchAll( $selectClient );			
    }
    
    /**
     *
     * @param array $data
     * @return Zend_Db_Table_Rowset 
     */
    public function listCasesResult( $data )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$selectClient = $mapperClient->selectClient();
	
	$dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	$dbActionPlanHasGroup = App_Model_DbTable_Factory::get( 'Action_Plan_has_Group' );
	$dbActionPlanBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	$dbOcupation = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$selectClient->join(
			array( 'ap1' => $dbActionPlan ),
			'ap1.fk_id_perdata = c.id_perdata',
			array( 'id_action_plan' )
		    )
		    ->join(
			array( 'apg' => $dbActionPlanHasGroup ),
			'apg.fk_id_action_plan = ap1.id_action_plan',
			array()
		    )
		    ->join(
			array( 'o' => $dbOcupation ),
			'o.id_profocupationtimor = ap1.fk_id_profocupationtimor',
			array( 'ocupation_name_timor' )
		    )
		    ->join(
			array( 'u' => $dbUser ),
			'u.id_sysuser = ap1.fk_id_counselor',
			array( 'name' )
		    )
		    ->joinLeft(
			array( 'apb' => $dbActionPlanBarrier ),
			'apb.fk_id_action_plan = ap1.id_action_plan
			 AND apb.fk_id_barrier_type = ' . $data['fk_id_barrier_type'] .
			' AND apb.fk_id_barrier_name = ' . $data['fk_id_barrier_name'] .
			' AND apb.fk_id_barrier_intervention = ' . $data['fk_id_barrier_intervention'],
			array( 'status' )
		    )
		    ->where( 'apg.fk_id_action_plan_group = ?', $data['fk_id_action_plan_group'] )
		    ->where( 'ap1.active != ?', ( empty( $data['all'] ) ? 2 : 3 ) )
		    ->where( 'ap1.type = ?', 'G' )
		    ->group( array( 'ap1.id_action_plan' ) );
	
	return $dbActionPlanHasGroup->fetchAll( $selectClient );
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
	$dbActionPlanHasGroup = App_Model_DbTable_Factory::get( 'Action_Plan_has_Group' );
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
					->join(
					    array( 'apg' => $dbActionPlanHasGroup ),
					    'apg.fk_id_action_plan = ab.fk_id_action_plan',
					    array( 'fk_id_action_plan_group' )
					)
					->where( 'apg.fk_id_action_plan_group = ?', $id )
					->group( array( 'fk_id_barrier_intervention' ) )
					->order( array( 'barrier_type_name', 'barrier_name' ) );
	
	return $dbActionPlanBarrier->fetchAll( $select );
    }
    
    /**
     *
     * @param int $barrier
     * @return Zend_Db_Table_Rowset
     */
    public function listJobIntervention( $intervention, $case )
    {
	$mapperVacancy = new Job_Model_Mapper_JobVacancy();
	$selectVacancy = $mapperVacancy->getSelectVacancy();
	
	$dbActionPlanBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	$dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	$dbActionpPlanHasGroup = App_Model_DbTable_Factory::get( 'Action_Plan_has_Group' );
	
	$selectVacancy->join(
			    array( 'apr' => $dbActionPlanReferences ),
			    'apr.fk_id_jobvacancy = jv.id_jobvacancy',
			    array()
			)
			->join(
			    array( 'apb' => $dbActionPlanBarrier ),
			    'apb.id_action_barrier = apr.fk_id_action_barrier',
			    array()
			)
			->join(
			    array( 'apg' => $dbActionpPlanHasGroup ),
			    'apg.fk_id_action_plan = apb.fk_id_action_plan',
			    array()
			)
			->where( 'apg.fk_id_action_plan_group = ?', $case )
			->where( 'apb.fk_id_barrier_intervention = ?', $intervention )
			->group( array( 'id_jobvacancy' ) );
	
	return $dbActionPlanReferences->fetchAll( $selectVacancy );
    }
    
    /**
     *
     * @param int $case
     * @param int $vacancy
     * @return Zend_Db_Table_Rowset
     */
    public function listClientVacancy( $case, $vacancy )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$selectClient = $mapperClient->selectClient();
	
	$dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	$dbActionPlanHasGroup = App_Model_DbTable_Factory::get( 'Action_Plan_has_Group' );
	$dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	
	$selectClient->join(
			array( 'ap1' => $dbActionPlan ),
			'ap1.fk_id_perdata = c.id_perdata',
			array( 'id_action_plan' )
		    )
		    ->join(
			array( 'apg' => $dbActionPlanHasGroup ),
			'apg.fk_id_action_plan = ap1.id_action_plan',
			array()
		    )
		     ->joinLeft(
			array( 'apr' => $dbActionPlanReferences ),
			'apr.fk_id_action_plan = ap1.id_action_plan
			 AND apr.fk_id_jobvacancy = ' . $vacancy,
			array( 'id_references' )
		    )
		    ->where( 'apg.fk_id_action_plan_group = ?', $case )
		    ->where( 'ap1.active != ?', 2 )
		    ->where( 'ap1.type = ?', 'G' )
		    ->group( array( 'ap1.id_action_plan' ) );
	
	return $dbActionPlanHasGroup->fetchAll( $selectClient );
    }
    
     /**
     *
     * @param int $barrier
     * @return Zend_Db_Table_Rowset
     */
    public function listClassIntervention( $intervention, $case )
    {
	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	$selectStudentClass = $mapperStudentClass->getSelectClass();
	
	$dbActionPlanBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	$dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	$dbActionpPlanHasGroup = App_Model_DbTable_Factory::get( 'Action_Plan_has_Group' );
	
	$selectStudentClass->join(
			    array( 'apr' => $dbActionPlanReferences ),
			    'apr.fk_id_fefpstudentclass = sc.id_fefpstudentclass',
			    array()
			)
			->join(
			    array( 'apb' => $dbActionPlanBarrier ),
			    'apb.id_action_barrier = apr.fk_id_action_barrier',
			    array()
			)
			->join(
			    array( 'apg' => $dbActionpPlanHasGroup ),
			    'apg.fk_id_action_plan = apb.fk_id_action_plan',
			    array()
			)
			->where( 'apg.fk_id_action_plan_group = ?', $case )
			->where( 'apb.fk_id_barrier_intervention = ?', $intervention )
			->group( array( 'id_fefpstudentclass' ) );
	
	return $dbActionPlanReferences->fetchAll( $selectStudentClass );
    }
    
     /**
     *
     * @param int $barrier
     * @return Zend_Db_Table_Rowset
     */
    public function listJobTrainingIntervention( $intervention, $case )
    {
	$mapperJobTraining = new StudentClass_Model_Mapper_JobTraining();
	$selectJobTraining = $mapperJobTraining->getSelectJobTraining();
	
	$dbActionPlanBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	$dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	$dbActionpPlanHasGroup = App_Model_DbTable_Factory::get( 'Action_Plan_has_Group' );
	
	$selectJobTraining->join(
			    array( 'apr' => $dbActionPlanReferences ),
			    'apr.fk_id_jobtraining = jt.id_jobtraining',
			    array()
			)
			->join(
			    array( 'apb' => $dbActionPlanBarrier ),
			    'apb.id_action_barrier = apr.fk_id_action_barrier',
			    array()
			)
			->join(
			    array( 'apg' => $dbActionpPlanHasGroup ),
			    'apg.fk_id_action_plan = apb.fk_id_action_plan',
			    array()
			)
			->where( 'apg.fk_id_action_plan_group = ?', $case )
			->where( 'apb.fk_id_barrier_intervention = ?', $intervention )
			->group( array( 'id_jobtraining' ) );
	
	return $dbActionPlanReferences->fetchAll( $selectJobTraining );
    }
    
    /**
     *
     * @param int $case
     * @param int $class
     * @return Zend_Db_Table_Rowset
     */
    public function listClientClass( $case, $class )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$selectClient = $mapperClient->selectClient();
	
	$dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	$dbActionPlanHasGroup = App_Model_DbTable_Factory::get( 'Action_Plan_has_Group' );
	$dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	
	$selectClient->join(
			array( 'ap1' => $dbActionPlan ),
			'ap1.fk_id_perdata = c.id_perdata',
			array( 'id_action_plan' )
		    )
		    ->join(
			array( 'apg' => $dbActionPlanHasGroup ),
			'apg.fk_id_action_plan = ap1.id_action_plan',
			array()
		    )
		     ->joinLeft(
			array( 'apr' => $dbActionPlanReferences ),
			'apr.fk_id_action_plan = ap1.id_action_plan
			 AND apr.fk_id_fefpstudentclass = ' . $class,
			array( 'id_references' )
		    )
		    ->where( 'apg.fk_id_action_plan_group = ?', $case )
		    ->where( 'ap1.active != ?', 2 )
		    ->where( 'ap1.type = ?', 'G' )
		    ->group( array( 'ap1.id_action_plan' ) );
	
	return $dbActionPlanHasGroup->fetchAll( $selectClient );
    }
    
    /**
     *
     * @param int $case
     * @param int $jobTraining
     * @return Zend_Db_Table_Rowset
     */
    public function listClientJobTraining( $case, $jobTraining )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$selectClient = $mapperClient->selectClient();
	
	$dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	$dbActionPlanHasGroup = App_Model_DbTable_Factory::get( 'Action_Plan_has_Group' );
	$dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	
	$selectClient->join(
			array( 'ap1' => $dbActionPlan ),
			'ap1.fk_id_perdata = c.id_perdata',
			array( 'id_action_plan' )
		    )
		    ->join(
			array( 'apg' => $dbActionPlanHasGroup ),
			'apg.fk_id_action_plan = ap1.id_action_plan',
			array()
		    )
		     ->joinLeft(
			array( 'apr' => $dbActionPlanReferences ),
			'apr.fk_id_action_plan = ap1.id_action_plan
			 AND apr.fk_id_jobtraining = ' . $jobTraining,
			array( 'id_references' )
		    )
		    ->where( 'apg.fk_id_action_plan_group = ?', $case )
		    ->where( 'ap1.active != ?', 2 )
		    ->where( 'ap1.type = ?', 'G' )
		    ->group( array( 'ap1.id_action_plan' ) );
	
	return $dbActionPlanHasGroup->fetchAll( $selectClient );
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
		'_validateClients',
		'_validateBarriersStatus'
	    );
	    
	    $validGeneral = ( 1 == $this->_data->status );
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
	$barriers = $this->listBarriers( $this->_data->id_action_plan_group );
	
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
    protected function _validateClients()
    {
	$clients = $this->listClientGroup( $this->_data->id_action_plan_group );
	
	$return = array(
	   'msg'    => 'IHA KLIENTE IHA KAZU GRUPU ?',
	   'valid'  => true
	);
	
	if ( $clients->count() < 1 )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @return array
     */
    protected function _validateBarriersStatus()
    {
	$dbActionPlanGroup = App_Model_DbTable_Factory::get( 'Action_Plan_has_Group' );
	$dbActionPlanBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	
	$select = $dbActionPlanBarrier->select()
				      ->from( array( 'apb' => $dbActionPlanBarrier ) )
				      ->setIntegrityCheck( false )
				      ->join(
					    array( 'apg' => $dbActionPlanGroup ),
					    'apg.fk_id_action_plan = apb.fk_id_action_plan',
					    array()
				      )
				      ->where( 'apg.fk_id_action_plan_group = ?', $this->_data->id_action_plan_group )
				      ->where( 'apb.status = ?', Client_Model_Mapper_Case::BARRIER_PENDING );
	
	$rows = $dbActionPlanBarrier->fetchAll( $select );
	
	$return = array(
	   'msg'    => 'BARRERIA STATUS HOTU ONA KA SEIDAUK ?',
	   'valid'  => true
	);
	
	if ( $rows->count() > 0 )
	    $return['valid'] = false;
	
	return $return;
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
	    $case->status = 0;
	    $case->date_finish = Zend_Date::now()->toString( 'yyyy-MM-dd' );
	    $case->save();
	    
	    $cases = $this->listCasesInGroup( $this->_data['id'] );
	    $dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
	    
	    // Finish all the single cases related to the group case
	    foreach ( $cases as $case ) {
		
		if ( $case->active != 1 )
		    continue;
		
		$where = array( 'id_action_plan = ?' => $case->id_action_plan );
		$update = array(
		    'active'	=> 0,
		    'date_end'	=> Zend_Date::now()->toString( 'yyyy-MM-dd' )
		);
		
		$dbActionPlan->update( $update, $where );
	    }
	    
	    // Save the auditing
	    $history = 'REMATA KAZU GRUPU NUMERU: %s';
	    $history = sprintf( $history, $this->_data['id'] );
	    $this->_sysAudit( $history  );
	    
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
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $form = Client_Form_CaseGroup::ID, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
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
}