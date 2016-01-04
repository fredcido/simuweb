<?php

class StudentClass_Model_Mapper_StudentClass extends App_Model_Abstract
{    
    const ENROLLED = 'E';
    
    const GRADUATED = 'G';
    
    const COMPLETED = 'C';
    
    const DROPPED_OUT = 'D';
    
    const NO_MANDATORY = 'N';
    
    /**
     * 
     * @var Model_DbTable_FEFPStudentClass
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEFPStudentClass();

	return $this->_dbTable;
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveInformation()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    //if ( !$this->_validateClass() ) 
		//return false;
	    
	    $date = new Zend_Date();
	    
	    $this->_data['start_date'] = $date->set( $this->_data['start_date'] )->toString( 'yyyy-MM-dd' );
	    $this->_data['schedule_finish_date'] = $date->set( $this->_data['schedule_finish_date'] )->toString( 'yyyy-MM-dd' );
	    
	    if ( !empty( $this->_data['real_finish_date'] ) )
		$this->_data['real_finish_date'] = $date->set( $this->_data['real_finish_date'] )->toString( 'yyyy-MM-dd' );
	    
	    if ( !empty( $this->_data['student_payment'] ) )
		$this->_data['student_payment'] = Zend_Locale_Format::getFloat( $this->_data['student_payment'], array( 'locale' => 'en_US' ) );
	    
	    if ( !empty( $this->_data['subsidy'] ) )
		$this->_data['subsidy'] = Zend_Locale_Format::getFloat( $this->_data['subsidy'], array( 'locale' => 'en_US' ) );
	    
	    if ( empty( $this->_data['id_fefpstudentclass'] ) ) {
		
		$history = 'REJISTRU  Klasse Formasaun - La Iha Proposta: %s';
		$this->_data['active'] = 1;
		
		$filters = array(
		    'fk_id_fefpeduinstitution'	=> $this->_data['fk_id_fefpeduinstitution'],
		    'fk_id_perscholarity'	=> $this->_data['fk_id_perscholarity'],
		    'fk_id_dec'			=> $this->_data['fk_id_dec'],
		    'start_date'		=> $this->_data['start_date'],
		    'schedule_finish_date'	=> $this->_data['schedule_finish_date'],
		    'active'			=> 1
		);
		
		$classIdentic = $this->_dbTable->fetchAll( $filters, array( 'id_fefpstudentclass DESC' ) )->current();
		
		if ( !empty( $classIdentic ) ) {
		    
		    $start = 1;
		    if ( preg_match( '/([0-9]+)$/i', $classIdentic->class_name, $match ) )
			$start = ( (int)$match[1] + 1 );
		    
		    $this->_data['class_name'] .= ' - ' . $start;
		}
		
	    } else {
		
		unset( 
		    $this->_data['fk_id_fefpeduinstitution'],
		    $this->_data['fk_id_dec']
		);
		
		if ( empty( $this->_data['fk_id_perscholarity'] ) )
		    unset( $this->_data['fk_id_perscholarity'] );
		
		$history = 'ATUALIZA  Klasse Formasaun - La Iha Proposta: %s';
	    }
	    
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
     * @return boolean 
     */
    protected function _validateClass()
    {
	if ( !empty( $this->_data['id_fefpstudentclass'] ) )
	    return true;
	
	$filters = array(
	    'fk_id_fefpeduinstitution'	=> $this->_data['fk_id_fefpeduinstitution'],
	    'fk_id_perscholarity'	=> $this->_data['fk_id_perscholarity'],
	    'fk_id_dec'			=> $this->_data['fk_id_dec'],
	    'active'			=> 1
	);
	
	$classes = $this->listByFilters( $filters );
	
	if ( $classes->count() > 0 ) {
	    
	    $this->_message->addMessage( 'Erro: Klase formasaun iha tiha ona.', App_Message::ERROR );
	    $this->addFieldError( 'fk_id_fefpeduinstitution' )->addFieldError( 'fk_id_perscholarity' );
	    return false;
	}
	
	return true;
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveCourse()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    // Check if the course is already saved
	    $row = $this->_checkCourse( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'ERRO, KURSU IDA NEE IHA REJISTU TIHA ONA.', App_Message::ERROR );
		return false;
	    }
	    
	    $dbClassScholarity = App_Model_DbTable_Factory::get( 'FEFPStudentClass_has_PerScholarity' );
	    
	    $id = parent::_simpleSave( $dbClassScholarity, false );
	    
	    // Save the auditing
	    $history = 'REJISTRU KURSU: %s, BA KLASE; %s';
	    $history = sprintf( $history, $this->_data['fk_id_perscholarity'], $this->_data['fk_id_fefpstudentclass'] );
	    $this->_sysAudit( $history, StudentClass_Form_RegisterCourse::ID  );
	    
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
    public function addList()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbStudentClassCandidates = App_Model_DbTable_Factory::get( 'StudentClass_Candidates' );
	    $clients = $dbStudentClassCandidates->fetchAll( array( 'fk_id_fefpstudentclass = ?' => $this->_data['fk_id_fefpstudentclass'] ) );
	    
	    $clientsList = array();
	    foreach ( $clients as $client )
		$clientsList[] = $client->fk_id_perdata;
	    
	    // Get just the new clients to the list
	    $clients = array_diff( $this->_data['clients'], $clientsList );
	    
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    
	    // Insert all the new clients in the list
	    foreach ( $clients as $client ) {
		
		// Add the client to the list candidates
		$row = $dbStudentClassCandidates->createRow();
		$row->fk_id_fefpstudentclass = $this->_data['fk_id_fefpstudentclass'];
		$row->fk_id_perdata = $client;
		$row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$row->shortlisted = 0;
		$row->source = $this->_data['source'];
		$row->save();
		
		// Save history to client
		$rowHistory = $dbPersonHistory->createRow();
		$rowHistory->fk_id_perdata = $client;
		$rowHistory->fk_id_student_class = $this->_data['fk_id_fefpstudentclass'];
		$rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
		$rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
		$rowHistory->action = sprintf( 'KLIENTE SELECIONADO BA LISTA KANDIDATU TURMA TREINAMENTU NUMERO: %s ', $this->_data['fk_id_fefpstudentclass'] );
		$rowHistory->description = 'KLIENTE SELECIONADO BA LISTA KANDIDATU TURMA TREINAMENTU NUMERO';
		$rowHistory->save();
		
		// Save the auditing
		$history = sprintf( 'KLIENTE BA LISTA KANDIDATU: %s - BA TURMA TREINAMENTU NUMERU: %s ', $client, $this->_data['fk_id_fefpstudentclass'] );
		$this->_sysAudit( $history );
	    }
	    
	    $dbAdapter->commit();
	    
	    return $this->_data['fk_id_fefpstudentclass'];
	    
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
    public function saveShortlist()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbShortlist = App_Model_DbTable_Factory::get( 'ShortlistStudentClass' );
	    $clients = $dbShortlist->fetchAll( array( 'fk_id_fefpstudentclass = ?' => $this->_data['id_fefpstudentclass'] ) );
	    
	    $clientsShortlist = array();
	    foreach ( $clients as $client )
		$clientsShortlist[] = $client->fk_id_perdata;
	    
	    // Get just the new clients to the shortlist
	    $clients = array_diff( $this->_data['clients'], $clientsShortlist );
	    
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    $dbStudentClassCandidates = App_Model_DbTable_Factory::get( 'StudentClass_Candidates' );
	    $dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	    
	    // Search the user who must receive notes when an user is refered to shortlist
	    $noteTypeMapper = new Admin_Model_Mapper_NoteType();
	    $users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::CLASS_SHORTLIST );
	    
	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	    $noteMapper = new Default_Model_Mapper_Note();
	    
	    // Insert all the new clients in the shortlist
	    foreach ( $clients as $client ) {
		
		// Add the client to the shortlist
		$row = $dbShortlist->createRow();
		$row->fk_id_fefpstudentclass = $this->_data['id_fefpstudentclass'];
		$row->fk_id_perdata = $client;
		$row->selected = 0;
		$row->save();
		
		// Save history to client
		$rowHistory = $dbPersonHistory->createRow();
		$rowHistory->fk_id_perdata = $client;
		$rowHistory->fk_id_student_class = $this->_data['id_fefpstudentclass'];
		$rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
		$rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
		$rowHistory->action = sprintf( 'KLIENTE HALO REJISTU LISTA BADAK BA KLASE NUMERO:%s ', $this->_data['id_fefpstudentclass'] );
		$rowHistory->description = 'KLIENTE HALO REJISTU LISTA BADAK BA KLASE';
		$rowHistory->save();
		
		// Set the list as shortlisted already to avoid to be shortlisted again
		$update = array( 'shortlisted' => 1 );
		$whereUpdate = array(
		    'fk_id_perdata = ?'	    =>  $client,
		    'fk_id_fefpstudentclass = ?'  =>	$this->_data['id_fefpstudentclass']
		);
		
		$dbStudentClassCandidates->update( $update, $whereUpdate );
		
		// Save the auditing
		$history = sprintf( 'SHORTED LIST KLIENTE: %s - BA FORMASAUN PROFISIONAL NUMERU: %s ', $client, $this->_data['id_fefpstudentclass'] );
		$this->_sysAudit( $history );
		
		// Search if the class was referencied by some barrier
		$whereReference = array(
		    'fk_id_fefpstudentclass = ?'  => $this->_data['id_fefpstudentclass'],
		    'fk_id_perdata = ?'		  => $client
		);
		
		$reference = $dbActionPlanReferences->fetchRow( $whereReference );
		if ( !empty( $reference ) ) {
		    
		    $usersNotify = $users;
		    $usersNotify[] = $reference->fk_id_sysuser;
		    
		    $dataNoteModel = array(
			'client' => $client,
			'class'   => $this->_data['id_fefpstudentclass'],
			'case'	  => $reference->fk_id_action_plan,
			'user'	  => $reference->fk_id_sysuser
		    );
		    
		    $dataNote = array(
			'title'   => 'KLIENTE REFERE BA SHORTLIST FORMASAUN PROFISIONAL',
			'level'   => 1,
			'message' => $noteModelMapper->geClassShortlist( $dataNoteModel ),
			'users'   => $usersNotify
		    );
		    
		    $noteMapper->setData( $dataNote )->saveNote();
		}
	    }
	    
	    $dbAdapter->commit();
	    
	    return $this->_data['id_fefpstudentclass'];
	    
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
    public function deleteShortlist()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbShortlist = App_Model_DbTable_Factory::get( 'ShortlistStudentClass' );
	    $where = array(
		'fk_id_fefpstudentclass = ?'	=>  $this->_data['id_studentclass'],
		'fk_id_perdata = ?'		=>  $this->_data['id'],
	    );
	    
	    // Delete cliente from Shortlist
	    $dbShortlist->delete( $where );
	    
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );

	    // Save history to client
	    $rowHistory = $dbPersonHistory->createRow();
	    $rowHistory->fk_id_perdata = $this->_data['id'];
	    $rowHistory->fk_id_student_class = $this->_data['id_studentclass'];
	    $rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
	    $rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
	    $rowHistory->action = sprintf( 'HAMOS KLIENT HUSI LISTA BADAK BA KLASE NUMERO:%s ', $this->_data['id_studentclass'] );
	    $rowHistory->description = 'HAMOS KLIENT HUSI LISTA BADAK BA KLASE';
	    $rowHistory->save();

	    // Save the auditing
	    $history = sprintf( 'HAMOS KLIENTE: %s - HUSI SHORTLIST BA FORMASAUN PROFISIONAL NUMERU: %s ', $this->_data['id'], $this->_data['id_studentclass'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return $this->_data['id_studentclass'];
	    
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
    public function saveClass()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbStudentClass = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	    $clientsClass = $dbStudentClass->fetchAll( array( 'fk_id_fefpstudentclass = ?' => $this->_data['id_fefpstudentclass'] ) );
	    
	    $clientsShortlist = array();
	    foreach ( $clientsClass as $client )
		$clientsShortlist[] = $client->fk_id_perdata;
	    
	    // Get just the new clients to the shortlist
	    $clients = array_diff( $this->_data['clients'], $clientsShortlist );
	    
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    $dbShortlist = App_Model_DbTable_Factory::get( 'ShortlistStudentClass' );
	    $dbClassCandidates = App_Model_DbTable_Factory::get( 'StudentClass_Candidates' );
	    
	    $class = $this->fetchRow( $this->_data['id_fefpstudentclass'] );
	    // Check if the total of participants exceeds the total defined in the information step
	    if ( ( $clientsClass->count() + count( $clients ) ) > $class['num_total_student'] ) {
		
		$message = sprintf( 'Erro: Total partisipante la bele liu: %s. Iha %s tiha ona, bele aumenta: %s', 
				    $class['num_total_student'], 
				    $clientsClass->count(),
				    ( $class['num_total_student'] - $clientsClass->count() )
			    );
		
		$this->_message->addMessage( $message, App_Message::ERROR );
		return false;
	    }
	    
	    // List the Student Class Competencies
	    $competencies = $this->listCompetencyClass( $this->_data['id_fefpstudentclass'] );
	
	    $dbStudentCompetency = App_Model_DbTable_Factory::get( 'StudentClass_Competency' );
	    $dbStudentCompetency = App_Model_DbTable_Factory::get( 'StudentClass_Competency' );
	    
	    $noteMapper = new Default_Model_Mapper_Note();
	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	    
	    // Insert all the new clients in the shortlist
	    foreach ( $clients as $client ) {
		
		// Add the client to the shortlist
		$row = $dbStudentClass->createRow();
		$row->fk_id_fefpstudentclass = $this->_data['id_fefpstudentclass'];
		$row->fk_id_perdata = $client;
		$row->concluded = 0;
		$row->graduated = 0;
		$row->status = self::ENROLLED;
		$row->save();
		
		// Save history to client
		$rowHistory = $dbPersonHistory->createRow();
		$rowHistory->fk_id_perdata = $client;
		$rowHistory->fk_id_student_class = $this->_data['id_fefpstudentclass'];
		$rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
		$rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
		$rowHistory->action = sprintf( 'KLIENTE SELECIONADO BA KLASE FORMASAUN NUMERU:%s ', $this->_data['id_fefpstudentclass'] );
		$rowHistory->description = 'KLIENTE HALO REJISTU LKLIENTE SELECIONADO BA KLASE FORMASAUN';
		$rowHistory->save();
		
		// Set the list as shortlisted already to avoid to be shortlisted again
		$update = array( 'selected' => 1 );
		$whereUpdate = array(
		    'fk_id_perdata  = ?'	  =>  $client,
		    'fk_id_fefpstudentclass = ?'  =>  $this->_data['id_fefpstudentclass']
		);
		
		$dbShortlist->update( $update, $whereUpdate );
		
		// Save the auditing
		$history = sprintf( 'KLIENTE %s SELECIONADO BA KLASE FORMASAUN NUMERU: %s ', $client, $this->_data['id_fefpstudentclass'] );
		$this->_sysAudit( $history );
		
		// Insert the competencies to the student as enrolled
		foreach ( $competencies as $competency ) {
		    
		    $rowCompetency = $dbStudentCompetency->createRow();
		    $rowCompetency->fk_id_competency = $competency->id_competency;
		    $rowCompetency->fk_id_perdata = $client;
		    $rowCompetency->fk_id_fefpstudentclass = $this->_data['id_fefpstudentclass'];
		    $rowCompetency->status = self::ENROLLED;
		    $rowCompetency->save();
		}
		
		$whereCandidates = array(
		   'fk_id_perdata = ?'		 => $client,
		    'fk_id_fefpstudentclass = ?' => $this->_data['id_fefpstudentclass']
		);
		
		$referer = $dbClassCandidates->fetchRow( $whereCandidates );
		
		if ( empty( $referer->fk_id_sysuser ) )
		    continue;
		
		$dataNote = array(
		    'title'   => 'KLIENTE SELECIONADO BA KLASE FORMASAUN',
		    'level'   => 1,
		    'message' => $noteModelMapper->getClassCandidate( $client, $this->_data['id_fefpstudentclass'] ),
		    'users'   => array( $referer->fk_id_sysuser )
		);
		
		$noteMapper->setData( $dataNote )->saveNote();
	    }
	    
	    $dbAdapter->commit();
	    
	    return $this->_data['id_fefpstudentclass'];
	    
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
    public function listCompetencyClass( $id, $student = false )
    {
	$dbCompetency = App_Model_DbTable_Factory::get( 'Competency' );
	$dbCompetencyScholarity = App_Model_DbTable_Factory::get( 'Competency_has_PerScholarity' );
	$dbStudentClass = App_Model_DbTable_Factory::get( 'FEFPStudentClass' );
	
	$select = $dbCompetency->select()
				->from( array( 'c' => $dbCompetency ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'cs' => $dbCompetencyScholarity ),
				    'cs.fk_id_competency = c.id_competency',
				    array()
				)
				->join(
				    array( 'sc' => $dbStudentClass ),
				    'sc.fk_id_perscholarity = cs.fk_id_perscholarity',
				    array()
				)
				->where( 'sc.id_fefpstudentclass = ?', $id )
				->order( array( 'external_code', 'name' ) )
				->group( array( 'external_code' ) );
	
	if ( !empty( $student ) ) {
	    
	    $dbStudentClassCompetency = App_Model_DbTable_Factory::get( 'StudentClass_Competency' );
	    $select->joinLeft(
			array( 'scc' => $dbStudentClassCompetency ),
			'scc.fk_id_fefpstudentclass = sc.id_fefpstudentclass
			 AND scc.fk_id_competency = c.id_competency
			 AND scc.fk_id_perdata = ' . $student,
			array( 'status' ) 
		    );
	}
	
	return $dbCompetency->fetchAll( $select );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveClient()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	    
	    $date = new Zend_Date();
	    foreach ( $this->_data['status'] as $client => $status ) {
		
		$dataUpdate = array( 'status' => $status );
		$where = array(
		    'fk_id_perdata = ?'		  => $client,
		    'fk_id_fefpstudentclass = ?'  => $this->_data['fk_id_fefpstudentclass']
		);
		
		if ( !empty( $this->_data['date_drop'][$client] ) )
		    $dataUpdate['date_drop_out'] = $date->setDate( $this->_data['date_drop'][$client], 'dd/MM/yyyy' )->toString( 'yyyy-MM-dd' );
		else
		    $dataUpdate['date_drop_out'] = null;
		    
		$dbStudentClassPerData->update( $dataUpdate, $where );
	    }
	    
	    // Save the auditing
	    $history = 'ATUALIZA ALUNO SIRA: BA KLASE %s';
	    $history = sprintf( $history, $this->_data['fk_id_fefpstudentclass'] );
	    $this->_sysAudit( $history, StudentClass_Form_RegisterClient::ID  );
	    
	    $dbAdapter->commit();
	    
	    return $this->_data['fk_id_fefpstudentclass'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     *
     * @return boolean 
     */
    public function saveCompetencies()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	  
	    $dbStudentClassCompetency = App_Model_DbTable_Factory::get( 'StudentClass_Competency' );
	    
	    $statusGeneral = array(
		self::GRADUATED	    => true,
		self::DROPPED_OUT   => true,
		self::COMPLETED	    => false
	    );
	    
	    foreach ( $this->_data['status'] as $idCompetency => $status ) {
		
		$where = array(
		    'fk_id_competency = ?'	 => $idCompetency,
		    'fk_id_perdata = ?'		 => $this->_data['fk_id_perdata'],
		    'fk_id_fefpstudentclass = ?' => $this->_data['fk_id_fefpstudentclass']
		);
		
		$row = $dbStudentClassCompetency->fetchRow( $where );
		
		// If there is no register
		if ( empty( $row ) ) {
		    
		    $row = $dbStudentClassCompetency->createRow( $this->_data );
		    $row->fk_id_competency = $idCompetency;
		}
		
		$statusGeneral[self::GRADUATED] = $statusGeneral[self::GRADUATED] && ( in_array( $status, array( self::GRADUATED, self::NO_MANDATORY ) ) );
		$statusGeneral[self::DROPPED_OUT] = $statusGeneral[self::DROPPED_OUT] && $status == self::DROPPED_OUT;
		$statusGeneral[self::COMPLETED] = $statusGeneral[self::COMPLETED] || $status == self::COMPLETED || $status == self::GRADUATED;
		
		$row->status = $status;
		$row->save();
	    }
	    
	    // Save the auditing
	    $history = 'ATUALIZA KOMPETENSIA SIRA BA ALUNO: %s BA KLASE %s';
	    $history = sprintf( $history, $this->_data['fk_id_perdata'], $this->_data['fk_id_fefpstudentclass'] );
	    $this->_sysAudit( $history, StudentClass_Form_RegisterClient::ID  );
	    
	    $statusClient = false;
	    foreach ( $statusGeneral as $status => $flag ) {
		if ( $flag ) {
		    $statusClient = $status;
		    break;
		}
	    }
	    
	    // If the client status can be updated	
	    if ( $statusClient ) {
		
		$dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );

		$dataUpdate = array( 'status' => $status );
		$where = array(
		    'fk_id_perdata = ?'		  => $this->_data['fk_id_perdata'],
		    'fk_id_fefpstudentclass = ?'  => $this->_data['fk_id_fefpstudentclass']
		);
		
		if ( !empty( $this->_data['date_drop_out'] ) ) {
		   
		    $date = new Zend_Date( $this->_data['date_drop_out'] );
		    $dataUpdate['date_drop_out'] = $date->toString( 'yyyy-MM-dd' );
		} else
		    $dataUpdate['date_drop_out'] = null;

		$dbStudentClassPerData->update( $dataUpdate, $where );
	    }
	    
	    $dbAdapter->commit();
	    return $this->_data['fk_id_fefpstudentclass'];
	    
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
    public function checkFinishClass( $id )
    {
	$return = array(
	    'valid'  => false,
	    'itens'  => array()
	);
	
	try {
	    
	    $this->_data = $this->fetchRow( $id );
	    
	    $validators = array(
		'_validateFinishDate',
		'_validateCourse',
		'_validateStudents',
		'_validateAllStudents',
		'_validateStudentsGender',
		'_validateStudentsStatus',
		'_validateStudentsCompetencyStatus'
	    );
	    
	    $validGeneral = ( 0 != $this->_data->active );
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
    protected function _validateFinishDate()
    {
	$date = new Zend_Date();
	$finishCourse = new Zend_Date( $this->_data->schedule_finish_date );
	
	$return = array(
	   'msg'    => 'LORON PLANU RAMATA KLASE: ' . $finishCourse->toString( 'dd/MM/yyyy' ),
	   'valid'  => true
	);
	
	if ( $finishCourse->isLater( $date ) )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @return array
     */
    protected function _validateCourse()
    {
	/*
	$dbStudentClassCourse = App_Model_DbTable_Factory::get( 'FEFPStudentClass_has_PerScholarity' );
	$courses = $dbStudentClassCourse->fetchAll( array( 'fk_id_fefpstudentclass = ?' => $this->_data->id_fefpstudentclass ) );
	 */
	
	$return = array(
	   'msg'    => 'IHA REJISTU KURSU ?',
	   'valid'  => true
	);

	if ( empty( $this->_data->fk_id_perscholarity ) )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @return array
     */
    protected function _validateStudents()
    {
	$dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	$students = $dbStudentClassPerData->fetchAll( array( 'fk_id_fefpstudentclass = ?' => $this->_data->id_fefpstudentclass ) );
	
	$return = array(
	   'msg'    => 'IHA REJISTU ALUNO BA KLASE ?',
	   'valid'  => true
	);

	if ( $students->count() < 1 )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @return array
     */
    protected function _validateAllStudents()
    {
	$return = array(
	   'msg'    => 'IHA REJISTU ALUNO HOTU-HOTU BA KLASE ?',
	   'valid'  => true
	);
	
	$numClients = (int)$this->_data->num_total_student;
	
	$dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	$students = $dbStudentClassPerData->fetchAll( array( 'fk_id_fefpstudentclass = ?' => $this->_data->id_fefpstudentclass ) );

	if ( $students->count() < 1 || $numClients != $students->count() )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @return array
     */
    protected function _validateStudentsStatus()
    {
	$return = array(
	   'msg'    => 'ALUNO SIRA IHA DADUS ATUALIZADO KONA BA RAMATA KURSO KA GRADUASAUN ?',
	   'valid'  => true
	);
	
	$dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	$students = $dbStudentClassPerData->fetchAll( array( 'fk_id_fefpstudentclass = ?' => $this->_data->id_fefpstudentclass ) );

	if ( $students->count() < 1 ) {
	    
	    $return['valid'] = false;
	    return $return;
	    
	} else {
	
	    $where = array( 
		'fk_id_fefpstudentclass = ?' => $this->_data->id_fefpstudentclass,
		'status = ?'		 => self::ENROLLED
	    );

	    $students = $dbStudentClassPerData->fetchAll( $where );

	    if ( $students->count() > 0 )
		$return['valid'] = false;
	
	}
	
	return $return;
    }
    
    /**
     *
     * @return boolean 
     */
    protected function _validateStudentsGender()
    {
	$return = array(
	   'msg'    => 'IHA REJISTU MANE NO FETO HOTU-HOTU ?',
	   'valid'  => true
	);
	
	$students = $this->listClientClass( $this->_data->id_fefpstudentclass );
	$genders = array(
	    'MANE' => 0,
	    'FETO' => 0
	);
	
	foreach ( $students as $student )
	    $genders[$student->gender]++;

	if ( $genders['MANE'] != $this->_data->num_men_student || $genders['FETO'] != $this->_data->num_women_student )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @return boolean 
     */
    protected function _validateStudentsCompetencyStatus()
    {
	$competency = $this->listCompetencyClass( $this->_data->id_fefpstudentclass );
	if ( $competency->count() > 0 ) {
	    
	    $return = array(
		'msg'    => 'KOMPETENSIA SIRA IHA DADUS ATUALIZADO KONA BA RAMATA KURSO KA GRADUASAUN ?',
		'valid'  => true
	    );
	
	    $dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	    $students = $dbStudentClassPerData->fetchAll( array( 'fk_id_fefpstudentclass = ?' => $this->_data->id_fefpstudentclass ) );

	    if ( $students->count() < 1 ) {

		$return['valid'] = false;
		return $return;

	    } else {

		$where = array( 
		    'fk_id_fefpstudentclass = ?' => $this->_data->id_fefpstudentclass,
		    'status = ?'		 => self::ENROLLED
		);

		$dbStudentClassCompetency = App_Model_DbTable_Factory::get( 'StudentClass_Competency' );
		$competencies = $dbStudentClassCompetency->fetchAll( $where );

		if ( $competencies->count() > 0 )
		    $return['valid'] = false;
	    }

	    return $return;
	}
	
	return false;
    }
    
    /**
     * 
     * @return int|bool
     */
    public function finishClass()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	    $dbActionPlanBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	    $dbClassCandidates = App_Model_DbTable_Factory::get( 'StudentClass_Candidates' );
	    
	    $dataValid = $this->_data;
	    $valid = $this->checkFinishClass( $this->_data['id'] );
	    
	    if ( empty( $valid['valid'] ) ) {
		
		$this->_message->addMessage( 'Erro: La bele remata klase! Haree kriterio sira.', App_Message::ERROR );
		return false;
	    }
	    
	    $this->_data = $dataValid;
	    
	    $class = $this->fetchRow( $this->_data['id'] );
	    $class->active = 0;
	    $class->real_finish_date = Zend_Date::now()->toString( 'yyyy-MM-dd' );
	    $class->save();
	    
	    // Search the Students Graduated
	    $dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	    $whereStudentClass = array(
		'fk_id_fefpstudentclass = ?' => $this->_data['id'],
		'status = ?'		     => self::GRADUATED
	    );
	    
	    $students = $dbStudentClassPerData->fetchAll( $whereStudentClass );
	    
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    $dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity_has_PerTypeScholarity' );
            
            // Search the class course
            $mapperScholarity = new Register_Model_Mapper_PerScholarity();
            $course = $mapperScholarity->fetchRow( $class->fk_id_perscholarity );
	    
	    $noteMapper = new Default_Model_Mapper_Note();
	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	    
	    foreach ( $students as $student ) {
		
		// Save history to client
		$rowHistory = $dbPersonHistory->createRow();
		$rowHistory->fk_id_perdata = $student->fk_id_perdata;
		$rowHistory->fk_id_student_class = $this->_data['id'];
		$rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
		$rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
		$rowHistory->action = sprintf( 'REMATA KLASE FORMASAUN: %s ', $this->_data['id'] );
		$rowHistory->description = sprintf( 'REMATA KLASE FORMASAUN: %s ', $this->_data['id'] );
		$rowHistory->save();
		
		$whereScholarity = array(
		    'fk_id_perdata = ?'		    => $student->fk_id_perdata,
		    'fk_id_perscholarity = ?'	    => $class->fk_id_perscholarity,
		    'fk_id_fefpeduinstitution = ?'  => $class->fk_id_fefpeduinstitution
		);
		
		$hasScholarity = $dbPerScholarity->fetchRow( $whereScholarity );
		
		// If the client already has the scholarity
		if ( !empty( $hasScholarity ) )
		    continue;
		
		// Insert the scholarities to the student
                $rowScholarity = $dbPerScholarity->createRow();
                $rowScholarity->fk_id_perdata = $student->fk_id_perdata;
                $rowScholarity->fk_id_perscholarity = $class->fk_id_perscholarity;
                $rowScholarity->fk_id_pertypescholarity = $course->fk_id_pertypescholarity;
                $rowScholarity->fk_id_fefpeduinstitution = $class->fk_id_fefpeduinstitution;
                $rowScholarity->start_date = $class->start_date;
                $rowScholarity->finish_date = $class->real_finish_date;
                $rowScholarity->save();
		
		// Search if the class was referencied by some barrier
		$whereReference = array(
		    'fk_id_fefpstudentclass = ?'  => $this->_data['id'],
		    'fk_id_perdata = ?'		  => $student->fk_id_perdata
		);
		
		$reference = $dbActionPlanReferences->fetchRow( $whereReference );
		if ( !empty( $reference ) ) {
		    
		    $barrier = $dbActionPlanBarrier->fetchRow( array( 'id_action_barrier = ?' => $reference->fk_id_action_barrier ) );
		    $barrier->status = Client_Model_Mapper_Case::BARRIER_COMPLETED;
		    $barrier->date_finish = Zend_Date::now()->toString( 'yyyy-MM-dd' );
		    $barrier->save();
		}
		
		$whereCandidates = array(
		    'fk_id_perdata = ?'		 => $student->fk_id_perdata,
		    'fk_id_fefpstudentclass = ?' => $this->_data['id']
		);
		
		$referer = $dbClassCandidates->fetchRow( $whereCandidates );
		
		if ( empty( $referer->fk_id_sysuser ) )
		    continue;
		
		$dataNote = array(
		    'title'   => 'KLIENTE GRADUADU IHA KLASE FORMASAUN',
		    'level'   => 1,
		    'message' => $noteModelMapper->getClassGraduated( $student->fk_id_perdata, $this->_data['id'] ),
		    'users'   => array( $referer->fk_id_sysuser )
		);
		
		$noteMapper->setData( $dataNote )->saveNote();
	    }
	    
	    // Save the auditing
	    $history = 'REMATA KLASE FORMASAUN NUMERU: %s';
	    $history = sprintf( $history, $this->_data['id'] );
	    $this->_sysAudit( $history, StudentClass_Form_RegisterFinish::ID  );
	    
	    // If the class has a remote ID, save the class to be sent to the INDMO application
	    if ( !empty( $course->remote_id ) ) {
		
		$dbStudentClassSent = App_Model_DbTable_Factory::get( 'StudentClass_Sent' );
		$row = $dbStudentClassSent->createRow();
		$row->fk_id_fefpstudentclass = $this->_data['id'];
		$row->save();
	    }
	    
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
    public function deleteCourse()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbClassScholarity = App_Model_DbTable_Factory::get( 'FEFPStudentClass_has_PerScholarity' );
	    
	    $where = array( $dbAdapter->quoteInto( 'id_relationship = ?', $this->_data['id'] ) );
	    
	    $classScholarity = $dbClassScholarity->fetchRow( $where );
	    $dbClassScholarity->delete( $where );
	    
	    $history = 'DELETA KURSU: %s BA KLASE: %s';
	    $history = sprintf( $history, $classScholarity->fk_id_perscholarity, $classScholarity->fk_id_fefpstudentclass );
	    $this->_sysAudit( $history, StudentClass_Form_RegisterCourse::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );

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
     * @param array $data
     * @return Zend_Db_Table_Row
     */
    protected function _checkCourse( $data )
    {
	$dbClassScholarity = App_Model_DbTable_Factory::get( 'FEFPStudentClass_has_PerScholarity' );
	
	$select = $dbClassScholarity->select()
				    ->from( array( 'cs' => $dbClassScholarity ) )
				    ->where( 'cs.fk_id_perscholarity = ?', $data['fk_id_perscholarity'] )
				    ->where( 'cs.fk_id_fefpstudentclass = ?', $data['fk_id_fefpstudentclass'] );
	
	return $dbClassScholarity->fetchRow( $select );
    }
    
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listCourse( $id )
    {
	$dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbClassScholarity = App_Model_DbTable_Factory::get( 'FEFPStudentClass_has_PerScholarity' );
	
	$select = $dbScholarity->select()
				->from( array( 's' => $dbScholarity ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'cs' => $dbClassScholarity ),
				    'cs.fk_id_perscholarity = s.id_perscholarity',
				    array( 'id_relationship' )
				)
				->where( 'cs.fk_id_fefpstudentclass = ?', $id )
				->order( array( 'scholarity' ) );
	
	return $dbScholarity->fetchAll( $select );
    }
    
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $form = StudentClass_Form_RegisterInformation::ID, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::FORMATION,
	    'fk_id_sysform'	    => $form,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
    
    /**
     *
     * @return Zend_Db_Select 
     */
    public function getSelectClass()
    {
	$dbStudentClass = App_Model_DbTable_Factory::get( 'FEFPStudentClass' );
	$dbEducationInstitute = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	
	$select = $dbStudentClass->select()
				 ->from( array( 'sc' => $dbStudentClass ) )
				 ->setIntegrityCheck( false )
				 ->join(
				    array( 'ei' => $dbEducationInstitute ),
				    'ei.id_fefpeduinstitution = sc.fk_id_fefpeduinstitution',
				    array( 
					'institution',
					'expired' => new Zend_Db_Expr( '( CASE WHEN sc.schedule_finish_date < CURDATE() THEN 1 ELSE 0 END )' )
				    )
				 )
				 ->join(
				    array( 's' => $dbPerScholarity ),
				    's.id_perscholarity = sc.fk_id_perscholarity',
				    array( 'external_code', 'scholarity' )
				 )
				 ->order( array( 'class_name' ) )
				 ->group( array( 'id_fefpstudentclass' ) );
	
	return $select;
    }
    
    /**
     *
     * @param array $filters
     * @return type 
     */
    public function listByFilters( array $filters = array() )
    {
	$dbStudentClass = App_Model_DbTable_Factory::get( 'FEFPStudentClass' );
	
	$select = $this->getSelectClass();
	
	if ( !empty( $filters['class_name'] ) )
	    $select->where( 'sc.class_name LIKE ?', '%' . $filters['class_name'] . '%' );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'sc.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	if ( !empty( $filters['fk_id_fefpeduinstitution'] ) )
	    $select->where( 'sc.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution'] );
	
	if ( !empty( $filters['fk_id_perscholarity'] ) )
	    $select->where( 'sc.fk_id_perscholarity = ?', $filters['fk_id_perscholarity'] );
	
	if ( !empty( $filters['not_id'] ) )
	    $select->where( 'sc.id_fefpstudentclass <> ?', $filters['not_id'] );
	
	if ( array_key_exists( 'active', $filters ) )
	    $select->where( 'sc.active = ?', (int)$filters['active'] );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['start_date'] ) )
	    $select->where( 'sc.start_date >= ?', $date->set( $filters['start_date'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['schedule_finish_date'] ) )
	    $select->where( 'sc.schedule_finish_date <= ?', $date->set( $filters['schedule_finish_date'] )->toString( 'yyyy-MM-dd' ) );
	
	return $dbStudentClass->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Select
     */
    protected function _selectMatchClient( $id )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$dbStudentClassCandidates = App_Model_DbTable_Factory::get( 'StudentClass_Candidates' );
	$select->joinLeft(
		    array( 'cc' => $dbStudentClassCandidates ),
		    'cc.fk_id_perdata = c.id_perdata AND cc.fk_id_fefpstudentclass = ' . $id,
		    array( 'list' => 'id_candidates' )
		);
	
	return $select;
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listClient( array $filters = array() )
    {
	$select = $this->_selectMatchClient( $filters['fk_id_fefpstudentclass'] );
	
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	
	// Dec
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'c.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	// Num District
	if ( !empty( $filters['num_district'] ) )
	    $select->where( 'c.num_district = ?', $filters['num_district'] );
	
	// Num SubDistrict
	if ( !empty( $filters['num_subdistrict'] ) )
	    $select->where( 'c.num_subdistrict = ?', $filters['num_subdistrict'] );
	
	// Num Service Code
	if ( !empty( $filters['num_servicecode'] ) )
	    $select->where( 'c.num_servicecode = ?', $filters['num_servicecode'] );
	
	// Num Year
	if ( !empty( $filters['num_year'] ) )
	    $select->where( 'c.num_year = ?', $filters['num_year'] );
	
	// Num Year
	if ( !empty( $filters['num_sequence'] ) )
	    $select->where( 'c.num_sequence = ?', $filters['num_sequence'] );
	
	if ( !empty( $filters['first_name'] ) )
	    $select->where( 'c.first_name LIKE ?', '%' . $filters['first_name'] . '%' );
	
	if ( !empty( $filters['last_name'] ) )
	    $select->where( 'c.last_name LIKE ?', '%' . $filters['last_name'] . '%' );
	
	$select ->where( 'c.active = ?', 1 )
		->group( 'id_perdata' )
		->order( array( 'date_registration' ) );
	
	return $dbPerData->fetchAll( $select );
    }
    
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listCandidate( $id )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$dbCandidates = App_Model_DbTable_Factory::get( 'StudentClass_Candidates' );
	$select->join(
		    array( 'lc' => $dbCandidates ),
		    'lc.fk_id_perdata = c.id_perdata',
		    array( 'shortlisted' )
		)
		->where( 'lc.fk_id_fefpstudentclass = ?', $id )
		->group( 'id_perdata' )
		->order( array( 'first_name', 'last_name' ) );
	
	return $dbCandidates->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listShortlist( $id )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$dbShortlist = App_Model_DbTable_Factory::get( 'ShortlistStudentClass' );
	$dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	
	$select->join(
		    array( 'sl' => $dbShortlist ),
		    'sl.fk_id_perdata = c.id_perdata',
		    array( 'selected' )
		)
		->joinLeft(
		    array( 'h' => $dbStudentClassPerData ),
		    'h.fk_id_perdata = c.id_perdata
		    AND h.fk_id_fefpstudentclass = sl.fk_id_fefpstudentclass',
		    array( 'student' => 'id_relationship' )
		)
		->where( 'sl.fk_id_fefpstudentclass = ?', $id )
		->group( 'id_perdata' )
		->order( array( 'first_name', 'last_name' ) );
	
	return $dbShortlist->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listClientClass( $id )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	$dbHandicapped = App_Model_DbTable_Factory::get( 'Handicapped' );
	
	$select->join(
		    array( 'sc' => $dbStudentClassPerData ),
		    'sc.fk_id_perdata = c.id_perdata',
		    array( 'status_class' => 'status', 'date_drop_out' )
		)
		->joinLeft(
		    array( 'hc' => $dbHandicapped ),
		    'sc.fk_id_perdata = hc.fk_id_perdata',
		    array( 'id_handicapped' )
		)
		->where( 'sc.fk_id_fefpstudentclass = ?', $id )
		->group( 'id_perdata' )
		->order( array( 'first_name', 'last_name' ) );
	
	return $dbStudentClassPerData->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detailStudentClass( $id )
    {
	$dbStudentClass = App_Model_DbTable_Factory::get( 'FEFPStudentClass' );
	$dbEduInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' ); 
	$dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' ); 
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' ); 
	$dbIsicTimor = App_Model_DbTable_Factory::get( 'ISICClassTimor' ); 
	
	$select = $dbStudentClass->select()
				 ->from( array( 'sc' => $dbStudentClass ) )
				 ->setIntegrityCheck( false )
				 ->join(
				    array( 'ei' => $dbEduInstitution ),
				    'ei.id_fefpeduinstitution = sc.fk_id_fefpeduinstitution',
				    array( 
					'institution',
					'num_register',
					'start_date_formated'		=> new Zend_Db_Expr( 'DATE_FORMAT( sc.start_date, "%d/%m/%Y" )' ),
					'schedule_finish_date_formated' => new Zend_Db_Expr( 'DATE_FORMAT( sc.schedule_finish_date, "%d/%m/%Y" )' ),
					'real_finish_date_formated'	=> new Zend_Db_Expr( 'DATE_FORMAT( sc.real_finish_date, "%d/%m/%Y" )' )
				    )
				 )
				 ->join(
				    array( 'ps' => $dbPerScholarity ),
				    'ps.id_perscholarity = sc.fk_id_perscholarity',
				    array( 'scholarity', 'external_code' )
				 )
				 ->join(
				    array( 'ce' => $dbDec ),
				    'ce.id_dec = sc.fk_id_dec',
				    array( 'name_dec' )
				 )
				 ->joinLeft(
				    array( 'ms' => $dbPerScholarity ),
				    'ms.id_perscholarity = sc.fk_minimal_scholarity',
				    array( 'minimal_scholarity' => 'scholarity', 'minimal_scholarity_code' => 'external_code' )
				 )
				 ->joinLeft(
				    array( 'is' => $dbIsicTimor ),
				    'is.id_isicclasstimor = sc.fk_id_sectorindustry',
				    array( 'name_classtimor' )
				 )
				 ->where( 'sc.id_fefpstudentclass = ?', $id );
	
	return $dbStudentClass->fetchRow( $select );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveCancel()
    {
	
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbStudentClass = App_Model_DbTable_Factory::get( 'FEFPStudentClass' );
	    
	    // Cancel vacancy
	    $where = $dbAdapter->quoteInto( 'id_fefpstudentclass = ?', $this->_data['fk_id_fefpstudentclass'] );
	    $dataUpdate = array(
		'active'		=>  2,
		'cancel_justification'	=>  $this->_data['cancel_justification']
	    );
	    
	    $dbStudentClass->update( $dataUpdate, $where );
	    
	    // Save auditing
	    $history = 'KANSELA KLASE FORMASAUN : %s - JUSTIFIKASAUN: %s';
	    
	    $history = sprintf( $history, $this->_data['fk_id_fefpstudentclass'], $this->_data['cancel_justification'] );
	    $this->_sysAudit( $history, StudentClass_Form_RegisterCancel::ID );
	    
	    $dbAdapter->commit();
	    return $this->_data['fk_id_fefpstudentclass'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
}