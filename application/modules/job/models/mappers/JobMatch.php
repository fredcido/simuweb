<?php

class Job_Model_Mapper_JobMatch extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_ShortlistVacancy
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_ShortlistVacancy();

	return $this->_dbTable;
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
	    
	    $dbJobCandidates = App_Model_DbTable_Factory::get( 'JOBVacancy_Candidates' );
	    $clients = $dbJobCandidates->fetchAll( array( 'fk_id_jobvacancy = ?' => $this->_data['fk_id_jobvacancy'] ) );
	    
	    $clientsList = array();
	    foreach ( $clients as $client )
		$clientsList[] = $client->fk_id_perdata;
	    
	    // Get just the new clients to the list
	    $clients = array_diff( $this->_data['clients'], $clientsList );
	    
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    
	    // Insert all the new clients in the list
	    foreach ( $clients as $client ) {
		
		// Add the client to the shortlist
		$row = $dbJobCandidates->createRow();
		$row->fk_id_jobvacancy = $this->_data['fk_id_jobvacancy'];
		$row->fk_id_perdata = $client;
		$row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$row->source = $this->_data['source'];
		$row->save();
		
		// Save history to client
		$rowHistory = $dbPersonHistory->createRow();
		$rowHistory->fk_id_perdata = $client;
		$rowHistory->fk_id_jobvacancy = $this->_data['fk_id_jobvacancy'];
		$rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
		$rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
		$rowHistory->action = sprintf( 'KLIENTE SELECIONADO BA LISTA KANDIDATU VAGA EMPREGU NUMERO:%s ', $this->_data['fk_id_jobvacancy'] );
		$rowHistory->description = 'KLIENTE SELECIONADO BA LISTA KANDIDATU BA VAGA EMPREGU';
		$rowHistory->save();
		
		// Save the auditing
		$history = sprintf( 'KLIENTE BA LISTA KANDIDATU: %s - BA VAGA EMPREGU NUMERU: %s ', $client, $this->_data['fk_id_jobvacancy'] );
		$this->_sysAudit( $history );
	    }
	    
	    $dbAdapter->commit();
	    
	    return $this->_data['fk_id_jobvacancy'];
	    
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
    public function save()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbShortlist = App_Model_DbTable_Factory::get( 'ShortlistVacancy' );
	    $clients = $dbShortlist->fetchAll( array( 'fk_id_jobvacancy = ?' => $this->_data['id_jobvacancy'] ) );
	    
	    $clientsShortlist = array();
	    foreach ( $clients as $client )
		$clientsShortlist[] = $client->fk_id_perdata;
	    
	    // Get just the new clients to the shortlist
	    $clients = array_diff( $this->_data['clients'], $clientsShortlist );
	    
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    $dbJobCandidates = App_Model_DbTable_Factory::get( 'JOBVacancy_Candidates' );
	    $dbActionPlanReferences = App_Model_DbTable_Factory::get( 'Action_Plan_References' );
	    
	    // Search the user who must receive notes when an user is refered to shortlist
	    $noteTypeMapper = new Admin_Model_Mapper_NoteType();
	    $users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::JOB_REFERED_SHORTLIST );
	    
	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	    $noteMapper = new Default_Model_Mapper_Note();
	    
	    // Insert all the new clients in the shortlist
	    foreach ( $clients as $client ) {
		
		// Add the client to the shortlist
		$row = $dbShortlist->createRow();
		$row->fk_id_jobvacancy = $this->_data['id_jobvacancy'];
		$row->fk_id_perdata = $client;
		$row->save();
		
		// Save history to client
		$rowHistory = $dbPersonHistory->createRow();
		$rowHistory->fk_id_perdata = $client;
		$rowHistory->fk_id_jobvacancy = $this->_data['id_jobvacancy'];
		$rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
		$rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
		$rowHistory->action = sprintf( 'KLIENTE SELECIONADO BA SHORTLIST VAGA EMPREGU NUMERO:%s ', $this->_data['id_jobvacancy'] );
		$rowHistory->description = 'KLIENTE SELECIONADO BA SHORTLISTA VAGA EMPREGU';
		$rowHistory->save();
		
		// Set the list as shortlisted already to avoid to be shortlisted again
		$update = array( 'shortlisted' => 1 );
		$whereUpdate = array(
		    'fk_id_perdata = ?'	    =>  $client,
		    'fk_id_jobvacancy = ?'  =>	$this->_data['id_jobvacancy']
		);
		
		$dbJobCandidates->update( $update, $whereUpdate );
		
		// Save the auditing
		$history = sprintf( 'SHORTED LIST KLIENTE: %s - BA VAGA EMPREGU NUMERU: %s ', $client, $this->_data['id_jobvacancy'] );
		$this->_sysAudit( $history );
		
		// Search if the vacancy was referencied by some barrier
		$whereReference = array(
		    'fk_id_jobvacancy = ?'  => $this->_data['id_jobvacancy'],
		    'fk_id_perdata = ?'	    => $client
		);
		
		$reference = $dbActionPlanReferences->fetchRow( $whereReference );
		if ( !empty( $reference ) ) {
		    
		    $usersNotify = $users;
		    $usersNotify[] = $reference->fk_id_sysuser;
		    
		    $dataNoteModel = array(
			'client' => $client,
			'vacancy' => $this->_data['id_jobvacancy'],
			'case'	  => $reference->fk_id_action_plan,
			'user'	  => $reference->fk_id_sysuser
		    );
		    
		    $dataNote = array(
			'title'   => 'KLIENTE REFERE BA SHORTLIST',
			'level'   => 1,
			'message' => $noteModelMapper->geJobShortlist( $dataNoteModel ),
			'users'   => $usersNotify
		    );
		    
		    $noteMapper->setData( $dataNote )->saveNote();
		}
	    }
	    
	    $dbAdapter->commit();
	    
	    return $this->_data['id_jobvacancy'];
	    
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
    public function deleteClient()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbShortlist = App_Model_DbTable_Factory::get( 'ShortlistVacancy' );
	    $dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	    
	    $where = array(
		'fk_id_perdata = ?' => $this->_data['client'],
		'fk_id_jobvacancy = ?' => $this->_data['id_jobvacancy']
	    );
	    
	    // Remove the client from the shortlist
	    $dbShortlist->delete( $where );
	    
	    // Save history to client
	    $rowHistory = $dbPersonHistory->createRow();
	    $rowHistory->fk_id_perdata = $this->_data['client'];
	    $rowHistory->fk_id_jobvacancy = $this->_data['id_jobvacancy'];
	    $rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
	    $rowHistory->date_time = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' );
	    $rowHistory->action = sprintf( 'KLIENTE REMOVIDO HUSI SHORTLIST VAGA EMPREGU NUMERO:%s ', $this->_data['id_jobvacancy'] );
	    $rowHistory->description = 'KLIENTE REMOVIDO HUSI SHORTLISTA VAGA EMPREGU';
	    $rowHistory->save();

	    // Save the auditing
	    $history = sprintf( 'DELETE KLIENTE: %s - HUSI SHORT LIST BA VAGA EMPREGU NUMERU: %s ', $this->_data['client'], $this->_data['id_jobvacancy'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return $this->_data['id_jobvacancy'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
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
	
	$dbJobCandidates = App_Model_DbTable_Factory::get( 'JOBVacancyCandidates' );
	$select->joinLeft(
		    array( 'jc' => $dbJobCandidates ),
		    'jc.fk_id_perdata = c.id_perdata AND jc.fk_id_jobvacancy = ' . $id,
		    array( 'list' => 'id_relationship' )
		);
	
	return $select;
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listAutomatic( $id )
    {
	$select = $this->_selectMatchClient( $id );
	
	$mapperVacancy = new Job_Model_Mapper_JobVacancy();
	
	$dbPerExperience = App_Model_DbTable_Factory::get( 'PerExperience' );
	$dbJobVacancy = App_Model_DbTable_Factory::get( 'JOBVacancy' );
	$dbJobLocation = App_Model_DbTable_Factory::get( 'JOBVacancy_has_Location' );
	$dbJobScholarity = App_Model_DbTable_Factory::get( 'JOBVacancy_has_PerScholarity' );
	$dbJobTraining = App_Model_DbTable_Factory::get( 'JOBVacancy_has_Training' );
	$dbJobLanguage = App_Model_DbTable_Factory::get( 'JOBVacancy_has_PerLanguage' );
	$dbAddress = App_Model_DbTable_Factory::get( 'AddAddress' );
	$dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity_has_PerTypeScholarity' );
	$dbPerLanguage = App_Model_DbTable_Factory::get( 'PerLanguage_has_PerLevelKnowledge' );
	$dbProfOcupation = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$dbHandicapped = App_Model_DbTable_Factory::get( 'handicapped' );
	
	$vacancy = $mapperVacancy->detailVacancy( $id );
	
	// Minimum Age
	if ( !empty( $vacancy->minimum_age ) )
	    $select->having( 'age >= ?', $vacancy->minimum_age );
	
	// Maximum Age
	if ( !empty( $vacancy->maximum_age ) )
	    $select->having( 'age <= ?', $vacancy->maximum_age );
	
	// Gender
	if ( !empty( $vacancy->gender ) )
	    $select->where( 'LEFT(c.gender, 1) = ?', $vacancy->gender );
	
	$selectOcupation = $dbProfOcupation->select()
						->setIntegrityCheck( false )
						->from( array( 'pot1' => $dbProfOcupation ) )
						->where( 'pot1.fk_id_profocupation = pot.fk_id_profocupation' )
						->where( 'pot1.id_profocupationtimor = ?', $vacancy->fk_id_profocupation );
	
	// Minimal Experience and the Profesional Occupation    
	$select->join(
		    array( 'xp' => $dbPerExperience ),
		    'xp.fk_id_perdata = c.id_perdata',
		    array()
		)
		->join(
		    array( 'pot' => $dbProfOcupation ),
		    'pot.id_profocupationtimor = xp.fk_id_profocupation',
		    array()
		)
		->where( 'EXISTS (?)', new Zend_Db_Expr( '(' . $selectOcupation . ')' ) );
	
	// Minimal Experience
	if ( !empty( $vacancy->minimum_experience ) )
	    $select->where( 'xp.experience_year >= ?', $vacancy->minimum_experience );
	
	// Search by SubDistrict
	$subDistricts = $mapperVacancy->listAddress( $id );
	
	if ( $subDistricts->count() > 0 ) {
	    
	    $selectAddress = $dbJobLocation->select()
					    ->from( 
						array( 'jl' => $dbJobLocation ),
						array( new Zend_Db_Expr( 'NULL' ) )
					    )
					    ->setIntegrityCheck( false )
					    ->where( 'jl.fk_id_jobvacancy = ?', $id )
					    ->where( '( jl.fk_id_addcountry = ad.fk_id_addcountry' )
					    ->orWhere( 'jl.fk_id_adddistrict = ad.fk_id_adddistrict )' );

	    $select->join(
			array( 'ad' => $dbAddress ),
			'ad.fk_id_perdata = c.id_perdata',
			array()
		    )
		    ->where( 'EXISTS (?)', new Zend_Db_Expr( '(' . $selectAddress . ')' ) );
	}
	
	// Search By Scholarity
	$scholarities = $mapperVacancy->listScholarity( $id );
	if ( $scholarities->count() > 0 ) {
	
	    $selectScholarity = $dbJobScholarity->select()
						->from( 
						    array( 'js' => $dbJobScholarity ),
						    array( new Zend_Db_Expr( 'NULL' ) )
						)
						->setIntegrityCheck( false )
						->where( 'js.fk_id_perscholarity = ps.fk_id_perscholarity' )
						->where( 'js.fk_id_jobvacancy = ?', $id );

	    $select->join(
			array( 'ps' => $dbPerScholarity ),
			'ps.fk_id_perdata = c.id_perdata',
			array()
		    )
		    ->where( 'EXISTS (?)', new Zend_Db_Expr( '(' . $selectScholarity . ')' ) );
	}
	
	// Search By Training
	$trainings = $mapperVacancy->listTraining( $id );
	
	if ( $trainings->count() > 0 ) {
	    $selectTraining = $dbJobTraining->select()
					    ->from( 
						array( 'jt' => $dbJobTraining ),
						array( new Zend_Db_Expr( 'NULL' ) )
					    )
					    ->where( 'jt.fk_id_perscholarity = pt.fk_id_perscholarity' )
					    ->where( 'jt.fk_id_jobvacancy = ?', $id );

	    $select->join(
			array( 'pt' => $dbPerScholarity ),
			'pt.fk_id_perdata = c.id_perdata',
			array()
		    )
		    ->where( 'EXISTS (?)', new Zend_Db_Expr( '(' . $selectTraining . ')' ) );
	}
	
	// Search By Language
	$languages = $mapperVacancy->listLanguage( $id );
	
	if ( $languages->count() > 0 ) {
	    $selectLanguage = $dbJobLanguage->select()
					    ->from( 
						array( 'jl' => $dbJobLanguage ),
						array( new Zend_Db_Expr( 'NULL' ) )
					    )
					    ->where( 'jl.fk_id_perlanguage = pl.fk_id_perlanguage' )
					    ->where( 'jl.fk_id_jobvacancy = ?', $id );

	    $select->join(
			array( 'pl' => $dbPerLanguage ),
			'pl.fk_id_perdata = c.id_perdata',
			array()
		    )
		    ->where( 'EXISTS (?)', new Zend_Db_Expr( '(' . $selectLanguage . ')' ) );
	}
	
	// Search by handicapped
	$handicapped = $mapperVacancy->listHandicapped( $id );
	if ( $handicapped->count() > 0 ) {
	    
	    $selectHandicapped = $dbHandicapped->select()
					    ->from( 
						array( 'hd' => $dbHandicapped ),
						array( new Zend_Db_Expr( 'NULL' ) )
					    )
					    ->where( 'hd.fk_id_typehandicapped = ph.fk_id_typehandicapped' )
					    ->where( 'hd.fk_id_jobvacancy = ?', $id );

	    $select->join(
			array( 'ph' => $dbHandicapped ),
			'ph.fk_id_perdata = c.id_perdata',
			array()
		    )
		    ->where( 'EXISTS (?)', new Zend_Db_Expr( '(' . $selectHandicapped . ')' ) );
	} 
	
	$select ->where( 'c.active = ?', 1 )
		->where( 'c.hired = ?', 0 )
		->group( 'id_perdata' )
		->order( array( 'date_registration' ) );

	return $dbJobVacancy->fetchAll( $select );
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listManual( array $filters = array() )
    {
	$select = $this->_selectMatchClient( empty( $filters['fk_id_jobvacancy']  ) ? 0 : $filters['fk_id_jobvacancy'] );
	
	$dbAddress = App_Model_DbTable_Factory::get( 'AddAddress' );
	$dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity_has_PerTypeScholarity' );
	$dbPerLanguage = App_Model_DbTable_Factory::get( 'PerLanguage_has_PerLevelKnowledge' );
	$dbPerExperience = App_Model_DbTable_Factory::get( 'PerExperience' );
	$dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbPerDocument = App_Model_DbTable_Factory::get( 'PerData_has_PerDocument' );
	$dbProfOcupation = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	
	if ( !empty( $filters['fk_id_adddistrict'] ) || !empty( $filters['fk_id_addsubdistrict'] ) ) {
	    $select->join(
			array( 'ad' => $dbAddress ),
			'ad.fk_id_perdata = c.id_perdata',
			array()
		    );
	    
	    if ( !empty( $filters['fk_id_adddistrict'] ) )
		$select->where( 'ad.fk_id_adddistrict = ?', $filters['fk_id_adddistrict'] );
	    
	    if ( !empty( $filters['fk_id_addsubdistrict'] ) )
		$select->where( 'ad.fk_id_addsubdistrict = ?', $filters['fk_id_addsubdistrict'] );
	}
	
	// Gender
	if ( !empty( $filters['gender'] ) )
	    $select->where( 'LEFT(c.gender, 1) = ?', $filters['gender'] );
	
	// Minimum Age
	if ( !empty( $filters['minimum_age'] ) )
	    $select->having( 'age >= ?', $filters['minimum_age'] );
	
	// Maximum Age
	if ( !empty( $filters['maximum_age'] ) )
	    $select->having( 'age <= ?', $filters['maximum_age'] );
	
	// Driver license
	if ( !empty( $filters['drive_licence'] ) ) {
	 
	    $select->join(
			array( 'pd' => $dbPerDocument ),
			'pd.fk_id_perdata = c.id_perdata',
			array()
		    )
		    ->where( 'pd.fk_id_pertypedocument = ?', 4 );
	}
	
	// Minimal Experience and the Profesional Occupation
	if ( !empty( $filters['fk_id_profocupation'] ) ) {
	    
	    $selectOcupation = $dbProfOcupation->select()
						->setIntegrityCheck( false )
						->from( array( 'pot1' => $dbProfOcupation ) )
						->where( 'pot1.fk_id_profocupation = pot.fk_id_profocupation' )
						->where( 'pot1.id_profocupationtimor = ?', $filters['fk_id_profocupation'] );
			
	    $select->join(
			array( 'xp' => $dbPerExperience ),
			'xp.fk_id_perdata = c.id_perdata',
			array()
		    )
		    ->join(
			    array( 'pot' => $dbProfOcupation ),
			    'pot.id_profocupationtimor = xp.fk_id_profocupation',
			    array()
		    )
		    ->where( 'EXISTS (?)', new Zend_Db_Expr( '(' . $selectOcupation . ')' ) );
	    
	    if ( !empty( $filters['minimum_experience'] ) )
		$select->where( 'xp.experience_year >= ?', $filters['minimum_experience'] );
	}
	
	// Search By Language
	if ( !empty( $filters['fk_id_perlanguage'] ) ) {
	    
	    $select->join(
			array( 'pl' => $dbPerLanguage ),
			'pl.fk_id_perdata = c.id_perdata',
			array()
		    );
	    
	    $select->where( 'pl.fk_id_perlanguage IN (?)', $filters['fk_id_perlanguage'] );
	}
	
	// Non Formal Scholarity
	if ( !empty( $filters['fk_id_training'] ) ) {
	    
	    $select->join(
			array( 'psf' => $dbPerScholarity ),
			'psf.fk_id_perdata = c.id_perdata',
			array()
		    );
	    
	    $select->where( 'psf.fk_id_perscholarity IN (?)', $filters['fk_id_training'] );
	}
	
	// Search By Scholarity
	if ( !empty( $filters['category'] ) || !empty( $filters['fk_id_perscholarity'] ) ) {
	
	    $select->join(
			array( 'ps' => $dbPerScholarity ),
			'ps.fk_id_perdata = c.id_perdata',
			array()
		    )
		    ->join(
			array( 'sp' => $dbScholarity ),
			'sp.id_perscholarity = ps.fk_id_perscholarity',
			array()
		    );
	    
	    if ( !empty( $filters['category'] ) )
		$select->where( 'sp.category = ?', $filters['category'] );
	    
	    if ( !empty( $filters['fk_id_perscholarity'] ) )
		$select->where( 'ps.fk_id_perscholarity = ?', $filters['fk_id_perscholarity'] );
	}
	
	$select ->where( 'c.active = ?', 1 )
		->where( 'c.hired = ?', 0 )
		->group( 'id_perdata' )
		->order( array( 'date_registration' ) );
	
	return $dbAddress->fetchAll( $select );
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listDirect( array $filters = array() )
    {
	$select = $this->_selectMatchClient( $filters['fk_id_jobvacancy'] );
	
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
		->where( 'c.hired = ?', 0 )
		->group( 'id_perdata' )
		->order( array( 'date_registration' ) );
	
	return $dbPerData->fetchAll( $select );
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
	
	$dbShortlist = App_Model_DbTable_Factory::get( 'ShortlistVacancy' );
	$dbHired = App_Model_DbTable_Factory::get( 'Hired' );
	
	$select->join(
		    array( 'sl' => $dbShortlist ),
		    'sl.fk_id_perdata = c.id_perdata',
		    array( 'selected' )
		)
		->joinLeft(
		    array( 'h' => $dbHired ),
		    'h.fk_id_perdata = c.id_perdata
		    AND h.fk_id_jobvacancy = sl.fk_id_jobvacancy',
		    array( 'hired' => 'id_relationship' )
		)
		->where( 'sl.fk_id_jobvacancy = ?', $id )
		->group( 'id_perdata' )
		->order( array( 'first_name', 'last_name' ) );
	
	return $dbShortlist->fetchAll( $select );
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
	
	$dbCandidates = App_Model_DbTable_Factory::get( 'JOBVacancy_Candidates' );
	$select->join(
		    array( 'lc' => $dbCandidates ),
		    'lc.fk_id_perdata = c.id_perdata',
		    array( 'shortlisted' )
		)
		->where( 'lc.fk_id_jobvacancy = ?', $id )
		->group( 'id_perdata' )
		->order( array( 'first_name', 'last_name' ) );
	
	return $dbCandidates->fetchAll( $select );
    }
    
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $form = Job_Form_Match::ID, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::JOB,
	    'fk_id_sysform'	    => $form,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}