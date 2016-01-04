<?php

class Cron_Model_Mapper_Notification extends App_Model_Abstract
{    
    /**
     * 
     */
    public function notifyAllMessages()
    {
	try {
	    
	    $this->notifyClassExpired();
	    $this->notifyJobVacancyExpired();
	    $this->notifyJobTrainingExpired();
	    $this->notifyAppointmentExpired();
	    $this->notifyFollowupCase();
	    $this->notifyFollowupJob();
	    
	    echo "All the notifications were sent\n";
	    
	} catch ( Exception $e ) {
	    echo 'Error sending notifications: ' . $e->getMessage() . "\n";
	}
    }
    
    
    /**
     * 
     */
   public function notifyClassExpired()
   {
       $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {

	    // Search all the expired classes
	    $studentClassMapper = new StudentClass_Model_Mapper_StudentClass();
	    $selectClass = $studentClassMapper->getSelectClass();
	    
	    $selectClass->where( 'sc.active = ?', 1 )->where( 'sc.schedule_finish_date < ?', Zend_Date::now()->toString( 'yyyy-MM-dd' ) );
	    
	    $dbStudentClass = App_Model_DbTable_Factory::get( 'FEFPStudentClass' );
	    $rows = $dbStudentClass->fetchAll( $selectClass );
	    
	    // Search the user who must receive notes when class are expired
	    $noteTypeMapper = new Admin_Model_Mapper_NoteType();
	    $users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::CLASS_EXPIRED );
	    
	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	    $noteMapper = new Default_Model_Mapper_Note();
	    
	    foreach ( $rows as $row ) {
		
		$dataNote = array(
		    'title'   => 'KLASE LORON REMATA LIU TIHA ONA',
		    'level'   => 0,
		    'message' => $noteModelMapper->getClassExpired( $row->id_fefpstudentclass ),
		    'users'   => $users
		);
		
		$noteMapper->setData( $dataNote )->saveNote();
	    }
	    
	    $dbAdapter->commit();
	    return true;
	    
	} catch ( Exception $e ) {
	    
	    $dbAdapter->rollBack();
	    echo "Error sending class expired notifications: " . $e->getMessage() . "\n";
	    return false;
	}
   }
   
   /**
     * 
     */
   public function notifyJobVacancyExpired()
   {
       $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {

	    // Search all the expired job vacancy
	    $jobVacancyMapper = new Job_Model_Mapper_JobVacancy();
	    $selectJob = $jobVacancyMapper->getSelectVacancy();
	    
	    $selectJob->where( 'jv.active = ?', 1 )->where( 'jv.close_date < ?', Zend_Date::now()->toString( 'yyyy-MM-dd' ) );
	    
	    $dbJobVacancy = App_Model_DbTable_Factory::get( 'JOBVacancy' );
	    $rows = $dbJobVacancy->fetchAll( $selectJob );
	    
	    // Search the user who must receive notes when job vacancy are expired
	    $noteTypeMapper = new Admin_Model_Mapper_NoteType();
	    $users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::JOB_EXPIRED );
	    
	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	    $noteMapper = new Default_Model_Mapper_Note();
	    $userMapper = new Admin_Model_Mapper_SysUser();
	    
	    $ceopsUser = array();
	    
	    foreach ( $rows as $row ) {
		
		$usersWarning = $users;
		
		if ( empty( $ceopsUser[$row->fk_id_dec] ) ) {
		
		    // Search all the users from the same CEOP
		    $usersCeop = $userMapper->listAll( $row->fk_id_dec );
		    foreach ( $usersCeop as $userCeop )
			$ceopsUser[$row->fk_id_dec][] = $userCeop->id_sysuser;
		
		    $usersWarning += $ceopsUser[$row->fk_id_dec];
		} else
		    $usersWarning += $ceopsUser[$row->fk_id_dec];
		
		$dataNote = array(
		    'title'   => 'VAGA EMPREGU LORON TAKA LIU TIHA ONA',
		    'level'   => 0,
		    'message' => $noteModelMapper->getJobExpired( $row->id_jobvacancy ),
		    'users'   => $usersWarning
		);
		
		$noteMapper->setData( $dataNote )->saveNote();
	    }
	    
	    $dbAdapter->commit();
	    return true;
	    
	} catch ( Exception $e ) {
	    
	    $dbAdapter->rollBack();
	    echo "Error sending job vacancy expired notifications: " . $e->getMessage() . "\n";
	    return false;
	}
   }
   
   /**
     * 
     */
   public function notifyJobTrainingExpired()
   {
       $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {

	    // Search all the expired job vacancy
	    $jobTrainingMapper = new StudentClass_Model_Mapper_JobTraining();
	    $selectJob = $jobTrainingMapper->getSelectJobTraining();
	    
	    $selectJob->where( 'jt.status = ?', 1 )->where( 'jt.date_finish < ?', Zend_Date::now()->toString( 'yyyy-MM-dd' ) );
	    
	    $dbJobTraining = App_Model_DbTable_Factory::get( 'JOBTraining' );
	    $rows = $dbJobTraining->fetchAll( $selectJob );
	    
	    // Search the user who must receive notes when job training are expired
	    $noteTypeMapper = new Admin_Model_Mapper_NoteType();
	    $users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::JOB_TRAINING_EXPIRED );
	    
	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	    $noteMapper = new Default_Model_Mapper_Note();
	    
	    foreach ( $rows as $row ) {
		
		$dataNote = array(
		    'title'   => 'VAGA ESTAJIU LORON TAKA LIU TIHA ONA',
		    'level'   => 0,
		    'message' => $noteModelMapper->getJobTrainingExpired( $row->id_jobtraining ),
		    'users'   => $users
		);
		
		$noteMapper->setData( $dataNote )->saveNote();
	    }
	    
	    $dbAdapter->commit();
	    return true;
	    
	} catch ( Exception $e ) {
	    
	    $dbAdapter->rollBack();
	    echo "Error sending job training expired notifications: " . $e->getMessage() . "\n";
	    return false;
	}
   }
   
   /**
     * 
     */
   public function notifyAppointmentExpired()
   {
       $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {

	    // List the appointments expired
	    $rows = $this->_listAppointmentsExpired();
	    
	    // Search the user who must receive notes when appointment is expired
	    $noteTypeMapper = new Admin_Model_Mapper_NoteType();
	    $users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::APPOINTMENT_EXPIRED );
	    
	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	    $noteMapper = new Default_Model_Mapper_Note();
	    $userMapper = new Admin_Model_Mapper_SysUser();
	    
	    $ceopsUser = array();
	    
	    foreach ( $rows as $row ) {
		
		$usersWarning = $users;
		$usersWarning[] = $row->fk_id_counselor;
		
		if ( empty( $ceopsUser[$row->fk_id_dec] ) ) {
		
		    // Search all the users from the same CEOP
		    $usersCeop = $userMapper->listAll( $row->fk_id_dec );
		    foreach ( $usersCeop as $userCeop )
			$ceopsUser[$row->fk_id_dec][] = $userCeop->id_sysuser;
		
		    $usersWarning += $ceopsUser[$row->fk_id_dec];
		} else
		    $usersWarning += $ceopsUser[$row->fk_id_dec];
		
		$dataNote = array(
		    'title'   => 'AUDIENSIA KAZU LIU TIHA ONA',
		    'level'   => 0,
		    'message' => $noteModelMapper->getAppointmentExpired( $row ),
		    'users'   => $usersWarning
		);
		
		$noteMapper->setData( $dataNote )->saveNote();
	    }
	    
	    $dbAdapter->commit();
	    return true;
	    
	} catch ( Exception $e ) {
	    
	    $dbAdapter->rollBack();
	    echo "Error sending appointment expired notifications: " . $e->getMessage() . "\n";
	    return false;
	}
   }
   
   /**
    *
    * @return Zend_Db_Table_Rowset 
    */
   public function _listAppointmentsExpired()
   {
       $mapperClient = new Client_Model_Mapper_Client();
       $selectClient = $mapperClient->selectClient();
       
       $dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
       $dbDec = App_Model_DbTable_Factory::get( 'Dec' );
       $dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
       $dbAppointment = App_Model_DbTable_Factory::get( 'Appointment' );
       
       $selectClient->join(
			array( 'apl' => $dbActionPlan ),
			'apl.fk_id_perdata = c.id_perdata',
			array( 'id_action_plan', 'fk_id_counselor' )
		     )
		     ->join(
			array( 'ce' => $dbDec ),
			'ce.id_dec = apl.fk_id_dec',
			array( 'ceop_case' => 'name_dec' )
		     )
		     ->join(
			array( 'co' => $dbUser ),
			'apl.fk_id_counselor = co.id_sysuser',
			array( 'counselor' => 'name' )
		     )
		     ->join(
			array( 'app' => $dbAppointment ),
			'app.fk_id_action_plan = apl.id_action_plan',
			array( 'date_appointment' )
		     )
		     ->where( 'apl.active = ?' , 1 )
		     ->where( 'app.appointment_filled = ?' , 0 )
		     ->where( 'DATE(app.date_appointment) < ?' , Zend_Date::now()->toString( 'yyyy-MM-dd' ) );
       
       return $dbActionPlan->fetchAll( $selectClient );
   }
   
    /**
     * 
     */
   public function notifyFollowupCase()
   {
       $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {

	    $filters = array( 'status' => 1 );
	    $today = new Zend_Date();
	    
	    $mapperCase = new Client_Model_Mapper_Case();
	    $rows = $mapperCase->listByFilters( $filters );
	    
	    // Search the user who must receive notes to follow up cases
	    $noteTypeMapper = new Admin_Model_Mapper_NoteType();
	    $users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::CASE_FOLLOW_UP );
	    
	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	    $noteMapper = new Default_Model_Mapper_Note();
	    
	    foreach ( $rows as $row ) {
		
		$dateCase = new Zend_Date( $row->date_start, 'dd/MM/yyyy' );
		$days = $today->sub( $dateCase );
		
		$measure = new Zend_Measure_Time( $days->toValue(), Zend_Measure_Time::SECOND );
		$diffDays = $measure->convertTo( Zend_Measure_Time::DAY, 0 );
		
		$diff = (float)preg_replace( '/[^0-9]/i', '', $diffDays );
		
		if ( ( $diff % 15 ) != 0 )
		    continue;
		
		$usersWarning = $users;
		$usersWarning[] = $row->fk_id_counselor;
		
		$dataNote = array(
		    'title'   => 'HALO AKOMPAÑAMENTU KAZU HO KLIENTE',
		    'level'   => 0,
		    'message' => $noteModelMapper->getFollowUpCase( $row->client ),
		    'users'   => $usersWarning
		);
		
		$noteMapper->setData( $dataNote )->saveNote();
	    }
	    
	    $dbAdapter->commit();
	    return true;
	    
	} catch ( Exception $e ) {
	    
	    $dbAdapter->rollBack();
	    echo "Error sending cases follow-ups notifications: " . $e->getMessage() . "\n";
	    return false;
	}
   }
   
    /**
     * 
     */
   public function notifyFollowupJob()
   {
       $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {

	    $rows = $this->_listJobHiredFollowup();
	    
	    // Search the user who must receive notes to follow up cases
	    $noteTypeMapper = new Admin_Model_Mapper_NoteType();
	    $users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::JOB_FOLLOW_UP );
	    
	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	    $noteMapper = new Default_Model_Mapper_Note();
	    
	    foreach ( $rows as $row ) {
		
		$usersWarning = $users;
		$usersWarning[] = $row->fk_id_sysuser;
		
		$dataNote = array(
		    'title'   => 'HALO AKOMPAÑAMENTU HO KLIENTE MAK HETAN SERBISU',
		    'level'   => 0,
		    'message' => $noteModelMapper->getFollowUpCase( $row->fk_id_perdata ),
		    'users'   => $usersWarning
		);
		
		$noteMapper->setData( $dataNote )->saveNote();
	    }
	    
	    $dbAdapter->commit();
	    return true;
	    
	} catch ( Exception $e ) {
	    
	    $dbAdapter->rollBack();
	    echo "Error sending cases follow-ups notifications: " . $e->getMessage() . "\n";
	    return false;
	}
   }
   
   /**
    * 
    * @return Zend_Db_Table_Rowset
    */
   protected function _listJobHiredFollowup()
   {
        $dbHired = App_Model_DbTable_Factory::get( 'Hired' );
        $dbActionPlanReferences = App_Model_DbTable_Factory::get( 'ActionPlanReferences' );
	
	$select = $dbHired->select()
			  ->from( array( 'h' => $dbHired ) )
			  ->setIntegrityCheck( false )
			  ->join(
				array( 'apr' => $dbActionPlanReferences ),
				'apr.fk_id_jobvacancy = h.fk_id_jobvacancy AND h.fk_id_perdata = apr.fk_id_perdata',
				array(
				    'fk_id_sysuser',
				    'diff' => new Zend_Db_Expr( 'DATEDIFF( CURDATE(), result_date )' )
				)
			  )
			  ->having( '( diff % 30 ) = 0' )
			  ->having( 'diff BETWEEN 1 AND 90' );
	
	return $dbHired->fetchAll( $select );
			      
   }
}