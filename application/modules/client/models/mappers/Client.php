<?php

class Client_Model_Mapper_Client extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_PerData
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_PerData();

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
	    
	    $dataForm = $this->_data;
	    	    
	    $dateBirth = new Zend_Date( $this->_data['birth_date'] );
	    $age = App_General_Date::getAge( $dateBirth );
	    	    
	    if ( $age < 14 ) {
		
		$this->_message->addMessage( 'Erro: Kliente iha jovem liu ba heta rejistrasaum.', App_Message::ERROR );
		$this->addFieldError( 'birth_date' );
		return false;
	    }
	    
	    if ( $age > 110 ) {
		
		$this->_message->addMessage( 'Erro: Kliente iha katuas liu ba heta rejistrasaum.', App_Message::ERROR );
		$this->addFieldError( 'birth_date' );
		return false;
	    }
	    
	    if ( !$this->_validateName() ) {
		
		$this->_message->addMessage( 'Erro: Kliente naran tiha ona.', App_Message::ERROR );
		$this->addFieldError( 'first_name' )->addFieldError( 'last_name' );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_perdata'] ) ) {
		
		$dateRegistration = new Zend_Date( $this->_data['date_registration'] );
		$dateNow = new Zend_Date();
	    
		if ( $dateRegistration->isLater( $dateNow ) ) {

		    $this->_message->addMessage( 'Erro: Data Rejistu depois ho data ohin', App_Message::ERROR );
		    $this->addFieldError( 'date_registration' );
		    return false;
		}
		
		$districtMapper = new Register_Model_Mapper_AddDistrict();
		$district = $districtMapper->fetchRow( $this->_data['fk_id_adddistrict'] );
	    
		$this->_data['num_year'] = date( 'y' );
		$this->_data['num_district'] = $district->acronym;
		$this->_data['num_servicecode'] = 'BU';
		$this->_data['num_sequence'] = str_pad( $this->_getNumSequence( $this->_data ), 4, '0', STR_PAD_LEFT );
		$this->_data['date_registration'] = $dateRegistration->toString( 'yyyy-MM-dd' );
		
		$history = 'REJISTRU KLIENTE: %s HAKAT 1 - REJISTRU DADOS OBRIGATORIU HUSI KLIENTE';
		
		// Prepare the data to the history
		$dataHistory = array(
		    'action'	    => 'KLIENTE HALO REJISTRU CEOP',
		    'description'   => 'REJISTRU DADOS OBRIGATORIU HUSI KLIENTE',
		);
		
	    } else {
		
		unset( 
		    $this->_data['date_registration'], 
		    $this->_data['fk_id_adddistrict'],
		    $this->_data['num_subdistrict']
		);
		
		$history = 'ATUALIZA KLIENTE: %s DADUS PRINCIPAL - ATUALIZA DADOS OBRIGATORIU HUSI KLIENTE';
		
		// Prepare the data to the history
		$dataHistory = array(
		    'action'		=> 'ATUALIZA KLIENTE DADUS PRINCIPAL',
		    'description'	=> 'ATUALIZA DADOS OBRIGATORIU HUSI KLIENTE',
		);
	    }
	    
	    $this->_data['birth_date'] = $dateBirth->toString( 'yyyy-MM-dd' );
	    
	    // Save the Client
	    $id = parent::_simpleSave();
	    
	    // If it is inserting the client and it has document
	    if ( empty( $dataForm['id_perdata'] ) && !empty( $dataForm['fk_id_pertypedocument'] ) ) {
		
		$dataForm['fk_id_perdata'] = $id;
		$this->_saveDocument( $dataForm );
	    }
	    
	    // Set the missing data to the history
	    $dataHistory['fk_id_perdata'] = $id;
	    $dataHistory['fk_id_dec'] = $dataForm['fk_id_dec'];
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function saveDocument()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    // Check if the document is already saved
	    $row = $this->_checkDocument( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'ERRO, KLIENTE NE\'E IHA REJISTU DOKUMENTU HANESAN TIHA ONA.', App_Message::ERROR );
		return false;
	    }
	    
	    // Save the document
	    $id = $this->_saveDocument( $this->_data );
	    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['fk_id_perdata'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'ATUALIZA DOCUMENTU KLIENTE',
		'description'	=> 'ATUALIZA DOCUMENTU KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
	    $history = 'ATUALIZA  DOCUMENTU KLIENTE: %s - ATUALIZA DOCUMENTU HUSI KLIENTE';
	    
	    // Save the audit
	    $history = sprintf( $history, $this->_data['fk_id_perdata'] );
	    $this->_sysAudit( $history, Client_Form_ClientDocument::ID );
	    
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
	 
	    $id = parent::_simpleSave( $dbAddAddress, false );
	    
	    // Save the auditing
	    $history = 'ATUALIZA HELA FATIN KLIENTE: %s  - ATUALIZA HELA FATIN HUSI KLIENTE';
	    $history = sprintf( $history, $this->_data['fk_id_perdata'] );
	    $this->_sysAudit( $history, Client_Form_ClientAddress::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['fk_id_perdata'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'ATUALIZA HELA FATIN KLIENTE',
		'description'	=> 'ATUALIZA HELA FATIN KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function saveBank()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbBankAccount = App_Model_DbTable_Factory::get( 'BankAccount' );
	    $dbPerBankAccount = App_Model_DbTable_Factory::get( 'PerData_has_BankAccount' );
	 
	    $dataForm = $this->_data;
	    
	    $id = parent::_simpleSave( $dbBankAccount, false );
	    
	    $this->_data = array(
		'fk_id_perdata'	    => $dataForm['fk_id_perdata'],
		'fk_id_bankaccount' => $id
	    );
	    
	    parent::_simpleSave( $dbPerBankAccount, false );
	    
	    // Save the auditing
	    $history = 'ATUALIZA KONTA BANKU KLIENTE: %s  - ATUALIZA KONTA BANKU KLIENTE';
	    $history = sprintf( $history, $this->_data['fk_id_perdata'] );
	    $this->_sysAudit( $history, Client_Form_ClientBank::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['fk_id_perdata'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'ATUALIZA KONTA BANKU KLIENTE',
		'description'	=> 'ATUALIZA KONTA BANKU KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function saveVisit()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbPerVisit = App_Model_DbTable_Factory::get( 'PerVisitPurpose' );
	    
	    $date = new Zend_Date( $this->_data['visit_date'] );
	    $this->_data['visit_date'] = $date->toString( 'yyyy-MM-dd' );
	 
	    $id = parent::_simpleSave( $dbPerVisit, false );
	    
	    // Save the auditing
	    $history = 'ATUALIZA OBJETIVU VISITA HUSI KLIENTE: %s  - ATUALIZA OBJETIVU VISITA HUSI KLIENTE';
	    $history = sprintf( $history, $this->_data['fk_id_perdata'] );
	    $this->_sysAudit( $history, Client_Form_ClientVisit::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['fk_id_perdata'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'ATUALIZA OBJETIVU VISITA',
		'description'	=> 'ATUALIZA OBJETIVU VISITA'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
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
    public function saveFormalScholarity()
    {
	return $this->_saveScholarity();
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveNonFormalScholarity()
    {
	return $this->_saveScholarity();
    }
    
    /**
     *
     * @return boolean 
     */
    protected function _saveScholarity()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity_has_PerTypeScholarity' );
	    
	    $date = new Zend_Date( $this->_data['start_date'] );
	    $this->_data['start_date'] = $date->toString( 'yyyy-MM-dd' );
	    $this->_data['finish_date'] = $date->set( $this->_data['finish_date'] )->toString( 'yyyy-MM-dd' );
	    
	    $id = parent::_simpleSave( $dbPerScholarity, false );
	    
	    // Save the auditing
	    $history = 'ATUALIZA REKURSU AKADEMIKU HUSI KLIENTE: %s  - ATUALIZA REKURSU AKADEMIKU HUSI KLIENTE';
	    $history = sprintf( $history, $this->_data['fk_id_perdata'] );
	    $this->_sysAudit( $history, Client_Form_ClientScholarity::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['fk_id_perdata'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'ATUALIZA REKURSU AKADEMIKU',
		'description'	=> 'ATUALIZA REKURSU AKADEMIKU'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function saveLanguage()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbPerLanguage = App_Model_DbTable_Factory::get( 'PerLanguage_has_PerLevelKnowledge' );
	    
	    $id = parent::_simpleSave( $dbPerLanguage, false );
	    
	    // Save the auditing
	    $history = 'ATUALIZA KOMPETENSIA LIAN FUAN HUSI KLIENTE: %s  - ATUALIZA KOMPETENSIA LIAN FUAN HUSI KLIENTE';
	    $history = sprintf( $history, $this->_data['fk_id_perdata'] );
	    $this->_sysAudit( $history, Client_Form_ClientLanguage::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['fk_id_perdata'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'ATUALIZA KOMPETENSIA LIAN FUAN',
		'description'	=> 'ATUALIZA KOMPETENSIA LIAN FUAN'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function saveKnowledge()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbPerKnowledge = App_Model_DbTable_Factory::get( 'PerKnowledge_has_PerLevelKnowledge' );
	    
	    $id = parent::_simpleSave( $dbPerKnowledge, false );
	    
	    // Save the auditing
	    $history = 'ATUALIZA KOMPETENSIA KOMPUTADOR HUSI KLIENTE: %s  - ATUALIZA KOMPETENSIA KOMPUTADOR HUSI KLIENTE';
	    $history = sprintf( $history, $this->_data['fk_id_perdata'] );
	    $this->_sysAudit( $history, Client_Form_ClientKnowledge::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['fk_id_perdata'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'ATUALIZA KOMPETENSIA KOMPUTADOR',
		'description'	=> 'ATUALIZA KOMPETENSIA KOMPUTADOR'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function saveExperience()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbPerExperience = App_Model_DbTable_Factory::get( 'PerExperience' );
	    
	    $date = new Zend_Date( $this->_data['start_date'] );
	    $this->_data['start_date'] = $date->toString( 'yyyy-MM-dd' );
	    $this->_data['finish_date'] = $date->set( $this->_data['finish_date'] )->toString( 'yyyy-MM-dd' );
	    $this->_data['post'] = '-';
	    
	    $id = parent::_simpleSave( $dbPerExperience, false );
	    
	    // Save the auditing
	    $history = 'ATUALIZA EXPERIENSIA PROFOSIONAL HUSI KLIENTE: %s  - ATUALIZA EXPERIENSIA PROFOSIONAL HUSI KLIENTE';
	    $history = sprintf( $history, $this->_data['fk_id_perdata'] );
	    $this->_sysAudit( $history, Client_Form_ClientExperience::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['fk_id_perdata'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'ATUALIZA EXPERIENSIA PROFOSIONAL',
		'description'	=> 'ATUALIZA EXPERIENSIA PROFOSIONAL'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function saveHandicapped()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbHandicapped = App_Model_DbTable_Factory::get( 'Handicapped' );
	    $id = parent::_simpleSave( $dbHandicapped, false );
	    
	   // Save the auditing
	    $history = 'ATUALIZA DEFICIENCIA HUSI KLIENTE: %s  - ATUALIZA DEFICIENCIA HUSI KLIENTE';
	    $history = sprintf( $history, $this->_data['fk_id_perdata'] );
	    $this->_sysAudit( $history, Client_Form_ClientHandicapped::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['fk_id_perdata'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'ATUALIZA DEFICIENCIA KLIENTE',
		'description'	=> 'ATUALIZA DEFICIENCIA KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function saveDependent()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbDependent = App_Model_DbTable_Factory::get( 'PerDependent' ); 
	    
	    $date = new Zend_Date( $this->_data['birth_date'] );
	    $this->_data['birth_date'] = $date->toString( 'yyyy-MM-dd' );
	    
	    $id = parent::_simpleSave( $dbDependent, false );
	    
	   // Save the auditing
	    $history = 'ATUALIZA DEPENDENTE FAMILIA HUSI KLIENTE: %s  - ATUALIZA DEPENDENTE FAMILIA HUSI KLIENTE';
	    $history = sprintf( $history, $this->_data['fk_id_perdata'] );
	    $this->_sysAudit( $history, Client_Form_ClientDependent::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['fk_id_perdata'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'ATUALIZA DEPENDENTE FAMILIA KLIENTE',
		'description'	=> 'ATUALIZA DEPENDENTE FAMILIA KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function saveContact()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	    $id = parent::_simpleSave( $dbContact, false );
	    
	   // Save the auditing
	    $history = 'ATUALIZA REFERENSIA HUSI KLIENTE: %s  - ATUALIZA REFERENSIA HUSI KLIENTE';
	    $history = sprintf( $history, $this->_data['fk_id_perdata'] );
	    $this->_sysAudit( $history, Client_Form_ClientContact::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['fk_id_perdata'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'ATUALIZA REFERENSIA KLIENTE',
		'description'	=> 'ATUALIZA REFERENSIA KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function saveAbout()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbAbout = App_Model_DbTable_Factory::get( 'AboutECGC' );
	    $id = parent::_simpleSave( $dbAbout, false );
	    
	   // Save the auditing
	    $history = 'ATUALIZA NE BE KLIENTE HATENE CEOP HUSI KLIENTE: %s  - ATUALIZA NE BE KLIENTE HATENE CEOP';
	    $history = sprintf( $history, $this->_data['fk_id_perdata'] );
	    $this->_sysAudit( $history, Client_Form_ClientAbout::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['fk_id_perdata'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'ATUALIZA NE BE KLIENTE HATENE CEOP',
		'description'	=> 'ATUALIZA NE BE KLIENTE HATENE CEOP'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function deleteAbout()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbAbout = App_Model_DbTable_Factory::get( 'AboutECGC' );
	    $where = array( $dbAdapter->quoteInto( 'id_aboutecgc = ?', $this->_data['id'] ) );
	    
	    $about = $dbAbout->fetchRow( $where );
	    $dbAbout->delete( $where );
	    
	    // Save the auditing
	    $history = 'HAMOS NE BE KLIENTE: %s  - HATENE CEOP';
	    $history = sprintf( $history, $about->fk_id_perdata );
	    $this->_sysAudit( $history, Client_Form_ClientAbout::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $about->fk_id_perdata,
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'DELETA NE BE KLIENTE HATENE CEOP',
		'description'	=> 'DELETA NE BE KLIENTE HATENE CEOP'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function deleteContact()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	    $where = array( $dbAdapter->quoteInto( 'id_percontact = ?', $this->_data['id'] ) );
	    
	    $contact = $dbContact->fetchRow( $where );
	    $dbContact->delete( $where );
	    
	    // Save the auditing
	    $history = 'HAMOS REFERENSIA KLIENTE: %s  - HAMOS REFERENSIA HUSI KLIENTE';
	    $history = sprintf( $history, $contact->fk_id_perdata );
	    $this->_sysAudit( $history, Client_Form_ClientContact::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $contact->fk_id_perdata,
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'DELETA REFERENSIA KLIENTE',
		'description'	=> 'DELETA REFERENSIA KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function deleteDependent()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbDependent = App_Model_DbTable_Factory::get( 'PerDependent' );
	    $where = array( $dbAdapter->quoteInto( 'id_perdependent = ?', $this->_data['id_dependent'] ) );
	    
	    $dependent = $dbDependent->fetchRow( $where );
	    $dbDependent->delete( $where );
	    
	    // Save the auditing
	    $history = 'HAMOS DEPENDENTE FAMILIA KLIENTE: %s  - HAMOS DEPENDENTE FAMILIA HUSI KLIENTE';
	    $history = sprintf( $history, $dependent->fk_id_perdata );
	    $this->_sysAudit( $history, Client_Form_ClientDependent::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $dependent->fk_id_perdata,
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'HAMOS DEPENDENTE FAMILIA KLIENTE',
		'description'	=> 'HAMOS DEPENDENTE FAMILIA KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function deleteHandicapped()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbHandicapped = App_Model_DbTable_Factory::get( 'Handicapped' );
	    $where = array( $dbAdapter->quoteInto( 'id_handicapped = ?', $this->_data['id'] ) );
	    
	    $handicapped = $dbHandicapped->fetchRow( $where );
	    $dbHandicapped->delete( $where );
	    
	    // Save the auditing
	    $history = 'HAMOS DEFICIENCIA KLIENTE: %s  - HAMOS DEFICIENCIA HUSI KLIENTE';
	    $history = sprintf( $history, $handicapped->fk_id_perdata );
	    $this->_sysAudit( $history, Client_Form_ClientHandicapped::ID  );
		    
	    $dataHistory = array(
		'fk_id_perdata'	=>  $handicapped->fk_id_perdata,
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'HAMOS DEFICIENCIA KLIENTE',
		'description'	=> 'HAMOS DEFICIENCIA KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function deleteVisit()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbPerVisti = App_Model_DbTable_Factory::get( 'PerVisitPurpose' );
	    $where = array( $dbAdapter->quoteInto( 'id_pervisitpurpose = ?', $this->_data['id_visit'] ) );
	    
	    $visit = $dbPerVisti->fetchRow( $where );
	    
	    $dbPerVisti->delete( $where );
	    
	    $history = 'HAMOS OBJETIVU VISITA KLIENTE: %s  - HAMOS OBJETIVU VISITA HUSI KLIENTE';
	    
	    $history = sprintf( $history, $visit->fk_id_perdata );
	    $this->_sysAudit( $history, Client_Form_ClientVisit::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
	     $dataHistory = array(
		'fk_id_perdata'	=>  $visit->fk_id_perdata,
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'HAMOS OBJETIVU VISITA KLIENTE',
		'description'	=> 'HAMOS OBJETIVU VISITA KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
	    
	    $address = $dbAddAddress->fetchRow( $where );
	    
	    $dbAddAddress->delete( $where );
	    
	    $history = 'HAMOS HELA FATIN KLIENTE: %s  - HAMOS HELA FATIN HUSI KLIENTE';
	    
	    $history = sprintf( $history, $address->fk_id_perdata );
	    $this->_sysAudit( $history, Client_Form_ClientAddress::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
	     $dataHistory = array(
		'fk_id_perdata'	=>  $address->fk_id_perdata,
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'HAMOS HELA FATIN KLIENTE',
		'description'	=> 'HAMOS HELA FATIN KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function deleteScholarity()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity_has_PerTypeScholarity' );
	    $where = array( $dbAdapter->quoteInto( 'id_relationship = ?', $this->_data['id_scholarity'] ) );
	    
	    $scholarity = $dbPerScholarity->fetchRow( $where );
	    
	    $dbPerScholarity->delete( $where );
	    
	    $history = 'HAMOS REKURSU AKADEMIKU KLIENTE: %s  - HAMOS REKURSU AKADEMIKU HUSI KLIENTE';
	    
	    $history = sprintf( $history, $scholarity->fk_id_perdata );
	    $this->_sysAudit( $history, Client_Form_ClientScholarity::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
	     $dataHistory = array(
		'fk_id_perdata'	=>  $scholarity->fk_id_perdata,
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'HAMOS REKURSU AKADEMIKU KLIENTE',
		'description'	=> 'HAMOS REKURSU AKADEMIKU KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function deleteLanguage()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbPerLanguage = App_Model_DbTable_Factory::get( 'PerLanguage_has_PerLevelKnowledge' );
	    
	    $where = array( $dbAdapter->quoteInto( 'id_relationship = ?', $this->_data['id'] ) );
	    
	    $language = $dbPerLanguage->fetchRow( $where );
	    
	    $dbPerLanguage->delete( $where );
	    
	    $history = 'HAMOS KOMPETENSIA LIAN FUAN KLIENTE: %s  - HAMOS KOMPETENSIA LIAN FUAN HUSI KLIENTE';
	    
	    $history = sprintf( $history, $language->fk_id_perdata );
	    $this->_sysAudit( $history, Client_Form_ClientLanguage::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
	     $dataHistory = array(
		'fk_id_perdata'	=>  $language->fk_id_perdata,
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'HAMOS KOMPETENSIA LIAN FUAN KLIENTE',
		'description'	=> 'HAMOS KOMPETENSIA LIAN FUAN KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function deleteKnowledge()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbPerKnowledge = App_Model_DbTable_Factory::get( 'PerKnowledge_has_PerLevelKnowledge' );
	    
	    $where = array( $dbAdapter->quoteInto( 'id_relationship = ?', $this->_data['id'] ) );
	    
	    $knowledge = $dbPerKnowledge->fetchRow( $where );
	    
	    $dbPerKnowledge->delete( $where );
	    
	    $history = 'HAMOS KOMPETENSIA KOMPUTADOR KLIENTE: %s  - HAMOS KOMPETENSIA KOMPUTADOR HUSI KLIENTE';
	    $history = sprintf( $history, $knowledge->fk_id_perdata );
	    $this->_sysAudit( $history, Client_Form_ClientLanguage::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
	     $dataHistory = array(
		'fk_id_perdata'	=>  $knowledge->fk_id_perdata,
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'HAMOS KOMPETENSIA KOMPUTADOR KLIENTE',
		'description'	=> 'HAMOS KOMPETENSIA KOMPUTADOR KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function deleteExperience()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbPerExperience = App_Model_DbTable_Factory::get( 'PerExperience' );
	    
	    $where = array( $dbAdapter->quoteInto( 'id_perexperience = ?', $this->_data['id'] ) );
	    
	    $experience = $dbPerExperience->fetchRow( $where );
	    $dbPerExperience->delete( $where );
	    
	    $history = 'HAMOS EXPERIENSIA PROFISIONAL KLIENTE: %s  - HAMOS EXPERIENSIA PROFISIONAL HUSI KLIENTE';
	    $history = sprintf( $history, $experience->fk_id_perdata );
	    $this->_sysAudit( $history, Client_Form_ClientExperience::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
	     $dataHistory = array(
		'fk_id_perdata'	=>  $experience->fk_id_perdata,
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'HAMOS EXPERIENSIA PROFISIONAL KLIENTE',
		'description'	=> 'HAMOS EXPERIENSIA PROFISIONAL KLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
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
     * @return App_Model_DbTable_Row_Abstract
     */
    protected function _checkDocument()
    {
	$dbDocument = App_Model_DbTable_Factory::get( 'PerDocument' );
	$dbPerDataDocument = App_Model_DbTable_Factory::get( 'PerData_has_PerDocument' );
	
	$select = $dbDocument->select()
			    ->setIntegrityCheck( false )
			    ->from(
				array( 'd' => $dbDocument ),
				array()
			    )
			    ->join(
				array( 'pd' => $dbPerDataDocument ),
				'pd.fk_id_perdocument = d.id_perdocument',
				array()
			    )
			    ->where( 'pd.fk_id_perdata = ?', $this->_data['fk_id_perdata'] )
			    ->where( 'd.number = ?', $this->_data['number'] )
			    ->where( 'DATE_FORMAT( d.issue_date, "%d/%m/%Y" ) = ?', $this->_data['issue_date'] )
			    ->where( 'd.fk_id_country = ?', $this->_data['fk_id_country'] );
	
	return $dbDocument->fetchRow( $select );
    }
    
    /**
     *
     * @param array $data
     * @return int 
     */
    protected function _saveDocument( $data )
    {
	$dbPerDocument = App_Model_DbTable_Factory::get( 'PerDocument' );
	$dbPerDataDocument = App_Model_DbTable_Factory::get( 'PerData_has_PerDocument' );
	
	$this->_data = $data;
	
	$dateIssue = new Zend_Date( $data['issue_date'] );
	$this->_data['issue_date'] = $dateIssue->toString( 'yyyy-MM-dd' );
	
	$id = parent::_simpleSave( $dbPerDocument );
	
	if ( empty( $data['fk_id_perdocument'] ) ) {
	    
	    $this->_data['fk_id_perdocument'] = $id;
	    parent::_simpleSave( $dbPerDataDocument );
	}
	
	return $id;
    }
    
    /**
     * 
     * @return int|bool
     */
    public function deleteDocument()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbDocument = App_Model_DbTable_Factory::get( 'PerDocument' );
	    $DbPerDataDocument = App_Model_DbTable_Factory::get( 'PerData_has_PerDocument' );
	    
	    $where = array(
		$dbAdapter->quoteInto( 'fk_id_perdocument = ?', $this->_data['id_document'] ),
		$dbAdapter->quoteInto( 'fk_id_perdata = ?', $this->_data['id'] )
	    );
	    
	    $DbPerDataDocument->delete( $where );
	    
	    $where = $dbAdapter->quoteInto( 'id_perdocument = ?', $this->_data['id_document'] );
	    $dbDocument->delete( $where );
	    
	    // Save the auditing
	    $history = 'HAMOS DOCUMENTU KLIENTE: %s  - DELETA DOCUMENTU HUSI KLIENTE';
	    
	    $history = sprintf( $history, $this->_data['id'] );
	    $this->_sysAudit( $history, Client_Form_ClientDocument::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
	     $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['id'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'HAMOS DOCUMENTU CLIENTE',
		'description'	=> 'HAMOS DOCUMENTU CLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
    public function deleteBank()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbBankAccount = App_Model_DbTable_Factory::get( 'BankAccount' );
	    $DbPerDataBank = App_Model_DbTable_Factory::get( 'PerData_has_BankAccount' );
	    
	    $where = array(
		$dbAdapter->quoteInto( 'fk_id_bankaccount = ?', $this->_data['id_bank'] ),
		$dbAdapter->quoteInto( 'fk_id_perdata = ?', $this->_data['id'] )
	    );
	    
	    $DbPerDataBank->delete( $where );
	    
	    $where = $dbAdapter->quoteInto( 'id_bankaccount = ?', $this->_data['id_bank'] );
	    $dbBankAccount->delete( $where );
	    
	    // Save the auditing
	    $history = 'HAMOS KONTA BANKU KLIENTE: %s  - DELETA KONTA BANKU HUSI KLIENTE';
	    
	    $history = sprintf( $history, $this->_data['id'] );
	    $this->_sysAudit( $history, Client_Form_ClientBank::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
	     $dataHistory = array(
		'fk_id_perdata'	=>  $this->_data['id'],
		'fk_id_dec'	=>  Zend_Auth::getInstance()->getIdentity()->fk_id_dec,
		'action'	=> 'HAMOS KONTA BANKU CLIENTE',
		'description'	=> 'HAMOS KONTA BANKU CLIENTE'
	    );
	    
	    // Save the client history
	    $this->_saveHistory( $dataHistory );
	    
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
     * @param array $data
     * @return string
     */
    protected function _getNumSequence( $data )
    {
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	
	$select = $dbPerData->select()
			   ->from ( 
				array( 'c' => $dbPerData ),
				array( 'num_sequence' => new Zend_Db_Expr( 'IFNULL( MAX( num_sequence ), 0 ) + 1' ) )
			    )
			   ->where( 'c.num_district = ?', $data['num_district'] )
			   ->where( 'c.num_subdistrict = ?', $data['num_subdistrict'] )
			   ->where( 'c.num_servicecode = ?', $data['num_servicecode'] )
			   ->where( 'c.num_year = ?', $data['num_year'] );
	
	$row = $dbPerData->fetchRow( $select );
	return $row->num_sequence;
    }
    
    /**
     *
     * @return boolean 
     */
    protected function _validateName()
    {
	$valid = false;
	
	try {
	    
	    $dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	    $dbAdapter = $dbPerData->getDefaultAdapter();
	    
	    $date = new Zend_Date( $this->_data['birth_date'] );
	    $formatedDateBirth = $date->toString( 'yyyy-MM-dd' );
	    
	    $where = array();
	    $where[] = $dbAdapter->quoteInto( 'first_name = ?', $this->_data['first_name'] );
	    $where[] = $dbAdapter->quoteInto( 'last_name = ?', $this->_data['last_name'] );
	    
	    if ( !empty( $this->_data['id_perdata'] ) )
		$where[] = $dbAdapter->quoteInto( 'id_perdata <> ?', $this->_data['id_perdata'] );
	    
	    $clients = $dbPerData->fetchAll( $where );
	    
	    foreach ( $clients as $client ) {
		
		if ( $client->birth_date == $formatedDateBirth ) {
		  
		    $this->addFieldError( $client->id_perdata, 'same_birth' );
		    return false;
		    
		} else {
		    
		    if ( empty( $this->_data['by_pass_name'] ) ) {
			
			$this->addFieldError( $client->id_perdata, 'same_name' );
			return false;
		    }
		}
	    }
	    
	    return true;
	    
	} catch ( Exception $e ) {
	    return $valid;
	}
    }
    
   /**
    *
    * @return Zend_Db_Select
    */
   public function selectClient()
   {
       $dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
       $dbDec = App_Model_DbTable_Factory::get( 'Dec' );
       $dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
       $dbLevelScholarity = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
       $dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity_has_PerTypeScholarity' );
       $dbActionPlan = App_Model_DbTable_Factory::get( 'Action_Plan' );
       $dbDocument = App_Model_DbTable_Factory::get( 'PerDocument' );
       $dbPerDataDocument = App_Model_DbTable_Factory::get( 'PerData_has_PerDocument' );
       
       $selectScholarity = $dbScholarity->select()
					->from( array( 's' => $dbScholarity ), array( 'scholarity' ) )
					->setIntegrityCheck( false )
					->join(
					    array( 'ps' => $dbPerScholarity ),
					    's.id_perscholarity = ps.fk_id_perscholarity',
					    array()
					)
					->where( 'ps.fk_id_perdata = c.id_perdata' )
					->where( 'ps.fk_id_pertypescholarity = 1' )
					->order( array( 's.max_level DESC' ) )
					->limit( 1 );
       
       $selectLevelScholarity = $dbScholarity->select()
					->from( array( 's' => $dbScholarity ), array() )
					->setIntegrityCheck( false )
					->join(
					    array( 'ps' => $dbPerScholarity ),
					    's.id_perscholarity = ps.fk_id_perscholarity',
					    array()
					)
					->join(
					    array( 'pls' => $dbLevelScholarity ),
					    's.fk_id_perlevelscholarity = pls.id_perlevelscholarity',
					    array( 'level_scholarity' )
					)
					->where( 'ps.fk_id_perdata = c.id_perdata' )
					->where( 'ps.fk_id_pertypescholarity = 1' )
					->order( array( 's.max_level DESC' ) )
					->limit( 1 );
		
       $select = $dbPerData->select()
			    ->from( 
				array( 'c' => $dbPerData ),
				array(
				    '*',
				    'date_registration_format'  => new Zend_Db_Expr( 'DATE_FORMAT( c.date_registration, "%d/%m/%Y" )' ),
				    'birth_date_format'		=> new Zend_Db_Expr( 'DATE_FORMAT( c.birth_date, "%d/%m/%Y" )' ),
				    'age'			=> new Zend_Db_Expr( 'FLOOR( DATEDIFF( CURRENT_DATE, c.birth_date ) / 365.25 )' ),
				    'month_registration'	=> 'MONTH( c.date_registration )',
				    'max_scholarity'		=> new Zend_Db_Expr( '(' . $selectScholarity . ')' ),
				    'max_level_scholarity'	=> new Zend_Db_Expr( '(' . $selectLevelScholarity . ')' ),
				    'evidence'			=> new Zend_Db_Expr( "CONCAT(c.num_district, '-', c.num_subdistrict, '-', c.num_servicecode, '-', c.num_year, '-', c.num_sequence)"),
				)
			    )
			    ->setIntegrityCheck( false )
			    ->joinLeft(
				array( 'ap' => $dbActionPlan ),
				"c.id_perdata = ap.fk_id_perdata AND ap.active = 1 AND ap.type = 'S'",
				array( 
				    'case' => 'active',
				    'id_action_plan'
				)
			    )
			    ->joinLeft(
				array( 'pdd' => $dbPerDataDocument ),
				'pdd.fk_id_perdata = c.id_perdata AND pdd.fk_id_pertypedocument = 2',
				array()
			    )
			    ->joinLeft(
				array( 'pd' => $dbDocument ),
				'pd.id_perdocument = pdd.fk_id_perdocument',
				array( 'electoral' => 'number' )
			    )
			    ->join(
				array( 'd' => $dbDec ),
				'd.id_dec = c.fk_id_dec',
				array( 'name_dec' )
			    );
       
       return $select;
   }
   
   /**
    *
    * @param int $id
    * @return Zend_Db_Table_Rowset
    */
   public function listDocument( $id )
   {
       $dbDocument = App_Model_DbTable_Factory::get( 'PerDocument' );
       $dbPerDataDocument = App_Model_DbTable_Factory::get( 'PerData_has_PerDocument' );
       $dbTypeDocument = App_Model_DbTable_Factory::get( 'PerTypeDocument' );
       $dbAddCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
       
       $select = $dbDocument->select()
	       ->from( array( 'd' => $dbDocument ) )
	       ->setIntegrityCheck( false )
	       ->join(
		    array( 'td' => $dbTypeDocument ),
		    'td.id_pertypedocument = d.fk_id_pertypedocument',
		    array( 'type_document' )
	       )
	       ->join(
		    array( 'c' => $dbAddCountry ),
		    'c.id_addcountry = d.fk_id_country',
		    array( 'country' )
	       )
	       ->join(
		    array( 'pd' => $dbPerDataDocument ),
		    'pd.fk_id_perdocument = d.id_perdocument',
		    array(
			'issue_date_formated'	=>  new Zend_Db_Expr( 'DATE_FORMAT( d.issue_date, "%d/%m/%Y" )' ) 
		    )
	       )
	       ->where( 'pd.fk_id_perdata = ?', $id )
	       ->order( array( 'id_relationship DESC' ) );
       
       return $dbDocument->fetchAll( $select );
   }
   
   /**
    *
    * @param int $id
    * @return Zend_Db_Table_Rowset
    */
   public function listBank( $id )
   {
       $dbBankAccount = App_Model_DbTable_Factory::get( 'BankAccount' );
       $dbBank = App_Model_DbTable_Factory::get( 'Bank' );
       $dbPerDataBank = App_Model_DbTable_Factory::get( 'PerData_has_BankAccount' );
       //$dbTypeBankAccount = App_Model_DbTable_Factory::get( 'TypeBankAccount' );
       $dbAddDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
       
       $select = $dbBankAccount->select()
		    ->from( array( 'ba' => $dbBankAccount ) )
		    ->setIntegrityCheck( false )
		    /*
		    ->join(
			    array( 'tb' => $dbTypeBankAccount ),
			    'tb.id_typebankaccount = ba.fk_id_typebankaccount',
			    array( 'type_bankaccount' )
		    )
		     */
		    ->join(
			    array( 'd' => $dbAddDistrict ),
			    'd.id_adddistrict = ba.fk_id_adddistrict',
			    array( 'District' )
		    )
		    ->join(
			    array( 'b' => $dbBank ),
			    'b.id_bank = ba.fk_id_bank',
			    array( 'name_bank' )
		    )
		    ->join(
			    array( 'pb' => $dbPerDataBank ),
			    'pb.fk_id_bankaccount = ba.id_bankaccount',
			    array()
		    )
		    ->where( 'pb.fk_id_perdata = ?', $id )
		    ->order( array( 'id_relationship DESC' ) );
       
       return $dbBankAccount->fetchAll( $select );
   }
   
   /**
    *
    * @param int $id
    * @return Zend_Db_Table_Row
    */
   public function fetchRow( $id )
   {
       $select = $this->selectClient();
       $select->where( 'c.id_perdata = ?', $id );
       
       $dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
       return $dbPerData->fetchRow( $select );
   }
   
   /**
    *
    * @param Zend_Db_Table_Row $row
    * @return string 
    */
   public static function buildNumRow( $row )
   {
	$numClient = array(
	    $row['num_district'],
	    $row['num_subdistrict'],
	    $row['num_servicecode'],
	    $row['num_year'],
	    $row['num_sequence']
	);
	
	return implode( '-', $numClient );
    }
    
    /**
     * 
     * @param int $idClient
     * @return boolean
     */
    public static function isHandicapped( $idClient )
    {
	$dbHandicapped = App_Model_DbTable_Factory::get( 'Handicapped' );
	$row = $dbHandicapped->fetchRow( array( 'fk_id_perdata = ?' => $idClient ) );
	
	return !empty( $row );
    }
    
    /**
     *
     * @param int $id
     * @return string
     */
    public static function buildNumById( $id )
    {
	$obj = new self();
	$row = $obj->fetchRow( $id );
	return self::buildNumRow( $row );
    }
    
    /**
     *
     * @param int $id
     * @return string
     */
    public static function buildNameById( $id )
    {
	$obj = new self();
	$row = $obj->fetchRow( $id );
	return self::buildName( $row );
    }


    /**
    *
    * @param Zend_Db_Table_Row $row
    * @return string 
    */
   public static function buildName( $row )
   {
	$clientName = array(
	    $row['first_name'],
	    $row['medium_name'],
	    $row['last_name']
	);
	
	return implode( ' ', $clientName );
    }
    
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $form = Client_Form_ClientInformation::ID, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
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
				->joinLeft(
				    array( 'd' => $dbAddDistrict ),
				    'd.id_adddistrict = a.fk_id_adddistrict',
				    array( 'District' )
				)
				->joinLeft(
				    array( 's' => $dbAddSubDistrict ),
				    's.id_addsubdistrict = a.fk_id_addsubdistrict',
				    array( 'sub_district' )
				)
				->joinLeft(
				    array( 'k' => $dbAddSucu ),
				    'k.id_addsucu = a.fk_id_addsucu',
				    array( 'sucu' )
				)
				->where( 'a.fk_id_perdata = ?', $id )
				->order( array( 'id_addaddress' ) );
	
	return $dbAddAddress->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listVisit( $id )
    {
	$dbPurpose = App_Model_DbTable_Factory::get( 'VisitPurpose' );
	$dbPerVisit = App_Model_DbTable_Factory::get( 'PerVisitPurpose' );
	
	$select = $dbPerVisit->select()
			     ->from( 
				array( 'v' => $dbPerVisit ),
				     array(
					 'id_pervisitpurpose',
					 'observation',
					 'description',
					 'visit_date_formated'  => new Zend_Db_Expr( 'DATE_FORMAT( v.visit_date, "%d/%m/%Y" )' ),
				     )
			     )
			     ->setIntegrityCheck( false )
			     ->join(
				array( 'p' => $dbPurpose ),
				'p.id_visitpurpose = v.fk_id_visitpurpose',
				array( 'purpose' )
			     )
			     ->where( 'v.fk_id_perdata = ?', $id )
			     ->order( array( 'visit_date DESC' ) );
	
	return $dbPerVisit->fetchAll( $select );	
    }
    
    /**
     *
     * @param int $id
     * @param int $type
     * @return Zend_Db_Table_Rowset 
     */
    public function listScholarity( $id, $type = false )
    {
	$dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbPerLevelScholarity = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
	$dbTypeScholarity = App_Model_DbTable_Factory::get( 'PerTypeScholarity' );
	$dbEduInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity_has_PerTypeScholarity' );
	
	$select = $dbScholarity->select()
				->from( array( 's' => $dbScholarity ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'ps' => $dbPerScholarity ),
				    'ps.fk_id_perscholarity = s.id_perscholarity',
				    array( 
					'id_relationship',
					'start_date_formated'  => new Zend_Db_Expr( 'DATE_FORMAT( ps.start_date, "%d/%m/%Y" )' ),
					'finish_date_formated'  => new Zend_Db_Expr( 'DATE_FORMAT( ps.finish_date, "%d/%m/%Y" )' )
				    )
				)
				->join(
				    array( 'ts' => $dbTypeScholarity ),
				    'ts.id_pertypescholarity = ps.fk_id_pertypescholarity',
				    array( 'type_scholarity' )
				)
				->joinLeft(
				    array( 'ls' => $dbPerLevelScholarity ),
				    'ls.id_perlevelscholarity = s.fk_id_perlevelscholarity',
				    array( 'level_scholarity' )
				)
				->join(
				    array( 'es' => $dbEduInstitution ),
				    'es.id_fefpeduinstitution = ps.fk_id_fefpeduinstitution',
				    array( 'institution' )
				)
				->where( 'ps.fk_id_perdata = ?', $id )
				->order( array( 'ps.id_relationship DESC' ) );
	
	if ( $type !== false )
	    $select->where( 'ps.fk_id_pertypescholarity = ?', $type );
	
	return $dbScholarity->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listLanguage( $id )
    {
	$dbPerLanguage = App_Model_DbTable_Factory::get( 'PerLanguage_has_PerLevelKnowledge' );
	$dbLanguage = App_Model_DbTable_Factory::get( 'PerLanguage' );
	$dbLevelKnowledge = App_Model_DbTable_Factory::get( 'PerLevelKnowledge' );
	
	$select = $dbPerLanguage->select()
				->from( array( 'pl' => $dbPerLanguage ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'l' => $dbLanguage ),
				    'l.id_perlanguage = pl.fk_id_perlanguage',
				    array( 'language' )
				)
				->join(
				    array( 'lk' => $dbLevelKnowledge ),
				    'lk.id_levelknowledge = pl.fk_id_levelknowledge',
				    array( 'name_level', 'level' => 'description' )
				)
				->where( 'pl.fk_id_perdata = ?', $id )
				->order( 'language' );
	
	return $dbPerLanguage->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listKnowledge( $id )
    {
	$dbPerKnowledge = App_Model_DbTable_Factory::get( 'PerKnowledge_has_PerLevelKnowledge' );
	$dbKnowledge = App_Model_DbTable_Factory::get( 'PerKnowledge' );
	$dbLevelKnowledge = App_Model_DbTable_Factory::get( 'PerLevelKnowledge' );
	$dbTypeKnowledge = App_Model_DbTable_Factory::get( 'PerTypeKnowledge' );
	
	$select = $dbPerKnowledge->select()
				->from( array( 'pk' => $dbPerKnowledge ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'k' => $dbKnowledge ),
				    'k.id_perknowledge = pk.fk_id_perknowledge',
				    array( 'name_knowledge' )
				)
				->join(
				    array( 'lk' => $dbLevelKnowledge ),
				    'lk.id_levelknowledge = pk.fk_id_levelknowledge',
				    array( 'name_level', 'level' => 'description' )
				)
				->join(
				    array( 'tk' => $dbTypeKnowledge ),
				    'tk.id_pertypeknowledge = k.fk_id_pertypeknowlegde',
				    array( 'type_knowledge' )
				)
				->where( 'pk.fk_id_perdata = ?', $id )
				->order( 'name_knowledge' );
	
	return $dbPerKnowledge->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listExperience( $id )
    {
	$dbPerExperience = App_Model_DbTable_Factory::get( 'PerExperience' );
	$dbOccupation = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	
	$select = $dbPerExperience->select()
				  ->from( array( 'e' => $dbPerExperience ) )
				  ->setIntegrityCheck( false )
				  ->join(
				    array( 'o' => $dbOccupation ),
				    'o.id_profocupationtimor = e.fk_id_profocupation',
				    array(
					'ocupation_name_timor',
					'start_date_formated'  => new Zend_Db_Expr( 'DATE_FORMAT( e.start_date, "%d/%m/%Y" )' ),
					'finish_date_formated'  => new Zend_Db_Expr( 'DATE_FORMAT( e.finish_date, "%d/%m/%Y" )' )
				    )
				  )
				  ->where( 'e.fk_id_perdata = ?', $id )
				  ->order( array( 'start_date DESC' ) );
	
	return $dbPerExperience->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listHandicapped( $id )
    {
	$dbTypeHandicapped = App_Model_DbTable_Factory::get( 'TypeHandicapped' );
	$dbHandicapped = App_Model_DbTable_Factory::get( 'Handicapped' );
	
	$select = $dbHandicapped->select()
				->from( array( 'h' => $dbHandicapped ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'th' => $dbTypeHandicapped ),
				    'th.id_typehandicapped = h.fk_id_typehandicapped',
				    array( 'type_handicapped' )
				)
				->where( 'h.fk_id_perdata = ?', $id )
				->order( 'handicapped' );
	
	return $dbHandicapped->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listDependent( $id )
    {
	$dbDependent = App_Model_DbTable_Factory::get( 'PerDependent' );
	
	$select = $dbDependent->select()
				->from( 
				    array( 'd' => $dbDependent ),
				    array(
					'*',
					'birth_date_formated'  => new Zend_Db_Expr( 'DATE_FORMAT( d.birth_date, "%d/%m/%Y" )' )
				    )
				)
				->setIntegrityCheck( false )
				->where( 'd.fk_id_perdata = ?', $id )
				->order( 'dependent_name' );
	
	return $dbDependent->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listContact( $id )
    {
	$dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	
	$select = $dbContact->select()
				->from( array( 'c' => $dbContact ) )
				->setIntegrityCheck( false )
				->where( 'c.fk_id_perdata = ?', $id )
				->order( 'contact_name' );
	
	return $dbContact->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listAbout( $id )
    {
	$dbAbout = App_Model_DbTable_Factory::get( 'AboutECGC' );
	
	$select = $dbAbout->select()
				->from( array( 'a' => $dbAbout ) )
				->setIntegrityCheck( false )
				->where( 'a.fk_id_perdata = ?', $id )
				->order( 'learn_option' );
	
	return $dbAbout->fetchAll( $select );
    }
    
    /**
     * 
     */
    public function listByFilters( $filters = array() )
    {
	$select = $this->selectClient();
	
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	
	// Evidence card
	if ( !empty( $filters['evidence'] ) )
	    $select->where( "CONCAT(c.num_district, '-', c.num_subdistrict, '-', c.num_servicecode, '-', c.num_year, '-', c.num_sequence) = ?", $filters['evidence'] );
	
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
	
	// First Name
	if ( !empty( $filters['first_name'] ) )
	    $select->where( 'c.first_name LIKE ?', '%' . $filters['first_name'] . '%' );
	
	// Last Name
	if ( !empty( $filters['last_name'] ) )
	    $select->where( 'c.last_name LIKE ?', '%' . $filters['last_name'] . '%' );
	
	// Active
	if ( array_key_exists( 'active', $filters ) && is_numeric( $filters['active'] ) )
	    $select->where( 'c.active = ?', (int)$filters['active'] );
	
	// Hired
	if ( array_key_exists( 'hired', $filters ) && is_numeric( $filters['hired'] ) )
	    $select->where( 'c.hired = ?', (int)$filters['hired'] );
	
	// Has Case
	if ( array_key_exists( 'has_case', $filters ) && is_numeric( $filters['has_case'] ) )
	    $select->where( 'IFNULL(ap.active, 0) = ?', (int)$filters['has_case'] );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_registration_ini'] ) )
	    $select->where( 'c.date_registration >= ?', $date->set( $filters['date_registration_ini'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_registration_fim'] ) )
	    $select->where( 'c.date_registration <= ?', $date->set( $filters['date_registration_fim'] )->toString( 'yyyy-MM-dd' ) );
	
	$select->group( 'id_perdata' )
		->order( array( 'first_name' ) );
	
	return $dbPerData->fetchAll( $select );
    }
 
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detailClient( $id )
    {
	$select = $this->selectClient();
	$select->where( 'c.id_perdata = ?', $id );
	
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	return $dbPerData->fetchRow( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listHistory( $id )
    {
	$dbPersonHistory = App_Model_DbTable_Factory::get( 'Person_History' );
	$dbSysUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	
	$select = $dbPersonHistory->select()
				  ->from( array( 'h' => $dbPersonHistory ) )
				  ->setIntegrityCheck( false )
				  ->join(
				    array( 'u' => $dbSysUser ),
				    'u.id_sysuser = h.fk_id_sysuser',
				    array( 
					'name',
					'date_time_formated'  => new Zend_Db_Expr( 'DATE_FORMAT( h.date_time, "%d/%m/%Y" )' )
				    )
				  )
				  ->join(
				    array( 'c' => $dbDec ),
				    'c.id_dec = h.fk_id_dec',
				    array( 'name_dec' )
				  )
				  ->where( 'h.fk_id_perdata = ?', $id )
				  ->order( array( 'date_time DESC' ) );
	
	return $dbPersonHistory->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return array
     */
    public function listLanguagePrint( $id )
    {
	$languages = $this->listLanguage( $id );
	
	$data = array();
	foreach ( $languages as $language )
	    $data[$language->language][] = $language;
	
	return $data;
    }
    
    /**
     *
     * @param int $id
     * @return array
     */
    public function listKnowledgePrint( $id )
    {
	$knowledges = $this->listKnowledge( $id );
	
	$data = array();
	foreach ( $knowledges as $knowledge )
	    $data[$knowledge->type_knowledge][] = $knowledge;
	
	return $data;
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row 
     */
    public function getEleitoralDocument( $id )
    {
	$dbDocumentPerData = App_Model_DbTable_Factory::get( 'PerData_has_PerDocument' );
	$dbPerDocument = App_Model_DbTable_Factory::get( 'PerDocument' );
	
	$select = $dbPerDocument->select()
				->from( array( 'd' => $dbPerDocument ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'pd' => $dbDocumentPerData ),
				    'pd.fk_id_perdocument = d.id_perdocument',
				    array()
				)
				->where( 'pd.fk_id_perdata = ?', $id )
				->where( 'd.fk_id_pertypedocument = ?', 2 );
	
	return $dbDocumentPerData->fetchRow( $select );
    }
}