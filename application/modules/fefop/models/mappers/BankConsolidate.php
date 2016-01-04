<?php

class Fefop_Model_Mapper_BankConsolidate extends App_Model_Abstract
{
    /**
     * 
     * @var Model_DbTable_FEFOPConsolidated
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEFOPConsolidated();

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
	   
	    $items = $this->_session->bank_to_consolidate;
	    
	    if ( empty( $this->_data['consolidate'] ) || empty( $items ) )
		throw new Exception( 'Items to be consolidated not found' );
	    
	    $dbFEFOPConsolidated = App_Model_DbTable_Factory::get( 'FEFOPConsolidated' );
	    $dbFEFOPConsolidatedBank = App_Model_DbTable_Factory::get( 'FEFOPConsolidatedBank' );
	    $dbFEFOPConsolidatedTransaction = App_Model_DbTable_Factory::get( 'FEFOPConsolidatedTransaction' );
	    
	    $bankStatements = array();
	    $transactions = array();
	    
	    foreach ( $this->_data['consolidate'] as $id => $flag ) {
		
		if ( empty( $flag ) ) continue;
		if ( empty( $items[$id] ) ) continue;
		if ( empty( $items[$id]['consolidate'] ) ) continue;
		if ( empty( $items[$id]['bank_rows'] ) ) continue;
		if ( empty( $items[$id]['financial_rows'] ) ) continue;
		
		// Save the main consolidation
		$dataConsolidate = array(
		    'fk_id_sysuser' => Zend_Auth::getInstance()->getIdentity()->id_sysuser,
		    'amount'	    => App_General_String::toFloat( $items[$id]['bank_amount'] ),
		);
		
		$idConsolidate = $dbFEFOPConsolidated->insert( $dataConsolidate );
		
		// Insert the bank statements being consolidate
		foreach ( $items[$id]['bank_rows'] as $bankId ) {
		    
		    $dataBank = array(
			'fk_id_fefop_consolidated_id'	=> $idConsolidate,
			'fk_id_fefop_bank_contract'	=> $bankId
		    );
		    
		    $dbFEFOPConsolidatedBank->insert( $dataBank );
		    $bankStatements[] = $bankId;
		}
		
		// Insert the financial transactions being consolidate
		foreach ( $items[$id]['financial_rows'] as $transactionId ) {
		    
		    $dataTransaction = array(
			'fk_id_fefop_consolidated_id'	=> $idConsolidate,
			'fk_id_fefop_transaction'	=> $transactionId
		    );
		    
		    $dbFEFOPConsolidatedTransaction->insert( $dataTransaction );
		    $transactions[] = $transactionId;
		}
	    }
	    
	    // Consolidate bank statemetns
	    $this->_consolidateBankStatements( $bankStatements );
	    // Concolidate financial transactions
	    $this->_consolidateTransactions( $transactions );
	    
	    $history = sprintf( "KONSOLIDA REJISTU IHA BANKU: %s", print_r( $items, true ) );
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
     * @return boolean
     */
    public function removeConsolidate( $id )
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	   
	    $dbConsolidateTransaction = App_Model_DbTable_Factory::get( 'FEFOPConsolidatedTransaction' );
	    $dbConsolidateBank = App_Model_DbTable_Factory::get( 'FEFOPConsolidatedBank' );
	    
	    $bankConsolidate = array();
	    $financialConsolidate = array();
	    
	    $where = array( 'fk_id_fefop_consolidated_id = ?' => $id );
	    
	    // Remove all consolidation in the bank and financial
	    $rows = $dbConsolidateTransaction->fetchAll( $where );
	    foreach ( $rows as $row )
		$financialConsolidate[] = $row->fk_id_fefop_transaction;
	    
	    $rows = $dbConsolidateBank->fetchAll( $where );
	    foreach ( $rows as $row )
		$bankConsolidate[] = $row->fk_id_fefop_bank_contract;
	    
	    $dbConsolidateBank->delete( $where );
	    $dbConsolidateTransaction->delete( $where );
	    
	    $dbBankContract = App_Model_DbTable_Factory::get( 'FEFOPBankContract' );
	    $bankRows = $dbBankContract->fetchAll( array( 'id_fefop_bank_contract IN(?)' => $bankConsolidate ) );
	    $bankStatements = array();
	    foreach ( $bankRows as $bank )
		$bankStatements[] = $bank->fk_id_fefop_bank_statements;
	    
	    $bankStatements = array_unique( $bankStatements );
		
	    $dbBankStatement = App_Model_DbTable_Factory::get( 'FEFOPBankStatements' );
	    foreach ( $bankStatements as $bank ) {
		
		$statement = $dbBankStatement->fetchRow( array( 'id_fefop_bank_statements = ?' => $bank ) );
		$statement->status = Fefop_Model_Mapper_BankStatement::PARCIAL;
		$statement->consolidate = 0;
		$statement->save();
	    }
	    
	    $dbFEFOPTransactionHistory = App_Model_DbTable_Factory::get( 'FEFOPTransactionHistory' );
	    foreach ( $financialConsolidate as $financial ) {
		
		$where = array( 'fk_id_fefop_transaction = ?' => $financial, 'status = ?' => 1 );
		$dataUpdate = array( 'status' => 0 );

		$dbFEFOPTransactionHistory->update( $dataUpdate, $where );

		$dataInsert = array(
		    'fk_id_fefop_transaction_status'    => Fefop_Model_Mapper_Financial::ACTIVE,
		    'fk_id_fefop_transaction'		=> $financial,
		    'fk_id_sysuser'			=> Zend_Auth::getInstance()->getIdentity()->id_sysuser,
		    'status'				=> 1
		);
		
		$dbFEFOPTransactionHistory->insert( $dataInsert );
	    }
	    
	    $dbConsolidate = App_Model_DbTable_Factory::get( 'FEFOPConsolidated' );
	    $where = array( 'id_fefop_consolidated_id = ?' => $id );
	    $dbConsolidate->delete( $where );
	    
	    $history = sprintf( "REMOVE KONSOLIDA REJISTU IHA BANKU: %s", $id );
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
     * @param array $bankContract
     */
    protected function _consolidateBankStatements( $bankContract )
    {
	$dbFEFOPBankContract = App_Model_DbTable_Factory::get( 'FEFOPBankContract' );
	$dbFEFOPConsolidatedBank = App_Model_DbTable_Factory::get( 'FEFOPConsolidatedBank' );
	$dbFEFOPBankStatements = App_Model_DbTable_Factory::get( 'FEFOPBankStatements' );
	
	$bankContractRows = $dbFEFOPBankContract->fetchAll( array( 'id_fefop_bank_contract IN (?)' => $bankContract ) );
	
	$selectNotConsolidated = $dbFEFOPBankContract->select()
						     ->from( array( 'bc' => $dbFEFOPBankContract ) )
						     ->setIntegrityCheck( false )
						     ->joinLeft(
							array( 'bcl' => $dbFEFOPConsolidatedBank ),
							'bcl.fk_id_fefop_bank_contract = bc.id_fefop_bank_contract',
							array()
						     )
						     ->where( 'bcl.id_relationship IS NULL' );
	
	$bankStatements = array();
	foreach ( $bankContractRows as $bankRow ) {
	    
	    if ( in_array( $bankRow->fk_id_fefop_bank_statements, $bankStatements ) )
		continue;
	    
	    $bankStatements[] = $bankRow->fk_id_fefop_bank_statements;
	    
	    $select = clone $selectNotConsolidated;
	    $select->where( 'bc.fk_id_fefop_bank_statements = ?', $bankRow->fk_id_fefop_bank_statements );
	    
	    $bankNotConsolidated = $dbFEFOPBankContract->fetchAll( $select );
	    
	    // If there are statements not consolidated yet
	    if ( $bankNotConsolidated->count() > 0 ) {
		
		$dataUpdate = array(
		    'status'	    => Fefop_Model_Mapper_BankStatement::PARCIAL,
		    'consolidate'   =>	0
		);
		
	    } else {
		
		$dataUpdate = array(
		    'status'	    => Fefop_Model_Mapper_BankStatement::CONSOLIDATED,
		    'consolidate'   =>	1
		);
	    }
	    
	    $where = array( 'id_fefop_bank_statements = ?' => $bankRow->fk_id_fefop_bank_statements );
	    $dbFEFOPBankStatements->update( $dataUpdate, $where );
	}
    }
    
    /**
     * 
     * @param array $transactions
     */
    protected function _consolidateTransactions( $transactions )
    {
	$dbFEFOPTransactionHistory = App_Model_DbTable_Factory::get( 'FEFOPTransactionHistory' );
	
	foreach ( $transactions as $transaction ) {
	    
	    $where = array( 'fk_id_fefop_transaction = ?' => $transaction, 'status = ?' => 1 );
	    $dataUpdate = array( 'status' => 0 );
	    
	    $dbFEFOPTransactionHistory->update( $dataUpdate, $where );
	    
	    $dataInsert = array(
		'fk_id_fefop_transaction_status'    => Fefop_Model_Mapper_Financial::CONSOLIDATED,
		'fk_id_fefop_transaction'	    => $transaction,
		'fk_id_sysuser'			    => Zend_Auth::getInstance()->getIdentity()->id_sysuser,
		'status'			    => 1
	    );
	    $dbFEFOPTransactionHistory->insert( $dataInsert );
	}
    }
    
    /**
     * 
     * @return array
     */
    public function listExpensesToConsolidate()
    {
	// Search items to be consolidated in bank statemetns and financial transactions
	$expensesBank = $this->listExpensesToConsolidateBank();
	$expensesFinancial = $this->listExpensesToConsolidateFinancial();
	
	$identifier = '%s_%s';
	
	// Group the bank statemtns by CONTRACT_EXPENSE
	$groupExpensesBank = array();
	foreach ( $expensesBank as $expenseBank ) {
	    
	    $currentIdentifier = sprintf( $identifier, $expenseBank->fk_id_fefop_contract, $expenseBank->fk_id_budget_category );
	    $groupExpensesBank[$currentIdentifier] = $expenseBank;
	}
	
	// Group the financial transactions by CONTRACT_EXPENSE
	$groupExpensesFinancial = array();
	foreach ( $expensesFinancial as $expenseFinancial ) {
	    
	    $currentIdentifier = sprintf( $identifier, $expenseFinancial->fk_id_fefop_contract, $expenseFinancial->fk_id_budget_category );
	    $groupExpensesFinancial[$currentIdentifier] = $expenseFinancial;
	}
	
	$toConsolidate = array();
	foreach ( $groupExpensesBank as $currentIdentifier => $row ) {
	    
	    // If there is no financial transaction to the current CONTRACT_EXPENSE
	    if ( !array_key_exists( $currentIdentifier, $groupExpensesFinancial ) ) {
		
		$consolidate = false;
		$financialTotal = 0;
		$financialContract = 0;
	    } else {
		
		// Get the financial transaction related to the current CONTRACT_EXPENSE
		$financialEntry = $groupExpensesFinancial[$currentIdentifier];
		$consolidate = (string)$row->total === (string)$financialEntry->total;
		
		$financialTotal = $financialEntry->total;
		$financialContract = $financialEntry->financial_contract;
	    }
	    
	    $itemToConsolidate = array(
		'contract'	    => Fefop_Model_Mapper_Contract::buildNumById( $row->fk_id_fefop_contract ),
		'id_contract'	    => $row->fk_id_fefop_contract,
		'bank_amount'	    => $row->total,
		'consolidate'	    => $consolidate,
		'expense'	    => $row->expense,
		'component'	    => $row->component,
		'total_financial'   => $financialTotal,
		'bank_rows'	    => explode( ',', $row->bank_contract ),
		'financial_rows'    => explode( ',', $financialContract ),
	    );
	    
	    $toConsolidate[$currentIdentifier] = $itemToConsolidate;
	    
	    unset( $groupExpensesBank[$currentIdentifier] );
	    unset( $groupExpensesFinancial[$currentIdentifier] );
	}
	
	foreach ( $groupExpensesFinancial as $currentIdentifier => $row ) {
	    
	    $itemToConsolidate = array(
		'contract'	    => Fefop_Model_Mapper_Contract::buildNumById( $row->fk_id_fefop_contract ),
		'id_contract'	    => $row->fk_id_fefop_contract,
		'bank_amount'	    => 0,
		'consolidate'	    => false,
		'expense'	    => $row->expense,
		'component'	    => $row->component,
		'total_financial'   => $row->total,
		'bank_rows'	    => array(),
		'financial_rows'    => explode( ',', $row->financial_contract ),
	    );
	    
	    $toConsolidate[$currentIdentifier] = $itemToConsolidate;
	}
	
	$this->_session->bank_to_consolidate = $toConsolidate;
	
	return $toConsolidate;
    }
    
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listExpensesToConsolidateBank()
    {
	$dbBankContract = App_Model_DbTable_Factory::get( 'FEFOPBankContract' );
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbBudgetCategoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	$dbBankConsolidate = App_Model_DbTable_Factory::get( 'FEFOPConsolidatedBank' );
	
	$select = $dbBankContract->select()
				   ->from( array( 'bc' => $dbBankContract ) )
				   ->setIntegrityCheck( false )
				   ->join(
					array( 'e' => $dbBudgetCategory ),
					'e.id_budget_category = bc.fk_id_budget_category',
					array( 
					    'expense'	    => 'description',
					    'total'	    => new Zend_Db_Expr( 'SUM( bc.amount * IF( bc.operation = "D", -1, 1) )' ),
					    'bank_contract' => new Zend_Db_Expr( 'GROUP_CONCAT(id_fefop_bank_contract)' )
					)
				    )
				    ->join(
					array( 'bct' => $dbBudgetCategoryType ),
					'e.fk_id_budget_category_type = bct.id_budget_category_type',
					array(
					    'component' => 'description'
					)
				    )
				    ->joinLeft(
					array( 'bcl' => $dbBankConsolidate ),
					'bcl.fk_id_fefop_bank_contract = bc.id_fefop_bank_contract',
					array()
				    )
				    ->where( 'bcl.id_relationship IS NULL' )
				    ->group( array( 'fk_id_fefop_contract', 'fk_id_budget_category' ) );
	
	return $dbBankContract->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listExpensesToConsolidateFinancial()
    {
	$mapperFinancial = new Fefop_Model_Mapper_Financial();
	$select = $mapperFinancial->getSelect();
	
	$select->reset(Zend_Db_Select::GROUP);
	
	$dbTransactionConsolidate = App_Model_DbTable_Factory::get( 'FEFOPConsolidatedTransaction' );
	
	$select->joinLeft(
		    array( 'bcl' => $dbTransactionConsolidate ),
		    'bcl.fk_id_fefop_transaction = t.id_fefop_transaction',
		    array(
			'total'			=> new Zend_Db_Expr( 'SUM( t.amount * IF( t.operation = "D", -1, 1) )' ),
			'financial_contract'	=> new Zend_Db_Expr( 'GROUP_CONCAT(id_fefop_transaction)' )
		    )
		)
		->group( array( 'fk_id_fefop_contract', 'fk_id_budget_category' ) );
	
	return $dbTransactionConsolidate->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
	$dbConsolidated = App_Model_DbTable_Factory::get( 'FEFOPConsolidated' );
	$dbConsolidatedBank = App_Model_DbTable_Factory::get( 'FEFOPConsolidatedBank' );
	$dbBankContract = App_Model_DbTable_Factory::get( 'FEFOPBankContract' );
	$dbBankStatements = App_Model_DbTable_Factory::get( 'FEFOPBankStatements' );
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	
	$select = $dbConsolidated->select()
				 ->from(
				    array( 'co' => $dbConsolidated ),
				    array(
					'id_fefop_consolidated_id',
					'date_consolidated'	=> 'date_inserted',
					'total_consolidated'	=> 'amount',
				    )
				 )
				 ->setIntegrityCheck( false )
				 ->join(
				    array( 'cb' => $dbConsolidatedBank ),
				    'cb.fk_id_fefop_consolidated_id = co.id_fefop_consolidated_id',
				    array()
				 )
				 ->join(
				    array( 'bc' => $dbBankContract ),
				    'bc.id_fefop_bank_contract = cb.fk_id_fefop_bank_contract',
				    array(
					'total_statement' => 'bc.amount',
					'date_statement'  => 'date_inserted'
				    )
				 )
				 ->join(
				    array( 'bs' => $dbBankStatements ),
				    'bs.id_fefop_bank_statements = bc.fk_id_fefop_bank_statements',
				    array( 'description', 'operation' )
				 )
				 ->join(
				    array( 'bgc' => $dbBudgetCategory ),
				    'bgc.id_budget_category = bc.fk_id_budget_category',
				    array()
				 )
				 ->group( array( 'id_fefop_consolidated_id', 'id_fefop_bank_contract' ) );
	
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
	
	if ( !empty( $filters['fk_id_fefop_type_transaction'] ) )
	    $select->where( 'bs.fk_id_fefop_type_transaction = ?', $filters['fk_id_fefop_type_transaction'] );
	
	if ( !empty( $filters['id_fefop_bank_contract'] ) )
	    $select->where( 'bc.fk_id_fefop_contract = ?', $filters['id_fefop_bank_contract'] );
	
	if ( !empty( $filters['fk_id_budget_category'] ) )
	    $select->where( 'bc.fk_id_budget_category IN (?)', (array)$filters['fk_id_budget_category'] );
	
	if ( !empty( $filters['fk_id_budget_category_type'] ) )
	    $select->where( 'bgc.fk_id_budget_category_type IN (?)', (array)$filters['fk_id_budget_category_type'] );

	if ( !empty( $filters['minimum_amount'] ) )
	    $select->having( 'ABS(total_consolidated) >= ?', App_General_String::toFloat ( $filters['minimum_amount'] ) );

	if ( !empty( $filters['maximum_amount'] ) )
	    $select->having( 'ABS(total_consolidated) <= ?', App_General_String::toFloat ( $filters['maximum_amount'] ) );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'DATE(co.date_inserted) >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'DATE(co.date_inserted) <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $consolidated_id
     * @return Zend_Db_Table_Rowset
     */
    public function listStatementsBankConsolidated( $consolidated_id )
    {
	$mapperBankStatements = new Fefop_Model_Mapper_BankStatement();
	$select = $mapperBankStatements->getSelect();
	
	$select->where( 'bcb.fk_id_fefop_consolidated_id = ?', $consolidated_id )
		->reset( Zend_Db_Select::GROUP );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param array $financial_transactions
     * @return Zend_Db_Table_Rowset
     */
    public function listFinancialConsolidated( $consolidated_id )
    {
	$mapperFinancial = new Fefop_Model_Mapper_Financial();
	$select = $mapperFinancial->getSelect();
	
	$dbFEFOPConsolidatedTransaction = App_Model_DbTable_Factory::get( 'FEFOPConsolidatedTransaction' );
	$select->join(
		    array( 'ct' => $dbFEFOPConsolidatedTransaction ),
		    'ct.fk_id_fefop_transaction = t.id_fefop_transaction',
		    array()
		)
		->where( 'ct.fk_id_fefop_consolidated_id = ?', $consolidated_id );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param array $bank_statements
     * @return Zend_Db_Table_Rowset
     */
    public function listBankStatementsIn( $bank_statements )
    {
	$mapperBankStatements = new Fefop_Model_Mapper_BankStatement();
	$select = $mapperBankStatements->getSelect();
	
	$select->where( 'bsc.id_fefop_bank_contract IN (?)', $bank_statements )
		->reset( Zend_Db_Select::GROUP );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param array $financial_transactions
     * @return Zend_Db_Table_Rowset
     */
    public function listFinancialTransactionsIn( $financial_transactions )
    {
	$mapperFinancial = new Fefop_Model_Mapper_Financial();
	$select = $mapperFinancial->getSelect();
	
	$select->where( 't.id_fefop_transaction IN (?)', $financial_transactions );
	
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
	    'fk_id_sysform'	    => Fefop_Form_BankConsolidate::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}