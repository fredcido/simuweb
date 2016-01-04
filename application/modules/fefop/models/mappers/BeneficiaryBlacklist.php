<?php

class Fefop_Model_Mapper_BeneficiaryBlacklist extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_BeneficiaryBlacklist
     */
    protected $_dbTable;
    
    /**
     *
     * @var array
     */
    protected $_identifiers = array(
	'fk_id_perdata',
	'fk_id_staff',
	'fk_id_fefpeduinstitution',
	'fk_id_fefpenterprise'
    );

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_BeneficiaryBlacklist();

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
	    
	    $this->disableLastBlacklist( $this->_data );
	    
	    $this->_data['fk_id_user_inserted'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( 'REJISTU BENEFISIARIU LA KUMPRIDOR: %s', $id );
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
     * @return array
     */
    public function checkBlacklist( $data )
    {
	$return = array(
	    'valid'	=> true,
	    'message'	=> null
	);
	
	$identifiers = $data['identifiers'];
	
	$dbBlacklist = App_Model_DbTable_Factory::get( 'BeneficiaryBlacklist' );
	
	$select = $dbBlacklist->select()
			      ->from( array( 'bl' => $dbBlacklist ) )
			      ->where( 'bl.active = ?', 1 );
	
	$where = array();
	foreach ( $identifiers as $id => $value ) {
	    if ( !empty( $value ) && in_array( $id, $this->_identifiers ) )
		$where[] = 'bl.' . $id . ' = ' . $value;
	}
	
	if ( empty( $where ) )
	    return $return;
	
	$select->where( implode( ' OR ', $where ) );
	
	$row = $dbBlacklist->fetchRow( $select );
	
	if ( !empty( $row ) ) {
	    
	    $return = array(
		'valid' => false,
		'msg'	=> sprintf( 'Benefisiariu iha blacklist ba modulu: %s', $row->fk_id_fefop_modules )
	    );
	}
	
	return $return;
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveDisableBlacklist()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $this->_data['active'] = 0;
	    $this->_data['fk_id_user_removed'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $this->_data['date_removed'] = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm:ss' );
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( 'REMOVE BENEFISIARIU LA KUMPRIDOR: %s', $id );
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
     * @return boolean
     */
    public function disableLastBlacklist( $data )
    {
	$identifier = $this->_discoverIdentifier( $data );
	if ( !in_array( $identifier, $this->_identifiers ) )
	    throw new Exception( $identifier . ' is not a valid identifier' );
	
	$where = array(
	    'active = ?' => 1,
	    $identifier . ' = ?' => $data[$identifier]
	);
	
	$data = array(
	    'date_removed'	    => Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm:ss' ),
	    'fk_id_user_removed'    => Zend_Auth::getInstance()->getIdentity()->id_sysuser,
	    'comment_remove'	    => 'INSERE REJISTU FOUN BA BENEFISIARIU LA KUMPRIDOR',
	    'active'		    => 0
	);
	
	$this->_dbTable->update( $data, $where );
    }
    
    /**
     * 
     * @param array $data
     * @return null|string
     */
    protected function _discoverIdentifier( $data )
    {
	foreach ( $this->_identifiers as $id ) {
	    if ( !empty( $data[$id] ) )
		return $id;
	}
	
	return null;
    }
    
    public function getLastBlacklist( $id, $type )
    {
	if ( !in_array( $type, $this->_identifiers ) )
	    throw new Exception( $type . ' is not a valid identifier' );
	
	$dbBeneficiaryBlacklist = App_Model_DbTable_Factory::get( 'BeneficiaryBlacklist' );
	
	$select = $dbBeneficiaryBlacklist->select()
					 ->from( array( 'bb' => $dbBeneficiaryBlacklist ) )
					 ->where( 'bb.' . $type . ' = ?', $id )
					 ->where( 'bb.active = ?', 1 );
	
	return $dbBeneficiaryBlacklist->fetchRow( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Select
     */
    public function getSelectBeneficiaryBlacklist()
    {
	$dbClient = App_Model_DbTable_Factory::get( 'PerData' );
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	$dbInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	$dbBeneficiaryBlacklist = App_Model_DbTable_Factory::get( 'BeneficiaryBlacklist' );
	$dbFEFOPPrograms = App_Model_DbTable_Factory::get( 'FEFOP_Programs' );
	$dbFEFOPModules = App_Model_DbTable_Factory::get( 'FEFOP_Modules' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	$dbEnterpriseContact = App_Model_DbTable_Factory::get( 'FefpEnterpriseHasPerContact' );
	$dbInstitutionContact = App_Model_DbTable_Factory::get( 'FefpEduInstitutionHasPerContact' );
	
	$select = $dbBeneficiaryBlacklist->select()
	                 ->setIntegrityCheck( false )
					 ->from( array( 'bb' => $dbBeneficiaryBlacklist ) )
					 ->join(
					    array( 'fm' => $dbFEFOPModules ),
					    'fm.id_fefop_modules = bb.fk_id_fefop_modules',
					    array( 'module_acronym' => new Zend_Db_Expr("CONCAT(fm.acronym, ' - ', fm.description)") )
					 )
					 ->join(
					    array( 'fp' => $dbFEFOPPrograms ),
					    'fp.id_fefop_programs = fm.fk_id_fefop_programs',
					    array( 'program_acronym' => new Zend_Db_Expr("CONCAT(fp.acronym, ' - ', fp.description)") )
					 )
					 ->join(
					    array( 'ui' => $dbUser ),
					    'ui.id_sysuser = bb.fk_id_user_inserted',
					    array( 'user_inserted' => 'name' )
					 )
					 ->joinLeft(
					    array( 'ur' => $dbUser ),
					    'ur.id_sysuser = bb.fk_id_user_removed',
					    array( 'user_removed' => 'name' )
					 )
					 ->joinLeft(
					    array( 'e' => $dbEnterprise ),
					    'e.id_fefpenterprise = bb.fk_id_fefpenterprise',
					    array('id_fefpenterprise')
					 )
					 ->joinLeft(
					    array( 'epc' => $dbEnterpriseContact ),
					    'bb.fk_id_fefpenterprise = epc.fk_id_fefpenterprise',
					    array()
					 )
					 ->joinLeft(
					    array( 'ec' => $dbContact ),
					    'epc.fk_id_id_percontact = ec.id_percontact AND ec.cell_fone IS NOT NULL',
					    array()
					 )
					 ->joinLeft(
					    array( 'i' => $dbInstitution ),
					    'i.id_fefpeduinstitution = bb.fk_id_fefpeduinstitution',
					    array('id_fefpeduinstitution')
					 )
					 ->joinLeft(
					    array( 'ipc' => $dbInstitutionContact ),
					    'bb.fk_id_fefpeduinstitution = ipc.fk_id_fefpeduinstitution',
					    array()
					 )
					 ->joinLeft(
					    array( 'ic' => $dbContact ),
					    'ipc.fk_id_percontact = ic.id_percontact AND ic.cell_fone IS NOT NULL',
					    array()
					 )
					 ->joinLeft(
					    array( 's' => $dbStaff ),
					    's.id_staff = bb.fk_id_staff',
					    array('id_staff')
					 )
					 ->joinLeft(
					    array( 'cs' => $dbClient ),
					    'cs.id_perdata = s.fk_id_perdata',
					    array()
					 )
					 ->joinLeft(
					    array( 'c' => $dbClient ),
					    'c.id_perdata = bb.fk_id_perdata',
					    array(
						'id_perdata',
						'beneficiary' => new Zend_Db_Expr(
							"COALESCE( 
							    CONCAT( c.num_district, '-', c.num_subdistrict, '-', c.num_servicecode, '-', 
								    c.num_year, '-', c.num_sequence, ' - ', c.first_name, ' ', 
								    IFNULL(c.medium_name, ''), ' ', c.last_name 
							    ),
							    e.enterprise_name,
							    i.institution,
							    CONCAT( cs.num_district, '-', cs.num_subdistrict, '-', cs.num_servicecode, '-', 
								    cs.num_year, '-', cs.num_sequence, ' - ',
								    cs.first_name, ' ', IFNULL(cs.medium_name, ''), ' ', cs.last_name )
							)"
						),
						'contact' => new Zend_Db_Expr(
							"COALESCE( 
							    c.client_fone,
							    ec.cell_fone,
							    ic.cell_fone,
							    cs.client_fone
							)"
						),
					    )
					 )
					 ->group( array( 'id_beneficiary_blacklist' ) );
	
	return $select;
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detail( $id )
    {
	$select = $this->getSelectBeneficiaryBlacklist();
	$select->where( 'bb.id_beneficiary_blacklist = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listByFilters( $filters = array() )
    {
	$select = $this->getSelectBeneficiaryBlacklist();
	
	if ( !empty( $filters['fk_id_fefop_modules'] ) )
	    $select->where( 'bb.fk_id_fefop_modules = ?', $filters['fk_id_fefop_modules'] );
	
	if ( !empty( $filters['fk_id_user_inserted'] ) )
	    $select->where( 'bb.fk_id_user_inserted = ?', $filters['fk_id_user_inserted'] );
	
	if ( !empty( $filters['fk_id_user_removed'] ) )
	    $select->where( 'bb.fk_id_user_removed = ?', $filters['fk_id_user_removed'] );
	
	if ( !empty( $filters['id_fefop_programs'] ) )
	    $select->where( 'fp.id_fefop_programs = ?', $filters['id_fefop_programs'] );
	
	if ( !empty( $filters['type_beneficiary'] ) )
	    $select->where( sprintf( 'bb.%s IS NOT NULL', $filters['type_beneficiary'] ) );
	
	// Beneficiary name
	if ( !empty( $filters['beneficiary_name'] ) )
	    $select->having( 'beneficiary LIKE ?', '%' . $filters['beneficiary_name'] . '%' );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_registration_ini'] ) )
	    $select->where( 'DATE(bb.date_insert) >= ?', $date->set( $filters['date_registration_ini'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_registration_fim'] ) )
	    $select->where( 'DATE(bb.date_insert) <= ?', $date->set( $filters['date_registration_fim'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( array_key_exists( 'status', $filters ) && is_numeric( $filters['status'] ) )
	    $select->where( 'bb.active = ?', (int)$filters['status'] );
	
	$select->order( array( 'id_beneficiary_blacklist DESC' ) );
	
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
	    'fk_id_sysform'	    => Fefop_Form_BeneficiaryBlacklist::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}