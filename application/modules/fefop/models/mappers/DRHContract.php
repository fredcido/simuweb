<?php

class Fefop_Model_Mapper_DRHContract extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_DRHContract
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_DRHContract();

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
	    $dataForm['amount'] = 0;
	    foreach ( $dataForm['expense'] as $costExpense )
		$dataForm['amount'] += App_General_String::toFloat ( $costExpense );
	    
	    $mapperRule = new Fefop_Model_Mapper_Rule();
	    $mapperRule->validate( $this->_message, $dataForm, Fefop_Model_Mapper_Expense::CONFIG_PFPCI_DRH );
	    
	    $idContract = $this->_saveContract( $this->_data );
	    $this->_message->addMessage( $this->_config->messages->success, App_Message::SUCCESS );
	    $dbAdapter->commit();
	    
	    return $idContract;
	    
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
    public function saveContracts()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dataForm = $this->_data;
	    $contracts = array();
	    foreach ( $dataForm['beneficiary'] as $id => $beneficiary ) {
		
		$dataContract = array(
		    'fk_id_drh_trainingplan' => $dataForm['fk_id_drh_trainingplan'],
		    'fk_id_drh_beneficiary'  => $id,
		    'fk_id_adddistrict'	     => $dataForm['fk_id_adddistrict'],
		    'date_start'	     => $dataForm['date_start'][$id],
		    'date_finish'	     => $dataForm['date_finish'][$id],
		    'duration_days'	     => $dataForm['duration_days'][$id],
		    'unit_cost'		     => $dataForm['unit_cost'][$id],
		    'training_fund'	     => $dataForm['training_fund'][$id],
		    'expense'		     => $dataForm['expense'][$id]
		);
		
		$contracts[] = $this->_saveContract( $dataContract );
	    }
	    
	    $this->_message->addMessage( $this->_config->messages->success, App_Message::SUCCESS );
	    $dbAdapter->commit();
	    
	    return $contracts;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @param array $data
     * @return int
     * @throws Exception
     */
    protected function _saveContract( $data )
    {
	$dateStart = new Zend_Date( $data['date_start'] );
	$dateFinish = new Zend_Date( $data['date_finish'] );

	// Check if the initial date is later than finish date
	if ( $dateStart->isLater( $dateFinish ) ) {

	    $message = 'Data loron keta liu data remata.';
	    $this->_message->addMessage( $message );
	    $this->addFieldError( 'date_start' )->addFieldError( 'date_finish' );
	    throw new Exception( $message );
	}

	// If there is no contract yet
	if ( empty( $data['fk_id_fefop_contract'] ) ) {

	    $dataContract = array(
		'module'	=> Fefop_Model_Mapper_Module::DRH,
		'district'	=> $data['fk_id_adddistrict']
	    );

	    $mapperFefopContract = new Fefop_Model_Mapper_Contract();
	    $data['fk_id_fefop_contract'] = $mapperFefopContract->save( $dataContract );
	}

	$data['date_start'] = $dateStart->toString( 'yyyy-MM-dd' );
	$data['date_finish'] = $dateFinish->toString( 'yyyy-MM-dd' );

	$dataForm = $data;
	$this->_data = $data;

	// Save the contract
	$dataForm['id_drh_contract'] = parent::_simpleSave( $this->_dbTable, false );

	// Save budget category
	$this->_saveExpenses( $dataForm );

	if ( empty( $data['id_drh_contract'] ) )
	    $history = 'REJISTU KONTRAKTU DRH: %s';
	else
	    $history = 'ATUALIZA KONTRAKTU DRH: %s';

	$history = sprintf( $history, $dataForm['id_drh_contract'] );
	$this->_sysAudit( $history );
	
	return $dataForm['id_drh_contract'];
    }
    
    /**
     * 
     * @param array $data
     */
    protected function _saveExpenses( $data )
    {
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	
	if ( empty( $data['expense'] ) ) {
	    
	    $message = 'La iha Rubrika Despeza';
	    $this->_message->addMessage( $message, App_Message::ERROR );
	    throw new Exception( $message );
	}
	
	// Save each budget category
	foreach ( $data['expense'] as $id => $costExpense ) {
	    
	    $whereBudget = array(
		'fk_id_fefop_contract = ?' => $data['fk_id_fefop_contract'],
		'fk_id_budget_category = ?' => $id,
	    );
	    
	    $row = $dbBudgetContract->fetchRow( $whereBudget );
	    if ( empty( $row ) ) {
		
		$row = $dbBudgetContract->createRow();
		$row->fk_id_budget_category = $id;
		$row->fk_id_fefop_contract = $data['fk_id_fefop_contract'];
		$row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$row->status = 1;
	    }
	    
	    $row->amount = App_General_String::toFloat( $costExpense );
	    $row->save();
	}
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listInstitutes()
    {
	$mapperInstitute = new Register_Model_Mapper_EducationInstitute();
	$select = $mapperInstitute->getSelectEducationInstitute();
	
	$dbFeContract = App_Model_DbTable_Factory::get( 'DRHContract' );
	
	$select->join(
		    array( 'fec' => $dbFeContract ),
		    'fec.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
		    array()
		);
	
	return $dbFeContract->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$select = $mapperContract->getSelect();
	
	$dbDRHContract = App_Model_DbTable_Factory::get( 'DRHContract' );
	$dbDRHTrainingPlan = App_Model_DbTable_Factory::get( 'DRHTrainingPlan' );
	$dbInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbScholarityArea = App_Model_DbTable_Factory::get( 'ScholarityArea' );
	$dbOcupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$dbAddCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$dbDRHBeneficiary = App_Model_DbTable_Factory::get( 'DRHBeneficiary' );
	$dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	
	$select->join( 
		    array( 'drhc' => $dbDRHContract ),
		    'drhc.fk_id_fefop_contract = c.id_fefop_contract'
		)
		->join( 
		    array( 'dhtp' => $dbDRHTrainingPlan ),
		    'dhtp.id_drh_trainingplan = drhc.fk_id_drh_trainingplan',
		    array( 'modality', 'city' )
		)
		->join(
		    array( 'ei' => $dbInstitution ),
		    'ei.id_fefpeduinstitution = dhtp.fk_id_fefpeduinstitution',
		    array( 'entity' => 'institution' )
		)
		->join(
		    array( 'sa' => $dbScholarityArea ),
		    'sa.id_scholarity_area = dhtp.fk_id_scholarity_area',
		    array( 'scholarity_area' )
		)
		->join(
		    array( 'ot' => $dbOcupationTimor ),
		    'ot.id_profocupationtimor = dhtp.fk_id_profocupationtimor',
		    array( 'ocupation_name_timor' )
		)
		->join(
		    array( 'ac' => $dbAddCountry ),
		    'ac.id_addcountry = dhtp.fk_id_addcountry',
		    array( 'country' )
		)
		->join(
		    array( 'db' => $dbDRHBeneficiary ),
		    'db.id_drh_beneficiary = drhc.fk_id_drh_beneficiary',
		    array( 'handicapped', 'gender' )
		)
		->join(
		    array( 's' => $dbStaff ),
		    's.id_staff = db.fk_id_staff',
		    array( 'id_staff', 'fk_id_perdata' )
		)
		->join(
		    array( 'eis' => $dbInstitution ),
		    's.fk_id_fefpeduinstitution = eis.id_fefpeduinstitution',
		    array( 'institution', 'responsible' => 'id_fefpeduinstitution' )
		)
		->join(
		    array( 'cli' => $dbPerData ),
		    's.fk_id_perdata = cli.id_perdata',
		    array( 'staff_name' => new Zend_Db_Expr( "CONCAT( cli.first_name, ' ', IFNULL(cli.medium_name, ''), ' ', cli.last_name )" ) )
		);
	
	return $select;
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listTrainingProvider()
    {
	$select = $this->getSelect();
	$select->group( array( 'institution' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listExpenses( $id )
    {
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	$dbDRHContract = App_Model_DbTable_Factory::get( 'DRHContract' );
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	
	$select = $dbBudgetCategory->select()
				   ->from( array( 'bc' => $dbBudgetCategory ) )
				   ->setIntegrityCheck( false )
				   ->join(
					array( 'bco' => $dbBudgetContract ),
					'bco.fk_id_budget_category = bc.id_budget_category',
					array( 'amount' )
				    )
				   ->join(
					array( 'fec' => $dbDRHContract ),
					'fec.fk_id_fefop_contract = bco.fk_id_fefop_contract',
					array()
				    )
				   ->join(
					array( 'fc' => $dbFEFOPContract ),
					'fec.fk_id_fefop_contract = fc.id_fefop_contract',
					array()
				    )
				    ->where( 'fec.id_drh_contract = ?', $id )
				    ->where( 'bco.status = ?', 1 )
				    ->order( array( 'id_budgetcategory_contract' ) );
	
	return $dbBudgetCategory->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listItemExpenses( $id )
    {
	$dbRiBudgetCategory = App_Model_DbTable_Factory::get( 'RIBudgetCategory' );
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	
	$select = $dbRiBudgetCategory->select()
				     ->from( array( 'ribc' => $dbRiBudgetCategory ) )
				     ->setIntegrityCheck( false )
				     ->join(
					array( 'bc' => $dbBudgetContract ),
					'bc.id_budgetcategory_contract = ribc.fk_id_budgetcategory_contract',
					array( 'fk_id_budget_category' )
				     )
				     ->where( 'ribc.fk_id_drh_contract = ?', $id )
				     ->order( array( 'description' ) );
	
	return $dbRiBudgetCategory->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detail( $id )
    {
	$select = $this->getSelect();
	$select->where( 'drhc.id_drh_contract = ?', $id );
	
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
	    $select->where( 'dhtp.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution'] );
	
	if ( !empty( $filters['training_provider'] ) )
	    $select->where( 'eis.id_fefpeduinstitution = ?', $filters['training_provider'] );
	
	if ( !empty( $filters['fk_id_scholarity_area'] ) )
	    $select->where( 'dhtp.fk_id_scholarity_area = ?', $filters['fk_id_scholarity_area'] );
	
	if ( !empty( $filters['fk_id_profocupationtimor'] ) )
	    $select->where( 'dhtp.fk_id_profocupationtimor = ?', $filters['fk_id_profocupationtimor'] );
	
	if ( !empty( $filters['fk_id_addcountry'] ) )
	    $select->where( 'dhtp.fk_id_addcountry = ?', $filters['fk_id_addcountry'] );
	
	if ( !empty( $filters['modality'] ) )
	    $select->where( 'dhtp.modality = ?', $filters['modality'] );
	
	if ( !empty( $filters['beneficiary'] ) )
	    $select->having( 'staff_name LIKE ?', '%' . $filters['beneficiary'] . '%' );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'dhtp.date_start >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'dhtp.date_finish <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
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
	    'fk_id_sysform'	    => Fefop_Form_DRHContract::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}