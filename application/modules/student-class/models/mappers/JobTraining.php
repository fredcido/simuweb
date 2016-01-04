<?php

class StudentClass_Model_Mapper_JobTraining extends App_Model_Abstract
{
    /**
     * 
     * @var Model_DbTable_JOBTraining
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_JOBTraining();

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
	    
	    $date = new Zend_Date();
	    
	    if ( !empty( $this->_data['date_start'] ) )
		$this->_data['date_start'] = $date->set( $this->_data['date_start'] )->toString( 'yyyy-MM-dd' );
	    
	    if ( !empty( $this->_data['date_finish'] ) )
		$this->_data['date_finish'] = $date->set( $this->_data['date_finish'] )->toString( 'yyyy-MM-dd' );
	    
	    if ( !empty( $this->_data['salary'] ) )
		$this->_data['salary'] = Zend_Locale_Format::getFloat( $this->_data['salary'], array( 'locale' => 'en_US' ) );
	    
	    if ( empty( $this->_data['id_jobtraining'] ) ) {
		
		$history = 'REJISTRU JOB TRAINING: %s';
		$this->_data['active'] = 1;
		
	    } else {
		
		unset( $this->_data['fk_id_dec'] );
		
		$history = 'ATUALIZA JOB TRAINING: %s';
		
		$where = array(
		    'fk_id_jobtraining = ?' => $this->_data['id_jobtraining'],
		    'status = ?' => 0
		);
		
		$dbTrainee = App_Model_DbTable_Factory::get( 'JOBTraining_Trainee' );
		$trainees = $dbTrainee->fetchAll( $where );
		
		foreach ( $trainees as $trainee ) {
		    
		    $trainee->date_start = $this->_data['date_start'];
		    $trainee->date_finish = $this->_data['date_finish'];
		    $trainee->duration = $this->_data['duration'];
		    $trainee->save();
		}
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
	    
	    $dbJobTrainingScholarity = App_Model_DbTable_Factory::get( 'JOBTraining_has_PerScholarity' );
	    
	    $id = parent::_simpleSave( $dbJobTrainingScholarity, false );
	    
	    // Save the auditing
	    $history = 'REJISTRU KURSU: %s, BA JOB TRAINING; %s';
	    $history = sprintf( $history, $this->_data['fk_id_perscholarity'], $this->_data['fk_id_jobtraining'] );
	    $this->_sysAudit( $history, StudentClass_Form_JobTrainingCourse::ID  );
	    
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
    public function saveEditTrainee()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbTrainee = App_Model_DbTable_Factory::get( 'JOBTraining_Trainee' );
	    
	    $date = new Zend_Date();
	    
	    if ( !empty( $this->_data['date_start'] ) )
		$this->_data['date_start'] = $date->set( $this->_data['date_start'] )->toString( 'yyyy-MM-dd' );
	    
	    if ( !empty( $this->_data['date_finish'] ) )
		$this->_data['date_finish'] = $date->set( $this->_data['date_finish'] )->toString( 'yyyy-MM-dd' );
	    
	    $history = 'ATUALIZA JOB TRAINING - TRAINEE: %s - IHA JOB TRAINING - %s';
	    
	    if ( !empty( $this->_data['status'] ) ) {
		
		$dateIni = new Zend_Date( $this->_data['date_start'] );
		$dateFinish = new Zend_Date( $this->_data['date_finish'] );
		
		$duration = App_General_Date::getMonth( $dateIni, $dateFinish );
		$jobTraining = $this->fetchRow( $this->_data['fk_id_jobtraining'] );
		
		$this->_data['completed'] = $duration < $jobTraining->duration ? 0 : 1;
	    }
	   
	    $id = parent::_simpleSave( $dbTrainee );
	    
	    $history = sprintf( $history, $id, $this->_data['fk_id_jobtraining'] );
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
    public function addList()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbJobTrainingCandidates = App_Model_DbTable_Factory::get( 'JOBTraining_Candidates' );
	    $clients = $dbJobTrainingCandidates->fetchAll( array( 'fk_id_jobtraining = ?' => $this->_data['fk_id_jobtraining'] ) );
	    
	    $clientsList = array();
	    foreach ( $clients as $client )
		$clientsList[] = $client->fk_id_perdata;
	    
	    // Get just the new clients to the list
	    $clients = array_diff( $this->_data['clients'], $clientsList );
	    
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    
	    // Insert all the new clients in the list
	    foreach ( $clients as $client ) {
		
		// Add the client to the list candidates
		$row = $dbJobTrainingCandidates->createRow();
		$row->fk_id_jobtraining = $this->_data['fk_id_jobtraining'];
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
		$rowHistory->action = sprintf( 'KLIENTE SELECIONADO BA LISTA KANDIDATU JOB TRAINING NUMERO: %s ', $this->_data['fk_id_jobtraining'] );
		$rowHistory->description = 'KLIENTE SELECIONADO BA LISTA KANDIDATU JOB TRAINING';
		$rowHistory->save();
		
		// Save the auditing
		$history = sprintf( 'KLIENTE BA LISTA KANDIDATU: %s - BA JOB TRAINING NUMERU: %s ', $client, $this->_data['fk_id_jobtraining'] );
		$this->_sysAudit( $history );
	    }
	    
	    $dbAdapter->commit();
	    
	    return $this->_data['fk_id_jobtraining'];
	    
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
    public function checkFinishJobTraining( $id )
    {
	$return = array(
	    'valid'  => false,
	    'itens'  => array()
	);
	
	try {
	    
	    $this->_data = $this->fetchRow( $id );
	    
	    $validators = array(
		'_validateCourse',
		'_validateStudents',
		'_validateAllStudents',
		'_validateStudentsGender',
		'_validateStudentsStatus',
		'_validateFinishDate'
	    );
	    
	    $validGeneral = ( 0 != $this->_data->status );
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
    protected function _validateCourse()
    {
	$dbJobTrainingScholarity = App_Model_DbTable_Factory::get( 'JOBTraining_has_PerScholarity' );
	$courses = $dbJobTrainingScholarity->fetchAll( array( 'fk_id_jobtraining = ?' => $this->_data->id_jobtraining ) );
	
	$return = array(
	   'msg'    => 'IHA REJISTU KURSU ?',
	   'valid'  => true
	);

	if ( $courses->count() < 1 )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @return array
     */
    protected function _validateFinishDate()
    {
	$date = new Zend_Date();
	$finishCourse = new Zend_Date( $this->_data->date_finish );
	
	$return = array(
	   'msg'    => 'LORON PLANU REMETA JOB TRAINING: ' . $finishCourse->toString( 'dd/MM/yyyy' ),
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
    protected function _validateStudents()
    {
	$dbJobTrainingTrainee = App_Model_DbTable_Factory::get( 'JOBTraining_Trainee' );
	$students = $dbJobTrainingTrainee->fetchAll( array( 'fk_id_jobtraining = ?' => $this->_data->id_jobtraining ) );
	
	$return = array(
	   'msg'    => 'IHA REJISTU PARTISIPANTES BA JOB TRAINING ?',
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
	   'msg'    => 'IHA REJISTU PARTISIPANTES HOTU-HOTU BA JOB TRAINING ?',
	   'valid'  => true
	);
	
	$numClients = (int)$this->_data->total_participants;
	
	$dbJobTrainingTrainee = App_Model_DbTable_Factory::get( 'JOBTraining_Trainee' );
	$students = $dbJobTrainingTrainee->fetchAll( array( 'fk_id_jobtraining = ?' => $this->_data->id_jobtraining ) );

	if ( $students->count() < 1 || $numClients != $students->count() )
	    $return['valid'] = false;
	
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
	
	$students = $this->listClientJobTraining( $this->_data->id_jobtraining );
	$genders = array(
	    'MANE' => 0,
	    'FETO' => 0
	);
	
	foreach ( $students as $student )
	    $genders[$student->gender]++;

	if ( $genders['MANE'] != $this->_data->total_man || $genders['FETO'] != $this->_data->total_woman )
	    $return['valid'] = false;
	
	return $return;
    }
    
    /**
     *
     * @return boolean 
     */
    protected function _validateStudentsStatus()
    {
	$return = array(
	   'msg'    => 'IHA REJISTU KLIENTE STATUS ATUALIZADU HOTU-HOTU ?',
	   'valid'  => true
	);
	
	$students = $this->listClientJobTraining( $this->_data->id_jobtraining );
	
	foreach ( $students as $student ) {
	    
	    if ( empty( $student->status ) ) {
		
		$return['valid'] = false;
		return $return;
	    }   
	}
	
	return $return;
    }
    
    /**
     * 
     * @return int|bool
     */
    public function finishJobTraining()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	    $dbJobTrainingCandidates = App_Model_DbTable_Factory::get( 'JOBTraining_Candidates' );
	    $dbActionPlanBarrier = App_Model_DbTable_Factory::get( 'Action_Plan_Barrier' );
	    
	    $dataValid = $this->_data;
	    $valid = $this->checkFinishJobTraining( $this->_data['id'] );
	    
	    if ( empty( $valid['valid'] ) ) {
		
		$this->_message->addMessage( 'Erro: La bele remata job training! Haree kriterio sira.', App_Message::ERROR );
		return false;
	    }
	    
	    $this->_data = $dataValid;
	    
	    $class = $this->fetchRow( $this->_data['id'] );
	    $class->status = 0;
	    $class->date_finish = Zend_DAte::now()->toString( 'yyyy-MM-dd' );
	    $class->save();
	    
	    // Save the auditing
	    $history = 'REMATA JOB TRAINING NUMERU: %s';
	    $history = sprintf( $history, $this->_data['id'] );
	    $this->_sysAudit( $history );
	    
	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	    $noteMapper = new Default_Model_Mapper_Note();
	    
	    $clients =  $this->listClientJobTraining( $this->_data['id'] );
	    foreach ( $clients as $client ) {
		
		if ( empty( $client->completed ) ) continue;
		
		// Search if the jpb training was referencied by some barrier
		$whereReference = array(
		    'fk_id_jobtraining = ?'  => $this->_data['id'],
		    'fk_id_perdata = ?'	    => $client->fk_id_perdata
		);
		
		$reference = $dbActionPlanReferences->fetchRow( $whereReference );
		if ( !empty( $reference ) ) {
		    
		    $barrier = $dbActionPlanBarrier->fetchRow( array( 'id_action_barrier = ?' => $reference->fk_id_action_barrier ) );
		    $barrier->status = Client_Model_Mapper_Case::BARRIER_COMPLETED;
		    $barrier->date_finish = Zend_Date::now()->toString( 'yyyy-MM-dd' );
		    $barrier->save();
		}
		
		$whereCandidates = array(
		   'fk_id_perdata = ?'	    => $client->fk_id_perdata,
		   'fk_id_jobtraining = ?'  => $this->_data['id']
		);
		
		$referer = $dbJobTrainingCandidates->fetchRow( $whereCandidates );
		
		if ( empty( $referer->fk_id_sysuser ) )
		    continue;
		
		$dataNote = array(
		    'title'   => 'KLIENTE REMATA JOB TRAINING',
		    'level'   => 1,
		    'message' => $noteModelMapper->getJobTrainingGraduated( $client->fk_id_perdata, $this->_data['id'] ),
		    'users'   => array( $referer->fk_id_sysuser )
		);
		
		$noteMapper->setData( $dataNote )->saveNote();
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
	    
	    $dbJobTrainingScholarity = App_Model_DbTable_Factory::get( 'JOBTraining_has_PerScholarity' );
	    
	    $where = array( $dbAdapter->quoteInto( 'id_relationship = ?', $this->_data['id'] ) );
	    
	    $classScholarity = $dbJobTrainingScholarity->fetchRow( $where );
	    $dbJobTrainingScholarity->delete( $where );
	    
	    $history = 'DELETA KURSU: %s BA JOB TRAINING: %s';
	    $history = sprintf( $history, $classScholarity->fk_id_perscholarity, $classScholarity->fk_id_jobtraining );
	    $this->_sysAudit( $history, StudentClass_Form_JobTrainingCourse::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );

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
	$dbJobTrainingScholarity = App_Model_DbTable_Factory::get( 'JOBTraining_has_PerScholarity' );
	
	$select = $dbJobTrainingScholarity->select()
				    ->from( array( 'jts' => $dbJobTrainingScholarity ) )
				    ->where( 'jts.fk_id_perscholarity = ?', $data['fk_id_perscholarity'] )
				    ->where( 'jts.fk_id_jobtraining = ?', $data['fk_id_jobtraining'] );
	
	return $dbJobTrainingScholarity->fetchRow( $select );
    }
    
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listCourse( $id )
    {
	$dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbJobTrainingScholarity = App_Model_DbTable_Factory::get( 'JOBTraining_has_PerScholarity' );
	
	$select = $dbScholarity->select()
				->from( array( 's' => $dbScholarity ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'jt' => $dbJobTrainingScholarity ),
				    'jt.fk_id_perscholarity = s.id_perscholarity',
				    array( 'id_relationship' )
				)
				->where( 'jt.fk_id_jobtraining = ?', $id )
				->order( array( 'scholarity' ) );
	
	return $dbScholarity->fetchAll( $select );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveTrainee()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbJobTrainingTrainee = App_Model_DbTable_Factory::get( 'JOBTraining_Trainee' );
	    $dbJobTrainingCandidates = App_Model_DbTable_Factory::get( 'JOBTraining_Candidates' );
	    $clientTrainee = $dbJobTrainingTrainee->fetchAll( array( 'fk_id_jobtraining = ?' => $this->_data['id_jobtraining'] ) );
	    
	    $clientsCandidates = array();
	    foreach ( $clientTrainee as $client )
		$clientsCandidates[] = $client->fk_id_perdata;
	    
	    // Get just the new clients to the trainee
	    $clients = array_diff( $this->_data['clients'], $clientsCandidates );
	    
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    
	    $jobTraining = $this->fetchRow( $this->_data['id_jobtraining'] );
	    
	    // Check if the total of participants exceeds the total defined in the information step
	    if ( ( $clientTrainee->count() + count( $clients ) ) > $jobTraining['total_participants'] ) {
		
		$message = sprintf( 'Erro: Total partisipante la bele liu: %s. Iha %s tiha ona, bele aumenta: %s', 
				    $jobTraining['total_participants'], 
				    $clientTrainee->count(),
				    ( $jobTraining['total_participants'] - $clientTrainee->count() )
			    );
		
		$this->_message->addMessage( $message, App_Message::ERROR );
		return false;
	    }
	    
	    // Search the user who must receive notes when an user is refered to shortlist
	    $noteTypeMapper = new Admin_Model_Mapper_NoteType();
	    $users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::CLASS_SHORTLIST );
	    
	    $noteMapper = new Default_Model_Mapper_Note();
	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	
	    // Insert all the new clients in the trainee program
	    foreach ( $clients as $client ) {
		
		// Add the client to the trainee program
		$row = $dbJobTrainingTrainee->createRow();
		$row->fk_id_jobtraining = $this->_data['id_jobtraining'];
		$row->fk_id_perdata = $client;
		$row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$row->date_start = $jobTraining->date_start;
		$row->date_finish = $jobTraining->date_finish;
		$row->duration = $jobTraining->duration;
		$row->status = 0;
		$row->completed = 0;
		$row->save();
		
		// Save history to client
		$rowHistory = $dbPersonHistory->createRow();
		$rowHistory->fk_id_perdata = $client;
		$rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
		$rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
		$rowHistory->action = sprintf( 'KLIENTE SELECIONADO BA JOB TRAINING NUMERU:%s ', $this->_data['id_jobtraining'] );
		$rowHistory->description = 'KLIENTE HALO REJISTU KLIENTE SELECIONADO BA JOB TRAINING';
		$rowHistory->save();
		
		// Set the candidate already selected to avoid to be selected again
		$update = array( 'selected' => 1 );
		$whereUpdate = array(
		    'fk_id_perdata  = ?'     =>  $client,
		    'fk_id_jobtraining = ?'  =>  $this->_data['id_jobtraining']
		);
		
		$dbJobTrainingCandidates->update( $update, $whereUpdate );
		
		// Save the auditing
		$history = sprintf( 'KLIENTE %s SELECIONADO BA JOB TRAINING NUMERU: %s ', $client, $this->_data['id_jobtraining'] );
		$this->_sysAudit( $history );
		
		$whereCandidates = array(
		   'fk_id_perdata = ?'	    => $client,
		   'fk_id_jobtraining = ?'  => $this->_data['id_jobtraining']
		);
		
		$referer = $dbJobTrainingCandidates->fetchRow( $whereCandidates );
		
		if ( empty( $referer->fk_id_sysuser ) )
		    continue;
		
		$usersNotify = $users;
		$usersNotify[] = $referer->fk_id_sysuser;
		
		$dataNote = array(
		    'title'   => 'KLIENTE SELECIONADO BA JOB TRAINING',
		    'level'   => 1,
		    'message' => $noteModelMapper->getJobTrainingCandidate( $client, $this->_data['id_jobtraining'] ),
		    'users'   => $usersNotify
		);
		
		$noteMapper->setData( $dataNote )->saveNote();
	    }
	    
	    $dbAdapter->commit();
	    
	    return $this->_data['id_jobtraining'];
	    
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
    protected function _sysAudit( $description, $form = StudentClass_Form_JobTrainingInformation::ID, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
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
    public function getSelectJobTraining()
    {
	$dbJobTraining = App_Model_DbTable_Factory::get( 'JOBTraining' );
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	$dbInstitute = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbScholarityArea = App_Model_DbTable_Factory::get( 'ScholarityArea' );
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	
	$select = $dbJobTraining->select()
				 ->from( array( 'jt' => $dbJobTraining ) )
				 ->setIntegrityCheck( false )
				 ->joinLeft(
				    array( 'e' => $dbEnterprise ),
				    'e.id_fefpenterprise = jt.fk_id_fefpenterprise',
				    array()
				 )
				 ->joinLeft(
				    array( 'ei' => $dbInstitute ),
				    'ei.id_fefpeduinstitution = jt.fk_id_fefpeduinstitution',
				    array( 
					'entity' => new Zend_Db_Expr( 'IFNULL( e.enterprise_name, ei.institution )' ),
					'date_start_formated'	=> new Zend_Db_Expr( 'DATE_FORMAT( jt.date_start, "%d/%m/%Y" )' ),
					'date_finish_formated'  => new Zend_Db_Expr( 'DATE_FORMAT( jt.date_finish, "%d/%m/%Y" )' ),
				    )
				 )
				 ->join(
				    array( 'd' => $dbDec ),
				    'd.id_dec = jt.fk_id_dec',
				    array( 'name_dec' )
				 )
				 ->join(
				    array( 'sa' => $dbScholarityArea ),
				    'sa.id_scholarity_area = jt.fk_id_scholarity_area',
				    array( 'scholarity_area' )
				 )
				 ->order( array( 'title' ) );
	
	return $select;
    }
    
    /**
     *
     * @param array $filters
     * @return type 
     */
    public function listByFilters( array $filters = array() )
    {
	$dbJobTraining = App_Model_DbTable_Factory::get( 'JOBTraining' );
	$dbJobTrainingPerScholarity = App_Model_DbTable_Factory::get( 'JOBTraining_has_PerScholarity' );
	
	$select = $this->getSelectJobTraining();
	
	$select->joinLeft(
		    array( 'jsc' => $dbJobTrainingPerScholarity ),
		    'jsc.fk_id_jobtraining = jt.id_jobtraining',
		    array()
		);
	
	if ( !empty( $filters['title'] ) )
	    $select->where( 'jt.title LIKE ?', '%' . $filters['title'] . '%' );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'jt.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	if ( !empty( $filters['fk_id_scholarity_area'] ) )
	    $select->where( 'jt.fk_id_scholarity_area = ?', $filters['fk_id_scholarity_area'] );
	
	if ( !empty( $filters['fk_id_fefpenterprise'] ) )
	    $select->where( 'jt.fk_id_fefpenterprise = ?', $filters['fk_id_fefpenterprise'] );
	
	if ( !empty( $filters['fk_id_fefpeduinstitution'] ) )
	    $select->where( 'jt.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution'] );
	
	if ( !empty( $filters['fk_id_perscholarity'] ) )
	    $select->where( 'jsc.fk_id_perscholarity = ?', $filters['fk_id_perscholarity'] );
	
	if ( !empty( $filters['not_id'] ) )
	    $select->where( 'jt.id_jobtraining <> ?', $filters['not_id'] );
	
	if ( array_key_exists( 'status', $filters ) )
	    $select->where( 'jt.status = ?', (int)$filters['status'] );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'jt.date_start >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'jt.date_finish <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	$select->group( array( 'id_jobtraining' ) );
	
	return $dbJobTraining->fetchAll( $select );
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
	
	$dbJobTrainingCandidates = App_Model_DbTable_Factory::get( 'JOBTraining_Candidates' );
	$select->joinLeft(
		    array( 'jtc' => $dbJobTrainingCandidates ),
		    'jtc.fk_id_perdata = c.id_perdata AND jtc.fk_id_jobtraining = ' . $id,
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
	$select = $this->_selectMatchClient( $filters['fk_id_jobtraining'] );
	
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
	
	$dbCandidates = App_Model_DbTable_Factory::get( 'JOBTraining_Candidates' );
	$select->join(
		    array( 'lc' => $dbCandidates ),
		    'lc.fk_id_perdata = c.id_perdata',
		    array( 'selected' )
		)
		->where( 'lc.fk_id_jobtraining = ?', $id )
		->group( 'id_perdata' )
		->order( array( 'first_name', 'last_name' ) );
	
	return $dbCandidates->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listClientJobTraining( $id )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$dbJobTrainingTrainee = App_Model_DbTable_Factory::get( 'JOBTraining_Trainee' );
	
	$select->join(
		    array( 'jtt' => $dbJobTrainingTrainee ),
		    'jtt.fk_id_perdata = c.id_perdata',
		    array(
			'*',
			'date_start_formated'	=> new Zend_Db_Expr( 'DATE_FORMAT( jtt.date_start, "%d/%m/%Y" )' ),
			'date_finish_formated'  => new Zend_Db_Expr( 'DATE_FORMAT( jtt.date_finish, "%d/%m/%Y" )' ),
		    )
		)
		->where( 'jtt.fk_id_jobtraining = ?', $id )
		->group( 'id_perdata' )
		->order( array( 'first_name', 'last_name' ) );
	
	return $dbJobTrainingTrainee->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function fetchTrainee( $id )
    {
	$dbTrainee = App_Model_DbTable_Factory::get( 'JOBTraining_Trainee' );
	
	$select = $this->getSelectJobTraining();
	$select->join(
		    array( 'jtt' => $dbTrainee ),
		    'jtt.fk_id_jobtraining = jt.id_jobtraining'
		)
		->where( 'jtt.id_trainee = ?', $id );
	
	return $dbTrainee->fetchRow( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detailJobTraining( $id )
    {
	$select = $this->getSelectJobTraining();
	$select->where( 'jt.id_jobtraining = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
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
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listEnterprises()
    {
	$select = $this->getSelectJobTraining();
	$select->where( 'jt.fk_id_fefpenterprise IS NOT NULL' )->group( array( 'fk_id_fefpenterprise' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listInstitutes()
    {
	$select = $this->getSelectJobTraining();
	$select->where( 'jt.fk_id_fefpeduinstitution IS NOT NULL' )->group( array( 'fk_id_fefpeduinstitution' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    public function listTraineeByFilters( $filters = array() )
    {
	$dbJobTraining = App_Model_DbTable_Factory::get( 'JOBTraining' );
	$dbJobTrainingPerScholarity = App_Model_DbTable_Factory::get( 'JOBTraining_has_PerScholarity' );
	$dbClient = App_Model_DbTable_Factory::get( 'PerData' );
	$dbTrainee = App_Model_DbTable_Factory::get( 'JOBTrainingTrainee' );
	
	$select = $this->getSelectJobTraining();
	
	$select->joinLeft(
		    array( 'jsc' => $dbJobTrainingPerScholarity ),
		    'jsc.fk_id_jobtraining = jt.id_jobtraining',
		    array()
		)
		->join( 
		    array( 'jtt' => $dbTrainee ),
		    'jtt.fk_id_jobtraining = jt.id_jobtraining',
		    array( 'id_trainee', 'fk_id_perdata' )
		)
		->join( 
		    array( 'cl' => $dbClient ),
		    'cl.id_perdata = jtt.fk_id_perdata',
		    array( 
			'first_name',
			'medium_name',
			'last_name',
			'gender'
		    )
		);
	
	if ( !empty( $filters['title'] ) )
	    $select->where( 'jt.title LIKE ?', '%' . $filters['title'] . '%' );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'jt.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	if ( !empty( $filters['fk_id_scholarity_area'] ) )
	    $select->where( 'jt.fk_id_scholarity_area = ?', $filters['fk_id_scholarity_area'] );
	
	if ( !empty( $filters['fk_id_fefpenterprise'] ) )
	    $select->where( 'jt.fk_id_fefpenterprise = ?', $filters['fk_id_fefpenterprise'] );
	
	if ( !empty( $filters['fk_id_fefpeduinstitution'] ) )
	    $select->where( 'jt.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution'] );
	
	if ( !empty( $filters['fk_id_perscholarity'] ) )
	    $select->where( 'jsc.fk_id_perscholarity = ?', $filters['fk_id_perscholarity'] );
	
	if ( !empty( $filters['not_id'] ) )
	    $select->where( 'jt.id_jobtraining <> ?', $filters['not_id'] );
	
	if ( array_key_exists( 'status', $filters ) )
	    $select->where( 'jt.status = ?', (int)$filters['status'] );
	
	if ( !empty( $filters['name'] ) ) {
	 
	    $select->where( '( jt.first_name LIKE ?', '%' . $filters['name'] . '%' );
	    $select->orWhere( 'jt.medium_name LIKE ?', '%' . $filters['name'] . '%' );
	    $select->orWhere( 'jt.last_name LIKE ? )', '%' . $filters['name'] . '%' );
	}
	
	if ( !empty( $filters['gender'] ) )
	    $select->where( 'cl.gender = ?', $filters['gender'] );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'jt.date_start >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'jt.date_finish <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	$select->group( array( 'id_jobtraining', 'fk_id_perdata' ) );
	
	return $dbJobTraining->fetchAll( $select );
    }
}