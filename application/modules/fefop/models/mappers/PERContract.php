<?php

class Fefop_Model_Mapper_PERContract extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_PERContract
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_PERContract();

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
	    
	    $dateStart = new Zend_Date( $this->_data['date_start'] );
	    $dateFinish = new Zend_Date( $this->_data['date_finish'] );
	    
	    $mapperExpense = new Fefop_Model_Mapper_Expense();
	    $item = $mapperExpense->getModuleToItem( $this->_data['fk_id_fefop_modules'] );
	    
	    $mapperRule = new Fefop_Model_Mapper_Rule();
	    $mapperRule->validate( $this->_message, $this->_data, $item );
	    
	    // Check if the initial date is later than finish date
	    if ( $dateStart->isLater( $dateFinish ) ) {
		
		$this->_message->addMessage( 'Data loron keta liu data remata.' );
		$this->addFieldError( 'date_start' )->addFieldError( 'date_finish' );
		return false;
	    }
	    
	    // If there is no contract yet
	    if ( empty( $this->_data['fk_id_fefop_contract'] ) ) {
		
		$dataContract = array(
		    'module'	=> $this->_data['fk_id_fefop_modules'],
		    'district'	=> $this->_data['fk_id_adddistrict']
		);
		
		$mapperFefopContract = new Fefop_Model_Mapper_Contract();
		$this->_data['fk_id_fefop_contract'] = $mapperFefopContract->save( $dataContract );
	    }
	    
	    $this->_data['amount'] = App_General_String::toFloat( $this->_data['amount'] );
	    
	    $this->_data['date_start'] = $dateStart->toString( 'yyyy-MM-dd' );
	    $this->_data['date_finish'] = $dateFinish->toString( 'yyyy-MM-dd' );
	   
	    $dataForm = $this->_data;
	    
	    // Save the contract
	    $dataForm['id_per_contract'] = parent::_simpleSave();
	    $dataForm['fk_id_per_contract'] = $dataForm['id_per_contract'];
	    
	    // Save budget category
	    $this->_saveExpenses( $dataForm );
	    
	    if ( empty( $this->_data['id_per_contract'] ) )
		$history = 'REJISTU KONTRAKTU PER: %s';
	    else
		$history = 'ATUALIZA KONTRAKTU PER: %s';
	    
	    $history = sprintf( $history, $dataForm['id_per_contract'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return $dataForm['id_per_contract'];
	    
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
    protected function _saveExpenses( $data )
    {
	$dbPERItem = App_Model_DbTable_Factory::get( 'PerItem' );
	$dbPERFormation = App_Model_DbTable_Factory::get( 'PerFormation' );
	$dbPEREmployment = App_Model_DbTable_Factory::get( 'PerEmployment' );
	$dbPERBugetCategoryItem = App_Model_DbTable_Factory::get( 'PerBudgetCategoryItem' );
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	
	// Delete all the detailed items
	$where = array( 'fk_id_per_contract = ?' => $data['id_per_contract'] );
	$dbPERFormation->delete( $where );
	$dbPEREmployment->delete( $where );
	$dbPERBugetCategoryItem->delete( $where );
	$dbPERItem->delete( $where );
	
	// Save each budget category
	foreach ( $data['expense'] as $id => $expense ) {
	    
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
	    
	    $row->amount = App_General_String::toFloat( $expense['total'] );
	    $row->save();
	    
	    if ( !empty( $data['item_expense'][$id] ) )
		$this->_saveItemExpense( $data, $id );
		
	    if ( !empty( $data['employment_expense'][$id] ) )
		$this->_saveItemEmploymentExpense( $data, $id );
	    
	    if ( !empty( $data['formation_expense'][$id] ) )
		$this->_saveItemFormationExpense( $data, $id );
	}
    }
    
    /**
     * 
     * @param array $data
     * @param int $id
     * @return boolean
     */
    protected function _saveItemExpense( $data, $id )
    {    
	if ( empty( $data['item_expense'][$id] ) )
	    return false;
	else
	    $items = $this->_arrangeItems( $data['item_expense'][$id] );
	
	$dbPERItem = App_Model_DbTable_Factory::get( 'PerItem' );
	$dbPERBugetCategoryItem = App_Model_DbTable_Factory::get( 'PerBudgetCategoryItem' );
	
	// For each budget category, save its detailed items
	foreach ( $items as $item ) {
	    
	    $item['fk_id_budget_category'] = $id;
	    $item['fk_id_per_contract'] = $data['fk_id_per_contract'];
	    $item['amount_unit'] = App_General_String::toFloat( $item['amount_unit'] );
	    $item['amount_total'] = App_General_String::toFloat( $item['amount_total'] );
	    
	    $rowItem = $dbPERItem->createRow( $item );
	    $idItem = $rowItem->save();
	    
	    $item['fk_id_per_item'] = $idItem;
	    $rowBudgetItem = $dbPERBugetCategoryItem->createRow( $item );
	    $rowBudgetItem->save();
	}
    }
    
    /**
     * 
     * @param array $data
     * @param int $id
     * @return boolean
     */
    protected function _saveItemEmploymentExpense( $data, $id )
    {
	if ( empty( $data['employment_expense'][$id] ) )
	    return false;
	else
	    $items = $this->_arrangeItems( $data['employment_expense'][$id] );
	
	$dbPERItem = App_Model_DbTable_Factory::get( 'PerItem' );
	$dbPEREmployment = App_Model_DbTable_Factory::get( 'PerEmployment' );
	
	// For each budget category, save its detailed employment items
	foreach ( $items as $item ) {
	    
	    $item['fk_id_budget_category'] = $id;
	    $item['fk_id_per_contract'] = $data['fk_id_per_contract'];
	    
	    $item['amount_unit'] = App_General_String::toFloat( $item['amount_unit'] );
	    $item['amount_total'] = App_General_String::toFloat( $item['amount_total'] );
	    
	    $rowItem = $dbPERItem->createRow( $item );
	    $idItem = $rowItem->save();
	    
	    $dateStart = new Zend_Date( $item['date_start'] );
	    $dateFinish = new Zend_Date( $item['date_finish'] );
	    
	    $item['date_start'] = $dateStart->toString( 'yyyy-MM-dd' );
	    $item['date_finish'] = $dateFinish->toString( 'yyyy-MM-dd' );
	    
	    $item['fk_id_per_item'] = $idItem;
	    $rowBudgetItem = $dbPEREmployment->createRow( $item );
	    $rowBudgetItem->save();
	}
    }
    
    /**
     * 
     * @param array $data
     * @param int $id
     * @return boolean
     */
    protected function _saveItemFormationExpense( $data, $id )
    {
	if ( empty( $data['formation_expense'][$id] ) )
	    return false;
	else
	    $items = $this->_arrangeItems( $data['formation_expense'][$id] );
	
	$dbPERItem = App_Model_DbTable_Factory::get( 'PerItem' );
	$dbPERFormation = App_Model_DbTable_Factory::get( 'PerFormation' );
	
	// For each budget category, save its detailed employment items
	foreach ( $items as $item ) {
	    
	    $item['fk_id_budget_category'] = $id;
	    $item['fk_id_per_contract'] = $data['fk_id_per_contract'];
	    
	    $item['amount_unit'] = App_General_String::toFloat( $item['amount_unit'] );
	    $item['amount_total'] = App_General_String::toFloat( $item['amount_total'] );
	    
	    $rowItem = $dbPERItem->createRow( $item );
	    $idItem = $rowItem->save();
	    
	    $item['fk_id_per_item'] = $idItem;
	    $rowBudgetItem = $dbPERFormation->createRow( $item );
	    $rowBudgetItem->save();
	}
    }
    
    /**
     * 
     * @param array $items
     * @return array
     */
    protected function _arrangeItems( $items )
    {
	$newItems = array();
	foreach ( $items as $label => $item ) {
	    foreach ( $item as $id => $value ) {
		$newItems[$id][$label] = $value;
	    }
	}
	
	return $newItems;
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$select = $mapperContract->getSelect();
	
	$dbPERContract = App_Model_DbTable_Factory::get( 'PerContract' );
	$dbPERArea = App_Model_DbTable_Factory::get( 'PerArea' );
	$dbSubDistrict = App_Model_DbTable_Factory::get( 'AddSubDistrict' );
	$dbAddSucu = App_Model_DbTable_Factory::get( 'AddSucu' );
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	$dbFEFOPModules = App_Model_DbTable_Factory::get( 'FEFOPModules' );
	
	$select->join(
		    array( 'pec' => $dbPERContract ),
		    'pec.fk_id_fefop_contract = c.id_fefop_contract'
		)
		->join(
		    array( 'e' => $dbEnterprise ),
		    'e.id_fefpenterprise = pec.fk_id_fefpenterprise',
		    array( 'enterprise' => 'enterprise_name' )
		)
		->join(
		    array( 'sd' => $dbSubDistrict ),
		    'pec.fk_id_addsubdistrict = sd.id_addsubdistrict',
		    array( 'sub_district' )
		)
		->join(
		    array( 'sk' => $dbAddSucu ),
		    'pec.fk_id_addsucu = sk.id_addsucu',
		    array( 'sucu' )
		)
		->join(
		    array( 'pa' => $dbPERArea ),
		    'pec.fk_id_per_area = pa.id_per_area',
		    array( 'area' => 'description' )
		)
		->join(
		    array( 'fm' => $dbFEFOPModules ),
		    'pec.fk_id_fefop_modules = fm.id_fefop_modules',
		    array( 'module' => 'acronym' )
		)
		->group( array( 'pec.id_per_contract' ) );
	
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
	$dbPerContract = App_Model_DbTable_Factory::get( 'PerContract' );
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
					array( 'pec' => $dbPerContract ),
					'pec.fk_id_fefop_contract = bco.fk_id_fefop_contract',
					array()
				    )
				    ->join(
					array( 'fc' => $dbFEFOPContract ),
					'pec.fk_id_fefop_contract = fc.id_fefop_contract',
					array()
				    )
				    ->where( 'pec.id_per_contract = ?', $id )
				    ->where( 'bco.status = ?', 1 )
				    ->order( array( 'id_budgetcategory_contract' ) );
	
	return $dbBudgetCategory->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listItemExpense( $contract, $expense = false )
    {
	$dbPERItem = App_Model_DbTable_Factory::get( 'PerItem' );
	$dbPERBugetCategoryItem = App_Model_DbTable_Factory::get( 'PerBudgetCategoryItem' );
	
	$select = $dbPERItem->select()
			    ->from( array( 'pei' => $dbPERItem ) )
			    ->setIntegrityCheck( false )
			    ->join(
			       array( 'bci' => $dbPERBugetCategoryItem ),
			       'bci.fk_id_per_item = pei.id_per_item',
			       array( 'fk_id_budget_category', 'description' )
			    )
			    ->where( 'pei.fk_id_per_contract = ?', $contract )
			    ->order( array( 'pei.id_per_item' ) );
	
	if ( !empty( $expense ) )
	    $select->where( 'bci.fk_id_budget_category = ?', $expense );
	
	return $dbPERItem->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listEmploymentExpense( $contract, $expense = false )
    {
	$dbPERItem = App_Model_DbTable_Factory::get( 'PerItem' );
	$dbPerEmployment = App_Model_DbTable_Factory::get( 'PerEmployment' );
	
	$select = $dbPERItem->select()
			    ->from( array( 'pei' => $dbPERItem ) )
			    ->setIntegrityCheck( false )
			    ->join(
			       array( 'pee' => $dbPerEmployment ),
			       'pee.fk_id_per_item = pei.id_per_item',
			       array( 
				   'fk_id_budget_category', 
				   'beneficiaries',
				   'date_start',
				   'date_finish',
				   'duration_days',
				)
			    )
			    ->where( 'pei.fk_id_per_contract = ?', $contract )
			    ->order( array( 'pei.id_per_item' ) );
	
	if ( !empty( $expense ) )
	    $select->where( 'pee.fk_id_budget_category = ?', $expense );
	
	return $dbPERItem->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listFormationExpense( $contract, $expense = false )
    {
	$dbPERItem = App_Model_DbTable_Factory::get( 'PerItem' );
	$dbPerFormation = App_Model_DbTable_Factory::get( 'PerFormation' );
	$dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbPerLevelScholarity = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
	
	$select = $dbPERItem->select()
			    ->from( array( 'pei' => $dbPERItem ) )
			    ->setIntegrityCheck( false )
			    ->join(
			       array( 'pef' => $dbPerFormation ),
			       'pef.fk_id_per_item = pei.id_per_item',
			       array( 
				   'fk_id_budget_category', 
				   'beneficiaries',
				   'fk_id_perscholarity'
				)
			    )
			    ->join(
			       array( 'ps' => $dbPerScholarity ),
			       'ps.id_perscholarity = pef.fk_id_perscholarity',
			       array()
			    )
			    ->joinLeft(
			       array( 'ls' => $dbPerLevelScholarity ),
			       'ps.fk_id_perlevelscholarity = ls.id_perlevelscholarity',
			       array( 'level' => 'level_scholarity' )
			    )
			    ->where( 'pei.fk_id_per_contract = ?', $contract )
			    ->order( array( 'pei.id_per_item' ) );
	
	if ( !empty( $expense ) )
	    $select->where( 'pef.fk_id_budget_category = ?', $expense );
	
	return $dbPERItem->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @param Zend_Db_Table_Rowset $expenses
     * @return array
     */
    public function expensesDetailed( $id, $expenses = array() )
    {
	if ( empty( $expenses ) )
	    $expenses = $this->listExpenses( $id );
	
	$itemDetailed = array();
	foreach ( $expenses as $expense ) {
	    
	    $itemExpense = $this->listItemExpense( $id, $expense->id_budget_category );
	    if ( $itemExpense->count() > 0 ) {
		
		$itemDetailed[$expense->id_budget_category] = array(
		    'type'  => 'item',
		    'items' => $itemExpense
		);
	    } else {
		
		$itemEmployment = $this->listEmploymentExpense( $id, $expense->id_budget_category );
		if ( $itemEmployment->count() > 0 ) {

		    $itemDetailed[$expense->id_budget_category] = array(
			'type'  => 'employment',
			'items' => $itemEmployment
		    );
		} else {
		    
		    $itemFormation = $this->listFormationExpense( $id, $expense->id_budget_category );
		    if ( $itemFormation->count() > 0 ) {

			$itemDetailed[$expense->id_budget_category] = array(
			    'type'  => 'formation',
			    'items' => $itemFormation
			);
		    }
		}
	    }
	}
	
	return $itemDetailed;
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detail( $id )
    {
	$select = $this->getSelect();
	$select->where( 'pec.id_per_contract = ?', $id );
	
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
	
	if ( !empty( $filters['fk_id_fefop_modules'] ) )
	    $select->where( 'pec.fk_id_fefop_modules = ?', $filters['fk_id_fefop_modules'] );
	
	if ( !empty( $filters['fk_id_fefpenterprise'] ) )
	    $select->where( 'pec.fk_id_fefpenterprise = ?', $filters['fk_id_fefpenterprise'] );
	
	if ( !empty( $filters['minimum_amount'] ) )
	    $select->where( 'pec.amount >= ?', (float)$filters['minimum_amount'] );
	
	if ( !empty( $filters['maximum_amount'] ) )
	    $select->where( 'pec.amount <= ?', (float)$filters['maximum_amount'] );
	
	if ( !empty( $filters['fk_id_adddistrict'] ) )
	    $select->where( 'pec.fk_id_adddistrict = ?', $filters['fk_id_adddistrict'] );
	
	if ( !empty( $filters['fk_id_addsubdistrict'] ) )
	    $select->where( 'pec.fk_id_addsubdistrict = ?', $filters['fk_id_addsubdistrict'] );
	
	if ( !empty( $filters['fk_id_addsucu'] ) )
	    $select->where( 'pec.fk_id_addsucu = ?', $filters['fk_id_addsucu'] );
	
	if ( !empty( $filters['fk_id_per_area'] ) )
	    $select->where( 'pec.fk_id_per_area = ?', $filters['fk_id_per_area'] );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'pec.date_start >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'pec.date_finish <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
   
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $form = Fefop_Form_EDCContract::ID, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::FEFOP,
	    'fk_id_sysform'	    => $form,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}