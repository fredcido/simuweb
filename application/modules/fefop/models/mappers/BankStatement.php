<?php

class Fefop_Model_Mapper_BankStatement extends App_Model_Abstract
{
    const CONSOLIDATED = 'C';
    
    const PENDING = 'P';
    
    const PARCIAL = 'A';
    
    const DEBIT = 'D';
    
    const CREDIT = 'C';
    
    
    /**
     * 
     * @var Model_DbTable_FEFOPBankStatements
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEFOPBankStatements();

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
	    
	    $dbFEFOPBankStatements = App_Model_DbTable_Factory::get( 'FEFOPBankStatements' );
	    
	    $dateStatement = new Zend_Date( $this->_data['date_statement'] );
	    $this->_data['date_statement'] = $dateStatement->toString( 'yyyy-MM-dd' );
	    $this->_data['amount'] = App_General_String::toFloat( $this->_data['amount'] );
		
	    $dbFEFOPTypeTransaction = App_Model_DbTable_Factory::get( 'FEFOPTypeTransaction' );
	    $transactionsType = $dbFEFOPTypeTransaction->fetchAll( array( 'type  = ?' => 'B' ) );

	    $transactionsTypeId = array();
	    foreach ( $transactionsType as $transactionType )
		$transactionsTypeId[] = $transactionType->id_fefop_type_transaction;

	    if ( in_array( $dataForm['fk_id_fefop_type_transaction'], $transactionsTypeId ) )
		$this->_data['status'] = self::CONSOLIDATED;
	    else
		$this->_data['status'] = self::PENDING;
	    
	    if ( empty( $dataForm['id_fefop_bank_statements'] ) )
		$history = "INSERE LANSAMENTU IHA BANKU: %s";
	    else
		$history = "ATUALIZA LANSAMENTU IHA BANKU: %s";
	    
	    $idStatement = parent::_simpleSave( $dbFEFOPBankStatements, false );
	    
	    $statementsId = array();
	    if ( !empty( $dataForm['total_contract'] ) ) {
	    
		foreach ( $dataForm['total_contract'] as $idContract => $totalContract ) {

		    if ( empty( $dataForm['fk_id_budget_category'][$idContract] ) ) continue;

		    foreach ( $dataForm['fk_id_budget_category'][$idContract] as $countExpense => $idExpense ) {

			$idBankContract = null;
			if ( !empty( $dataForm['id_fefop_bank_contract'][$idContract][$countExpense] ) )
			    $idBankContract = $dataForm['id_fefop_bank_contract'][$idContract][$countExpense];

			$dataTransaction = array(
			    'id_fefop_bank_contract'	    => $idBankContract,
			    'fk_id_fefop_bank_statements'   => $idStatement,
			    'fk_id_fefop_contract'	    => $idContract,
			    'description'		    => $dataForm['description'],
			    'amount'			    => App_General_String::toFloat( $dataForm['total_expense'][$idContract][$countExpense] ),
			    'operation'			    => $dataForm['operation'],
			    'fk_id_budget_category'	    => $idExpense
			);

			// Save the Bank Statement
			$row = $this->_saveBankStatement( $dataTransaction );
			$idBankContract = $row->id_fefop_bank_contract;

			$statementsId[] = $idBankContract;
		    }
		}
	    }
	 
	    // Remove all the contract transactions not related with the Transaction
	    $this->_removeTransactionsContract( $idStatement, $statementsId );
	    
	    // Set the current amount at time of the transaction
	    $totals = $this->calcTotals();
	    $dbFEFOPBankStatements->update( array( 'current_balance' => $totals['current'] ), array( 'id_fefop_bank_statements = ?' => $idStatement ) );
	    
	    $history = sprintf( $history, $idStatement );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return $idStatement;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @param int $idStatement
     * @param array $statementsId
     */
    protected function _removeTransactionsContract( $idStatement, $statementsId )
    {
	$dbFEFOPBankContract = App_Model_DbTable_Factory::get( 'FEFOPBankContract' );
	
	$where = array( 'fk_id_fefop_bank_statements = ?' => $idStatement );
	$rows = $dbFEFOPBankContract->fetchAll( $where );
	
	$idsInDataBase = array();
	foreach ( $rows as $row )
	    $idsInDataBase[] = $row->id_fefop_bank_contract;
	
	$idsToDelete = array_diff( $idsInDataBase, $statementsId );
	
	foreach ( $idsToDelete as $id ) {
	    
	    $where = array( 'id_fefop_bank_contract = ?' => $id );
	    $row = $dbFEFOPBankContract->fetchRow( $where );
	    $dbFEFOPBankContract->delete( $where );
	    
	    $description = 'FEFOP - REMOVE LANSAMENTU BANKARIU: %s';
	    $description = sprintf( $description, print_r( $row->toArray(), true ) );
	    $this->_sysAudit( $description, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	}
    }
    
    /**
     * 
     * @param array $data
     * @return int
     */
    protected function _saveBankStatement( $data )
    {
	$dbFEFOPBankContract = App_Model_DbTable_Factory::get( 'FEFOPBankContract' );
	
	// If the value is already a transaction
	if ( !empty( $data['id_fefop_bank_contract'] ) ) {

	    $idBankStatement = $data['id_fefop_bank_contract'];
	    $where = array( 'id_fefop_bank_contract = ?' => $idBankStatement );
	    $row = $dbFEFOPBankContract->fetchRow( $where );
	}

	if ( empty( $row ) )
	    $row = $dbFEFOPBankContract->createRow();
	
	$row->setFromArray( $data );
	$row->save();
	return $row;
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
	$dbBankStatement = App_Model_DbTable_Factory::get( 'FEFOPBankStatements' );
	$dbBankContract = App_Model_DbTable_Factory::get( 'FEFOPBankContract' );
	$dbFEFOPFund = App_Model_DbTable_Factory::get( 'FEFOPFund' );
	$dbFEFOPTypeTransaction = App_Model_DbTable_Factory::get( 'FEFOPTypeTransaction' );
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbFEFOPConsolidatedBank = App_Model_DbTable_Factory::get( 'FEFOPConsolidatedBank' );
	
	$select = $dbBankStatement->select()
				    ->from( array( 'bs' => $dbBankStatement ) )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 'tt' => $dbFEFOPTypeTransaction ),
					'tt.id_fefop_type_transaction = bs.fk_id_fefop_type_transaction',
					array( 'type_transaction' => 'description', 'type' )
				    )
				    ->joinLeft(
					array( 'bsc' => $dbBankContract ),
					'bsc.fk_id_fefop_bank_statements = bs.id_fefop_bank_statements',
					array(
					    'amount_contract' => 'amount',
					    'id_fefop_bank_contract'
					)
				    )
				    ->joinLeft(
					array( 'bc' => $dbBudgetCategory ),
					'bsc.fk_id_budget_category = bc.id_budget_category',
					array()
				    )
				    ->joinLeft(
					array( 'bcb' => $dbFEFOPConsolidatedBank ),
					'bcb.fk_id_fefop_bank_contract = bsc.id_fefop_bank_contract',
					array( 'fk_id_fefop_consolidated_id' )
				    )
				    ->joinLeft(
					array( 'ff' => $dbFEFOPFund ),
					'ff.id_fefopfund = bs.fk_id_fefopfund',
					array( 'name_fund' )
				    )
				    ->group( array( 'bs.id_fefop_bank_statements' ) )
				    ->order( array( 'bs.date_statement' ) );
	
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
	$select->where( 'bs.id_fefop_bank_statements = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @param Zend_Db_Select $select
     * @param array $filters
     * @return Zend_Db_Select
     */
    public function applyFiltersSelect( $select, $filters )
    {
	if ( !empty( $filters['fk_id_fefop_type_transaction'] ) )
	    $select->where( 'bs.fk_id_fefop_type_transaction = ?', $filters['fk_id_fefop_type_transaction'] );
	
	if ( !empty( $filters['fk_id_budget_category'] ) )
	    $select->where( 'bsc.fk_id_budget_category IN (?)', (array)$filters['fk_id_budget_category'] );
	
	if ( !empty( $filters['fk_id_budget_category_type'] ) )
	    $select->where( 'bc.fk_id_budget_category_type IN (?)', (array)$filters['fk_id_budget_category_type'] );
	
	if ( !empty( $filters['fk_id_fefopfund'] ) )
	    $select->where( 'bs.fk_id_fefopfund = ?', $filters['fk_id_fefopfund'] );
	
	if ( !empty( $filters['status'] ) )
	    $select->where( 'bs.status = ?', $filters['status'] );

	if ( !empty( $filters['minimum_amount'] ) )
	    $select->where( 'bs.amount >= ?', App_General_String::toFloat ( $filters['minimum_amount'] ) );

	if ( !empty( $filters['maximum_amount'] ) )
	    $select->where( 'bs.amount <= ?', App_General_String::toFloat ( $filters['maximum_amount'] ) );
	
	if ( !empty( $filters['num_contract'] ) ) {
	    
	    $dbFefopContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	    
	    $select->join(
			array( 'fct' => $dbFefopContract ),
			'fct.id_fefop_contract = bsc.fk_id_fefop_contract',
			array( 'num_contract' => new Zend_Db_Expr( 'CONCAT( fct.num_program, "-",'
								    . ' fct.num_module, "-", '
								    . 'fct.num_district, "-", '
								    . 'fct.num_year, "-", fct.num_sequence )' ) )
		    )
		    ->having( 'num_contract LIKE ?', '%' . $filters['num_contract'] . '%' );
	}
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'bs.date_statement >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'bs.date_statement <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
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
	
	$this->applyFiltersSelect( $select, $filters );
	
	$select->limit( 1000 );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listContractsStatements( $id )
    {
	$dbBankContract = App_Model_DbTable_Factory::get( 'FEFOPBankContract' );
	$dbConsolidatedBank = App_Model_DbTable_Factory::get( 'FEFOPConsolidatedBank' );
	
	$select = $dbBankContract->select()
				 ->from( array( 'bc' => $dbBankContract ) )
				 ->setIntegrityCheck( false )
				 ->joinLeft(
				    array( 'cb' => $dbConsolidatedBank ),
				    'cb.fk_id_fefop_bank_contract = bc.id_fefop_bank_contract',
				    array( 'fk_id_fefop_consolidated_id' )
				 )
				 ->where( 'bc.fk_id_fefop_bank_statements = ?', $id );
	
	return $dbBankContract->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return array
     */
    public function groupContracts( $id )
    {
	$contracts = $this->listContractsStatements( $id );
	
	$data = array();
	foreach ( $contracts as $contract ) {
	    
	    $numContract = Fefop_Model_Mapper_Contract::buildNumById( $contract->fk_id_fefop_contract );
	    
	    if ( !array_key_exists( $numContract, $data ) ) {
		
		$data[$numContract] = array(
		    'id_fefop_contract'	=> $contract->fk_id_fefop_contract,
		    'num_contract'	=> $numContract,
		    'total_contract'	=> 0,
		    'can-delete'	=> true,
		    'expenses'		=> array()
		);
	    }
	    
	    $data[$numContract]['total_contract'] += $contract['amount'];
	    $data[$numContract]['expenses'][] = $contract;
	    $data[$numContract]['can-delete'] = $data[$numContract]['can-delete'] && empty( $contract['fk_id_fefop_consolidated_id'] );
	}
	
	return $data;
    }
    
    /**
     * 
     * @return array
     */
    public function calcTotals()
    {
	$dbFEFOPBankStatements = App_Model_DbTable_Factory::get( 'FEFOPBankStatements' );
	
	$select = $dbFEFOPBankStatements->select()
					->from(
					    array( 'bs' => $dbFEFOPBankStatements ),
					    array( 'total' => new Zend_Db_Expr( 'SUM( bs.amount * IF( bs.operation = "D", -1, 1) )' ) )
					);
	
	
	$selectConsolidated = clone $select;
	$selectLastMonth = clone $select;
	
	$today = new Zend_Date();
	$today->setDay(1)->subDay(1);
	
	$selectConsolidated->where( 'bs.status = ?', self::CONSOLIDATED );
	$selectLastMonth->where( 'bs.date_statement < ?', $today->toString( 'yyyy-MM-dd' ) );
	
	$currentTotal = $dbFEFOPBankStatements->fetchRow( $select )->total;
	$consolidatedTotal = $dbFEFOPBankStatements->fetchRow( $selectConsolidated )->total;
	$lastMonthTotal = $dbFEFOPBankStatements->fetchRow( $selectLastMonth )->total;
	
	$totals = array(
	    'current'	    => (float)$currentTotal,
	    'consolidated'  => (float)$consolidatedTotal,
	    'last_month'    => (float)$lastMonthTotal,
	);
	
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
	    'fk_id_sysform'	    => Fefop_Form_BankStatement::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}