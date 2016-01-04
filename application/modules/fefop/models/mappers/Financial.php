<?php

class Fefop_Model_Mapper_Financial extends App_Model_Abstract
{
    const ACTIVE = 1;
    
    const INACTIVE = 2;
    
    const CONSOLIDATED = 3;
    
    const TYPE_CONTRACT = 1;
    
    const TYPE_REIMBURSEMENT = 2;
    
    const DEBIT = 'D';
    
    const CREDIT = 'C';
    
    
    /**
     * 
     * @var Model_DbTable_FEFOPTransaction
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEFOPTransaction();

	return $this->_dbTable;
    }

    /**
     * 
     * @return int|bool
     */
    public function saveTransaction()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dataForm = $this->_data;
	    
	    $dbFEFOPReceipt = App_Model_DbTable_Factory::get( 'FEFOPReceipt' );
	    
	    if ( !empty( $dataForm['fk_id_fefpenterprise'] ) ) {
	    
		$datePurchased = new Zend_Date( $this->_data['date_purchased'] );
		$this->_data['date_purchased'] = $datePurchased->toString( 'yyyy-MM-dd' );
		$this->_data['amount'] = App_General_String::toFloat( $this->_data['amount'] );

		$idReceipt = parent::_simpleSave( $dbFEFOPReceipt, false );
	    }
	    
	    $transactionsId = array();
	    foreach ( $dataForm['total_contract'] as $idContract => $totalContract ) {
		
		if ( empty( $dataForm['fk_id_budget_category'][$idContract] ) ) continue;
		
		foreach ( $dataForm['fk_id_budget_category'][$idContract] as $countExpense => $idExpense ) {
		    
		    $idTransaction = null;
		    if ( !empty( $dataTransaction['fk_id_fefop_transaction'][$idContract][$countExpense] ) )
			$idTransaction = $dataTransaction['fk_id_fefop_transaction'][$idContract][$countExpense];
		    
		    $dataTransaction = array(
			'id_fefop_transaction'		=> $idTransaction,
			'fk_id_fefop_type_transaction'  => self::TYPE_CONTRACT,
			'fk_id_fefop_contract'		=> $idContract,
			'description'			=> $dataForm['description'],
			'amount'			=> App_General_String::toFloat( $dataForm['total_expense'][$idContract][$countExpense] ),
			'date_reference'		=> $this->_data['date_purchased'],
			'operation'			=> self::DEBIT,
			'fk_id_budget_category'		=> $idExpense
		    );
		    
		    // Save the Transaction
		    $row = $this->_saveTransaction( $dataTransaction );
		    $idTransaction = $row->id_fefop_transaction;
		    
		    // Save the History to the Transacton
		    $this->_saveTransactionHistory( $idTransaction, self::ACTIVE );
		    
		    if ( !empty( $idReceipt ) ) {
			
			//  Save the Transaction related with the receipt
			$this->_saveTransactionReceipt( $idTransaction, $idReceipt );
		    }
		    
		    // Save Auditing to Transaction
		    $this->_saveTransactionAuditing( $row );
		    
		    $transactionsId[] = $idTransaction;
		}
		
		// Calculate the redistribution of fund
		$this->calcRealFund( $idContract );
	    }
	    
	    if ( !empty( $idReceipt ) ) {
		
		// Remove all the transactions not related with the Receipt
		$this->_removeTransactionsReceipt( $idReceipt, $transactionsId );
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
     * @return int|bool
     */
    public function saveTransactionContract()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dataForm = $this->_data;
	    
	    $dateReferente = new Zend_Date( $dataForm['date_reference'] );
	    
	    $dataTransaction = array(
		'id_fefop_transaction'		=> $dataForm['id_fefop_transaction'],
		'fk_id_fefop_type_transaction'  => $dataForm['fk_id_fefop_type_transaction'],
		'fk_id_fefop_contract'		=> $dataForm['fk_id_fefop_contract'],
		'description'			=> $dataForm['description'],
		'amount'			=> App_General_String::toFloat( $dataForm['amount'] ),
		'date_reference'		=> $dateReferente->toString( 'yyyy-MM-dd' ),
		'operation'			=> $dataForm['fk_id_fefop_type_transaction'] == self::TYPE_CONTRACT ? self::DEBIT : self::CREDIT,
		'fk_id_budget_category'		=> $dataForm['fk_id_budget_category'],
		'fk_id_budget_category_type'	=> $dataForm['fk_id_budget_category_type']
	    );
		    
	    // Save the Transaction
	    $row = $this->_saveTransaction( $dataTransaction );
	    $idTransaction = $row->id_fefop_transaction;

	    // Save the History to the Transacton
	    $this->_saveTransactionHistory( $idTransaction, self::ACTIVE );

	    // Save Auditing to Transaction
	    $this->_saveTransactionAuditing( $row );
	    
	    // Calculate the redistribution of fund
	    $this->calcRealFund( $dataForm['fk_id_fefop_contract'] );
	    
	    // Calculate proportional additional costs
	    if ( Fefop_Model_Mapper_ExpenseType::ADDITIONALS == $dataForm['fk_id_budget_category_type'] ) {
		$this->calcProportionalAdditionalCost( $dataTransaction['fk_id_fefop_contract'], $dataTransaction['fk_id_budget_category'] );
	    }
	    
	    $dbAdapter->commit();
	    
	    return $idTransaction;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @param int$contract
     * @param int $expense
     * @return boolean
     */
    public function calcProportionalAdditionalCost( $contract, $expense )
    {
	$dbContractAdditional = App_Model_DbTable_Factory::get( 'FEFOPContractAdditional' );
	
	$where = array(
	    'fk_id_fefop_contract = ?'	=> $contract,
	    'fk_id_budget_category = ?'	=> $expense,
	    'user_entered = ?'		=> 1
	);
	
	$expenseAdditional = $dbContractAdditional->fetchAll( $where );
	
	if ( $expenseAdditional->count() < 1 )
	    return true;
	
	$total = 0;
	foreach ( $expenseAdditional as $additional )
	    $total += $additional->amount;
	
	$totalExpense = abs($this->getTotalExpenseContract( $contract, $expense ));
	foreach ( $expenseAdditional as $additional ) {
	    
	    $additional->amount = round($totalExpense * ( $additional->amount / $total ), 2);
	    $additional->save();
	}
    }
    
    /**
     * 
     * @param int $contract
     * @param int $expense
     * @return boolean
     */
    public function calcAdditionalCost( $contract )
    {
	$sql = "SELECT 
		    t.fk_id_budget_category,
		    f.id_fefopfund,
		    ROUND(( t.total * f.percent ) / 100, 2 ) real_amount
		FROM (
			SELECT 
			      ft.fk_id_budget_category,
			      ABS(SUM(ft.amount * IF (ft.operation='D', -1, 1))) total
			FROM FEFOP_Transaction ft
			WHERE ft.fk_id_budget_category_type = :component_add
			    AND ft.fk_id_fefop_contract = :contract
			GROUP BY ft.fk_id_budget_category
		) t
		CROSS JOIN (
			SELECT ff.id_fefopfund,
			IFNULL(( cf.contract_amount / 
			    (
				(
				    SELECT SUM(ff1.contract_amount)
				    FROM FEFOP_Contract_Fund ff1
				    WHERE ff1.fk_id_fefop_contract = :contract
				) / 100 
			    ) 
			 ),0) AS percent
			      FROM FEFOPFund ff
			      LEFT JOIN FEFOP_Contract_Fund cf ON
				ff.id_fefopfund = cf.fk_id_fefopfund
				AND cf.fk_id_fefop_contract = :contract
				GROUP BY ff.id_fefopfund
		) f
		WHERE NOT EXISTS (
		    SELECT NULL
		    FROM FEFOP_Contract_Additional ca
		    WHERE ca.fk_id_budget_category = t.fk_id_budget_category
			AND ca.fk_id_fefop_contract = :contract
			AND ca.user_entered = 1
		)";
	
	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
	$stmt = $db->prepare( $sql );
	
	$bind = array(
	    ':component_add'	=> Fefop_Model_Mapper_ExpenseType::ADDITIONALS,
	    ':contract'		=> $contract
	);
	
	$dbContractAdditional = App_Model_DbTable_Factory::get( 'FEFOPContractAdditional' );
	
	$stmt->execute( $bind );
	$rows = $stmt->fetchAll();
	
	foreach ( $rows as $row ) {
	    
	    $where = array(
		'fk_id_fefop_contract = ?'  => $contract,
		'fk_id_budget_category = ?' => $row['fk_id_budget_category'],
		'fk_id_fefopfund = ?'	    => $row['id_fefopfund']
	    );
	    
	    $rowFund = $dbContractAdditional->fetchRow( $where );
	    if ( empty( $rowFund ) ) {
		
		$rowFund = $dbContractAdditional->createRow();
		$rowFund->fk_id_fefop_contract = $contract;
		$rowFund->fk_id_budget_category = $row['fk_id_budget_category'];
		$rowFund->fk_id_fefopfund = $row['id_fefopfund'];
	    }
	    
	    $rowFund->amount = App_General_String::toFloat( $row['real_amount'] );
	    $rowFund->save();
	}
    }
    
    /**
     * 
     * @return boolean
     */
    public function saveFundContract()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dataForm = $this->_data;
	    
	    $dbFEFOPContractFund = App_Model_DbTable_Factory::get( 'FEFOPContractFund' );
	    $history = 'FEFOP - KONTABILIZA FUNDO: %s BA KONTRATU: %s - FOLIN: %s';
	    
	    $totalComponents = $this->totalComponentByContract( $dataForm['fk_id_fefop_contract'] );
	    
	    $totalContract = 0;
	    $totalComponentsContract = array();
	    
	    foreach ( $totalComponents as $total ) {
		$totalComponentsContract[$total->fk_id_budget_category_type] = $total->total;
		$totalContract += $total->total;
	    }
	    
	    $totalFundComponent = array();
	    $totalFund = 0;
	    foreach ( $dataForm['fund'] as $fund => $components ) {
		foreach ( $components as $component => $amount ) {
		    
		    $where = array(
			'fk_id_fefop_contract = ?'	=> $dataForm['fk_id_fefop_contract'],
			'fk_id_budget_category_type = ?'=> $component,
			'fk_id_fefopfund = ?'		=> $fund
		    );

		    $contractFund = $dbFEFOPContractFund->fetchRow( $where );
		    if ( empty( $contractFund ) ) {

			$contractFund = $dbFEFOPContractFund->createRow();
			$contractFund->fk_id_fefop_contract = $dataForm['fk_id_fefop_contract'];
			$contractFund->fk_id_budget_category_type = $component;
			$contractFund->fk_id_fefopfund = $fund;
			$contractFund->real_amount = 0;
			$contractFund->percent = 0;
		    }

		    $contractFund->contract_amount = App_General_String::toFloat( $amount );
		    $contractFund->save();
		    
		    $historyFund = sprintf( $history, $fund, $dataForm['fk_id_fefop_contract'], $amount );
		    $this->_sysAudit( $historyFund );
		    
		    if ( !array_key_exists( $component, $totalFundComponent ) )
			$totalFundComponent[$component] = 0;
		    
		    $totalFundComponent[$component] += $contractFund->contract_amount;
		    
		    $totalFund += $contractFund->contract_amount;
		}
	    }
	    
	    foreach ( $totalFundComponent as $component => $total ) {
		
		if ( array_key_exists( $component, $totalComponentsContract ) 
			&& $totalComponentsContract[$component] < $total ) {
		    
		    $this->_message->addMessage( 'Haree total husi komponente ba fundu sira ne\'e. La bele liu folin hira iha kontratu!', App_Message::ERROR );
		    return false;
		}
	    }
	    
	    if ( (string)$totalFund != (string)$totalContract ) {
		
		$currency = new Zend_Currency();
		
		$message = 'Total husi Fundu: %s keta la hanesan Total Kontratu: %s';
		$message = sprintf( $message, $currency->setValue( $totalFund )->toCurrency(), $currency->setValue( $totalContract )->toCurrency() );
			
		$this->_message->addMessage( $message, App_Message::ERROR );
		return false;
	    }
	    
	    // Calculate the redistribution of fund
	    $this->calcRealFund( $dataForm['fk_id_fefop_contract'] );
	    // Calculate the redistribution of additional costs
	    $this->calcAdditionalCost( $dataForm['fk_id_fefop_contract'] );
	    
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
     * @return boolean
     */
    public function saveAdditionalContract()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dataForm = $this->_data;
	    
	    $dbContractAdditional = App_Model_DbTable_Factory::get( 'FEFOPContractAdditional' );
	    
	    $totalCost = abs($this->getTotalExpenseContract( $dataForm['contract'], $dataForm['expense'] ));
	    $totalFunds = 0;
	    foreach ( $dataForm['funds'] as $fund )
		$totalFunds += App_General_String::toFloat ( $fund );
	    
	    if ( (string)$totalCost != (string)$totalFunds ) {
		
		$currency = new Zend_Currency();
		
		$message = 'Total husi Fundu: %s keta la hanesan Total Kustu Extra: %s';
		$message = sprintf( $message, $currency->setValue( $totalFunds )->toCurrency(), $currency->setValue( $totalCost )->toCurrency() );
			
		$this->_message->addMessage( $message, App_Message::ERROR );
		return false;
	    }
	    
	    foreach ( $dataForm['funds'] as $idFund => $fund ) {
		
		$where = array(
		    'fk_id_budget_category = ?' => $dataForm['expense'],
		    'fk_id_fefop_contract = ?'	=> $dataForm['contract'],
		    'fk_id_fefopfund = ?'	=> $idFund,
		);
		
		$row = $dbContractAdditional->fetchRow( $where );
		if ( empty( $row ) ) {
		    
		    $row = $dbContractAdditional->createRow();
		    $row->fk_id_budget_category = $dataForm['expense'];
		    $row->fk_id_fefop_contract = $dataForm['contract'];
		    $row->fk_id_fefopfund = $idFund;
		}
		
		$row->user_entered = 1;
		$row->amount = App_General_String::toFloat( $fund );
		$row->save();
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
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function totalComponentByContract( $id )
    {
	$dbBudgetCategoryContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	
	$select = $dbBudgetCategory->select()
				   ->from( 
					array( 'bc' => $dbBudgetCategory ),
					array( 'fk_id_budget_category_type' )
				    )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 'bcc' => $dbBudgetCategoryContract ),
					'bcc.fk_id_budget_category = bc.id_budget_category',
					array(
					    'total' => new Zend_Db_Expr( 'SUM(amount)' )
					)
				    )
				    ->where( 'bcc.fk_id_fefop_contract = ?', $id )
				    ->where( 'bcc.status = ?', 1 )
				    ->group( array( 'fk_id_budget_category_type' ) );
	
	return $dbBudgetCategory->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $idReceipt
     * @param array $transactionsId
     */
    protected function _removeTransactionsReceipt( $idReceipt, $transactionsId )
    {
	$dbFEFOPTransactionReceipt = App_Model_DbTable_Factory::get( 'FEFOPTransactionReceipt' );
	$dbFEFOPTransactionHistory = App_Model_DbTable_Factory::get( 'FEFOPTransactionHistory' );
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	
	$where = array( 'fk_id_fefop_receipt = ?' => $idReceipt );
	$rows = $dbFEFOPTransactionReceipt->fetchAll( $where );
	
	$idsInDataBase = array();
	foreach ( $rows as $row )
	    $idsInDataBase[] = $row->fk_id_fefop_transaction;
	
	$idsToDelete = array_diff( $idsInDataBase, $transactionsId );
	
	foreach ( $idsToDelete as $id ) {
	    
	    $where = array( 'fk_id_fefop_transaction = ?' => $id );
	    $dbFEFOPTransactionHistory->delete( $where );
	    $dbFEFOPTransactionReceipt->delete( $where );
	    
	    $whereTransaction = array( 'id_fefop_transaction = ?' => $id );
	    $row = $dbFEFOPTransaction->fetchRow( $whereTransaction );
	    $dbFEFOPTransaction->delete( $whereTransaction );
	    
	    $description = 'FEFOP - REMOVE LANSAMENTU FINANSEIRO: %s';
	    $description = sprintf( $description, print_r( $row->toArray(), true ) );
	    $this->_sysAudit( $description );
	}
    }
    
	    
    /**
     * 
     * @param array $data
     * @return int
     */
    protected function _saveTransaction( $data )
    {
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbFEFOPContractFund = App_Model_DbTable_Factory::get( 'FEFOPContractFund' );
	
	$where = array( 'fk_id_fefop_contract = ?' => $data['fk_id_fefop_contract'] );
	$contractFund = $dbFEFOPContractFund->fetchRow( $where );
	if ( empty( $contractFund ) ) {
	    
	    $message = sprintf( 'Kontratu %s seidauk iha folin husi Fundu.', Fefop_Model_Mapper_Contract::buildNumById( $data['fk_id_fefop_contract'] ) );
	    $this->_message->addMessage( $message, App_Message::ERROR );
	    throw new Exception( $message );
	}
	
	$mapperBudgetCategory = new Fefop_Model_Mapper_Expense();
	$expense = $mapperBudgetCategory->fetchRow( $data['fk_id_budget_category'] );
	
	$data['fk_id_budget_category_type'] = $expense->fk_id_budget_category_type;
	
	// If the value is already a transaction
	if ( !empty( $data['id_fefop_transaction'] ) ) {

	    $idTransaction = $data['id_fefop_transaction'];
	    $where = array( 'id_fefop_transaction = ?' => $idTransaction );
	    $row = $dbFEFOPTransaction->fetchRow( $where );
	}

	if ( empty( $row ) ) {

	    $row = $dbFEFOPTransaction->createRow();
	    $data['fk_id_sysuser'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	}
	
	$idContract = $data['fk_id_fefop_contract'];
	$idExpense = $data['fk_id_budget_category'];
	
	switch ( $data['fk_id_fefop_type_transaction'] ) {
	
	    case self::TYPE_CONTRACT:
		
		$mapperContract = new Fefop_Model_Mapper_Contract();
		$contract = $mapperContract->detail( $idContract );
		
		$statusForbidden = array(
		    Fefop_Model_Mapper_Status::CANCELLED,
		    Fefop_Model_Mapper_Status::CEASED,
		    Fefop_Model_Mapper_Status::REJECTED
		);
		
		if ( in_array( $contract->id_fefop_status, $statusForbidden ) ) {
		    
		    $message = sprintf( 'Kontratu %s ho status %s. Keta halo lansamentu', Fefop_Model_Mapper_Contract::buildNumById( $idContract ), $contract->status_description );
		    $this->_message->addMessage( $message, App_Message::ERROR );
		    throw new Exception( $message );
		}
		
		// Se custos acrescidos
		if ( Fefop_Model_Mapper_ExpenseType::ADDITIONALS == $data['fk_id_budget_category_type'] )
		    break;

		$totalContract = $mapperContract->getTotalContract( $idContract );
		// Get financial total without additional costs
		$totalFinancial = abs($this->getTotalContract( $idContract, true ));
		// Sum up current transaction
		$totalFinancial += abs($data['amount']) - (float)$row->amount;
		
		// Check if the total financial is over the contract amount
		if ( $totalFinancial > $totalContract ) {
		    
		    $currency = new Zend_Currency();
		    $amount =  $currency->setValue( $totalContract )->toCurrency();

		    $message = sprintf( 'Total Lansamentu hotu-hotu keta liu Total Kontratu hotu-hotu. %s', $amount );
		    $this->_message->addMessage( $message, App_Message::ERROR );
		    throw new Exception( $message );
		}

		$totalExpenseContract = $mapperContract->getTotalExpenseContract( $idContract, $idExpense );
		$totalExpenseFinancial = abs($this->getTotalExpenseContract( $idContract, $idExpense ) );
		
		// Sum up current transaction
		$totalExpenseFinancial += abs($data['amount']) - (float)$row->amount;;

		if ( $totalExpenseFinancial > $totalExpenseContract ) {
		    
		    $currency = new Zend_Currency();
		    $amount =  $currency->setValue( $totalExpenseContract )->toCurrency();

		    $message = sprintf( "Total Rubrika liu Total Rubrika iha Kontratu. %s", $amount );
		    $this->_message->addMessage( $message, App_Message::INFO );
		}

		break;

	    case self::TYPE_REIMBURSEMENT:

		$totalPaymentsExpense = abs($this->getTotalExpenseContract( $idContract, $idExpense, self::TYPE_CONTRACT ));
		$totalReibursementExpense = abs($this->getTotalExpenseContract( $idContract, $idExpense, self::TYPE_REIMBURSEMENT ));
		
		// Sum up current transaction
		$totalReibursementExpense += abs($data['amount']) - (float)$row->amount;;
		
		if ( $totalPaymentsExpense <= 0 ) {

		    $message = 'Keta halo lansamentu devolusan, seidauk iha lansamentu ba pagamentu ba Rúbrica nee';
		    $this->_message->addMessage( $message, App_Message::ERROR );
		    throw new Exception( $message );
		}

		if ( $totalReibursementExpense > $totalPaymentsExpense ) {

		    $currency = new Zend_Currency();
		    $amount =  $currency->setValue( $totalPaymentsExpense )->toCurrency();
		    
		    $message = 'Keta halo lansamentu devolusan, folin hira nee liu pagamentu hotu ba Rúbrica nee: %s';
		    $this->_message->addMessage( sprintf( $message, $amount ), App_Message::ERROR );
		    throw new Exception( $message );
		}

		break;
	}
	
	$row->setFromArray( $data );
	$row->save();
	
	// Calculate Additional cost proportionality
	$this->calcAdditionalCost( $data['fk_id_fefop_contract'] );
	// Try to change contract status
	$this->_tryUpdateStatusContract( $data['fk_id_fefop_contract'] );
	
	return $row;
    }
    
    /**
     * 
     * @param int $idContract
     * @return boolean
     */
    protected function _tryUpdateStatusContract( $idContract )
    {
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$contract = $mapperContract->detail( $idContract );
	
	if ( Fefop_Model_Mapper_Status::INITIAL != $contract->id_fefop_status )
	    return false;
	
	$mapperStatus = new Fefop_Model_Mapper_Status();
	
	$dataStatus = array(
	    'id_fefop_followup'	=> null,
	    'contract'		=> $idContract,
	    'status'		=> Fefop_Model_Mapper_Status::PROGRESS,
	    'description'	=> 'PRIMEIRO LANSAMENTU FINANSEIRU'
	);
	
	$mapperStatus->setData( $dataStatus )->save();
	
    }
    
    /**
     * 
     * @param Zend_Db_Table_Row $row
     */
    protected function _saveTransactionAuditing( $row )
    {
	$description = 'FEFOP - SALVA LANSAMENTU FINANSEIRO: %s';
	$description = sprintf( $description, print_r( $row->toArray(), true ) );
	$this->_sysAudit( $description );
    }
    
    /**
     * 
     * @param int $idTransaction
     * @param int $idReceipt
     * @return boolean
     */
    protected function _saveTransactionReceipt( $idTransaction, $idReceipt )
    {
	$dbFEFOPTransactionReceipt = App_Model_DbTable_Factory::get( 'FEFOPTransactionReceipt' );
	
	$where = array(
	    'fk_id_fefop_transaction = ?'  => $idTransaction,
	    'fk_id_fefop_receipt = ?'	   => $idReceipt
	);
	
	$row = $dbFEFOPTransactionReceipt->fetchRow( $where );
	if ( !empty( $row ) )
	    return true;
	
	$row = $dbFEFOPTransactionReceipt->createRow();
	$row->fk_id_fefop_transaction = $idTransaction;
	$row->fk_id_fefop_receipt = $idReceipt;
	return $row->save();
    }
    
    /**
     * 
     * @param int $contract
     * @return array
     */
    public function getExpensesAdditional( $contract )
    {
	$dbTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$select = $dbTransaction->select()
				->from( 
				    array( 't' => $dbTransaction ),
				    array( 
					'total' => new Zend_Db_Expr( 'SUM(t.amount)'),
					'fk_id_budget_category'
				    )
				 )
				->where( 't.fk_id_fefop_contract = ?', $contract )
				->where( 't.fk_id_budget_category_type = ?', Fefop_Model_Mapper_ExpenseType::ADDITIONALS )
				->group( array( 'fk_id_budget_category' ) );
	
	$selectTotal = clone $select;
	$selectTotal->where( 't.fk_id_fefop_type_transaction = ?', self::TYPE_CONTRACT );
	$rowsTotal = $dbTransaction->fetchAll( $selectTotal );
	$totals = array();
	foreach ( $rowsTotal as $row ) 
	    $totals[$row->fk_id_budget_category] = $row->total;
	
	$selectReimbursement = clone $select;
	$selectReimbursement->where( 't.fk_id_fefop_type_transaction = ?', self::TYPE_REIMBURSEMENT );
	$reimbursement = array();
	$rowsReimbursement = $dbTransaction->fetchAll( $selectReimbursement );
	foreach ( $rowsReimbursement as $row ) 
	    $reimbursement[$row->fk_id_budget_category] = $row->total;
	
	$data = array(
	    'expenses'  => array(),
	    'funds'	=> array()
	);
	
	$rows = $this->listAdditionalCostFund( $contract )->toArray();
	
	foreach ( $rows as $row ) {
	    
	    if ( !array_key_exists( $row['fk_id_budget_category'], $data['expenses'] ) ) {
		
		$reimbursementExpense = empty( $reimbursement[$row['fk_id_budget_category']] ) ? 0 : $reimbursement[$row['fk_id_budget_category']];
		$row['total'] = (float)(empty( $totals[$row['fk_id_budget_category']] ) ? 0 : $totals[$row['fk_id_budget_category']]) - $reimbursementExpense;
		
		$data['expenses'][$row['fk_id_budget_category']] = array(
		    'data'	    => $row,
		    'reimbursement' => $reimbursementExpense,
		    'funds'	    => array()
		);
	    }
	    
	    $data['expenses'][$row['fk_id_budget_category']]['funds'][$row['fk_id_fefopfund']] = $row;
	    $data['funds'][$row['fk_id_fefopfund']] = $row['name_fund'];
	}
	
	return $data;
    }
    
    /**
     * 
     * @param int $contract
     * @return Zend_Db_Table_Rowset
     */
    public function listAdditionalCostFund( $contract )
    {
	$dbContractAdditional = App_Model_DbTable_Factory::get( 'FEFOPContractAdditional' );
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbFEFOPFund = App_Model_DbTable_Factory::get( 'FEFOPFund' );
	
	$select = $dbContractAdditional->select()
					->from( array( 'ca' => $dbContractAdditional ) )
					->setIntegrityCheck( false )
					->join(
					    array( 'e' => $dbBudgetCategory ),
					    'e.id_budget_category = ca.fk_id_budget_category',
					    array( 'expense' => 'description' )
					)
					->join(
					    array( 'f' => $dbFEFOPFund ),
					    'f.id_fefopfund = ca.fk_id_fefopfund',
					    array( 'name_fund' )
					)
					->where( 'ca.fk_id_fefop_contract = ?', $contract )
					->order( array( 'expense' ) );
	
	return $dbContractAdditional->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $idTransaction
     * @param int $status
     * @return boolean
     */
    protected function _saveTransactionHistory( $idTransaction, $status )
    {
	$dbFEFOPTransactionHistory = App_Model_DbTable_Factory::get( 'FEFOPTransactionHistory' );
	$lastHistory = $this->getLastStatusTransaction( $idTransaction );
	
	if ( !empty( $lastHistory ) ) {
	    
	    if ( $lastHistory->fk_id_fefop_transaction_status == $status )
		return true;
	    
	    $lastHistory->status = 0;
	    $lastHistory->save();
	}
	
	$row = $dbFEFOPTransactionHistory->createRow();
	$row->fk_id_fefop_transaction_status = $status;
	$row->fk_id_fefop_transaction = $idTransaction;
	$row->status = 1;
	$row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	return $row->save();
     }
    
    /**
     * 
     * @param int $idTransaction
     * @return Zend_Db_Table_Row
     */
    public function getLastStatusTransaction( $idTransaction )
    {
	$dbFEFOPTransactionHistory = App_Model_DbTable_Factory::get( 'FEFOPTransactionHistory' );
	
	$select = $dbFEFOPTransactionHistory->select()
					    ->where( 'status = ?', 1 )
					    ->where( 'fk_id_fefop_transaction = ?', $idTransaction )
					    ->order( array( 'id_fefop_transaction_history DESC' ) )
					    ->limit( 1 );
	
	return $dbFEFOPTransactionHistory->fetchRow( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
	$dbFEFOPReceipt = App_Model_DbTable_Factory::get( 'FEFOPReceipt' );
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	$dbFEFOPTransactionReceipt = App_Model_DbTable_Factory::get( 'FEFOPTransactionReceipt' );
	$dbFEFOPTransactionHistory = App_Model_DbTable_Factory::get( 'FEFOPTransactionHistory' );
	$dbFEFOPTransactionStatus = App_Model_DbTable_Factory::get( 'FEFOPTransactionStatus' );
	$dbFEFOPTypeTransaction = App_Model_DbTable_Factory::get( 'FEFOPTypeTransaction' );
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbBudgetCategoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	
	$dbFEFOPContract = App_Model_DbTable_Factory::get('FEFOPContract');
	$dbFEFOPPrograms = App_Model_DbTable_Factory::get('FEFOPPrograms');
	$dbFEFOPModules = App_Model_DbTable_Factory::get('FEFOPModules');
	
	//Status Contract
	$dbFEFOPStatus 			= App_Model_DbTable_Factory::get('FEFOPStatus');
	$dbFEFOPContractStatus  = App_Model_DbTable_Factory::get('FEFOPContractStatus');
	
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$selectBeneficiary = $mapperContract->getSelectBeneficiary();
	$selectWhereBeneficiary = $dbFEFOPTransaction->select()
						    ->setIntegrityCheck( false )
						    ->from( array( 't' => new Zend_Db_Expr( '(' . $selectBeneficiary . ')' ) ) )
						    ->where( 'target = 1' );
	 
	$subSelectContract = $dbFEFOPStatus->select()
		->setIntegrityCheck(false)
		->from(
			$dbFEFOPStatus->__toString(),
			array('id_fefop_status', 'status_description')
		)
		->join(
			$dbFEFOPContractStatus->__toString(),
			'FEFOP_Status.id_fefop_status = FEFOP_Contract_Status.fk_id_fefop_status',
			array('fk_id_fefop_contract')
		)
		->where('FEFOP_Contract_Status.status = ?', 1)
		->order('FEFOP_Contract_Status.date_inserted DESC');
	 
	$adapter = App_Model_DbTable_Abstract::getDefaultAdapter();
	
	$selectStatusContract = $adapter->select()
		->from(
			array('s' => new Zend_Db_Expr('(' . $subSelectContract . ')')),
			array('*')
		)
		->group('s.fk_id_fefop_contract');
		
	$selectHistory = $dbFEFOPTransactionHistory->select()
		->from( 
			array('th' => $dbFEFOPTransactionHistory), 
			array('fk_id_fefop_transaction') 
		)
		->setIntegrityCheck( false )
		->join( 
			array('ts' => $dbFEFOPTransactionStatus), 
			'ts.id_fefop_transaction_status = th.fk_id_fefop_transaction_status' 
		)
		->where( 'th.status = ?', 1 )
		->order( 'id_fefop_transaction_history DESC' );
		
	$select = $dbFEFOPTransaction->select()
		->from( 
			array('t' => $dbFEFOPTransaction)
		)
		->setIntegrityCheck( false )
		->join( 
			array('tt' => $dbFEFOPTypeTransaction), 
			'tt.id_fefop_type_transaction = t.fk_id_fefop_type_transaction',
			array(
				'type_transaction' => 'description',
				'code_transaction' => 'acronym'
			)
		)
		->join( 
			array('ben' => new Zend_Db_Expr( '('. $selectWhereBeneficiary . ')'  )), 
			'ben.fk_id_fefop_contract = t.fk_id_fefop_contract',
			array(
			    'beneficiary' => new Zend_Db_Expr( 'GROUP_CONCAT(ben.name)' ),
			)
		)
		->joinLeft( 
			array('bc' => $dbBudgetCategory), 
			'bc.id_budget_category = t.fk_id_budget_category', 
			array(
				'expense' => 'description'
			) 
		)
		->joinLeft( 
			array('bct' => $dbBudgetCategoryType), 
			'bct.id_budget_category_type = t.fk_id_budget_category_type', 
			array(
				'component' => 'description'
			)
		)
		->joinLeft(
			array('tr' => $dbFEFOPTransactionReceipt), 
			'tr.fk_id_fefop_transaction = t.id_fefop_transaction', 
			array(
				'fk_id_fefop_receipt'
			)
		)
		->joinLeft( 
			array('trc' => $dbFEFOPReceipt), 
			'tr.fk_id_fefop_receipt = trc.id_fefop_receipt',
			array(
				'identifier',
				'fk_id_fefpenterprise'
			) 
		)
		->joinLeft( 
			array('et' => $dbEnterprise),
			'et.id_fefpenterprise = trc.fk_id_fefpenterprise',
			array('enterprise_name') 
		)
		->join( 
			array('ths' => new Zend_Db_Expr( '(' . $selectHistory . ')' )), 
			't.id_fefop_transaction = ths.fk_id_fefop_transaction', 
			array(
				'id_fefop_transaction_status',
				'status_description' => 'description'
			)
		)
		->join(
			$dbFEFOPContract->__toString(),
			'FEFOP_Contract.id_fefop_contract = t.fk_id_fefop_contract',
			array(
				'code_contract' => new Zend_Db_Expr("CONCAT(FEFOP_Contract.num_program, '-', FEFOP_Contract.num_module, '-', FEFOP_Contract.num_district, '-', FEFOP_Contract.num_year, '-', FEFOP_Contract.num_sequence)"),
			)
		)
		->join(
			array('s' => new Zend_Db_Expr('(' . $selectStatusContract . ')')),
			's.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract',
			array('status_contract' => 's.status_description')
		)
		->join(
			$dbFEFOPPrograms->__toString(),
			'FEFOP_Programs.id_fefop_programs = FEFOP_Contract.fk_id_fefop_programs',
			array(
				'program' => new Zend_Db_Expr("CONCAT(FEFOP_Programs.acronym, ' - ', FEFOP_Programs.description)"),
			)
		)
		->join(
			$dbFEFOPModules->__toString(),
			'FEFOP_Modules.id_fefop_modules = FEFOP_Contract.fk_id_fefop_modules',
			array(
				'module' => new Zend_Db_Expr("CONCAT(FEFOP_Modules.acronym, ' - ', FEFOP_Modules.description)"),
			)
		)
		->group( array( 'id_fefop_transaction' ) )
		->order( array( 'id_fefop_transaction DESC' ) );
		
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
	
	if ( !empty( $filters['fk_id_fefop_contract'] ) )
	    $select->where( 't.fk_id_fefop_contract = ?', $filters['fk_id_fefop_contract'] );
	
	if ( !empty( $filters['fk_id_fefop_type_transaction'] ) )
	    $select->where( 't.fk_id_fefop_type_transaction = ?', $filters['fk_id_fefop_type_transaction'] );
	
	if ( !empty( $filters['fk_id_budget_category'] ) )
	    $select->where( 't.fk_id_budget_category IN (?)', (array)$filters['fk_id_budget_category'] );
	
	if ( !empty( $filters['fk_id_budget_category_type'] ) )
	    $select->where( 't.fk_id_budget_category_type IN (?)', (array)$filters['fk_id_budget_category_type'] );
	
	if ( !empty( $filters['id_fefop_transaction_status'] ) )
	    $select->where( 'ths.id_fefop_transaction_status = ?', $filters['id_fefop_transaction_status'] );
	
	if ( !empty( $filters['identifier'] ) )
	    $select->where( 'trc.identifier = ?', $filters['identifier'] );
	
	if ( !empty( $filters['fk_id_fefpenterprise'] ) )
	    $select->where( 'trc.fk_id_fefpenterprise = ?', $filters['fk_id_fefpenterprise'] );
	
	if ( !empty( $filters['minimum_amount'] ) )
	    $select->where( 't.amount >= ?', $filters['minimum_amount'] );

	if ( !empty( $filters['maximum_amount'] ) )
	    $select->where( 't.amount <= ?', $filters['maximum_amount'] );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 't.date_reference >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 't.date_reference <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	
	if (!empty($filters['fk_id_fefop_status'])) {
		$select->where('s.id_fefop_status = ?', $filters['fk_id_fefop_status'] );
	}
	
	if (!empty($filters['fk_id_fefop_programs'])) {
		$select->where('FEFOP_Contract.fk_id_fefop_programs = ?', $filters['fk_id_fefop_programs'] );
	}
	
	if (!empty($filters['fk_id_fefop_modules'])) {
		$select->where('FEFOP_Contract.fk_id_fefop_modules = ?', $filters['fk_id_fefop_modules'] );
	}
	
	if (!empty($filters['num_district'])) {
		$select->where('FEFOP_Contract.num_district = ?', $filters['num_district'] );
	}
	
	if (!empty($filters['num_year'])) {
		$select->where('FEFOP_Contract.num_year = ?', $filters['num_year'] );
	}
	
	if (!empty($filters['num_sequence'])) {
		$select->where('FEFOP_Contract.num_sequence = ?', $filters['num_sequence'] );
	}
	
	if ( !empty( $filters['num_contract'] ) )
	    $select->having( 'code_contract LIKE ?', '%' . $filters['num_contract'] . '%' );
	
	$select->limit( 1000 );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id_contract
     */
    public function calcRealFund( $id_contract )
    {
	$sql = "UPDATE FEFOP_Contract_Fund fcf
		INNER JOIN (
		    SELECT
			t.fk_id_fefopfund,
			t.fk_id_budget_category_type,
			t.porcent,
			t.total,
			t.contract_amount,
			t.total_financial,
			ROUND( ( t.total_financial * t.porcent ) / 100, 2 ) total_real
		    FROM (
			SELECT 
			    ( 
				SELECT ABS(SUM(ft.amount * IF ( ft.operation = 'D', -1, 1))) 
				FROM FEFOP_Transaction ft 
				  INNER JOIN (
					 SELECT
					    th.fk_id_fefop_transaction,
					    ts.*
					  FROM FEFOP_Transaction_History AS th
					  INNER JOIN FEFOP_TransactionStatus AS ts
					      ON ts.id_fefop_transaction_status = th.fk_id_fefop_transaction_status
					  WHERE (th.status = 1)
					  ORDER BY id_fefop_transaction_history DESC
				  ) AS ts
				ON ft.id_fefop_transaction = ts.fk_id_fefop_transaction
				WHERE ft.fk_id_fefop_contract = cf.fk_id_fefop_contract 
					AND ft.fk_id_fefop_type_transaction <> 2
					AND ts.id_fefop_transaction_status <> 2
					AND ft.fk_id_budget_category_type = cf.fk_id_budget_category_type	
			    ) total_financial,
			    cf.fk_id_fefopfund,
			    cf.fk_id_budget_category_type,
			    cf1.total, 
			    cf.contract_amount,
			    ( cf.contract_amount  / ( cf1.total / 100 ) ) AS porcent
			FROM FEFOP_Contract_Fund cf
			INNER JOIN (
				SELECT SUM(contract_amount) total, fk_id_budget_category_type
				FROM FEFOP_Contract_Fund
				WHERE fk_id_fefop_contract = :id_contract
				GROUP BY fk_id_budget_category_type
			) cf1
			ON cf1.fk_id_budget_category_type = cf.fk_id_budget_category_type
			WHERE cf.fk_id_fefop_contract = :id_contract
			GROUP BY cf.fk_id_fefopfund, cf.fk_id_budget_category_type
		    ) t
		) scf ON
		scf.fk_id_fefopfund = fcf.fk_id_fefopfund
		AND scf.fk_id_budget_category_type = fcf.fk_id_budget_category_type
		SET fcf.real_amount = scf.total_real,
		    fcf.percent = scf.porcent
		WHERE fcf.fk_id_fefop_contract = :id_contract
		AND scf.total_real IS NOT NULL";
	
	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
	$stmt = $dbAdapter->prepare( $sql );
	$stmt->execute( array( ':id_contract' => $id_contract ) );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detail( $id )
    {
	$select = $this->getSelect();
	$select->where( 't.id_fefop_transaction = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function fetchReceipt( $id )
    {
	$dbFEFOPReceipt = App_Model_DbTable_Factory::get( 'FEFOPReceipt' );
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	
	$select = $dbFEFOPReceipt->select()
				 ->from( array( 'rc' => $dbFEFOPReceipt ) )
				 ->setIntegrityCheck( false )
				 ->join(
				    array( 'e' => $dbEnterprise ),
				    'e.id_fefpenterprise = rc.fk_id_fefpenterprise',
				    array( 'enterprise' => 'enterprise_name' )
				 )
				 ->where( 'rc.id_fefop_receipt = ?', $id );
	
	return $dbFEFOPReceipt->fetchRow( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listContractsReceipt( $id )
    {
	$select = $this->getSelect();
	$select->where( 'tr.fk_id_fefop_receipt = ?', $id );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return array
     */
    public function groupContractRecept( $id )
    {
	$contracts = $this->listContractsReceipt( $id );
	
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
	    $data[$numContract]['can-delete'] = $data[$numContract]['can-delete'] && $contract['id_fefop_transaction_status'] == self::ACTIVE;
	}
	
	return $data;
    }
    
    /**
     * 
     * @param int $id_contract
     * @return Zend_Db_Table_Rowset 
     */
    public function listExpenseTotalsByContract( $id_contract, $type = self::TYPE_CONTRACT )
    {
	$dbBudgetCategoryContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	$dbTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	
	$select = $dbBudgetCategoryContract->select()
					   ->from( 
						array( 't' => $dbTransaction ),
						array( 
						    'total' => new Zend_Db_Expr( 'ABS(SUM(t.amount))' ),
						    'fk_id_budget_category'
						)
					   )
					   ->setIntegrityCheck( false )
					   ->join(
						array( 'bc' => $dbBudgetCategoryContract ),
						'bc.fk_id_budget_category = t.fk_id_budget_category'
						. ' AND bc.fk_id_fefop_contract = t.fk_id_fefop_contract',
						array()
					    )
					    ->where( 't.fk_id_fefop_contract = ?', $id_contract )
					    ->where( 't.fk_id_fefop_type_transaction = ?', $type )
					    ->group( array( 't.fk_id_budget_category' ) );
	
	return $dbBudgetCategoryContract->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $contract
     * @param int $expense
     * @param int $type
     * @return float
     */
    public function getTotalExpenseContract( $contract, $expense, $type = false )
    {
	$dbTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	
	$select = $dbTransaction->select()
					   ->from( 
						array( 't' => $dbTransaction ),
						array( 
						    'total' => new Zend_Db_Expr( 'SUM( t.amount * IF(t.operation = "D", -1, 1) )' )
						)
					   )
					   ->setIntegrityCheck( false )
					    ->where( 't.fk_id_fefop_contract = ?', $contract )
					    ->where( 't.fk_id_budget_category = ?', $expense );
	
	if ( $type )
	    $select->where( 't.fk_id_fefop_type_transaction = ?', $type );
	
	return $dbTransaction->fetchRow( $select )->total;
    }
    
    /**
     * 
     * @param int $contract
     * @return float
     */
    public function getTotalContract( $contract, $no_additional = false )
    {
	$dbTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	
	$select = $dbTransaction->select()
					   ->from( 
						array( 't' => $dbTransaction ),
						array( 
						    'total' => new Zend_Db_Expr( 'SUM( t.amount * IF(t.operation = "D", -1, 1) )' )
						)
					   )
					   ->setIntegrityCheck( false )
					    ->where( 't.fk_id_fefop_contract = ?', $contract );
	
	// Remove additional costs
	if ( $no_additional ) {
	    
	    $dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	    
	    $select->join(
			array( 'bc' => $dbBudgetContract ),
			'bc.fk_id_budget_category = t.fk_id_budget_category '
			. 'AND bc.fk_id_fefop_contract = t.fk_id_fefop_contract',
			array()
		    );
	}
	
	return $dbTransaction->fetchRow( $select )->total;
    }
    
    /**
     * 
     * @param int $id_contract
     * @return Zend_Db_Table_Rowset
     */
    public function listExpenseTypeTotalsByContract( $id_contract, $type = self::TYPE_CONTRACT )
    {
	$dbBudgetCategoryContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	$dbTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	
	$select = $dbBudgetCategoryContract->select()
					   ->from( 
						array( 't' => $dbTransaction ),
						array( 
						    'total' => new Zend_Db_Expr( 'ABS(SUM(t.amount))' ),
						    'fk_id_budget_category_type'
						)
					   )
					   ->setIntegrityCheck( false )
					   ->join(
						array( 'bc' => $dbBudgetCategoryContract ),
						'bc.fk_id_budget_category = t.fk_id_budget_category'
						. ' AND bc.fk_id_fefop_contract = t.fk_id_fefop_contract',
						array()
					    )
					    ->where( 't.fk_id_fefop_contract = ?', $id_contract )
					    ->where( 't.fk_id_fefop_type_transaction = ?', $type )
					    ->group( array( 't.fk_id_budget_category_type' ) );
	
	return $dbBudgetCategoryContract->fetchAll( $select );
    }
    
    /**
     * 
     * @return array
     */
    public function calcTotals()
    {
	// Total preview to the contracts
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$selectContract = $mapperContract->getSelect();
	
	$statusContract = array( 
			    Fefop_Model_Mapper_Status::INITIAL, 
			    Fefop_Model_Mapper_Status::PROGRESS,
			    Fefop_Model_Mapper_Status::REVIEWED,
			    Fefop_Model_Mapper_Status::SEMI
			  );
	
	$selectContract->where( 'cs.id_fefop_status IN(?)', $statusContract );
	
	$selectTotal = $this->_dbTable->select()
				      ->setIntegrityCheck( false )
				      ->from( 
					    array( 't' => new Zend_Db_Expr( '(' . $selectContract . ')' ) ),
					    array( 'total' => new Zend_Db_Expr( 'SUM(t.total)' ) )
				      );
	
	// Current total
	$dbTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbTransactionStatus = App_Model_DbTable_Factory::get( 'FEFOPTransactionStatus' );
	$dbTransactionHistory = App_Model_DbTable_Factory::get( 'FEFOPTransactionHistory' );
	
	$selectStatus = $dbTransactionStatus->select()
				 ->from( array( 'th' => $dbTransactionHistory ), array( 'fk_id_fefop_transaction' ) )
				 ->setIntegrityCheck( false )
				 ->join(
				    array( 'ts' => $dbTransactionStatus ),
				    'ts.id_fefop_transaction_status = th.fk_id_fefop_transaction_status'
				 )
				 ->where( 'th.status = ?', 1 )
				 ->order( 'id_fefop_transaction_history DESC' );
	
	$selectCurrentTotal = $dbTransaction->select()
					    ->setIntegrityCheck( false )
					    ->from( 
						array( 't' => $dbTransaction ),
						array( 'total' => new Zend_Db_Expr( 'SUM( t.amount * IF(t.operation = "D", -1, 1) )' ) )
					    )
					    ->join(
						array( 'ts' => new Zend_Db_Expr( '(' . $selectStatus . ')' ) ),
						't.id_fefop_transaction = ts.fk_id_fefop_transaction',
						array( 'id_fefop_transaction_status' )
					     )
					     ->where( 'ts.id_fefop_transaction_status <> ?', self::INACTIVE );
	
	$selectTotalPayment = clone $selectCurrentTotal;
	$selectTotalPayment->where( 't.fk_id_fefop_type_transaction = ?', self::TYPE_CONTRACT );
	
	$selectTotalReimbursement = clone $selectCurrentTotal;
	$selectTotalReimbursement ->where( 't.fk_id_fefop_type_transaction = ?', self::TYPE_REIMBURSEMENT );
	
	$totalContract = (float)$this->_dbTable->fetchRow( $selectTotal )->total;
	$totalPayment = (float)$this->_dbTable->fetchRow( $selectTotalPayment )->total;
	$totalReimbursement = (float)$this->_dbTable->fetchRow( $selectTotalReimbursement )->total;
	
	$totals = array(
	    'total_contract'	    => $totalContract,
	    'total_payments'	    => abs($totalPayment),
	    'total_reimbursement'   => abs($totalReimbursement),
	    'total_real'	    => abs($totalPayment) - $totalReimbursement
	);
	
	return $totals;
    }
    
    /**
     * 
     * @param int $id
     * @return array
     */
    public function listFundsContract( $id )
    {
	$dbFund = App_Model_DbTable_Factory::get( 'FEFOPFund' );
	$dbContractFund = App_Model_DbTable_Factory::get( 'FEFOPContractFund' );
	$dbBudgetCategoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	
	$select = $dbFund->select()
			 ->from( array( 'f' => $dbFund ) )
			 ->setIntegrityCheck( false )
			 ->join(
			    array( 'cf' => $dbContractFund ),
			    'cf.fk_id_fefopfund = f.id_fefopfund',
			    array( 'contract_amount', 'real_amount', 'fk_id_budget_category_type' )
			 )
			 ->join(
			    array( 'bc' => $dbBudgetCategoryType ),
			    'bc.id_budget_category_type = cf.fk_id_budget_category_type',
			    array( 'component' => 'description' )
			  )
			  ->where( 'cf.fk_id_fefop_contract = ?', $id )
			  ->order( array( 'name_fund' ) );
	
	$rows = $dbFund->fetchAll( $select );
	$data = array(
	    'funds'  => array(),
	    'totals' => array(
		'totals' => array(
		    'contract_amount'	=> 0,
		    'real_amount'	=> 0,
		),
		'components' => array()
	    )
	);
	
	foreach ( $rows as $row ) {
	    
	    if ( !array_key_exists( $row->id_fefopfund, $data['funds'] ) )
		$data['funds'][$row->id_fefopfund] = array();
	    
	    $data['funds'][$row->id_fefopfund][$row->fk_id_budget_category_type] = $row->toArray();
	    $data['totals']['totals']['contract_amount'] += $row['contract_amount'];
	    $data['totals']['totals']['real_amount'] += $row['real_amount'];
	    
	    if ( !array_key_exists( $row->id_fefopfund, $data['totals'] ) ) {
		
		$data['totals'][$row->id_fefopfund] = array(
		    'contract_amount'	=>  0,
		    'real_amount'	=>  0,
		);
	    }
	    
	    $data['totals'][$row->id_fefopfund]['contract_amount'] += $row['contract_amount'];
	    $data['totals'][$row->id_fefopfund]['real_amount'] += $row['real_amount'];
	    
	    if ( !array_key_exists( $row->fk_id_budget_category_type, $data['totals']['components'] ) ) {
		
		$data['totals']['components'][$row->fk_id_budget_category_type] = array(
		    'contract_amount'	=>  0,
		    'real_amount'	=>  0,
		);
	    }
	    
	    $data['totals']['components'][$row->fk_id_budget_category_type]['contract_amount'] += $row['contract_amount'];
	    $data['totals']['components'][$row->fk_id_budget_category_type]['real_amount'] += $row['real_amount'];
	}
	
	return $data;
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
	    'fk_id_sysform'	    => Fefop_Form_Financial::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}