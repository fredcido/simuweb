<?php

class Register_Model_Mapper_EducationInstitute extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_FefpEduInstitution
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FefpEduInstitution();

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
	   
	    $row = $this->_checkInstitution( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Instituisaun Ensinu iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( !$this->_checkUserInstitution( $this->_data ) )
		return false;
	    
	    $dateVisit = new Zend_Date( $this->_data['date_visit'] );
	    $dateRegistration = new Zend_Date( $this->_data['date_registration'] );
	    
	    $this->_data['date_visit'] = $dateVisit->toString( 'yyyy-MM-dd' );
	    $this->_data['date_registration'] = $dateRegistration->toString( 'yyyy-MM-dd' );
	    
	    if ( empty( $this->_data['id_fefpeduinstitution'] ) ) {
		
		
		$this->_data['fk_user_registration'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$history = 'INSERE INSTITUISAUN ENSINU: %s DADUS PRINCIPAL - INSERE NOVO INSTITUISAUN ENSINU';
		
	    } else {
		
		unset( $this->_data['fk_id_dec'] );
		$history = 'ALTERA INSTITUISAUN ENSINU: %s DADUS PRINCIPAL - ALTERA INSTITUISAUN ENSINU';
	    }
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['institution'] );
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
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detailEducationInstitution( $id )
    {
	$dbEduInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$dbTypeInstitution = App_Model_DbTable_Factory::get( 'TypeInstitution' );
	
	$select = $dbEduInstitution->select()
				    ->from( array( 'ei' => $dbEduInstitution ) )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 'c' => $dbDec ),
					'c.id_dec = ei.fk_id_dec',
					array( 'name_dec' )
				    )
				    ->join(
					array( 'ti' => $dbTypeInstitution ),
					'ti.id_typeinstitution = ei.fk_typeinstitution',
					array( 'type_institution' )
				    )
				    ->where( 'ei.id_fefpeduinstitution = ?', $id );
	
	return $dbEduInstitution->fetchRow( $select );
    }
    
    /**
     *
     * @param array $data
     * @return boolean 
     */
    protected function _checkUserInstitution( $data )
    {
	if ( empty( $data['fk_id_sysuser'] ) )
	    return true;
	
	$select = $this->_dbTable->select()->where( 'fk_id_sysuser = ?', $data['fk_id_sysuser'] );
	
	if ( !empty( $data['id_fefpeduinstitution'] ) )
	    $select->where( 'id_fefpeduinstitution <> ?', $data['id_fefpeduinstitution'] );
	
	$row = $this->_dbTable->fetchRow( $select );
	if ( empty( $row ) )
	    return true;
	
	$this->_message->addMessage( 'Uzuariu iha ne\'e iha rejistu tiha ona iha Instituisaun Ensinu seluk.', App_Message::ERROR );
	$this->addFieldError( 'fk_id_sysuser' );
	return false;
    }
   
    /**
     *
     * @param array $data
     * @return App_Model_DbTable_Row_Abstract
     */
    protected function _checkInstitution()
    {
	$select = $this->_dbTable->select()->where( 'institution = ?', $this->_data['institution'] );

	if ( !empty( $this->_data['id_fefpeduinstitution'] ) )
	    $select->where( 'id_fefpeduinstitution <> ?', $this->_data['id_fefpeduinstitution'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveContact()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	    $dbInstitutionContact = App_Model_DbTable_Factory::get( 'FefpEduInstitution_has_PerContact' );
	    
	    $dataForm = $this->_data;
	    
	    $id = parent::_simpleSave( $dbContact, false );
	
	    if ( empty( $dataForm['id_percontact'] ) ) {
		
		$row = $dbInstitutionContact->createRow();
		$row->fk_id_fefpeduinstitution = $dataForm['fk_id_fefpeduinstitution'];
		$row->fk_id_percontact = $id;
		$row->save();
		
		$history = 'INSERE CONTACT OF INSTITUISAUN ENSINU: %s-%s DADUS PRINCIPAL - INSERE NOVO CONTACT OF INSTITUISAUN ENSINU';
		
	    } else
		$history = 'ALTERA CONTACT OF INSTITUISAUN ENSINU: %s-%s DADUS PRINCIPAL - ALTERA INSTITUISAUN ENSINU';
	    
	    $history = sprintf( $history, $id, $dataForm['fk_id_fefpeduinstitution'] );
	    $this->_sysAudit( $history, Register_Form_EducationInstitutionContact::ID );
	    
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
    public function deleteContact()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	    $dbInstitutionContact = App_Model_DbTable_Factory::get( 'FefpEduInstitution_has_PerContact' );
	    
	    $where = array(
		$dbAdapter->quoteInto( 'fk_id_fefpeduinstitution = ?', $this->_data['id'] ),
		$dbAdapter->quoteInto( 'fk_id_percontact = ?', $this->_data['id_contact'] )
	    );
	    
	    $dbInstitutionContact->delete( $where );
	    
	   $where = $dbAdapter->quoteInto( 'id_percontact = ?', $this->_data['id_contact'] );
	    $dbContact->delete( $where );
	    
	    $history = 'DELETA CONTACT INSTITUISAUN ENSINU: %s DADUS PRINCIPAL - DELETA CONTACT INSTITUISAUN ENSINU';
	    
	    $history = sprintf( $history, $this->_data['id_contact'] );
	    $this->_sysAudit( $history, Register_Form_EducationInstitutionContact::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
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
     * @return int|bool
     */
    public function deleteCourse()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbInstitutionScholarity = App_Model_DbTable_Factory::get( 'FefpEduInstitution_has_PerScholarity' );
	    
	    $where = array(
		$dbAdapter->quoteInto( 'fk_id_fefpeduinstitution = ?', $this->_data['id'] ),
		$dbAdapter->quoteInto( 'fk_id_scholarity = ?', $this->_data['id_course'] )
	    );
	    
	    $dbInstitutionScholarity->delete( $where );
	    
	    $history = 'DELETA SCHOLARITY INSTITUISAUN ENSINU: %s DADUS PRINCIPAL - DELETA SCHOLARITY INSTITUISAUN ENSINU';
	    
	    $history = sprintf( $history, $this->_data['id_course'] );
	    $this->_sysAudit( $history, Register_Form_EducationInstitutionCourse::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
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
     * @return int|bool
     */
    public function deleteStaff()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	    
	    $where = array( $dbAdapter->quoteInto( 'id_staff = ?', $this->_data['id'] ) );
	    
	    $dbStaff->delete( $where );
	    
	    $history = 'DELETA STAFF OF INSTITUISAUN ENSINU: %s DADUS PRINCIPAL - DELETA STAFF OF INSTITUISAUN ENSINU';
	    
	    $history = sprintf( $history, $this->_data['id'] );
	    $this->_sysAudit( $history, Register_Form_EducationInstitutionStaff::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
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
     * @return int|bool
     */
    public function deleteQualification()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbStaff = App_Model_DbTable_Factory::get( 'TrainerQualifications' );
	    
	    $where = array( $dbAdapter->quoteInto( 'id_relationship = ?', $this->_data['id'] ) );
	    $dbStaff->delete( $where );
	    $history = 'DELETA KUALIFIKASAUM TREINADOR: %s';
	    
	    $history = sprintf( $history, $this->_data['id'] );
	    $this->_sysAudit( $history, Register_Form_EducationInstitutionQualification::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
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
     * @return int|bool
     */
    public function deleteAddress()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbAddAddress = App_Model_DbTable_Factory::get( 'AddAddress' );
	    
	    $where = array( $dbAdapter->quoteInto( 'id_addaddress = ?', $this->_data['id'] ) );
	    
	    $dbAddAddress->delete( $where );
	    
	    $history = 'DELETA ADDRESS OF INSTITUISAUN ENSINU: %s DADUS PRINCIPAL - DELETA ADDRESS OF INSTITUISAUN ENSINU';
	    
	    $history = sprintf( $history, $this->_data['id'] );
	    $this->_sysAudit( $history, Register_Form_EducationInstitutionAddress::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
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
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function fetchContact( $id )
    {
	$dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	return $dbContact->fetchRow( array( 'id_percontact = ?' => $id ) );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function fetchStaff( $id )
    {
	$dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	$dbClient = App_Model_DbTable_Factory::get( 'PerData' );
	$dbOccupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$dbInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbHandicapped = App_Model_DbTable_Factory::get( 'Handicapped' );
	
	$select = $dbStaff->select()
			  ->setIntegrityCheck( false )
			  ->from(
			    array( 's' => $dbStaff ),
			    array(
				'id_staff',
				'fk_id_perdata',
				'fk_id_fefpeduinstitution',
				'post',
				'position',
				'description'
			    )
			  )
			  ->join(
			    array( 'ei' => $dbInstitution ),
			    'ei.id_fefpeduinstitution = s.fk_id_fefpeduinstitution',
			    array( 'institution' )
			    )
			  ->join(
			    array( 'c' => $dbClient ),
			    'c.id_perdata = s.fk_id_perdata',
			    array(
				'staff_name' => new Zend_Db_Expr( "CONCAT( c.first_name, ' ', IFNULL(c.medium_name, ''), ' ', c.last_name )" ),
				'birth_date',
				'gender'     =>  new Zend_Db_Expr( 'SUBSTRING( c.gender, 1, 1)' ),
			    )
			  )
			  ->joinLeft(
			    array( 'hc' => $dbHandicapped ),
			    'hc.fk_id_perdata = c.id_perdata',
			    array( 'id_handicapped' )
			  )
			  ->join(
			    array( 'ot' => $dbOccupationTimor ),
			    's.position = ot.id_profocupationtimor',
			    array( 'ocupation_name_timor' )
			  )
			  ->where( 's.id_staff = ?', $id )
			  ->group( 's.id_staff' );
			  
	return $dbStaff->fetchRow( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function fetchAddress( $id )
    {
	$dbAddAddress = App_Model_DbTable_Factory::get( 'AddAddress' );
	return $dbAddAddress->fetchRow( array( 'id_addaddress = ?' => $id ) );
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
	    
	    $dbInstitutionScholarity = App_Model_DbTable_Factory::get( 'FefpEduInstitution_has_PerScholarity' );
	    
	    $dataForm = $this->_data;
	    
	    if ( empty( $dataForm['id_relationship'] ) ) {
		
		$where = array(
		    $dbAdapter->quoteInto( 'fk_id_fefpeduinstitution = ?', $this->_data['fk_id_fefpeduinstitution'] ),
		    $dbAdapter->quoteInto( 'fk_id_scholarity = ?', $this->_data['fk_id_scholarity'] ),
		);

		$row = $dbInstitutionScholarity->fetchRow( $where );

		if ( !empty( $row ) ) {

		    $this->_message->addMessage( 'Kurso iha tiha ona ba Instituisaun Ensinu ne\'e.', App_Message::ERROR );
		    return false;
		}
		
		$history = 'INSERE KURSU OF INSTITUISAUN ENSINU: %s-%s DADUS PRINCIPAL - INSERE NOVO KURSU OF INSTITUISAUN ENSINU';
		
	    } else
		$history = 'ALTERA KURSU OF INSTITUISAUN ENSINU: %s-%s DADUS PRINCIPAL - ALTERA INSTITUISAUN KURSU';
	    
	    $id = parent::_simpleSave( $dbInstitutionScholarity, false );
	    
	    $history = sprintf( $history, $id, $dataForm['fk_id_fefpeduinstitution'] );
	    $this->_sysAudit( $history, Register_Form_EducationInstitutionCourse::ID );
	    
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
    public function saveStaff()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	    
	    $dataForm = $this->_data;
	    
	    $mapperClient = new Client_Model_Mapper_Client();
	    $client = $mapperClient->detailClient( $dataForm['fk_id_perdata'] );
	    
	    $this->_data['birth_date'] = $client->birth_date;
	    $this->_data['gender'] = substr( $client->gender, 0, 1 );
	    
	    //$dateBirth = new Zend_Date( $this->_data['birth_date'] );
	    //$this->_data['birth_date'] = $dateBirth->toString( 'yyyy-MM-dd' );
	    
	    if ( empty( $dataForm['id_staff'] ) )
		$history = 'INSERE STAFF OF INSTITUISAUN ENSINU: %s - %s - %s DADUS PRINCIPAL - INSERE NOVO STAFF OF INSTITUISAUN ENSINU';
	    else
		$history = 'ALTERA STAFF OF INSTITUISAUN ENSINU: %s - %s - %s DADUS PRINCIPAL - ALTERA STAFF OF INSTITUISAUN ENSINU';
	    
	    $id = parent::_simpleSave( $dbStaff, false );
	    
	    $history = sprintf( $history, $dataForm['staff_name'], $id, $dataForm['fk_id_fefpeduinstitution'] );
	    $this->_sysAudit( $history, Register_Form_EducationInstitutionStaff::ID );
	    
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
    public function saveQualification()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbTrainerQualifications = App_Model_DbTable_Factory::get( 'TrainerQualifications' );
	    
	    $dataForm = $this->_data;
	    $this->_data['fk_id_perscholarity'] = $this->_data['fk_id_perscholarity_staff'];
	 
	    $history = 'INSERE KUALIFIKASAUN %s BA TREINADOR %s IHA INSTITUISAUN DE ENSINU: - %s ';
	    
	    $id = parent::_simpleSave( $dbTrainerQualifications, false );
	    
	    $history = sprintf( $history, $dataForm['fk_id_perscholarity_staff'], $dataForm['fk_id_staff'], $dataForm['fk_id_fefpeduinstitution'] );
	    $this->_sysAudit( $history, Register_Form_EducationInstitutionQualification::ID );
	    
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
    public function saveAddress()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbAddAddress = App_Model_DbTable_Factory::get( 'AddAddress' );
	    
	    $dataForm = $this->_data;
	    
	    $startDate = new Zend_Date( $this->_data['start_date'] );
	    $this->_data['start_date'] = $startDate->toString( 'yyyy-MM-dd' );
	    
	    $finishDate = new Zend_Date( $this->_data['finish_date'] );
	    $this->_data['finish_date'] = $finishDate->toString( 'yyyy-MM-dd' );
	    
	    if ( empty( $dataForm['id_addaddress'] ) )
		$history = 'INSERE ADDRESS OF INSTITUISAUN ENSINU: %s - %s DADUS PRINCIPAL - INSERE NOVO ADDRESS OF INSTITUISAUN ENSINU';
	    else
		$history = 'ALTERA ADDRESS OF INSTITUISAUN ENSINU:  %s - %s DADUS PRINCIPAL - ALTERA ADDRESS OF INSTITUISAUN ENSINU';
	    
	    $id = parent::_simpleSave( $dbAddAddress, false );
	    
	    $history = sprintf( $history, $dataForm['fk_id_fefpeduinstitution'], $id );
	    $this->_sysAudit( $history, Register_Form_EducationInstitutionAddress::ID );
	    
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
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $form = Register_Form_EducationInstitutionInformation::ID, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::REGISTER,
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
    public function getSelectEducationInstitute()
    {
	$dbEducationInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbInstitutionCourse = App_Model_DbTable_Factory::get( 'FefpEduInstitution_has_PerScholarity' );
	$dbAddress = App_Model_DbTable_Factory::get( 'AddAddress' );
	$dbTypeInstitution = App_Model_DbTable_Factory::get( 'TypeInstitution' );
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$dbInstitutionContact = App_Model_DbTable_Factory::get( 'FefpEduInstitution_has_PerContact' );
	$dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	
	$select = $dbEducationInstitution->select()
					 ->from( array( 'ei' => $dbEducationInstitution ) )
					 ->setIntegrityCheck( false )
					 ->joinLeft(
					    array( 'ad' => $dbAddress ),
					    'ad.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
					    array(
						'date_visit_formated' => new Zend_Db_Expr( 'DATE_FORMAT( ei.date_visit, "%d/%m/%Y" )' )
					    )
					 )
					 ->join(
					    array( 'ti' => $dbTypeInstitution ),
					    'ti.id_typeinstitution = ei.fk_typeinstitution',
					    array( 'type_institution' )
					 )
					 ->joinLeft(
					    array( 'ic' => $dbInstitutionCourse ),
					    'ic.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
					    array()
					 )
					 ->join(
					    array( 'ce' => $dbDec ),
					    'ce.id_dec = ei.fk_id_dec',
					    array( 'name_dec' )
					 )
					 ->joinLeft(
					    array( 'icc' => $dbInstitutionContact ),
					    'icc.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
					    array()
					 )
					 ->joinLeft(
					    array( 'c' => $dbContact ),
					    'icc.fk_id_percontact = c.id_percontact',
					    array(
						'contact_name',
						'cell_fone',
						'email'
					    )
					 )
					 ->order( array( 'institution' ) )
					 ->group( array( 'id_fefpeduinstitution' ) );
	
	return $select;
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset 
     */
    public function listByFilters( $filters = array() )
    {
	$select = $this->getSelectEducationInstitute();
	
	$dbEducationInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	
	if ( !empty( $filters['institution'] ) )
	    $select->where( 'ei.institution LIKE ?', '%' . $filters['institution'] . '%' );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'ei.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	if ( !empty( $filters['fk_typeinstitution'] ) )
	    $select->where( 'ei.fk_typeinstitution = ?', $filters['fk_typeinstitution'] );
	
	if ( !empty( $filters['register'] ) )
	    $select->where( 'ei.register = ?', $filters['register'] );
	
	if ( !empty( $filters['fk_id_perscholarity'] ) )
	    $select->where( 'ic.fk_id_scholarity = ?', $filters['fk_id_perscholarity'] );
	
	if ( !empty( $filters['fk_id_adddistrict'] ) )
	    $select->where( 'ad.fk_id_adddistrict = ?', $filters['fk_id_adddistrict'] );
	
	if ( !empty( $filters['fk_id_pertypescholarity'] ) ) {
	    
	    $dbEduInsitutionScholarity = App_Model_DbTable_Factory::get( 'FefpEduInstitution_has_PerScholarity' );
	    $dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	    
	    $select->join(
			array( 'es' => $dbEduInsitutionScholarity ),
			'es.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
			array()
		    )
		    ->join(
			    array( 'sc' => $dbScholarity ),
			    'sc.id_perscholarity = es.fk_id_scholarity',
			    array()
		    )
		    ->where( 'sc.fk_id_pertypescholarity = ?', $filters['fk_id_pertypescholarity'] );
	}
	
	$select->group( array( 'id_fefpeduinstitution' ) );
	
	return $dbEducationInstitution->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listContacts( $id )
    {
	$dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	$dbInstitutionContact = App_Model_DbTable_Factory::get( 'FefpEduInstitution_has_PerContact' );
	
	$select = $dbContact->select()
			    ->from( array( 'c' => $dbContact ) )
			    ->setIntegrityCheck( false )
			    ->join(
				array( 'ic' => $dbInstitutionContact ),
				'ic.fk_id_percontact = c.id_percontact',
				array()
			    )
			    ->where( 'ic.fk_id_fefpeduinstitution = ?', $id )
			    ->order( array( 'c.contact_name' ) );
	
	return $dbContact->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset 
     */
    public function listCourses( $id )
    {
	$dbInstitutionCourse = App_Model_DbTable_Factory::get( 'FefpEduInstitution_has_PerScholarity' );
	$dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbTypeScholarity = App_Model_DbTable_Factory::get( 'PerTypeScholarity' );
	
	$select = $dbInstitutionCourse->select()
				      ->from( array ( 'ic' => $dbInstitutionCourse ) )
				      ->setIntegrityCheck( false )
				      ->join(
					array( 's' => $dbScholarity ),
					's.id_perscholarity = ic.fk_id_scholarity',
					array( 'scholarity', 'category', 'external_code' )
				      )
				      ->join(
					array( 'ts' => $dbTypeScholarity ),
					'ts.id_pertypescholarity = s.fk_id_pertypescholarity',
					array( 'type_scholarity' )
				      )
				      ->where( 'ic.fk_id_fefpeduinstitution = ?', $id )
				      ->order( array( 'ic.id_relationship' ) );
	
	return $dbInstitutionCourse->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row 
     */
    public function fetchCourse( $id )
    {
	$dbInstitutionCourse = App_Model_DbTable_Factory::get( 'FefpEduInstitution_has_PerScholarity' );
	$dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbTypeScholarity = App_Model_DbTable_Factory::get( 'PerTypeScholarity' );
	
	$select = $dbInstitutionCourse->select()
				      ->from( array ( 'ic' => $dbInstitutionCourse ) )
				      ->setIntegrityCheck( false )
				      ->join(
					array( 's' => $dbScholarity ),
					's.id_perscholarity = ic.fk_id_scholarity',
					array( 'scholarity', 'fk_id_pertypescholarity', 'category' )
				      )
				      ->join(
					array( 'ts' => $dbTypeScholarity ),
					'ts.id_pertypescholarity = s.fk_id_pertypescholarity',
					array( 'type_scholarity' )
				      )
				      ->where( 's.id_perscholarity = ?', $id );
	
	return $dbInstitutionCourse->fetchRow( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listStaff( $id )
    {
	$dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	$dbClient = App_Model_DbTable_Factory::get( 'PerData' );
	$dbOccupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	
	$select = $dbStaff->select()
			  ->setIntegrityCheck( false )
			  ->from(
			    array( 's' => $dbStaff ),
			    array(
				'id_staff',
				'fk_id_perdata',
				'post',
				'position',
				'description'
			    )
			  )
			  ->join(
			    array( 'c' => $dbClient ),
			    'c.id_perdata = s.fk_id_perdata',
			    array(
				'staff_name' => new Zend_Db_Expr( "CONCAT( c.first_name, ' ', IFNULL(c.medium_name, ''), ' ', c.last_name )" ),
				'birth_date',
				'gender'     =>  new Zend_Db_Expr( 'SUBSTRING( c.gender, 1, 1)' ),
			    )
			  )
			  ->join(
			    array( 'ot' => $dbOccupationTimor ),
			    's.position = ot.id_profocupationtimor',
			    array( 'ocupation_name_timor' )
			  )
			  ->where( 's.fk_id_fefpeduinstitution = ?', $id )
			  ->order( array( 'staff_name' ) );
	
	return $dbStaff->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listAddress( $id )
    {
	$dbAddAddress = App_Model_DbTable_Factory::get( 'AddAddress' );
	$dbAddCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$dbAddDistrict = App_Model_DbTable_Factory:: get( 'AddDistrict' );
	$dbAddSubDistrict = App_Model_DbTable_Factory::get( 'AddSubDistrict' );
	$dbAddSucu = App_Model_DbTable_Factory::get( 'AddSucu' );
	
	$select = $dbAddAddress->select()
				->from( array( 'a' => $dbAddAddress ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'c' => $dbAddCountry ),
				    'c.id_addcountry = a.fk_id_addcountry',
				    array( 'country' )
				)
				->join(
				    array( 'd' => $dbAddDistrict ),
				    'd.id_adddistrict = a.fk_id_adddistrict',
				    array( 'District' )
				)
				->join(
				    array( 's' => $dbAddSubDistrict ),
				    's.id_addsubdistrict = a.fk_id_addsubdistrict',
				    array( 'sub_district' )
				)
				->join(
				    array( 'k' => $dbAddSucu ),
				    'k.id_addsucu = a.fk_id_addsucu',
				    array( 'sucu' )
				)
				->where( 'a.fk_id_fefpeduinstitution = ?', $id )
				->order( array( 'id_addaddress' ) );
	
	return $dbAddAddress->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listQualification( $id )
    {
	$dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	$dbTrainerQualifications = App_Model_DbTable_Factory::get( 'TrainerQualifications' );
	$dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	
	$select = $dbPerScholarity->select()
				  ->from( array( 's' => $dbPerScholarity ) )
				  ->setIntegrityCheck( false )
				  ->join(
				    array( 'tq' => $dbTrainerQualifications ),
				    'tq.fk_id_perscholarity = s.id_perscholarity',
				    array( 'id_relationship' )
				  )
				  ->join(
				    array( 'st' => $dbStaff ),
				    'tq.fk_id_staff = st.id_staff',
				    array( 'staff_name' )
				  )
				  ->where( 'st.fk_id_fefpeduinstitution = ?', $id );
	
	return $dbPerScholarity->fetchAll( $select );
    }
}