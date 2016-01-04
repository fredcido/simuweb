<?php

class External_Model_Mapper_Pce extends App_Model_Abstract
{
    /**
     *
     * @var array
     */
    protected $_limitAmounts = array(
	Fefop_Model_Mapper_Module::CEG => 10000,
	Fefop_Model_Mapper_Module::CEC => 11000,
	Fefop_Model_Mapper_Module::CED => 11000
    );
    
    /**
     * 
     * @var Model_DbTable_FEFOPPce
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_BusinessPlan();

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
	  
	    // If there is no business plan yet
	    if ( empty( $dataForm['id_businessplan'] ) ) {
		
		$mapperModule = new Fefop_Model_Mapper_Module();
		$module = $mapperModule->fetchModule( $dataForm['module'] );
		
		// Get the District
		$mapperDistrict = new Register_Model_Mapper_AddDistrict();
		$district = $mapperDistrict->fetchRow( $dataForm['fk_id_adddistrict'] );
		
		$dataForm += array(
		    'fk_id_fefop_modules'   => $module->id_fefop_modules,
		    'num_district'	    => $district->acronym,
		    'num_module'	    => $module->num_module,
		    'num_year'		    => date( 'y' )
		);
		
		$dataForm['num_sequence'] = str_pad( $this->_getNumSequence( $dataForm ), 4, '0', STR_PAD_LEFT );
		$dataForm['bussines_plan_developer'] = $dataForm['fk_id_perdata'];
		
		//If it is not CEG, fetch the first contract
		if ( Fefop_Model_Mapper_Module::CEG != $dataForm['module'] ) {
		    
		    $dbPceContract = App_Model_DbTable_Factory::get( 'PCEContract' );
		    $whereContract = array(
			'fk_id_fefop_modules = ?' => $dataForm['module'],
			'fk_id_perdata = ?'	  => $dataForm['fk_id_perdata'],
		    );
		    
		    $pceContract = $dbPceContract->fetchRow( $whereContract );
		    $dataForm['fk_id_pce_contract'] = $pceContract->id_pce_contract;
		}
	    }
	    
	    // If it is CED module, check to see if the client has any disability
	    if ( Fefop_Model_Mapper_Module::CED == $dataForm['module']  
		    && !Client_Model_Mapper_Client::isHandicapped( $dataForm['fk_id_perdata'] ) ) {
		
		$this->_message->addMessage( "Benefisiariu ne'e la iha defisiénsia ba halo modulu CED.", App_Message::ERROR );
		return false;
	    }
	    
	    // Check to see if there are another participants
	    $dataForm['partisipants'] = !empty( $dataForm['clients'] ) ? 'G' : 'S';
	    
	    if ( !empty( $dataForm['clients'] ) )
		$dataForm['total_partisipants'] = count( $dataForm['clients'] ) + 1;
	    else
		$dataForm['total_partisipants'] = 1;
		
	    $this->_data = $dataForm;
	    
	    $id = parent::_simpleSave();
	    
	    $dataForm['id_businessplan'] = $id;
	    
	    if ( !empty( $dataForm['clients'] ) && count( $dataForm['clients'] ) > 5 )
		throw new Exception( 'More then five clients' );
	    
	    // save participants in the Business Plan
	    $this->_saveParticipants( $dataForm );
	    
	    $history = "INSERE PLANU NEGOSIU: %s BA PROGRAMA PCE IHA MODULU: %s";
	    $history = sprintf( $history, $id, $dataForm['id_businessplan'] );
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
     * @return int
     */
    public function _getNumSequence( $data )
    {
	$dbBusinessPlan = App_Model_DbTable_Factory::get( 'BusinessPlan' );
	
	$select = $dbBusinessPlan->select()
			   ->from ( 
				array( 'bp' => $dbBusinessPlan ),
				array( 'num_sequence' => new Zend_Db_Expr( 'IFNULL( MAX( num_sequence ), 0 ) + 1' ) )
			    )
			   ->where( 'bp.num_district = ?', $data['num_district'] )
			   ->where( 'bp.num_module = ?', $data['num_module'] )
			   ->where( 'bp.num_year = ?', $data['num_year'] );
	
	$row = $dbBusinessPlan->fetchRow( $select );
	return $row->num_sequence;
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveBusinessPlan()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dataForm = $this->_data;
	  
	    // Save in the Business Plan table
	    $id = parent::_simpleSave();
	    
	    // Save the descriptions
	    $this->_saveDescriptions( $dataForm );
	    
	    // Save totals
	    $this->_saveTotals( $dataForm );
	    
	    // Save totals
	    $this->_saveExpenses( $dataForm );
	    
	    $history = "ATUALIZA PLANU NEGOISU: %s";
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
     * @return boolean
     */
    public function saveTechnicalFeedback()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbTechnicalFeedback = App_Model_DbTable_Factory::get( 'TechnicalFeedback' );
	    $this->_data['fk_id_sysuser'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	  
	    // Save in the Business Plan table
	    $id = parent::_simpleSave( $dbTechnicalFeedback );
	    
	    $history = "INSERE TECHNICAL FEEDBACK BA BUSINESS PLAN: %s";
	    $history = sprintf( $history, $this->_data['fk_id_businessplan'] );
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
     * @return boolean
     */
    public function saveCouncilDecision()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbBusinessPlanField = App_Model_DbTable_Factory::get( 'BusinessPlanField' );
	    
	    $where = array(
		'identifier = ?'	=> 'council_negative',
		'fk_id_businessplan = ?' => $this->_data['fk_id_businessplan']
	    );
	    
	    $description = $dbBusinessPlanField->fetchRow( $where );
	    if ( empty( $description ) ) {
		
		$description = $dbBusinessPlanField->createRow();
		$description->fk_id_businessplan = $this->_data['fk_id_businessplan'];
		$description->identifier = 'council_negative';
	    }
	    
	    $description->value = $this->_data['council_negative'];
	    
	    $description->save();
		    
	    if ( !empty( $this->_data['approved'] ) )
		$this->_createContractFromBusinessPlan( $this->_data['fk_id_businessplan'] );
	  
	    $history = "INSERE PARESER KONSELLU BA BUSINESS PLAN: %s";
	    $history = sprintf( $history, $this->_data['fk_id_businessplan'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return $this->_data['fk_id_businessplan'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * Replicates all Business Plan to a Contract
     * @param int $id
     */
    protected function _createContractFromBusinessPlan( $id )
    {
	$businessPlan = $this->fetchBusinessPlan( $id );
	
	$mapperBudget = new Fefop_Model_Mapper_Expense();
	$itemConfig = $mapperBudget->getModuleToItem( $businessPlan['fk_id_fefop_modules'] );
	
	$dataValidate = array(
	    'fk_id_perdata' => $businessPlan->fk_id_perdata,
	    'amount'	    => $this->getTotal( $id, 'total_expense' ),
	);
	
	$mapperRule = new Fefop_Model_Mapper_Rule();
	$mapperRule->validate( $this->_message, $dataValidate, $itemConfig );
	
	$dataContract = array(
	    'module'	=> $businessPlan['fk_id_fefop_modules'],
	    'district'	=> $businessPlan['fk_id_adddistrict'],
	    'status'	=> Fefop_Model_Mapper_Status::PROGRESS
	);

	$mapperFefopContract = new Fefop_Model_Mapper_Contract();
	$idContract = $mapperFefopContract->save( $dataContract );
	
	// Save relationship between contract and businessplan
	$businessPlanRow = $this->fetchRow( $id );
	$businessPlanRow->fk_id_fefop_contract = $idContract;
	$businessPlanRow->save();
	
	$expenses = $this->listExpenses( $id, $itemConfig )->toArray();
	
	$dbBudgetCategoryContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	
	foreach ( $expenses as $expense ) {
	    
	    $row = $dbBudgetCategoryContract->createRow();
	    $row->fk_id_budget_category = $expense['id_budget_category'];
	    $row->fk_id_fefop_contract = $idContract;
	    $row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $row->amount = $expense['amount'];
	    $row->status = 1;
	    $row->save();
	}
    }
    
    /**
     * 
     * @return boolean
     */
    public function saveRevision()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbRevision = App_Model_DbTable_Factory::get( 'BusinessPlanRevision' );
	    $this->_data['fk_id_sysuser'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    
	    if ( !empty( $this->_data['return_revision'] ) ) {
		
		$dbBusinessPlan = App_Model_DbTable_Factory::get( 'BusinessPlan' );
		
		$where = array( 'id_businessplan = ?' => $this->_data['fk_id_businessplan'] );
		$row = $dbBusinessPlan->fetchRow( $where );
		
		$row->submitted = 0;
		$row->date_sumitted= null;
		$row->save();
	    }
	    
	    // Save in the Business Plan table
	    $id = parent::_simpleSave( $dbRevision );
	   
	    $history = "INSERE REVISAUN BA BUSINESS PLAN: %s";
	    $history = sprintf( $history, $this->_data['fk_id_businessplan'] );
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
    public function saveFinishPlan()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $this->_data['date_sumitted'] = Zend_Date::now()->toString( 'yyyy-MM-dd' );
	  
	    // Save in the Business Plan table
	    $id = parent::_simpleSave();
	    
	    $history = "SUBMETE PLANU NEGOISU: %s";
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
     * @param array $data
     * @return boolean
     */
    protected function _saveDescriptions( $data )
    {
	$dbBusinessPlanField = App_Model_DbTable_Factory::get( 'BusinessPlanField' );
	
	if ( empty( $data['dynamic_fields'] ) )
	    return false;
	
	foreach ( $data['dynamic_fields'] as $id => $value ) {
	    
	    $where = array(
		'fk_id_businessplan = ?' => $data['id_businessplan'],
		'identifier = ?'	 => $id,
	    );
	    
	    $description = $dbBusinessPlanField->fetchRow( $where );
	    if ( empty( $description ) ) {
		
		$description = $dbBusinessPlanField->createRow();
		$description->fk_id_businessplan = $data['id_businessplan'];
		$description->identifier = $id;
	    }
	    
	    $description->value = trim($value);
	    $description->save();
	}
    }
    
    /**
     * 
     * @param array $data
     * @return boolean
     */
    protected function _saveTotals( $data )
    {
	$dbBusinessPlanTotal = App_Model_DbTable_Factory::get( 'BusinessPlanTotal' );
	
	if ( empty( $data['total_fields'] ) )
	    return false;
	
	foreach ( $data['total_fields'] as $id => $value ) {
	    
	    $where = array(
		'fk_id_businessplan = ?' => $data['id_businessplan'],
		'identifier = ?'	 => $id,
	    );
	    
	    $total = $dbBusinessPlanTotal->fetchRow( $where );
	    if ( empty( $total ) ) {
		
		$total = $dbBusinessPlanTotal->createRow();
		$total->fk_id_businessplan = $data['id_businessplan'];
		$total->identifier = $id;
	    }
	    
	    $total->amount = App_General_String::toFloat( $value );
	    $total->save();
	}
    }
    
    /**
     * 
     * @param array $data
     */
    protected function _saveExpenses( $data )
    {
	$dbBusinessPlanExpense = App_Model_DbTable_Factory::get( 'BusinessPlanExpense' );
	$dbBusinessPlanBudgetCategory = App_Model_DbTable_Factory::get( 'BusinessPlanBugdetCategory' );
	#$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	
	// Delete all the detailed items
	$where = array( 'fk_id_businessplan = ?' => $data['id_businessplan'] );
	$dbBusinessPlanBudgetCategory->delete( $where );
	$dbBusinessPlanExpense->delete( $where );
	
	// Fetch Business Plan
	$contract = $this->fetchBusinessPlan( $data['id_businessplan'] );
	
	// Save each budget category
	foreach ( $data['expense'] as $typeExpense => $expense ) {
	    
	    foreach ( $expense as $id => $cost ) {
	    
		$whereBudget = array(
		    'fk_id_businessplan = ?'	=> $contract['id_businessplan'],
		    'fk_id_budget_category = ?' => $id,
		);

		$row = $dbBusinessPlanExpense->fetchRow( $whereBudget );
		if ( empty( $row ) ) {

		    $row = $dbBusinessPlanExpense->createRow();
		    $row->fk_id_budget_category = $id;
		    $row->fk_id_businessplan = $contract['id_businessplan'];
		}

		$row->amount = App_General_String::toFloat( $cost );
		$idBudgetExpense = $row->save();

		// If it wasn't defined detailed items
		if ( $typeExpense != "cost_expense" || empty( $data['detailed_expense']['quantity'][$id] ) ) continue;
		
		$detailedExpense = $data['detailed_expense'];

		// For each budget category, save its detailed items
		foreach ( $detailedExpense['quantity'][$id] as $count => $itemExpense ) {

		    $rowItemExpense = $dbBusinessPlanBudgetCategory->createRow();
		    $rowItemExpense->fk_id_businessplan = $data['id_businessplan'];
		    $rowItemExpense->fk_id_business_plan_expense = $idBudgetExpense;
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
     * @param array $data
     */
    protected function _saveParticipants( $data )
    {
	$dbPceGroup = App_Model_DbTable_Factory::get( 'PCEGroup' );
	
	$update = array( 'status' => 0 );
	$where = array( 'fk_id_businessplan = ?' => $data['id_businessplan'] );
	
	$dbPceGroup->update( $update, $where );
	
	if ( !empty( $data['clients'] ) ) {
	    
	    foreach ( $data['clients'] as $client ) {
		
		$where = array(
		    'fk_id_perdata = ?'		=> $client,
		    'fk_id_businessplan = ?'	=> $data['id_businessplan']
		);
		
		$row = $dbPceGroup->fetchRow( $where );
		if ( empty( $row ) ) {
		    
		    $row = $dbPceGroup->createRow();
		    $row->fk_id_perdata = $client;
		    $row->fk_id_businessplan = $data['id_businessplan'];
		    
		    $clientBusinessPlan = $this->fetchBusinessPlanByClient( $client, $data['module'] );
		    if ( !empty( $clientBusinessPlan ) )
			$row->business_plan = $clientBusinessPlan->id_businessplan;
		}
		
		$row->status = 1;
		$row->save();
	    }
	}
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
	    'fk_id_sysform'	    => External_Form_Pce::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
    
    /**
     * 
     * @param int $client
     * @param int $module
     * @return Zend_Db_Table_Row
     */
    public function fetchBusinessPlanByClient( $client, $module )
    {
	$select = $this->getSelectBusiness();
	
	$select->where( 'bp.fk_id_perdata = ?', $client )
		->where( 'bp.fk_id_fefop_modules = ?', $module );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
    *
    * @param Zend_Db_Table_Row $row
    * @return string 
    */
   public static function buildNumRow( $row )
   {
	$numContract = array(
	    $row['num_module'],
	    $row['num_district'],
	    $row['num_year'],
	    $row['num_sequence']
	);
	
	return implode( '-', $numContract );
    }
    
    /**
     * 
     * @param int $idBusinessPlan
     * @return Zend_Db_Table_Row
     */
    public function fetchBusinessPlan( $idBusinessPlan )
    {
	$select = $this->getSelectBusiness();
	$select->where( 'bp.id_businessplan = ?', $idBusinessPlan );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @param int $idBusinessPlan
     * @return Zend_Db_Table_Rowset
     */
    public function listClientBusinessPlan( $idBusinessPlan )
    {
	$clientMapper = new Client_Model_Mapper_Client();
	$selectClient = $clientMapper->selectClient();
	
	$dbPceGroup = App_Model_DbTable_Factory::get( 'PCEGroup' );
	$dbBusinessPlan = App_Model_DbTable_Factory::get( 'BusinessPlan' );
	
	$selectClient->join(
			array( 'pcg' => $dbPceGroup ),
			'pcg.fk_id_perdata = c.id_perdata',
			array( 'business_plan' )
		    )
		    ->join(
			array( 'bp' => $dbBusinessPlan ),
			'bp.id_businessplan = pcg.fk_id_businessplan',
			array( 'bussines_plan_developer', 'fk_id_fefop_modules' )
		    )
		    ->where( 'pcg.status = ?', 1 )
		    ->where( 'pcg.fk_id_businessplan = ?', $idBusinessPlan );
	
	return $dbPceGroup->fetchAll( $selectClient );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listFieldsBusinessPlan( $id )
    {
	$dbBusinessPlanField = App_Model_DbTable_Factory::get( 'BusinessPlanField' );
	return $dbBusinessPlanField->fetchAll( array( 'fk_id_businessplan = ?' => $id ) );
    }
    
    /**
     * 
     * @param int $id
     * @return array
     */
    public function groupFieldsBusinessPlan( $id )
    {
	$fields = $this->listFieldsBusinessPlan( $id );
	
	$dataFields = array();
	foreach ( $fields as $field )
	    $dataFields[$field->identifier] = trim( $field->value );
	
	return $dataFields;
    }
    
    /**
     * 
     * @param int $id
     * @param string $description
     * @return float
     */
    public function getDescription( $id, $description )
    {
	$descriptions = $this->groupFieldsBusinessPlan($id);
	return empty( $descriptions[$description] ) ? '' : $descriptions[$description];
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listTotals( $id )
    {
	$dbBusinessPlanTotal = App_Model_DbTable_Factory::get( 'BusinessPlanTotal' );
	return $dbBusinessPlanTotal->fetchAll( array( 'fk_id_businessplan = ?' => $id ) );
    }
    
    /**
     * 
     * @param int $id
     * @return array
     */
    public function groupTotals( $id )
    {
	$fields = $this->listTotals( $id );
	
	$dataTotals = array();
	foreach ( $fields as $field )
	    $dataTotals[$field->identifier] = $field->amount;
	
	return $dataTotals;
    }
    
    /**
     * 
     * @param int $id
     * @param string $total
     * @return float
     */
    public function getTotal( $id, $total )
    {
	$totals = $this->groupTotals($id);
	return empty( $totals[$total] ) ? 0 : $totals[$total];
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listExpenses( $id, $typeItem )
    {
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbBusinessPlanExpense = App_Model_DbTable_Factory::get( 'BusinessPlanExpense' );
	$dbBudgetCategoryConfiguration = App_Model_DbTable_Factory::get( 'BudgetCategoryConfiguration' );
	
	$select = $dbBudgetCategory->select()
				   ->from( array( 'bc' => $dbBudgetCategory ) )
				   ->setIntegrityCheck( false )
				   ->join(
					array( 'bpe' => $dbBusinessPlanExpense ),
					'bpe.fk_id_budget_category = bc.id_budget_category',
					array( 'amount' )
				   )
				   ->join(
					array( 'bcc' => $dbBudgetCategoryConfiguration ),
					'bcc.fk_id_budget_category  = bpe.fk_id_budget_category',
					array()
				    )
				    ->where( 'bpe.fk_id_businessplan = ?', $id )
				    ->where( 'bcc.identifier = ?', $typeItem )
				    ->order( array( 'id_business_plan_expense' ) );
	
	return $dbBudgetCategory->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $idBusinessPlan
     * @return boolean
     */
    public function hasBudgetCategory( $idBusinessPlan )
    {
	$dbBusinessPlan = App_Model_DbTable_Factory::get( 'BusinessPlan' );
	$dbBusinessPlanExpense = App_Model_DbTable_Factory::get( 'BusinessPlanExpense' );
	
	$select = $dbBusinessPlan->select()
				 ->from( array( 'bp' => $dbBusinessPlan ) )
				 ->setIntegrityCheck( false )
				 ->join(
					array( 'bpe' => $dbBusinessPlanExpense ),
					'bp.id_businessplan = bpe.fk_id_businessplan',
					array()
				 )
				 ->where( 'bp.id_businessplan = ?', $idBusinessPlan );
	
	$row = $dbBusinessPlan->fetchRow( $select );
	return !empty( $row );
    }
    
     /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listItemExpenses( $id )
    {
	$dbBusinessPlanBudgetCategory = App_Model_DbTable_Factory::get( 'BusinessPlanBugdetCategory' );
	$dbBusinessPlanExpense = App_Model_DbTable_Factory::get( 'BusinessPlanExpense' );
	
	$select = $dbBusinessPlanBudgetCategory->select()
				     ->from( array( 'bpbc' => $dbBusinessPlanBudgetCategory ) )
				     ->setIntegrityCheck( false )
				     ->join(
					array( 'bpe' => $dbBusinessPlanExpense ),
					'bpe.id_business_plan_expense = bpbc.fk_id_business_plan_expense',
					array( 'fk_id_budget_category' )
				     )
				     ->where( 'bpbc.fk_id_businessplan = ?', $id )
				     ->order( array( 'description' ) );
	
	return $dbBusinessPlanBudgetCategory->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function getSelectBusiness()
    {
	//$mapperContract = new Fefop_Model_Mapper_Contract();
	//$select = $mapperContract->getSelect();
	
	$dbBusinessPlan = App_Model_DbTable_Factory::get( 'BusinessPlan' );
	$dbAddSubDistrict = App_Model_DbTable_Factory::get( 'AddSubDistrict' );
	$dbAddSucu = App_Model_DbTable_Factory::get( 'AddSucu' );
	$dbIsicDivision = App_Model_DbTable_Factory::get( 'ISICDivision' );
	$dbISICClassTimor = App_Model_DbTable_Factory::get( 'ISICClassTimor' );
	$dbTypeEnterprise = App_Model_DbTable_Factory::get( 'FEFPTypeEnterprise' );
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$dbFEFOPModules = App_Model_DbTable_Factory::get( 'FEFOPModules' );
	$dbPCEGroup = App_Model_DbTable_Factory::get( 'PCEGroup' );
	
	$select = $dbBusinessPlan->select()
				->from( array( 'bp' => $dbBusinessPlan ) )
				->setIntegrityCheck( false )
				->joinLeft(
				    array( 'su' => $dbAddSucu ),
				    'bp.fk_id_addsucu = su.id_addsucu',
				    array( 'sucu' )
				)
				->joinLeft(
				    array( 'pcg' => $dbPCEGroup ),
				    'pcg.business_plan = bp.id_businessplan',
				    array( 'business_group' => 'fk_id_businessplan' )
				)
				->joinLeft(
				    array( 'sd' => $dbAddSubDistrict ),
				    'su.fk_id_addsubdistrict = sd.id_addsubdistrict',
				    array( 'sub_district' )
				)
				->joinLeft(
				    array( 'te' => $dbTypeEnterprise ),
				    'te.id_fefptypeenterprise = bp.fk_id_fefptypeenterprise',
				    array( 'type_enterprise' )
				)
				->join(
				    array( 'id' => $dbIsicDivision ),
				    'bp.fk_id_isicdivision = id.id_isicdivision',
				    array( 'name_disivion' )
				)
				->join(
				    array( 'ad' => $dbDistrict ),
				    'ad.id_adddistrict = bp.fk_id_adddistrict',
				    array( 'district' => 'District' )
				)
				->join(
				    array( 'fm' => $dbFEFOPModules ),
				    'fm.id_fefop_modules = bp.fk_id_fefop_modules',
				    array( 'module' => 'description' )
				)
				->join(
				    array( 'ct' => $dbISICClassTimor ),
				    'bp.fk_id_isicclasstimor = ct.id_isicclasstimor',
				    array( 'name_classtimor' )
				)
				->group( array( 'id_businessplan' ) );
	
	return $select;
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listBeneficiaries()
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$dbBusinessPlan = App_Model_DbTable_Factory::get( 'BusinessPlan' );
	
	$select->join(
		    array( 'bp' => $dbBusinessPlan ),
		    'bp.bussines_plan_developer = c.id_perdata',
		    array()
		)
		->group( array( 'c.id_perdata' ) );
	
	return $dbBusinessPlan->fetchAll( $select );
    }
    
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listByFilters( $filters = array() )
    {
	$select = $this->getSelectBusiness();
	
	if ( !empty( $filters['fk_id_adddistrict'] ) )
	    $select->where( 'bp.fk_id_adddistrict = ?', $filters['fk_id_adddistrict'] );
	
	if ( !empty( $filters['fk_id_fefop_modules'] ) )
	    $select->where( 'bp.fk_id_fefop_modules = ?', $filters['fk_id_fefop_modules'] );
	
	if ( !empty( $filters['fk_id_isicdivision'] ) )
	    $select->where( 'bp.fk_id_isicdivision = ?', $filters['fk_id_isicdivision'] );
	
	if ( !empty( $filters['fk_id_isicclasstimor'] ) )
	    $select->where( 'bp.fk_id_isicclasstimor = ?', $filters['fk_id_isicclasstimor'] );
	
	if ( !empty( $filters['partisipants'] ) )
	    $select->where( 'bp.partisipants = ?', $filters['partisipants'] );
	
	if ( !empty( $filters['bussines_plan_developer'] ) )
	    $select->where( 'bp.bussines_plan_developer = ?', $filters['bussines_plan_developer'] );
	
	if ( array_key_exists( 'submitted', $filters ) && $filters['submitted'] != '' )
	    $select->where( 'bp.submitted = ?', $filters['submitted'] );
	
	if ( array_key_exists( 'contract', $filters ) && $filters['contract'] != '' ) {
	    if ( $filters['contract'] == '1')
		$select->where( 'bp.fk_id_fefop_contract IS NOT NULL' );
	    else
		$select->where( 'bp.fk_id_fefop_contract IS NULL' );
	}
	
	return $this->_dbTable->fetchAll( $select );    
    }
    
    /**
     * 
     * @return array
     */
    public function getDescriptionFields()
    {
	$descriptionFields = array(
				'visao'		    => 'Vizaun',
				'missao'	    => 'Misaun',
				'ameacas'	    => 'Ameasa sira',
				'oportunidades'	    => 'Oportunidade sira',
				'forcas'	    => "Forsa (Karik projetu ne'e grupu ida mak dezenvolve, hatudu tarefa atu dezenvolve husi membru idak-idak no formasaun/esperiénsia kona-ba funsaun sira ne'e)",
				'fraquezas'	    => 'Frakeza',
				'objetivos_metas'   => 'Hatudu objetivu SMART (espesífiku, menzurável, posível atu atinje, relevante no bele atinje iha prazu tinan 2 nia laran)',
				'estrategia'	    => 'Lideransa kustus nian, diferensiasaun ka foku merkadu nian',
				'desc_produto'	    => "Deskrisaun konaba Produtu (sira)  no/ka Servisu(sira) no Folin(sira) estimadu atu fa'an",
				'why_produto'	    => "Tamba sa halo produtu ne'e no/ka servisu neebe oferese mesak/espesial?",
				'analise_mercado'   => 'Análize ba Merkadu',
				'canais_dist'	    => 'Dalan Distribuisaun nian',
				'plano_marketing'   => "Planu Marketing/Fa'an",
			    );
	
	return $descriptionFields;
    }
    
    /**
     * 
     * @param int $id
     * @return array
     */
    public function getTechnicalFeedback( $id )
    {
	$technicalMethods = array(
	    'beneficiary_elegibility'	=> '_checkBeneficiaryElegibility',
	    'beneficiary_status'	=> '_checkBeneficiaryStatus',
	    'beneficiary_graduation'	=> '_checkBeneficiaryFormation',
	);
	
	$this->_data['id'] = $id;
	
	$technicalFeeback = array();
	foreach ( $technicalMethods as $field => $method )
	    if ( method_exists( $this, $method) )
		$technicalFeeback[$field] = call_user_func( array( $this, $method ) );
	    
	return $technicalFeeback;
    }
    
    /**
     * 
     * @return array
     */
    protected function _checkBeneficiaryFormation()
    {
	$businessPlan = $this->fetchBusinessPlan( $this->_data['id'] );
	
	$returnValues = array(
	    'valid'	    => is_bool( $this->_checkBeneficiaryGraduation( $businessPlan ) ),
	    'label'	    => 'Formasaun kona-ba Jestaun Negósiu',
	    'description'   => 'Karik benefisiáriu konklui ona ho susesu formasaun entau status mosu hanesan konkluídu (automátiku).'
	);
	
	return $returnValues;
    }
    
    /**
     * 
     * @return array
     */
    protected function _checkBeneficiaryElegibility()
    {
	$businessPlan = $this->fetchBusinessPlan( $this->_data['id'] );
	
	$elegibilityMethods = array(
	    '_checkBeneficiaryAge',
	    '_checkBeneficiaryGraduation',
	    '_checkBeneficiaryDisability',
	);
	
	$description = "Kampu peskiza tinan, kódigu inskrisaun iha SEOP, nível graduasaun, iha sentru formasaun akreditadu, "
		     . "kona-ba Distritu iha ne'ebé hakarak hahú atividade ka lisensiatura/baxarelatu (automátiku)";
	
	$resultElegibility = array();
	foreach ( $elegibilityMethods as $method ) {
	    if ( method_exists( $this, $method) ) {
		
		$resultMethod = call_user_func( array( $this, $method ), $businessPlan );
		if ( is_string( $resultMethod ) )
		    $resultElegibility[] = $resultMethod;
	    }
	}
	
	$description .= '<br>' . implode( '<br>', $resultElegibility );
	
	$returnValues = array(
	    'valid'	    => empty( $resultElegibility ),
	    'label'	    => 'Elejibilidade',
	    'description'   => $description
	);
	
	return $returnValues;
    }
    
    /**
     * 
     * @param Zend_Db_Table_Row $businessPlan
     * @return boolean|string
     */
    protected function _checkBeneficiaryAge( $businessPlan )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$client = $mapperClient->detailClient( $businessPlan->fk_id_perdata );
	
	switch ( $businessPlan->fk_id_fefop_modules ) {
	    case Fefop_Model_Mapper_Module::CEC:
		
		    if ( $client->age < 18 || $client->age > 40 )
			return sprintf( 'Tinan hira keta kiik liu 18 no mos boot liu 40. Benefisiariu tinan: %d', $client->age );
		    else
			return true;
		break;
	    case Fefop_Model_Mapper_Module::CED:
		
		    if ( $client->age < 18 || $client->age > 60 )
			return sprintf( 'Tinan hira keta kiik liu 18 no mos boot liu 60. Benefisiariu tinan: %d', $client->age );
		    else
			return true;
		break;
	    case Fefop_Model_Mapper_Module::CEG:
		
		    if ( $client->age < 18 )
			return sprintf( 'Tinan hira keta kiik liu 18. Benefisiariu tinan: %d', $client->age );
		    else
			return true;
		break;
	}
	
	return true;
    }
    
    /**
     * 
     * @param Zend_Db_Table_Row $businessPlan
     * @return boolean|string
     */
    protected function _checkBeneficiaryGraduation( $businessPlan )
    {
	//if ( Fefop_Model_Mapper_Module::CEG != $businessPlan->fk_id_fefop_modules )
	//    return true;
	
	$mapperClient = new Client_Model_Mapper_Client();
	$client = $mapperClient->detailClient( $businessPlan->fk_id_perdata );
	
	$maxDili = array(
	    'SUPERIOR',
	    'POS-GRADUASAUN'
	);
	
	if ( strtolower($businessPlan->district) == 'dili' && ( 
		(
		    is_int( $client->max_level_scholarity )
		    &&
		    $client->max_level_scholarity < 2 
		)
		||
		!in_array( $client->max_level_scholarity, $maxDili )
	    )
	    || ( strtolower($businessPlan->district) != 'dili' && $client->max_level_scholarity < 1 ) )
	    return sprintf( 'Kliente la iha Nivel Sertifikasaun Nasional level: %d', ( strtolower($businessPlan->district) == 'Dili' ? 2 : 1 ) );
	else
	    return true;
    }
    
    /**
     * 
     * @param Zend_Db_Table_Row $businessPlan
     * @return boolean|string
     */
    protected function _checkBeneficiaryDisability( $businessPlan )
    {
	if ( Fefop_Model_Mapper_Module::CED != $businessPlan->fk_id_fefop_modules )
	    return true;
	
	if ( !Client_Model_Mapper_Client::isHandicapped( $businessPlan->fk_id_perdata ) )
	    return 'Kliente nee la disabilidade ba halo Modulu CED';
	else
	    return true;
    }


    /**
     * 
     * @return array
     */
    protected function _checkBeneficiaryStatus()
    {
	$businessPlan = $this->fetchBusinessPlan( $this->_data['id'] );
	$blacklist = new Fefop_Model_Mapper_BeneficiaryBlacklist();
	
	$dataCheck = array(
	    'identifiers' => array( 'fk_id_perdata' => $businessPlan->fk_id_perdata )
	);
	
	// Check if the client has a blacklist register
	$response = $blacklist->checkBlacklist( $dataCheck );
	
	$description = "Karik status iha perfil benefisiáriu mak la kumpre (tanba projetu sira uluk ho status suspensu) "
		     . "entaun status tenke preenxe automaticamente ho rejeisaun (automátiku)";
	
	
	$returnValues = array(
	    'valid'	    => (boolean)$response['valid'],
	    'label'	    => 'Status',
	    'description'   => $description
	);
	
	return $returnValues;
    }
    
    /**
     * 
     * @param int $idBusinessPlan
     * @return Zend_Db_Table_Row
     */
    public function fetchTechnicalFeedback( $idBusinessPlan )
    {
	$dbTechnicalFeedback = App_Model_DbTable_Factory::get( 'TechnicalFeedback' );
	$where = array(
	    'fk_id_businessplan = ?' => $idBusinessPlan
	);
	
	return $dbTechnicalFeedback->fetchRow( $where );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listRevisions( $id )
    {
	$dbRevision = App_Model_DbTable_Factory::get( 'BusinessPlanRevision' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$select = $dbRevision->select()
			     ->from( array( 'r' => $dbRevision ) )
			     ->setIntegrityCheck( false )
			     ->join(
				array( 'u' => $dbUser ),
				'u.id_sysuser = r.fk_id_sysuser',
				array( 'name' )
			     )
			     ->where( 'r.fk_id_businessplan = ?', $id )
			     ->order( 'r.date_time DESC' );
	
	return $dbRevision->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function getLastRevision( $id )
    {
	return $this->listRevisions( $id )->current();
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function getMaxContractAmount( $id )
    {
	$bunsinessPlan = $this->fetchBusinessPlan( $id );
	$clients = $this->listClientBusinessPlan( $id );
	
	$totalModule = $this->getTotalModule($bunsinessPlan->fk_id_fefop_modules);
	
	$beneficiariesGroup = $clients->count();
	
	if ( Fefop_Model_Mapper_Module::CEG == $bunsinessPlan->fk_id_fefop_modules)
	    $totalModule += ( 5000 * $beneficiariesGroup );
	else {
	    
	    $totalFirstFase = $this->_getTotalFirstFase( $clients );
	    if ( $totalFirstFase >= 3 )
		$totalModule = 35000;
	    else
		$totalModule = $beneficiariesGroup * ( $totalModule - ( 1000 * $beneficiariesGroup ) );
	    //$totalModule += $totalModule * $beneficiariesGroup * ( 1 * 0.05 * $beneficiariesGroup );
	}
	
	return $totalModule;
    }
    
    /**
     * 
     * @param Zend_Db_Table_Rowset $clients
     */
    protected function _getTotalFirstFase( $clients )
    {
	$totalGreaterFiveThousands = 0;
	$mapperPceFase = new Fefop_Model_Mapper_PCEContract();
	
	foreach ( $clients as $client ) {
	    
	    $contractFormation = $mapperPceFase->getContractByClientModule( $client->id_perdata, 
									    $client->fk_id_fefop_modules );
	    
	    if ( !empty( $contractFormation ) && (string)$contractFormation->amount >= 5000 )
		$totalGreaterFiveThousands++;
	}
	
	return $totalGreaterFiveThousands;
    }
    
    /**
     * 
     * @param int $module
     * @return float
     */
    public function getTotalModule( $module )
    {
	$mapperBudgetCategory = new Fefop_Model_Mapper_Expense();
	$identifier = $mapperBudgetCategory->getModuleToItem( $module );
		
	$mapperRule = new Fefop_Model_Mapper_Rule();
	$ruleAmount = $mapperRule->getRuleIdentifier($identifier, Fefop_Model_Mapper_Rule::AMOUNT_MAX );
	
	if ( empty( $ruleAmount ) )
	    return (float)$this->_limitAmounts[$module];
	else
	    return (float)$ruleAmount->value;
    }
    
    /**
     * 
     * @param array $expenses
     * @param int $id
     * @param int|bool $type
     * @return array
     */
    public function aggregateExpenses( &$expenses, $businessPlan, $type )
    {
	$expensesAmounts = array();
	
	$clients = $this->listClientBusinessPlan( $businessPlan->fk_id_fefop_modules );
	if ( $clients->count() > 0 ) {
	    
	    foreach ( $clients as $client ) {

		if ( empty( $client->business_plan ) )
		    continue;

		$expensesSubBusinessPlan = $this->listExpenses( $client->business_plan, $type );
		foreach ( $expensesSubBusinessPlan as $expense ) {

		    if ( empty( $expensesAmounts[$expense['id_budget_category']] ) )
			$expensesAmounts[$expense['id_budget_category']] = 0;

		    $expensesAmounts[$expense['id_budget_category']] += $expense['amount'];
		}
	    }
	}
	
	if ( !empty( $businessPlan->fk_id_pce_contract ) ) {
	    
	    $mapperPceContract = new Fefop_Model_Mapper_PCEContract();
	    $itemPceContract = $mapperPceContract->listExpenses( $businessPlan->fk_id_pce_contract );
	    
	    foreach ( $itemPceContract as $item ) {
		if ( empty( $expensesAmounts[$item['id_budget_category']] ) )
		    $expensesAmounts[$item['id_budget_category']] = 0;
		
		$expensesAmounts[$item['id_budget_category']] += $item['amount'];
	    }
	}
	
	foreach ( $expenses as $expenseBusinessPlan )
	    if ( !empty( $expensesAmounts[$expenseBusinessPlan['id_budget_category']] ) )
		$expenseBusinessPlan['amount'] += $expensesAmounts[$expenseBusinessPlan['id_budget_category']];
	    
	return $expenses;
    }
    
    /**
     * 
     * @param int $id
     * @return array
     */
    public function aggregateItemsExpense( $itemsExpense, $businessPlan )
    {
	$clients = $this->listClientBusinessPlan( $businessPlan->id_businessplan );
	if ( $clients->count() > 0 ) {
	
	    foreach ( $clients as $client ) {
		
		if ( empty( $client->business_plan ) )
		    continue;

		$itemsSubPlan = $this->listItemExpenses( $client->business_plan );

		foreach ( $itemsSubPlan as $item ) {
		    if ( !array_key_exists( $item->fk_id_budget_category, $itemsExpense ) )
			$itemsExpense[$item->fk_id_budget_category] = array();

		    $itemsExpense[$item->fk_id_budget_category][] = $item;
		}
	    }
	}
	
	if ( !empty( $businessPlan->fk_id_pce_contract ) ) {
	    
	    $mapperPceContract = new Fefop_Model_Mapper_PCEContract();
	    $itemPceContract = $mapperPceContract->listItemExpenses( $businessPlan->fk_id_pce_contract );
	    
	    foreach ( $itemPceContract as $item ) {
		if ( !array_key_exists( $item->fk_id_budget_category, $itemsExpense ) )
		    $itemsExpense[$item->fk_id_budget_category] = array();

		$itemsExpense[$item->fk_id_budget_category][] = $item;
	    }
	}
	
	return $itemsExpense;
    }
    
    /**
     * 
     * @param int $client
     * @return boolean
     */
    public function canCreateBusinessPlan( $client, $module )
    {
	if ( Fefop_Model_Mapper_Module::CEG == $module )
	    return true;
	
	$mapperPceFase = new Fefop_Model_Mapper_PCEContract();
	$select = $mapperPceFase->getSelect();
	
	$select->where( 'pcc.fk_id_perdata = ?', $client )
		->where( 'pcc.fk_id_fefop_modules = ?', $module );
	
	$row = $this->_dbTable->fetchRow( $select );
	
	return !empty( $row );
    }
    
    /**
     * 
     * @param array $data
     * @return array
     */
    public function financialAnalysis( $data )
    {
	$defaults = array(
	    'years'	    => array(),
	    'expenses'	    => array(),
	    'incomes'	    => array(),
	    'annual_cost'   => array(),
	    'rai'	    => array(),
	    'sale_tax_item' => array(),
	    'net_income'    => array(),
	    'cash_flow'	    => array(),
	    'payback'	    => array(),
	    'initial'	    => 0,
	    'year_payback'  => 0,
	    'roi'	    => 0,
	    'income_incr'   => 0,
	    'income_cost'   => 0,
	    'sale_tax'	    => 0,
	    'annual_sale'   => 0,
	    'first_year'    => 0
	);
	
	if ( !empty( $data['income_incr'] ) )
	    $defaults['income_incr'] = (float)$data['income_incr'];
	
	if ( !empty( $data['income_cost'] ) )
	    $defaults['income_cost'] = (float)$data['income_cost'];
	
	if ( !empty( $data['sale_tax'] ) )
	    $defaults['sale_tax'] = (float)$data['sale_tax'];
	
	if ( !empty( $data['year'] ) && $data['year'] > 0 )
	    $defaults['first_year'] = $data['year'];
	
	if ( !empty( $data['annual'] ) )
	    $defaults['annual_sale'] = (float)$data['annual'];
	
	if ( empty( $data['total_expense'] ) )
	    $data['total_expense'] = 0;
	
	if ( empty( $data['investiment'] ) )
	    $data['investiment'] = 0;
	
	$defaults['initial'] = ( $data['total_expense'] * -1 ) - $data['investiment'];
	
	if ( $defaults['initial'] > 0 )
	    $defaults['year_payback'] = $defaults['first_year'];
	
	$defaults['income_incr_calc'] = $defaults['income_incr'] / 100;
	$defaults['income_cost_calc'] = $defaults['income_cost'] / 100;
	$defaults['sale_tax_calc'] = $defaults['sale_tax'] / 100;
	
	$count = 0;
	$countExpense = 'A';
	$amortizations = array();
	$cashFlowSum = 0;
	$payBack = $defaults['initial'];
	
	for ( $year = $defaults['first_year'] + 1; $year <= $defaults['first_year'] + 5; $year++ ) {
	 
	    $defaults['years'][$year] = $year;
	    $amortizations[$year] = 0;
	    
	    $income = $defaults['annual_sale'] * pow(( 1 + $defaults['income_incr_calc'] ), $count );
	    $defaults['incomes'][$year] = $income;
	    
	    if ( empty( $defaults['annual_cost'][$year] ) )
		$defaults['annual_cost'][$year] = 0;
	    
	    foreach ( $data['expenses'] as $expense ) {
		
		if ( !array_key_exists( $expense->id_budget_category, $defaults['expenses'] ) ) {
		    
		    $defaults['expenses'][$expense->id_budget_category] = array(
			'description'	=> $countExpense ++ . ' - ' . $expense->description,
			'years'		=>  array()
		    );
		}
		
		$valueYear = $expense['amount'] * pow(( 1 + $defaults['income_cost_calc'] ), $count );
		$defaults['expenses'][$expense->id_budget_category]['years'][$year] = $valueYear;
		
		$defaults['annual_cost'][$year] += $valueYear;
		
		$nameExpense = preg_replace( '/[^a-z]/i', '', $expense->description );
		if ( preg_match( '/^amorti/i', $nameExpense ) )
		    $amortizations[$year] = $valueYear;
	    }
	    
	    $defaults['rai'][$year] = $income - $defaults['annual_cost'][$year];
	    $defaults['sale_tax_item'][$year] = $defaults['rai'][$year] * $defaults['sale_tax_calc'];
	    $defaults['net_income'][$year] = $defaults['rai'][$year] - $defaults['sale_tax_item'][$year];
	    $defaults['cash_flow'][$year] = $defaults['net_income'][$year] + $amortizations[$year];
	    $payBack = $payBack + $defaults['cash_flow'][$year];
	    $defaults['payback'][$year] = $payBack;
	    
	    $cashFlowSum += $defaults['cash_flow'][$year];
	    
	    if ( $payBack > 0 && empty( $defaults['year_payback'] ) )
		$defaults['year_payback'] = $year;
	    
	    $count++;
	}
	
	if ( $defaults['initial'] != 0 ) {
	    
	    $cashFlowSum += $defaults['initial'];
	    $defaults['roi'] = round( 1 - ($cashFlowSum / $defaults['initial']), 2);
	}
	
	return $defaults;
    }
}