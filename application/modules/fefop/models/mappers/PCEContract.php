<?php

class Fefop_Model_Mapper_PCEContract extends App_Model_Abstract
{
    
    /**
     * 
     * @var Model_DbTable_PCEContract
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_PCEContract();

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
	    
	    if ( Fefop_Model_Mapper_Module::CED == $dataForm['fk_id_fefop_modules'] ) {
		
		if ( !Client_Model_Mapper_Client::isHandicapped( $dataForm['fk_id_perdata'] ) ) {
		    
		    $this->_message->addMessage( 'Kliente nee la iha defisiensia', App_Message::ERROR );
		    return false;
		}
	    }
	    
	    $mapperRule = new Fefop_Model_Mapper_Rule();
	    
	    if ( Fefop_Model_Mapper_Module::CEC == $dataForm['fk_id_fefop_modules'] )
		$itemConfig = Fefop_Model_Mapper_Expense::CONFIG_PCE_CEC_FASE_I;
	    else
		$itemConfig = Fefop_Model_Mapper_Expense::CONFIG_PCE_CED_FASE_I;
	    
	    $mapperRule->validate( $this->_message, $dataForm, $itemConfig );
	    
	    // If there is no business plan yet
	    if ( empty( $dataForm['id_pce_contract'] ) ) {
		
		$dataContract = array(
		    'module'	=> $dataForm['fk_id_fefop_modules'],
		    'district'	=> $dataForm['fk_id_adddistrict'],
		    'status'	=> Fefop_Model_Mapper_Status::ANALYSIS
		);
		
		$mapperFefopContract = new Fefop_Model_Mapper_Contract();
		$dataForm['fk_id_fefop_contract'] = $mapperFefopContract->save( $dataContract );
	    }
		
	    $this->_data = $dataForm;
	    $this->_data['amount'] = App_General_String::toFloat( $this->_data['amount'] );
	    
	    $id = parent::_simpleSave();
	    
	    $dataForm['fk_id_pce_contract'] = $id;
	    
	    // Save Expenses
	    $this->_saveExpensesContract( $dataForm );
	    
	    $history = "INSERE KONTRATU PCE FASE I: %s BA PROGRAMA PCE IHA MODULU: %s";
	    $history = sprintf( $history, $dataForm['fk_id_fefop_contract'], $dataForm['fk_id_fefop_modules'] );
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
     */
    protected function _saveExpensesContract( $data )
    {
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	$dbPceBudgetCategory = App_Model_DbTable_Factory::get( 'PCEBudgetCategory' );
	
	// Delete all the detailed items
	$where = array( 'fk_id_pce_contract = ?' => $data['fk_id_pce_contract'] );
	$dbPceBudgetCategory->delete( $where );
	
	// Save each budget category
	foreach ( $data['expense'] as $expense ) {
	    
	    foreach ( $expense as $id => $cost ) {
	    
		 $whereBudget = array(
		    'fk_id_fefop_contract = ?'  => $data['fk_id_fefop_contract'],
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

		$row->amount = App_General_String::toFloat( $cost );
		$row->save();
		
		// If it wasn't defined detailed items
		if ( empty( $data['detailed_expense']['quantity'][$id] ) ) continue;
		
		$detailedExpense = $data['detailed_expense'];

		// For each budget category, save its detailed items
		foreach ( $detailedExpense['quantity'][$id] as $count => $itemExpense ) {

		    $rowItemExpense = $dbPceBudgetCategory->createRow();
		    $rowItemExpense->fk_id_pce_contract = $data['fk_id_pce_contract'];
		    $rowItemExpense->fk_id_budget_category = $id;
		    $rowItemExpense->description = $detailedExpense['item_expense'][$id][$count];
		    $rowItemExpense->quantity = $itemExpense;
		    $rowItemExpense->amount_unit = App_General_String::toFloat( $detailedExpense['amount_unit'][$id][$count] );
		    $rowItemExpense->amount_total = App_General_String::toFloat( $detailedExpense['amount_total'][$id][$count] );
		    $rowItemExpense->save();
		}
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
	
	$dbPCEContract = App_Model_DbTable_Factory::get( 'PCEContract' );
	$dbStudentClass = App_Model_DbTable_Factory::get( 'FEFPStudentClass' );
	$dbIsicDivision = App_Model_DbTable_Factory::get( 'ISICDivision' );
	$dbISICClassTimor = App_Model_DbTable_Factory::get( 'ISICClassTimor' );
	$dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbAddAddress = App_Model_DbTable_Factory::get( 'AddAddress' );
	$dbAddDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$dbAddSubDistrict = App_Model_DbTable_Factory::get( 'AddSubDistrict' );
	
	$select->join(
		    array( 'pcc' => $dbPCEContract ),
		    'pcc.fk_id_fefop_contract = c.id_fefop_contract'
		)
		->join(
		    array( 'sc' => $dbStudentClass ),
		    'pcc.fk_id_fefpstudentclass = sc.id_fefpstudentclass',
		    array( 
			'class_name',
			'date_start'	=> new Zend_Db_Expr( 'sc.start_date' ),
			'date_finish'	=> new Zend_Db_Expr( 'sc.schedule_finish_date' ),
			'duration'	=> new Zend_Db_Expr( 'DATEDIFF(sc.schedule_finish_date, sc.start_date)' ),
		    )
		)
		->join(
		    array( 'scc' => $dbScholarity ),
		    'scc.id_perscholarity = sc.fk_id_perscholarity',
		    array( 
			'scholarity',
			'external_code'
		    )
		)
		->joinLeft(
		    array( 'add' => $dbAddAddress ),
		    'add.fk_id_fefpeduinstitution = sc.fk_id_fefpeduinstitution',
		    array()
		)
		->joinLeft(
		    array( 'addd' => $dbAddDistrict ),
		    'addd.id_adddistrict = add.fk_id_adddistrict',
		    array( 'district_course' => 'District' )
		)
		->joinLeft(
		    array( 'adsd' => $dbAddSubDistrict ),
		    'adsd.id_addsubdistrict = add.fk_id_addsubdistrict',
		    array( 'sub_district' )
		)
		->join(
		    array( 'id' => $dbIsicDivision ),
		    'pcc.fk_id_isicdivision = id.id_isicdivision',
		    array( 'name_disivion' )
		)
		->join(
		    array( 'ct' => $dbISICClassTimor ),
		    'pcc.fk_id_isicclasstimor = ct.id_isicclasstimor',
		    array( 'name_classtimor' )
		)
		->group( array( 'id_pce_contract' ) );
	
	return $select;
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
	$dbPCEContract=  App_Model_DbTable_Factory::get( 'PCEContract' );
	
	$select = $dbBudgetCategory->select()
				   ->from( array( 'bc' => $dbBudgetCategory ) )
				   ->setIntegrityCheck( false )
				   ->join(
					array( 'bco' => $dbBudgetContract ),
					'bco.fk_id_budget_category = bc.id_budget_category',
					array( 'amount' )
				    )
				   ->join(
					array( 'pcc' => $dbPCEContract ),
					'pcc.fk_id_fefop_contract = bco.fk_id_fefop_contract',
					array()
				    )
				    ->where( 'pcc.id_pce_contract = ?', $id )
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
	$dbPCEBudgetCategory = App_Model_DbTable_Factory::get( 'PCEBudgetCategory' );
	
	$select = $dbPCEBudgetCategory->select()
				     ->from( array( 'pcbc' => $dbPCEBudgetCategory ) )
				     ->setIntegrityCheck( false )
				     ->where( 'pcbc.fk_id_pce_contract = ?', $id )
				     ->order( array( 'id_pce_budget_category' ) );
	
	return $dbPCEBudgetCategory->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detail( $id )
    {
	$select = $this->getSelect();
	$select->where( 'pcc.id_pce_contract = ?', $id );
	
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
	
	if ( !empty( $filters['fk_id_fefpstudentclass'] ) )
	    $select->where( 'pcc.fk_id_fefpstudentclass = ?', $filters['fk_id_fefpstudentclass'] );
	
	if ( !empty( $filters['minimum_amount'] ) )
	    $select->where( 'pcc.amount >= ?', (float)$filters['minimum_amount'] );
	
	if ( !empty( $filters['maximum_amount'] ) )
	    $select->where( 'pcc.amount <= ?', (float)$filters['maximum_amount'] );
	
	if ( !empty( $filters['fk_id_adddistrict'] ) )
	    $select->where( 'pcc.fk_id_adddistrict = ?', $filters['fk_id_adddistrict'] );
	
	if ( !empty( $filters['beneficiary'] ) )
	    $select->having( 'beneficiary LIKE ?', '%' . $filters['beneficiary'] . '%' );
	
	if ( !empty( $filters['fk_id_isicdivision'] ) )
	    $select->where( 'pcc.fk_id_isicdivision = ?', $filters['fk_id_isicdivision'] );
	
	if ( !empty( $filters['fk_id_isicclasstimor'] ) )
	    $select->where( 'pcc.fk_id_isicclasstimor = ?', $filters['fk_id_isicclasstimor'] );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'sc.start_date >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'sc.start_date <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listStudentClassContract()
    {
	$mapperStudentClasss = new StudentClass_Model_Mapper_StudentClass();
	$select = $mapperStudentClasss->getSelectClass();
	
	$dbPCEContract = App_Model_DbTable_Factory::get( 'PCEContract' );
	
	$select->join(
		    array( 'pcc' => $dbPCEContract ),
		    'pcc.fk_id_fefpstudentclass = sc.id_fefpstudentclass',
		    array()
		)
		->group( array( 'id_fefpstudentclass' ) );
	
	return $dbPCEContract->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $client
     * @param int $module
     * @return Zend_Db_Table_Row
     */
    public function getContractByClientModule( $client, $module )
    {
	$select = $this->getSelect();
	
	$select->where( 'pcc.fk_id_perdata = ?', $client )
		->where( 'pcc.fk_id_fefop_modules', $module );
	
	return $this->_dbTable->fetchRow( $select );
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
	    'fk_id_sysform'	    => Fefop_Form_PceFaseContract::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}