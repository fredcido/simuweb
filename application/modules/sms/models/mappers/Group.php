<?php

class Sms_Model_Mapper_Group extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_SmsGroup
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_SmsGroup();

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

	    $row = $this->_checkNameGroup( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Grupu SMS iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_sms_group'] ) ) {
		
		$history = 'INSERE GRUPU SMS: %s DADUS PRINCIPAL - INSERE NOVO GRUPU SMS';
		$this->_data['user_registered'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		
	    } else {
		
		$history = 'ALTERA GRUPU SMS: %s DADUS PRINCIPAL - ALTERA GRUPU SMS';
		$this->_data['user_updated'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    }
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['sms_group_name'] );
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
    protected function _checkNameGroup()
    {
	$select = $this->_dbTable->select()->where( 'sms_group_name = ?', $this->_data['sms_group_name'] );

	if ( !empty( $this->_data['id_sms_group'] ) )
	    $select->where( 'id_sms_group <> ?', $this->_data['id_sms_group'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listClient( $id )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$selectClient = $mapperClient->selectClient();
	
	$dbGroupContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	
	$selectClient->join(
			array( 'gc' => $dbGroupContact ),
			'gc.fk_id_perdata = c.id_perdata',
			array()
		    )
		    ->where( 'gc.fk_id_sms_group = ?', $id );
	
	return $dbGroupContact->fetchAll( $selectClient );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listEnterprise( $id )
    {
	$mapperEnterprise = new Register_Model_Mapper_Enterprise();
	$select = $mapperEnterprise->getSelectEnterprise();
	
	$dbGroupContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	
	$select->join(
		    array( 'gc' => $dbGroupContact ),
		    'gc.fk_id_fefpenterprise = e.id_fefpenterprise',
		    array()
		)
		->where( 'gc.fk_id_sms_group = ?', $id );
	
	return $dbGroupContact->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listInstitute( $id )
    {
	$mapperEducationInstitue = new Register_Model_Mapper_EducationInstitute();
	$select = $mapperEducationInstitue->getSelectEducationInstitute();
	
	$dbGroupContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	
	$select->join(
		    array( 'gc' => $dbGroupContact ),
		    'gc.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
		    array()
		)
		->where( 'gc.fk_id_sms_group = ?', $id );
	
	return $dbGroupContact->fetchAll( $select );
    }
    
    /**
     *
     * @return boolean 
     */
    public function saveClient()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {

	    $row = $this->_checkClientGroup( $this->_data );
	    
	    if ( !empty( $row ) ) {
		
		$this->_message->addMessage( 'Keta rejistu! Kliente iha grupu tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    $dbGroupContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	    $row = $dbGroupContact->createRow();
	    $row->fk_id_sms_group = $this->_data['group'];
	    $row->fk_id_perdata = $this->_data['client'];
	    $row->save();
	
	    $history = 'INSERE KLIENTE: %s IHA GRUPU SMS: %s';
	    
	    $history = sprintf( $history, $this->_data['client'], $this->_data['group'] );
	    $this->_sysAudit( $history );
	    
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
    protected function _checkClientGroup()
    {
	$dbGroupContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	$select = $dbGroupContact->select()
				 ->where( 'fk_id_perdata = ?', $this->_data['client'] )
				 ->where( 'fk_id_sms_group = ?', $this->_data['group'] );

	return $dbGroupContact->fetchRow( $select );
    }
    
    /**
     *
     * @return boolean 
     */
    public function saveEnterprise()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {

	    $row = $this->_checkEnterpriseGroup( $this->_data );
	    
	    if ( !empty( $row ) ) {
		
		$this->_message->addMessage( 'Keta rejistu! Empreza iha grupu tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    $dbGroupContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	    $row = $dbGroupContact->createRow();
	    $row->fk_id_sms_group = $this->_data['group'];
	    $row->fk_id_fefpenterprise = $this->_data['enterprise'];
	    $row->save();
	
	    $history = 'INSERE EMPREZA: %s IHA GRUPU SMS: %s';
	   
	    $history = sprintf( $history, $this->_data['enterprise'], $this->_data['group'] );
	    $this->_sysAudit( $history );
	    
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
     * @return Zend_Db_Table_Rowset
     */
    public function listGroupWithTotals()
    {
	$selectConcats = $this->getSelectContacts();
	
	$dbGroup = App_Model_DbTable_Factory::get( 'SmsGroup' );
	
	$select = $selectConcats->join( 
				    array( 'g' => $dbGroup ),
				    't.fk_id_sms_group = g.id_sms_group',
				    array(
					'sms_group_name',
					'id_sms_group',
					'total' => new Zend_Db_Expr( 'COUNT(1)' )
				    )
			      )
			      ->group( array( 'id_sms_group' ) )
			      ->order( array( 'sms_group_name' ) );
	
	return $dbGroup->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelectContacts()
    {
	$dbGroupContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	$dbClient = App_Model_DbTable_Factory::get( 'PerData' );
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	$dbEduInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbEnterpriseContact = App_Model_DbTable_Factory::get( 'FefpEnterprise_has_PerContact' );
	$dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	$dbInstitutionContact = App_Model_DbTable_Factory::get( 'FefpEduInstitution_has_PerContact' );
	
	$selectContactClient = $dbClient->select()
					->from(
					    array( 'c' => $dbClient ),
					    array(
						'contact' => new Zend_Db_Expr( "CONCAT( c.first_name, ' ', IFNULL(c.medium_name, ''), ' ', c.last_name )" ),
						'type'	  => new Zend_Db_Expr( "'Kliente'" )
					    )
					)
					->setIntegrityCheck( false )
					->join(
					    array( 'sgc' => $dbGroupContact ),
					    'sgc.fk_id_perdata = c.id_perdata',
					    array( 
						'id_sms_group_contact',
						'fk_id_sms_group',
						'number'  => 'c.client_fone'
					    )
					)
					->where( "TRIM( IFNULL( c.client_fone, '' ) ) > ''" );
	
	$selectContactEnterprise = $dbClient->select()
					->from(
					    array( 'e' => $dbEnterprise ),
					    array(
						'contact' => 'enterprise_name',
						'type'	  => new Zend_Db_Expr( "'Empreza'" )
					    )
					)
					->setIntegrityCheck( false )
					->join(
					    array( 'sgc' => $dbGroupContact ),
					    'sgc.fk_id_fefpenterprise = e.id_fefpenterprise',
					    array( 
						'id_sms_group_contact',
						'fk_id_sms_group'
					    )
					)
					->join(
					    array( 'ecc' => $dbEnterpriseContact ),
					    'ecc.fk_id_fefpenterprise = e.id_fefpenterprise',
					    array()
					)
					->join(
					    array( 'c' => $dbContact ),
					    'ecc.fk_id_id_percontact = c.id_percontact',
					    array(
						'number' => 'cell_fone'
					    )
					)
					->where( "TRIM( IFNULL( c.cell_fone, '' ) ) > ''" )
					->group( array( 'id_sms_group_contact', 'id_fefpenterprise' ) );
	
	$selectContactInstitution = $dbClient->select()
					->from(
					    array( 'ei' => $dbEduInstitution ),
					    array(
						'contact' => 'institution',
						'type'	  => new Zend_Db_Expr( "'Inst. Ensinu'" )
					    )
					)
					->setIntegrityCheck( false )
					->join(
					    array( 'sgc' => $dbGroupContact ),
					    'sgc.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
					    array( 
						'id_sms_group_contact',
						'fk_id_sms_group'
					    )
					)
					->join(
					    array( 'icc' => $dbInstitutionContact ),
					    'icc.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
					    array()
					 )
					 ->join(
					    array( 'c' => $dbContact ),
					    'icc.fk_id_percontact = c.id_percontact',
					    array(
						'number' => 'cell_fone'
					    )
					 )
					 ->where( "TRIM( IFNULL( c.cell_fone, '' ) ) > ''" )
					 ->group( array( 'id_sms_group_contact', 'id_fefpeduinstitution' ) );
	
	$selectUnion = $dbClient->select()
			    ->union( array( $selectContactClient, $selectContactEnterprise, $selectContactInstitution ) )
			    ->setIntegrityCheck( false )
			    ->order( array( 'contact' ) );
	
	$selectFinal = $dbClient->select()
				->from( array( 't' => new Zend_Db_Expr( '(' .$selectUnion . ')' ) ) )
				->setIntegrityCheck( false );
	
	return $selectFinal;
    }
    
     /**
     *
     * @param array $data
     * @return App_Model_DbTable_Row_Abstract
     */
    protected function _checkEnterpriseGroup()
    {
	$dbGroupContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	$select = $dbGroupContact->select()
				 ->where( 'fk_id_fefpenterprise = ?', $this->_data['enterprise'] )
				 ->where( 'fk_id_sms_group = ?', $this->_data['group'] );

	return $dbGroupContact->fetchRow( $select );
    }
    
    /**
     *
     * @return boolean 
     */
    public function saveInstitute()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {

	    $row = $this->_checkInstituteGroup( $this->_data );
	    
	    if ( !empty( $row ) ) {
		
		$this->_message->addMessage( 'Keta rejistu! Inst. Ensinu iha grupu tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    $dbGroupContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	    $row = $dbGroupContact->createRow();
	    $row->fk_id_sms_group = $this->_data['group'];
	    $row->fk_id_fefpeduinstitution = $this->_data['institute'];
	    $row->save();
	
	    $history = 'INSERE INST. ENSINU: %s IHA GRUPU SMS: %s';
	    
	    $history = sprintf( $history, $this->_data['institute'], $this->_data['group'] );
	    $this->_sysAudit( $history );
	    
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
    protected function _checkInstituteGroup()
    {
	$dbGroupContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	$select = $dbGroupContact->select()
				 ->where( 'fk_id_fefpeduinstitution = ?', $this->_data['institute'] )
				 ->where( 'fk_id_sms_group = ?', $this->_data['group'] );

	return $dbGroupContact->fetchRow( $select );
    }
    
    /**
     *
     * @return boolean 
     */
    public function removeItem()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {

	    $where = array(
		'fk_id_sms_group = ?' => $this->_data['group'],
		$this->_data['field'] . ' = ?' => $this->_data['id']
	    );
	    
	    $dbGroupContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	    $dbGroupContact->delete( $where );
	
	    $history = 'REMOVE ITEM: %s HO ID: %s IHA GRUPU SMS: %s';
	    
	    $history = sprintf( $history, $this->_data['field'], $this->_data['id'], $this->_data['group'] );
	    $this->_sysAudit( $history );
	    
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
     * @param array $status
     * @return Zend_Db_Table_Rowset
     */
    public function listCampaignsRelated( $id, $status = array() )
    {
	if ( empty( $status ) ) {
	    
	    $status = array(
		Sms_Model_Mapper_Campaign::STATUS_INITIED,
		Sms_Model_Mapper_Campaign::STATUS_ROBOT,
		Sms_Model_Mapper_Campaign::STATUS_SCHEDULED,
		Sms_Model_Mapper_Campaign::STATUS_STOPPED,
	    );
	}
	
	$filters = array(
	    'group'	=> array( $id ),
	    'status'	=> $status
	);
	
	$mapperCampaign = new Sms_Model_Mapper_Campaign();
	$campaigns = $mapperCampaign->listByFilters( $filters );
	
	return $campaigns;
    }
    
    
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::SMS,
	    'fk_id_sysform'	    => Sms_Form_Group::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}