<?php

class Fefop_Model_Mapper_FERegistration extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_FERegistration
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FERegistration();

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
	    
	    $mapperRule = new Fefop_Model_Mapper_Rule();
	    $mapperRule->validate( $this->_message, $this->_data, Fefop_Model_Mapper_Expense::CONFIG_PISE_FE_REGISTRATION );
	    
	    if ( empty( $this->_data['id_fe_registration'] ) )
		$this->_data['fk_id_sysuser'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    
	    $dataForm = $this->_data;
	    
	    $dataForm['id_fe_registration'] = parent::_simpleSave();
	    
	    // Save Formation
	    $this->_saveFormation( $dataForm );
	    
	    // Save Entity
	    $this->_saveEntity( $dataForm );
	    
	    if ( empty( $this->_data['id_fe_contract'] ) )
		$history = 'REJISTU FICHA INSKRISAUN FE: %s';
	    else
		$history = 'ATUALIZA FICHA INSKRISAUN FE: %s';
	    
	    $history = sprintf( $history, $dataForm['id_fe_registration'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return $dataForm['id_fe_registration'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @param array $data
     */
    protected function _saveFormation( $data )
    {
	$dbRegistrationFormation = App_Model_DbTable_Factory::get( 'FERegistrationFormation' );
	
	$formationTypes = array(
	    'professional',
	    'formation',
	    'selected',
	);
	
	// Save each formation type
	foreach ( $formationTypes as $formationType ) {
	    
	    $where = array(
		'fk_id_fe_registration = ?' => $data['id_fe_registration'],
		'identifier = ?'	    => $formationType,
	    );
	    
	    $row = $dbRegistrationFormation->fetchRow( $where );
	    
	    if ( empty( $row ) ) {
		
		$row = $dbRegistrationFormation->createRow();
		$row->fk_id_fe_registration = $data['id_fe_registration'];
		$row->identifier = $formationType;
	    }
	    
	    $formationData = $data[$formationType];
	    
	    $row->fk_id_scholarity_area = empty( $formationData['area'] ) ? null : $formationData['area'];
	    $row->fk_id_profocupationtimor = empty( $formationData['occupation'] ) ? null : $formationData['occupation'];
	    $row->fk_id_perlevelscholarity = empty( $formationData['level'] ) ? null : $formationData['level'];
	    $row->save();
	}
    }
    
    /**
     * 
     * @param array $data
     */
    protected function _saveEntity( $data )
    {
	$dbRegistrationEntity = App_Model_DbTable_Factory::get( 'FERegistrationEntity' );
	
	$identityMap = array(
	    'enterprise'    => 'fk_id_fefpenterprise',
	    'institute'	    => 'fk_id_fefpeduinstitution'
	);
	
	// Delete all the entities related with the Registration Form
	$where = array( 'fk_id_fe_registration = ?' => $data['id_fe_registration'] );
	$dbRegistrationEntity->delete( $where );
	
	foreach ( $data['entity'] as $type => $entity ) {
	    foreach ( $entity as $id ) {
		
		$entityData = array(
		    'fk_id_fe_registration' => $data['id_fe_registration'],
		    $identityMap[$type]	    => $id
		);
		$dbRegistrationEntity->insert( $entityData );
	    }
	}
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listBeneficiaries()
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$dbFERegistration = App_Model_DbTable_Factory::get( 'FERegistration' );
	
	$select->join(
		    array( 'fer' => $dbFERegistration ),
		    'fer.fk_id_perdata = c.id_perdata',
		    array()
		)
		->group( array( 'id_perdata' ) );
	
	return $dbFERegistration->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listEnterprises()
    {
	$mapperEnterprises = new Register_Model_Mapper_Enterprise();
	$select = $mapperEnterprises->getSelectEnterprise();
	
	$dbFERegistrationEntity = App_Model_DbTable_Factory::get( 'FERegistrationEntity' );
	
	$select->join(
		    array( 'fee' => $dbFERegistrationEntity ),
		    'fee.fk_id_fefpenterprise = e.id_fefpenterprise',
		    array()
		)
		->group( array( 'fee.fk_id_fefpenterprise' ) );
	
	return $dbFERegistrationEntity->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listInstitutes()
    {
	$mapperInstitute = new Register_Model_Mapper_EducationInstitute();
	$select = $mapperInstitute->getSelectEducationInstitute();
	
	$dbFERegistrationEntity = App_Model_DbTable_Factory::get( 'FERegistrationEntity' );
	
	$select->join(
		    array( 'fei' => $dbFERegistrationEntity ),
		    'fei.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
		    array()
		)
		->group( array( 'fei.fk_id_fefpeduinstitution' ) );
	
	return $dbFERegistrationEntity->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
	$client = new Client_Model_Mapper_Client();
	$select = $client->selectClient();
	
	$dbRegistrationForm = App_Model_DbTable_Factory::get( 'FERegistration' );
	$dbRegistrationEntity = App_Model_DbTable_Factory::get( 'FERegistrationEntity' );
	$dbRegistrationFormation = App_Model_DbTable_Factory::get( 'FERegistrationFormation' );
	$dbFEContract = App_Model_DbTable_Factory::get( 'FEContract' );
	
	$select->join(
		    array( 'fer' => $dbRegistrationForm ),
		    'fer.fk_id_perdata = c.id_perdata'
		)
		->joinLeft(
		    array( 'fee' => $dbRegistrationEntity ),
		    'fee.fk_id_fe_registration = fer.id_fe_registration',
		    array()
		)
		->joinLeft(
		    array( 'fef' => $dbRegistrationFormation ),
		    'fef.fk_id_fe_registration = fer.id_fe_registration',
		    array()
		)
		->joinLeft(
		    array( 'fec' => $dbFEContract ),
		    'fec.fk_id_fe_registration = fer.id_fe_registration',
		    array( 'id_fe_contract' )
		)
		->group( array( 'id_fe_registration' ) );
	
	return $select;
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listEntities( $id )
    {
	$dbRegistratonEntity = App_Model_DbTable_Factory::get( 'FERegistrationEntity' );
	
	$mapperEduInstitute = new Register_Model_Mapper_EducationInstitute();
	$selectEduInstitute = $mapperEduInstitute->getSelectEducationInstitute();
	
	$mapperEnterprise = new Register_Model_Mapper_Enterprise();
	$selectEnterprise = $mapperEnterprise->getSelectEnterprise();
	
	$select = $dbRegistratonEntity->select()
				      ->from( array( 're' => $dbRegistratonEntity ) )
				      ->setIntegrityCheck( false )
				      ->joinLeft(
					array( 'ee' => new Zend_Db_Expr( '(' . $selectEnterprise . ')' ) ),
					'ee.id_fefpenterprise = re.fk_id_fefpenterprise',
					array()
				      )
				      ->joinLeft(
					array( 'ei' => new Zend_Db_Expr( '(' . $selectEduInstitute . ')' ) ),
					'ei.id_fefpeduinstitution = re.fk_id_fefpeduinstitution',
					array(
					    'type' => "IF(re.fk_id_fefpenterprise, 'enterprise', 'institute')",
					    'id'   => 'IFNULL(re.fk_id_fefpenterprise, re.fk_id_fefpeduinstitution)',
					    'name' => 'IFNULL(ee.enterprise_name, ei.institution)'
					)
				      )
				      ->where( 're.fk_id_fe_registration = ?', $id )
				      ->where( 'IFNULL(re.fk_id_fefpenterprise, re.fk_id_fefpeduinstitution) IS NOT NULL' );
	
	return $dbRegistratonEntity->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listFormation( $id )
    {
	$dbRegistrationFormation = App_Model_DbTable_Factory::get( 'FERegistrationFormation' );
	$dbScholarityArea = App_Model_DbTable_Factory::get( 'ScholarityArea' );
	$dbLevelScholarity = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
	$dbOcupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	
	$select = $dbRegistrationFormation->select()
					  ->from( 
					    array( 'rf' => $dbRegistrationFormation ),
					    array(
						'identifier',
						'area'	     => 'fk_id_scholarity_area',
						'level'	     => 'fk_id_perlevelscholarity',
						'occupation' => 'fk_id_profocupationtimor'
					    )
					  )
					  ->setIntegrityCheck( false )
					  ->joinLeft(
					    array( 'sa' => $dbScholarityArea ),
					    'sa.id_scholarity_area = rf.fk_id_scholarity_area',
					    array( 'area_name' => 'scholarity_area' )
					  )
					  ->joinLeft(
					    array( 'ls' => $dbLevelScholarity ),
					    'ls.id_perlevelscholarity = rf.fk_id_perlevelscholarity',
					    array( 'level_name' => 'level_scholarity' )
					  )
					  ->joinLeft(
					    array( 'ot' => $dbOcupationTimor ),
					    'ot.id_profocupationtimor = rf.fk_id_profocupationtimor',
					    array( 'occupation_name' => 'ocupation_name_timor' )
					  )
					  ->where( 'rf.fk_id_fe_registration = ?', $id )
					  ->order( array( 'identifier' ) );
	
	return $dbRegistrationFormation->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return array
     */
    public function groupFormation( $id )
    {
	$rows = $this->listFormation( $id );
	
	$groups = array();
	foreach ( $rows as $row ) {
	    
	    if ( empty( $groups[$row->identifier] ) )
		$groups[$row->identifier] = array();
	    
	    $groups[$row->identifier] = $row->toArray();
	}
	
	return $groups;
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detail( $id )
    {
	$select = $this->getSelect();
	$select->where( 'fer.id_fe_registration = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listByFilters( $filters = array() )
    {
	$select = $this->getSelect();
	
	if ( !empty( $filters['fk_id_perdata'] ) )
	    $select->where( 'fer.fk_id_perdata = ?', $filters['fk_id_perdata'] );
	
	if ( !empty( $filters['fk_id_fefpenterprise'] ) )
	    $select->where( 'fee.fk_id_fefpenterprise = ?', $filters['fk_id_fefpenterprise'] );
	
	if ( !empty( $filters['fk_id_fefpeduinstitution'] ) )
	    $select->where( 'fee.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution'] );
	
	if ( !empty( $filters['fk_id_scholarity_area'] ) )
	    $select->where( 'fef.fk_id_scholarity_area = ?', $filters['fk_id_scholarity_area'] );
	
	if ( !empty( $filters['fk_id_profocupationtimor'] ) )
	    $select->where( 'fef.fk_id_profocupationtimor = ?', $filters['fk_id_profocupationtimor'] );
	
	if ( !empty( $filters['fk_id_perlevelscholarity'] ) )
	    $select->where( 'fef.fk_id_perlevelscholarity = ?', $filters['fk_id_perlevelscholarity'] );
	
	if ( !empty( $filters['identifier'] ) )
	    $select->where( 'fef.identifier = ?', $filters['identifier'] );
	
	return $this->_dbTable->fetchAll( $select );
    }
   
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::FEFOP,
	    'fk_id_sysform'	    => Fefop_Form_FERegistration::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}