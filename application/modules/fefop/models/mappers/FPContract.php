<?php

class Fefop_Model_Mapper_FPContract extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_FPContract
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FPContract();

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
	    
	    $dataForm = $this->_data;
	    $dataForm['client'] = array_keys( $this->_data['cost_client'] );
	    
	    $mapperRule = new Fefop_Model_Mapper_Rule();
	    $mapperRule->validate( $this->_message, $dataForm, Fefop_Model_Mapper_Expense::CONFIG_PFPCI_FP );
	    
	    // If there is no contract yet
	    if ( empty( $this->_data['fk_id_fefop_contract'] ) ) {
		
		$dataContract = array(
		    'module'	=> Fefop_Model_Mapper_Module::FP,
		    'district'	=> $this->_data['fk_id_adddistrict']
		);
		
		$mapperFefopContract = new Fefop_Model_Mapper_Contract();
		$this->_data['fk_id_fefop_contract'] = $mapperFefopContract->save( $dataContract );
	    }
	    
	    $this->_data['amount'] = App_General_String::toFloat( $this->_data['amount'] );
	    
	    $date = new Zend_Date();
	    $this->_data['date_start'] = $date->setDate( $this->_data['start_date'] )->toString( 'yyyy-MM-dd' );
	    $this->_data['date_finish'] = $date->setDate( $this->_data['finish_date'] )->toString( 'yyyy-MM-dd' );
	   
	    $dataForm = $this->_data;
	    
	    // Save the contract
	    $dataForm['id_fp_contract'] = parent::_simpleSave();
	    
	    // Set the class to the Planning Course
	    $dbPlanningCourse = App_Model_DbTable_Factory::get( 'FPPlanningCourse' );
	    $row = $dbPlanningCourse->fetchRow( array( 'id_planning_course = ?' => $dataForm['fk_id_planning_course'] ) );
	    $row->fk_id_fefpstudentclass = $dataForm['fk_id_fefpstudentclass'];
	    $row->save();

	    // Save budget category
	    $this->_saveBudgetCategory( $dataForm );
	    
	    // Save the contract beneficiaries
	    $this->_saveBeneficiaries( $dataForm );
	    
	    if ( empty( $this->_data['id_fp_contract'] ) )
		$history = 'REJISTU KONTRAKTU: %s BA ANNUAL PLANNING: %s';
	    else
		$history = 'ATUALIZA KONTRAKTU: %s BA ANNUAL PLANNING: %s';
	    
	    $history = sprintf( $history, $dataForm['id_fp_contract'], $this->_data['fk_id_annual_planning'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return $dataForm['id_fp_contract'];
	    
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
    protected function _saveBudgetCategory( $data )
    {
	$row = $this->getBudgetCategory( $data['fk_id_fefop_contract'] );
	
	if ( empty( $row ) ) {
	    
	    $dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	    $row = $dbBudgetContract->createRow();
	    $row->fk_id_budget_category = $data['id_budget_category'];
	    $row->fk_id_fefop_contract = $data['fk_id_fefop_contract'];
	    $row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $row->status = 1;
	}
	
	$row->amount = $data['amount'];
	$row->save();
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function getBudgetCategory( $id )
    {
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	
	$where = array( 'fk_id_fefop_contract = ?' => $id );
	return $dbBudgetContract->fetchRow( $where );
    }
    
    /**
     * 
     * @param array $data
     */
    protected function _saveBeneficiaries( $data )
    {
	$dbBeneficiary = App_Model_DbTable_Factory::get( 'FP_Beneficiary' );
	
	foreach ( $data['cost_client'] as $client => $cost ) {
	    
	    $where = array(
		'fk_id_fp_contract = ?' => $data['id_fp_contract'],
		'fk_id_perdata = ?'	=> $client,
	    );
	    
	    $row = $dbBeneficiary->fetchRow( $where );
	    
	    if ( empty( $row ) ) {
		
		$row = $dbBeneficiary->createRow();
		$row->fk_id_fp_contract = $data['id_fp_contract'];
		$row->fk_id_unit_cost = $data['fk_id_unit_cost'];
		$row->fk_id_perdata = $client;
		$row->handicapped = $data['client_handicapped'][$client];
		$row->amount = App_General_String::toFloat( $cost );
		$row->save();
	    }
	}
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$select = $mapperContract->getSelect();
	
	$dbFpContract = App_Model_DbTable_Factory::get( 'FPContract' );
	$dbInstitute = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbFPAnnualPlanning = App_Model_DbTable_Factory::get( 'FPAnnualPlanning' );
	$dbFPPlanningCourse = App_Model_DbTable_Factory::get( 'FPPlanningCourse' );
	$dbStudentClass = App_Model_DbTable_Factory::get( 'FEFPStudentClass' );
	$dbUnitCost = App_Model_DbTable_Factory::get( 'UnitCost' );
	$dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbLevelScholarity = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
	
	$select->join(
		    array( 'fpc' => $dbFpContract ),
		    'fpc.fk_id_fefop_contract = c.id_fefop_contract'
		)
		->join(
		    array( 'ap' => $dbFPAnnualPlanning ),
		    'ap.id_annual_planning = fpc.fk_id_annual_planning'
		)
		->join(
		    array( 'pc' => $dbFPPlanningCourse ),
		    'pc.id_planning_course = fpc.fk_id_planning_course'
		)
		->join(
		    array( 'ei' => $dbInstitute ),
		    'ei.id_fefpeduinstitution = ap.fk_id_fefpeduinstitution',
		    array( 'institute' => 'institution' )
		)
		->join(
		    array( 'sc' => $dbStudentClass ),
		    'fpc.fk_id_fefpstudentclass = sc.id_fefpstudentclass',
		    array( 'class_name' )
		)
		->join(
		    array( 'sl' => $dbScholarity ),
		    'pc.fk_id_perscholarity = sl.id_perscholarity',
		    array( 'scholarity', 'external_code' )
		)
		->joinLeft(
		    array( 'ls' => $dbLevelScholarity ),
		    'ls.id_perlevelscholarity = sl.fk_id_perlevelscholarity',
		    array( 'level_scholarity' )
		)
		->join(
		    array( 'uc' => $dbUnitCost ),
		    'uc.id_unit_cost = fpc.fk_id_unit_cost',
		    array( 'unit_cost' => 'cost' )
		);
	
	return $select;
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detail( $id )
    {
	$select = $this->getSelect();
	$select->where( 'fpc.id_fp_contract = ?', $id );
	
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
	
	if ( !empty( $filters['fk_id_fefpeduinstitution'] ) )
	    $select->where( 'ap.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution'] );
	
	if ( !empty( $filters['category'] ) )
	    $select->where( 'sl.category = ?', $filters['category'] );
	
	if ( !empty( $filters['minimum_amount'] ) )
	    $select->where( 'fpc.amount >= ?', (float)$filters['minimum_amount'] );
	
	if ( !empty( $filters['maximum_amount'] ) )
	    $select->where( 'fpc.amount <= ?', (float)$filters['maximum_amount'] );
	
	if ( !empty( $filters['fk_id_perscholarity'] ) )
	    $select->where( 'pc.fk_id_perscholarity = ?', $filters['fk_id_perscholarity'] );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'fpc.date_start >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'fpc.date_finish <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listBeneficiaries( $id )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	$dbHandicapped = App_Model_DbTable_Factory::get( 'Handicapped' );
	$dbFPBeneficiary = App_Model_DbTable_Factory::get( 'FPBeneficiary' );
	$dbFPContract = App_Model_DbTable_Factory::get( 'FP_Contract' );
	
	$select->join(
		    array( 'sc' => $dbStudentClassPerData ),
		    'sc.fk_id_perdata = c.id_perdata',
		    array( 'status_class' => 'status', 'date_drop_out' )
		)
		->join(
		    array( 'fb' => $dbFPBeneficiary ),
		    'fb.fk_id_perdata = c.id_perdata',
		    array( 'amount' )
		)
		->join(
		    array( 'fc' => $dbFPContract ),
		    'fc.id_fp_contract = fb.fk_id_fp_contract
		    AND sc.fk_id_fefpstudentclass = fc.fk_id_fefpstudentclass',
		    array()
		)
		->joinLeft(
		    array( 'hc' => $dbHandicapped ),
		    'sc.fk_id_perdata = hc.fk_id_perdata',
		    array( 'id_handicapped' )
		)
		->where( 'fc.id_fp_contract = ?', $id )
		->group( 'id_perdata' )
		->order( array( 'first_name', 'last_name' ) );
	
	return $dbStudentClassPerData->fetchAll( $select );
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
	    'fk_id_sysform'	    => Fefop_Form_FPContract::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}