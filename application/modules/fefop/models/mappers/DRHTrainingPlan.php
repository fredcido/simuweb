<?php

class Fefop_Model_Mapper_DRHTrainingPlan extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_DRHTrainingPlan
     */
    protected $_dbTable;
    
    const LIMIT_AMOUNT = 25000;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_DRHTrainingPlan();

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
	    
	    $mapperRule = new Fefop_Model_Mapper_Rule();
	    $mapperRule->validate( $this->_message, $dataForm, Fefop_Model_Mapper_Expense::CONFIG_PFPCI_DRH_PLAN );
	    
	    $dateIni = new Zend_Date( $dataForm['date_start'] );
	    $dateFin = new Zend_Date( $dataForm['date_finish'] );
	    
	    // Check if the start date is later than finish date
	    if ( $dateIni->isLater( $dateFin ) ) {
		
		$this->_message->addMessage( 'Loron inisiu la bele liu loron remata.' );
		$this->addFieldError( 'date_start' )->addFieldError( 'date_finish' );
		return false;
	    }
	    
	    $this->_data['date_start'] = $dateIni->toString( 'yyyy-MM-dd' );
	    $this->_data['date_finish'] = $dateFin->toString( 'yyyy-MM-dd' );
	    $this->_data['amount'] = App_General_String::toFloat( $this->_data['amount'] );
	    $this->_data['amount_expenses'] = App_General_String::toFloat( $this->_data['amount_expenses'] );
	   
	     // Save the training plan
	    $dataForm['id_drh_trainingplan'] = parent::_simpleSave();
	    
	    //if ( $this->_data['amount'] > self::LIMIT_AMOUNT )
		//$this->_sendWarningAmount( $dataForm['id_drh_trainingplan'] );
	    
	    // Save beneficiaries
	    $this->_saveBeneficiaries( $dataForm );
	    
	    // Save Expenses
	    $this->_saveExpenses( $dataForm );
	    
	    $history = sprintf( 'REJISTU PLANU FORMASAUN DRH: %s', $dataForm['id_drh_trainingplan'] );
	    $this->_sysAudit( $history );
	    
	    $this->_checkTravelInsurance( $dataForm );
	    
	    $dbAdapter->commit();
	    
	    return $dataForm['id_drh_trainingplan'];
	    
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
    protected function _checkTravelInsurance( $data )
    {
	if ( empty( $data['need_insurance'] ) )
	    return true;
	
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$select = $dbBudgetCategory->select()
				   ->where( 'description LIKE ?', '%seguro de vida%' );
	
	$rowExpense = $dbBudgetCategory->fetchRow( $select );
	
	if ( empty( $rowExpense ) || 
	    !in_array( $rowExpense->id_budget_category, $data['expense'] ) ||
	    empty( $data['amount_expense'][$rowExpense->id_budget_category] ) ) {
	    
	    $this->_message->addMessage( 'La iha de Rúbrica ba Seguro de Vida', App_Message::ERROR );
	}
    }
    
    /**
     * 
     * @param int $idTraining
     */
    protected function _sendWarningAmount( $idTraining )
    {
	// Search the user who must receive notes when the amount is ultrapassed
	$noteTypeMapper = new Admin_Model_Mapper_NoteType();
	$users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::DRH_GREATER );

	$noteModelMapper = new Default_Model_Mapper_NoteModel();
	$noteMapper = new Default_Model_Mapper_Note();
	
	$dataNote = array(
	    'title'   => 'DRH TRAINING PLAN FOLIN HIRA LIU $' . self::LIMIT_AMOUNT,
	    'level'   => 0,
	    'message' => $noteModelMapper->getDrhTrainingPlan( $this->detail( $idTraining ) ),
	    'users'   => $users
	);

	$noteMapper->setData( $dataNote )->saveNote();
    }
    
    
    /**
     * 
     * @param array $data
     */
    protected function _saveBeneficiaries( $data )
    {
	$dbBeneficary = App_Model_DbTable_Factory::get( 'DRHBeneficiary' );
	
	$staffCurrent = array();
	foreach ( $data['staff'] as $idStaff ) {
	    
	    $staffCurrent[] = $idStaff;
	    
	    $where = array(
		'fk_id_staff = ?'		=> $idStaff,
		'fk_id_drh_trainingplan = ?'	=> $data['id_drh_trainingplan']
	    );
	    
	    $row = $dbBeneficary->fetchRow( $where );
	    if ( empty( $row ) ) {
		
		$row = $dbBeneficary->createRow();
		$row->fk_id_staff = $idStaff;
		$row->fk_id_drh_trainingplan = $data['id_drh_trainingplan'];
	    }
	    
	    $row->handicapped = $data['handicapped'][$idStaff];
	    $row->gender = $data['gender'][$idStaff];
	    $row->unit_cost = App_General_String::toFloat( $data['unit_cost'][$idStaff] );
	    $row->final_cost = App_General_String::toFloat( $data['final_cost'][$idStaff] );
	    $row->training_fund = App_General_String::toFloat( $data['training_fund'][$idStaff] );
	    $row->save();
	}
	
	// Remove all the Staff not in the data submitted
	if ( !empty( $staffCurrent ) ) {
	    
	    $whereDelete = array(
		'fk_id_staff NOT IN (?)'	=> $staffCurrent,
		'fk_id_drh_trainingplan = ?'	=> $data['id_drh_trainingplan']
	    );
	    
	    $dbBeneficary->delete( $whereDelete );
	}
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
	$dbDRHTrainingPlan = App_Model_DbTable_Factory::get( 'DRHTrainingPlan' );
	$dbInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbScholarityArea = App_Model_DbTable_Factory::get( 'ScholarityArea' );
	$dbOcupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$dbAddCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	
	$select = $dbDRHTrainingPlan->select()
				    ->from( array( 'tp' => $dbDRHTrainingPlan ) )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 'ei' => $dbInstitution ),
					'ei.id_fefpeduinstitution = tp.fk_id_fefpeduinstitution',
					array( 'entity' => 'institution' )
				    )
				    ->join(
					array( 'sa' => $dbScholarityArea ),
					'sa.id_scholarity_area = tp.fk_id_scholarity_area',
					array( 'scholarity_area' )
				    )
				    ->join(
					array( 'ot' => $dbOcupationTimor ),
					'ot.id_profocupationtimor = tp.fk_id_profocupationtimor',
					array( 'ocupation_name_timor' )
				    )
				    ->join(
					array( 'ac' => $dbAddCountry ),
					'ac.id_addcountry = tp.fk_id_addcountry',
					array( 'country' )
				    );
	
	return $select;
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
	    $select->where( 'tp.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution'] );
	
	if ( !empty( $filters['minimum_amount'] ) )
	    $select->where( 'tp.amount >= ?', (float)$filters['minimum_amount'] );
	
	if ( !empty( $filters['maximum_amount'] ) )
	    $select->where( 'tp.amount <= ?', (float)$filters['maximum_amount'] );
	
	if ( !empty( $filters['fk_id_scholarity_area'] ) )
	    $select->where( 'tp.fk_id_scholarity_area = ?', $filters['fk_id_scholarity_area'] );
	
	if ( !empty( $filters['fk_id_profocupationtimor'] ) )
	    $select->where( 'tp.fk_id_profocupationtimor = ?', $filters['fk_id_profocupationtimor'] );
	
	if ( !empty( $filters['fk_id_addcountry'] ) )
	    $select->where( 'tp.fk_id_addcountry = ?', $filters['fk_id_addcountry'] );
	
	if ( !empty( $filters['modality'] ) )
	    $select->where( 'tp.modality = ?', $filters['modality'] );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'tp.date_start >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'tp.date_finish <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     * 
     */
    public function detail( $id )
    {
	$select = $this->getSelect();
	$select->where( 'tp.id_drh_trainingplan = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listBeneficiaries( $id )
    {
	$dbDRHBeneficiary = App_Model_DbTable_Factory::get( 'DRHBeneficiary' );
	$dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	$dbInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbDRHContract = App_Model_DbTable_Factory::get( 'DRHContract' );
	
	$select = $this->getSelect();
	
	$select->join(
		    array( 'db' => $dbDRHBeneficiary ),
		    'db.fk_id_drh_trainingplan = tp.id_drh_trainingplan'
		)
		->joinLeft( 
		    array( 'drhc' => $dbDRHContract ),
		    'drhc.fk_id_drh_beneficiary = db.id_drh_beneficiary',
		    array( 'id_drh_contract' )
		)
		->join(
		    array( 's' => $dbStaff ),
		    's.id_staff = db.fk_id_staff',
		    array( 
			'id_staff', 
			'fk_id_perdata',
			'id_handicapped' => 'db.handicapped'
		    )
		)
		->join(
		    array( 'eis' => $dbInstitution ),
		    's.fk_id_fefpeduinstitution = eis.id_fefpeduinstitution',
		    array( 'institution' )
		)
		->join(
		    array( 'c' => $dbPerData ),
		    's.fk_id_perdata = c.id_perdata',
		    array(
			'staff_name' => new Zend_Db_Expr( "CONCAT( c.first_name, ' ', IFNULL(c.medium_name, ''), ' ', c.last_name )" )
		    )
		)
		->where( 'db.fk_id_drh_trainingplan = ?', $id )
		->order( array( 'staff_name') );
	
	return $dbDRHBeneficiary->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Sleect
     */
    public function getSelectBeneficiary()
    {
	$select = $this->getSelect();
	
	$dbDRHBeneficiary = App_Model_DbTable_Factory::get( 'DRHBeneficiary' );
	$dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	$dbDRHContract = App_Model_DbTable_Factory::get( 'DRHContract' );
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	$dbInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	
	$select->join(
		    array( 'db' => $dbDRHBeneficiary ),
		    'db.fk_id_drh_trainingplan = tp.id_drh_trainingplan'
		)
		->joinLeft( 
		    array( 'drhc' => $dbDRHContract ),
		    'drhc.fk_id_drh_beneficiary = db.id_drh_beneficiary',
		    array( 'id_drh_contract' )
		)
		->join(
		    array( 's' => $dbStaff ),
		    's.id_staff = db.fk_id_staff',
		    array( 
			'id_staff', 
			'fk_id_perdata',
			'id_handicapped' => 'db.handicapped'
		    )
		)
		->join(
		    array( 'eis' => $dbInstitution ),
		    's.fk_id_fefpeduinstitution = eis.id_fefpeduinstitution',
		    array( 'institution' )
		)
		->join(
		    array( 'c' => $dbPerData ),
		    's.fk_id_perdata = c.id_perdata',
		    array( 'staff_name' => new Zend_Db_Expr( "CONCAT( c.first_name, ' ', IFNULL(c.medium_name, ''), ' ', c.last_name )" ) )
		);
	
	return $select;
    }
    
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listBeneficiariesByFilter( $filters = array() )
    {
	$select = $this->getSelectBeneficiary();
	
	if ( !empty( $filters['fk_id_fefpeduinstitution'] ) )
	    $select->where( 'tp.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution'] );
	
	if ( !empty( $filters['fk_id_scholarity_area'] ) )
	    $select->where( 'tp.fk_id_scholarity_area = ?', $filters['fk_id_scholarity_area'] );
	
	if ( !empty( $filters['fk_id_profocupationtimor'] ) )
	    $select->where( 'tp.fk_id_profocupationtimor = ?', $filters['fk_id_profocupationtimor'] );
	
	if ( !empty( $filters['fk_id_addcountry'] ) )
	    $select->where( 'tp.fk_id_addcountry = ?', $filters['fk_id_addcountry'] );
	
	if ( !empty( $filters['modality'] ) )
	    $select->where( 'tp.modality = ?', $filters['modality'] );
	
	if ( !empty( $filters['training_plan'] ) )
	    $select->where( 'db.fk_id_drh_trainingplan = ?', $filters['training_plan'] );
	
	if ( !empty( $filters['beneficiary'] ) )
	    $select->having( 'staff_name LIKE ?', '%' . $filters['beneficiary'] . '%' );
	
	if ( !empty( $filters['no_contract'] ) ) {
	    
	    $dbDRHContract = App_Model_DbTable_Factory::get( 'DRHContract' );
	    $select->joinLeft(
			array( 'dhc' => $dbDRHContract ),
			'dhc.fk_id_drh_beneficiary = db.id_drh_beneficiary',
			array()
		    )
		    ->where( 'dhc.id_drh_contract IS NULL' );
	}
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'tp.date_start >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'tp.date_finish <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function fetchBeneficiary( $id )
    {
	$select = $this->getSelectBeneficiary();
	$select->where( 'db.id_drh_beneficiary = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listExpenses( $id )
    {
	$dbDRHBudgetCategory = App_Model_DbTable_Factory::get( 'DRHBudgetCategory' );
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	
	$select = $this->getSelect();
	
	$select->join(
		    array( 'dbc' => $dbDRHBudgetCategory ),
		    'dbc.fk_id_drh_trainingplan = tp.id_drh_trainingplan'
		)
		->join(
		    array( 'bc' => $dbBudgetCategory ),
		    'bc.id_budget_category = dbc.fk_id_budget_category'
		)
		->where( 'tp.id_drh_trainingplan = ?', $id )
		->order( array( 'description' ) );
	
	return $dbDRHBudgetCategory->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listIntitutes()
    {
	$select = $this->getSelect();
	$select->group( array( 'entity' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return string
     */
    public static function buildNum( $id )
    {
	$num = array( 'DTP', str_pad( $id, 5, '0', STR_PAD_LEFT ) );
	return implode( '-', $num );
    }
    
    /**
     * 
     * @param array $data
     */
    protected function _saveExpenses( $data )
    {
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'DRHBudgetCategory' );
	
	$budgetCategoryCurrent = array();
	foreach ( $data['expense'] as $idBudgetCategory ) {
	    
	    $budgetCategoryCurrent[] = $idBudgetCategory;
	    
	    $where = array(
		'fk_id_budget_category = ?'	=> $idBudgetCategory,
		'fk_id_drh_trainingplan = ?'	=> $data['id_drh_trainingplan']
	    );
	    
	    $row = $dbBudgetCategory->fetchRow( $where );
	    if ( empty( $row ) ) {
		
		$row = $dbBudgetCategory->createRow();
		$row->fk_id_budget_category = $idBudgetCategory;
		$row->fk_id_drh_trainingplan = $data['id_drh_trainingplan'];
	    }
	    
	    $row->amount = App_General_String::toFloat( $data['amount_expense'][$idBudgetCategory] );
	    $row->save();
	}
	
	// Remove all the Budget Category not in the data submitted
	if ( !empty( $budgetCategoryCurrent ) ) {
	    
	    $whereDelete = array(
		'fk_id_budget_category NOT IN (?)'  => $budgetCategoryCurrent,
		'fk_id_drh_trainingplan = ?'	    => $data['id_drh_trainingplan']
	    );
	    
	    $dbBudgetCategory->delete( $whereDelete );
	}
    }
    
    
    /**
     * 
     * @param array $data
     * @return array
     */
    public function calcTotals( $data )
    {
	$totals = array(
	    'staff'		=> array(),
	    'amount'		=> 0,
	    'amount_expenses'	=> 0,
	);
	
	foreach ( $data['amount_expense'] as $expense ) {
	    
	    $total = App_General_String::toFloat( $expense );
	    $totals['amount_expenses'] += $total;
	}
	
	$unitCost = round( ( $totals['amount_expenses'] / count( $data['staff'] ) ), 2 );
	
	foreach ( $data['staff'] as $idStaff ) {
	    
	    $amount = $unitCost;
	    if ( $data['gender'][$idStaff] == 'F' )
		$amount *= 1.1;
	    
	    if ( !empty( $data['handicapped'][$idStaff] ) )
		$amount *= 1.1;
	    
	    $totals['staff'][] = array(
		'id_staff'	=> $idStaff,
		'unit_cost'	=> $amount,
		'final_cost'	=> $unitCost,
		'training_cost' => $amount - $unitCost
	    );
	    
	    $totals['amount'] += $amount;
	}
	
	return $totals;
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
	    'fk_id_sysform'	    => Fefop_Form_DRHTrainingPlan::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}