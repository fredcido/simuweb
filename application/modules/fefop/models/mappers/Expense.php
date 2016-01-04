<?php

class Fefop_Model_Mapper_Expense extends App_Model_Abstract
{
    const CONFIG_PCE_CEG = 'pce-ceg';
    const CONFIG_PCE_CED = 'pce-ced';
    const CONFIG_PCE_CED_FASE_I = 'pce-ced-fase-i';
    const CONFIG_PCE_CEC = 'pce-cec';
    const CONFIG_PCE_CEC_FASE_I = 'pce-cec-fase-i';
    const CONFIG_PFPCI_FP = 'pfpci-fp';
    const CONFIG_PFPCI_DRH = 'pfpci-drh';
    const CONFIG_PFPCI_DRH_PLAN = 'pfpci-drh-plan';
    const CONFIG_PFPCI_RI = 'pfpci-ri';
    const CONFIG_PISE_FE = 'pise-fe';
    const CONFIG_PISE_FE_REGISTRATION = 'pise-fe-registration';
    const CONFIG_PCE_INITIAL = 'pce-initial';
    const CONFIG_PCE_ANNUAL = 'pce-annual';
    const CONFIG_PCE_REVENUE = 'pce-revenue';
    const CONFIG_PER_EDC = 'per-edc';
    const CONFIG_PER_ETC = 'per-etc';
    
    /**
     *
     * @var array
     */
    protected $_configLabels = array(
	self::CONFIG_PCE_CEG		    => 'PCE-CEG (Apoio à Criação de Empresas para os Graduados)',
	self::CONFIG_PCE_CED_FASE_I	    => 'PCE-CED - FASE I (Apoio à Criação de Empresas para cidadãos portadores de Deficiência)',
	self::CONFIG_PCE_CED		    => 'PCE-CED - (Apoio à Criação de Empresas para cidadãos portadores de Deficiência)',
	self::CONFIG_PCE_CEC_FASE_I	    => 'PCE-CEC - FASE I (Apoio à Criação de Empresas para as Comunidades)',
	self::CONFIG_PCE_CEC		    => 'PCE-CEC - (Apoio à Criação de Empresas para as Comunidades)',
	self::CONFIG_PFPCI_FP		    => 'PFPCI-FP - (Formação Profissional)',
	self::CONFIG_PFPCI_DRH		    => 'PFPCI-DRH - (Desenvolvimento dos Recursos Humanos)',
	self::CONFIG_PFPCI_DRH_PLAN	    => 'PFPCI-DRH - Plano Formação (Desenvolvimento dos Recursos Humanos)',
	self::CONFIG_PFPCI_RI		    => 'PFPCI-RI - (Reforço Institucional)',
	self::CONFIG_PISE_FE		    => 'PISE-FE - (Formação em Exercício)',
	self::CONFIG_PER_ETC		    => 'PER-ETC - Módulo de Emprego e Turismo Comunitário',
	self::CONFIG_PER_EDC		    => 'PER-EDC - Módulo de Emprego e Desenvolvimento Comunitário',
	self::CONFIG_PISE_FE_REGISTRATION   => 'PISE-FE - Fisha Inskrisaun (Formação em Exercício)',
	self::CONFIG_PCE_INITIAL	    => 'Investimento Inicial não financiado pelo FEFOP - primeiro ano',
	self::CONFIG_PCE_ANNUAL		    => 'Despesas anuais',
	self::CONFIG_PCE_REVENUE	    => 'Receitas',
    );
    
    /**
     *
     * @var array
     */
    protected $_moduleToItem = array(
	Fefop_Model_Mapper_Module::CEC	=> self::CONFIG_PCE_CEC,
	Fefop_Model_Mapper_Module::CED	=> self::CONFIG_PCE_CED,
	Fefop_Model_Mapper_Module::CEG	=> self::CONFIG_PCE_CEG,
	Fefop_Model_Mapper_Module::DRH	=> self::CONFIG_PFPCI_DRH,
	Fefop_Model_Mapper_Module::FE	=> self::CONFIG_PISE_FE,
	Fefop_Model_Mapper_Module::FP	=> self::CONFIG_PFPCI_FP,
	Fefop_Model_Mapper_Module::RI	=> self::CONFIG_PFPCI_RI,
	Fefop_Model_Mapper_Module::EDC	=> self::CONFIG_PER_EDC,
	Fefop_Model_Mapper_Module::ETC	=> self::CONFIG_PER_ETC,
    );
    
    /**
     * 
     * @var Model_DbTable_BudgetCategory
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_BudgetCategory();

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

	    $row = $this->_checkExpense( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Rúbrica iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_budget_category'] ) )
		$history = 'INSERE RÚBRICA: %s - INSERIDO NOVO RÚBRICA';
	    else
		$history = 'ALTERA RÚBRICA: %s - ALTERADO RÚBRICA';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['description'] );
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
    public function saveExpensesItem()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbCategoryItem = App_Model_DbTable_Factory::get( 'BudgetCategoryConfiguration' );
	    $lastOrder = $this->getLastOrderItem( $this->_data['item_config'] );
	    
	    foreach ( $this->_data['expenses'] as $expense ) {
		
		$dataInsert = array(
		    'fk_id_budget_category' => $expense,
		    'identifier'	    => $this->_data['item_config'],
		    'order'		    => ++$lastOrder
		);
		
		$dbCategoryItem->insert( $dataInsert );
		
		$history = sprintf( 'REJISTU RÚBRICA: %s HO ITEM: %s', $expense, $this->_data['item_config'] );
		$this->_sysAudit( $history, Admin_Model_Mapper_SysUserHasForm::SAVE, Fefop_Form_ExpenseModule::ID );
	    }
	    
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
     * @param int $item
     * @return int
     */
    public function getLastOrderItem( $item )
    {
	$dbCategoryItem = App_Model_DbTable_Factory::get( 'BudgetCategoryConfiguration' );
	$select = $dbCategoryItem->select()
				   ->from( array( 'ci' => $dbCategoryItem ) )
				   ->columns( array( 'last_order' => new Zend_Db_Expr( 'MAX(ci.order)' ) ) )
				   ->where( 'ci.identifier = ?', $item );
	
	$row = $dbCategoryItem->fetchRow( $select );
	return empty( $row->last_order ) ? 0 : $row->last_order;
				   
    }
    
    /**
     * 
     * @return int|bool
     */
    public function removeExpensesItem()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbCategoryItem = App_Model_DbTable_Factory::get( 'BudgetCategoryConfiguration' );
	    
	    foreach ( $this->_data['expenses'] as $expense ) {
		
		$whereDelete = array( 
		    'identifier = ?'		=> $this->_data['item_config'],
		    'fk_id_budget_category = ?' => $expense
		);
		
		// Delete all the registers to the current expense / module
		$dbCategoryItem->delete( $whereDelete );
		
		// save auditing to the inactiveting ( Is that a word ? )
		$history = sprintf( 'HAMOOS RÚBRICA: %s HO MODULU: %s', $expense, $this->_data['item_config'] );
		$this->_sysAudit( $history, Admin_Model_Mapper_SysUserHasForm::SAVE, Fefop_Form_ExpenseModule::ID );
	    }
	    
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
    protected function _checkExpense()
    {
	$select = $this->_dbTable->select()
			->where( 'fk_id_budget_category_type = ?', $this->_data['fk_id_budget_category_type'] )
			->where( 'description = ?', $this->_data['description'] );

	if ( !empty( $this->_data['id_budget_category'] ) )
	    $select->where( 'id_budget_category <> ?', $this->_data['id_budget_category'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listAll( $type = false )
    {
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbBudgetCataegoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	
	$dbTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbBankContract = App_Model_DbTable_Factory::get( 'FEFOPBankContract' );
	$dbDRHBudgetCategory = App_Model_DbTable_Factory::get( 'DRHBudgetCategory' );
	$dbBudgetCategoryConfiguration = App_Model_DbTable_Factory::get( 'BudgetCategoryConfiguration' );
	$dbBudgetCategoryContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	$dbBusinessPlanExpense = App_Model_DbTable_Factory::get( 'BusinessPlanExpense' );
	
	$select = $dbBudgetCategory->select()
				    ->from( array( 'bc' => $dbBudgetCategory ) )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 'bct' => $dbBudgetCataegoryType ),
					'bct.id_budget_category_type = bc.fk_id_budget_category_type',
					array( 'type_expense' => 'description' )
				    )
				    ->joinLeft(
					array( 't' => $dbTransaction ),
					't.fk_id_budget_category = bc.id_budget_category',
					array(
					    'can_delete' => new Zend_Db_Expr(
						    'COALESCE('
							. 't.id_fefop_transaction,'
							. 'bcct.id_fefop_bank_contract,'
							. 'dbc.id_drh_budgetcategory,'
							. 'bccg.id_budget_category_configuration,'
							. 'bcc.id_budgetcategory_contract,'
							. 'bpe.id_business_plan_expense'
						    . ')'
					    )
					)
				    )
				    ->joinLeft(
					array( 'bcct' => $dbBankContract ),
					'bcct.fk_id_budget_category = bc.id_budget_category',
					array()
				    )
				    ->joinLeft(
					array( 'dbc' => $dbDRHBudgetCategory ),
					'dbc.fk_id_budget_category = bc.id_budget_category',
					array()
				    )
				    ->joinLeft(
					array( 'bccg' => $dbBudgetCategoryConfiguration ),
					'bccg.fk_id_budget_category = bc.id_budget_category',
					array()
				    )
				    ->joinLeft(
					array( 'bcc' => $dbBudgetCategoryContract ),
					'bcc.fk_id_budget_category = bc.id_budget_category',
					array()
				    )
				    ->joinLeft(
					array( 'bpe' => $dbBusinessPlanExpense ),
					'bpe.fk_id_budget_category = bc.id_budget_category',
					array()
				    )
				    ->order( array( 'type_expense', 'description' ) )
				    ->group( array( 'id_budget_category' ) );
	
	if ( !empty( $type ) )
	    $select->where( 'bc.fk_id_budget_category_type = ?', $type );
	
	return $dbBudgetCategory->fetchAll( $select );
    }
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function expensesInItem( $item )
    {
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbBudgetCataegoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	$dbBudgetCategoryConfiguration = App_Model_DbTable_Factory::get( 'BudgetCategoryConfiguration' );
	
	$select = $dbBudgetCategory->select()
				    ->from( array( 'bc' => $dbBudgetCategory ) )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 'bccfg' => $dbBudgetCategoryConfiguration ),
					'bccfg.fk_id_budget_category = bc.id_budget_category',
					array( 'order', 'amount' )
				    )
				    ->join(
					array( 'bct' => $dbBudgetCataegoryType ),
					'bct.id_budget_category_type = bc.fk_id_budget_category_type',
					array( 'type_expense' => 'description' )
				    )
				    ->where( 'bccfg.identifier = ?', $item )
				    ->group( array( 'fk_id_budget_category' ) )
				    ->order( array( 'order' ) );
	
	return $dbBudgetCategory->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function expensesNotInItem( $item )
    {
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbBudgetCategoryConfiguration = App_Model_DbTable_Factory::get( 'BudgetCategoryConfiguration' );
	$dbBudgetCataegoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	
	$selectCategoryItem = $dbBudgetCategoryConfiguration->select()
						 ->where( 'identifier = ?', $item )
						 ->where( 'fk_id_budget_category = bc.id_budget_category' )
						 ->group( array( 'fk_id_budget_category' ) );
	
	$select = $dbBudgetCategory->select()
				    ->from( array( 'bc' => $dbBudgetCategory ) )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 'bct' => $dbBudgetCataegoryType ),
					'bct.id_budget_category_type = bc.fk_id_budget_category_type',
					array( 'type_expense' => 'description' )
				    )
				    ->where( 'NOT EXISTS (?)', new Zend_Db_Expr( '(' . $selectCategoryItem . ')' ) )
				    ->order( array( 'description' ) );
	
	return $dbBudgetCategory->fetchAll( $select );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function orderExpense( $data )
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbCategoryConfiguration = App_Model_DbTable_Factory::get( 'BudgetCategoryConfiguration' );
	    
	    $expensesItem = $this->expensesInItem( $data['item'] );
	  
	    $reorderItem = array();
	    $pos = 0;
	    foreach ( $expensesItem as $expense ) {
		if ( $expense->id_budget_category == $data['id'] ) continue;
		$reorderItem[++$pos] = $expense->id_budget_category;
	    }
	    
	    foreach ( $reorderItem as $pos => $expense ) {
		
		if ( $pos >= $data['toPosition'] )
		    $reorderItem[++$pos] = $expense;
	    }
	   
	    $reorderItem[$data['toPosition']] = $data['id'];
	    
	    foreach ( $reorderItem as $pos => $expense ) {
		
		// Update current expense
		$where = array(
		    'identifier = ?'		=> $data['item'],
		    'fk_id_budget_category = ?' => $expense
		);

		$toUpdate = array( 'order' => $pos );
		$dbCategoryConfiguration->update( $toUpdate, $where );
	    }
	    
	    $updateValue = array( 'amount' => App_General_String::toFloat( $data['amount'] ) );
	    $where = array(
		'identifier = ?'	    => $data['item'],
		'fk_id_budget_category = ?' => $data['id']
	    );
	    
	    $dbCategoryConfiguration->update( $updateValue, $where );
	    
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
    public function updateAmount( $data )
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbCategoryConfiguration = App_Model_DbTable_Factory::get( 'BudgetCategoryConfiguration' );
	    
	    $updateValue = array( 'amount' => App_General_String::toFloat( $data['amount'] ) );
	    $where = array(
		'identifier = ?'	    => $data['item'],
		'fk_id_budget_category = ?' => $data['id']
	    );
	    
	    $dbCategoryConfiguration->update( $updateValue, $where );
	    
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
    public function removeExpense( $data )
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $expense = $this->fetchRow( $data['id'] );
	    $expense->delete();
	    
	    $history = sprintf( 'REMOVE RÚBRICA: %s - REMOVE RÚBRICA', $data['id'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return array( 'status' => true );
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    return array( 'status' => false );
	}
    }
    
    /**
     * 
     * @return array
     */
    public function getItemsConfig()
    {
	return $this->_configLabels;
    }
    
    /**
     * 
     * @return array
     */
    public function getItemsConfigModule()
    {
	return array_intersect_key( $this->_configLabels, array_flip($this->_moduleToItem ) );
    }
    
    /**
     * 
     * @param int $module
     * @return array
     */
    public function expensesInModule( $module ) 
    {
	if ( empty( $this->_moduleToItem[$module] ) )
	    return array();
	
	return $this->expensesInItem( $this->_moduleToItem[$module] );
    }
    
    /**
     * 
     * @param int $module
     * @return null|string
     */
    public function getModuleToItem( $module )
    {
	if ( empty( $this->_moduleToItem[$module] ) )
	    return null;
	
	return $this->_moduleToItem[$module];
    }
    
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE, $form = Fefop_Form_Expense::ID )
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