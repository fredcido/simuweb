<?php

/**
 * 
 */
class Fefop_FinancialController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_Financial
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_Financial();
	
	$stepBreadCrumb = array(
	    'label' => 'Kontrole Finanseru',
	    'url'   => 'fefop/financial'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Kontrole Financeiru' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$formSearch = new Fefop_Form_FinancialSearch();
	$formSearch->setAction( $this->_helper->url( 'list-transaction' ) );
	$this->view->formSearch = $formSearch;
    }
    
    /**
     * 
     */
    public function listTransactionAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$rows = $this->_mapper->listByFilters( $this->_getAllParams() );
	
	$this->view->rows = $rows;
    }
    
    /**
     * 
     */
    public function fetchTransactionContractAction()
    {
	$this->_helper->viewRenderer->setRender( 'list-transaction' );
	$this->_helper->layout()->disableLayout();
	
	$filters = array( 'fk_id_fefop_contract' => $this->_getParam( 'id' ) );
	
	$rows = $this->_mapper->listByFilters( $filters );
	$this->view->rows = $rows;
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Fefop_Form_BeneficiaryBlacklist();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function newTransactionAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$formTransaction = new Fefop_Form_Transaction();
	$formTransaction->setAction( $this->_helper->url( 'save-transaction' ) );
	
	$this->view->form = $formTransaction;
    }

    /**
     * 
     */
    public function saveTransactionAction()
    {
	$form = new Fefop_Form_Transaction();
	
	if ( $this->getRequest()->isPost() ) {

	    if ( $form->isValid( $this->getRequest()->getPost() ) ) {
	    	
		$this->_mapper->setData( $form->getValues() );
		$return = $this->_mapper->saveTransaction();	
		$message = $this->_mapper->getMessage()->toArray();

		$result = array(
		    'status'	=> (bool) $return,
		    'id'		=> $return,
		    'description'	=> $message,
		    'data'		=> $form->getValues(),
		    'fields'	=> $this->_mapper->getFieldsError()
		);

		$this->_helper->json( $result );
		    
	    } else {
		
		$message = new App_Message();
		$message->addMessage( $this->_config->messages->warning, App_Message::WARNING );

		$result = array(
		    'status'	    => false,
		    'description'   => $message->toArray(),
		    'errors'	    => $form->getMessages()
		);
	    }
	}
    }
    
    /**
     * 
     */
    public function searchEnterpriseAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'enterprise', 'register' );
    }
    
    /**
     * 
     */
    public function searchEnterpriseForwardAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'search-enterprise', 'enterprise', 'register' );
    }
    
     /**
     * 
     */
    public function fetchEnterpriseAction()
    {
	$mapperEnterpise = new Register_Model_Mapper_Enterprise();
	$enterprise = $mapperEnterpise->fetchRow( $this->_getParam( 'id' ) );
	
	$data = array();
	$data['fk_id_fefpenterprise'] = $enterprise['id_fefpenterprise'];
	$data['enterprise'] = $enterprise['enterprise_name'];
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function searchContractAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$formSearchContract = new Fefop_Form_ContractSearch();
	$formSearchContract->setAction( $this->_helper->url( 'list-contract' ) );
	
	$this->view->form = $formSearchContract;
    }
    
    /**
     * 
     */
    public function listContractAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$this->view->rows = $mapperContract->listByFilters( $this->_getAllParams() );
	$this->view->listAjax = $this->_getParam( 'list-ajax' );
    }
    
     /**
     * 
     */
    public function fetchContractAction()
    {
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$row = $mapperContract->detail( $this->_getParam( 'id' ) );
	
	$data = $row->toArray();
	$data['num_contract'] = Fefop_Model_Mapper_Contract::buildNumRow( $row );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function addContractAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$row = $this->_getParam( 'row' );
	$this->view->row = $row;
    }
    
    /**
     * 
     */
    public function addExpenseAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$idContract = $this->_getParam( 'id_contract' );
	$row = $this->_getParam( 'row' );
	
	if ( !empty( $row ) ) {
	    
	    $this->view->row = $row;
	    $idContract = $row->fk_id_fefop_contract;
	}
	    
	$this->view->idContract = $idContract;
	
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$expenses = $mapperContract->listExpensesContract( $idContract );
	
	$optExpenses = array( '' => '' );
	$cont = 'A';
	foreach ( $expenses as $expense )
	    $optExpenses[$expense->id_budget_category] = $cont++ . ' - ' . $expense->description . ' - ' . $this->view->currency( $expense->amount );
	
	$this->view->optionsBudgetCategory = $optExpenses;
    }
    
    /**
     * 
     */
    public function calcTotalsAction()
    {
	$totals = $this->_mapper->calcTotals();
	
	foreach ( $totals as $t => $v )
	    $totals[$t] = $this->view->currency( $v );
	
	$this->_helper->json( $totals );
    }
    
    /**
     * 
     */
    public function editReceiptAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$formTransaction = new Fefop_Form_Transaction();
	$formTransaction->setAction( $this->_helper->url( 'save-transaction' ) );
	
	$id = $this->_getParam( 'id');
	
	$receipt = $this->_mapper->fetchReceipt( $id );
	if ( empty( $receipt ) ) 
	    $this->_helper->redirector->goToSimple( 'new-transaction', 'financial' );
	
	$data = $receipt->toArray();
	$data['date_purchased'] = $this->view->date( $data['date_purchased'] );
	
	// Contracts
	$contracts = $this->_mapper->groupContractRecept( $id );
	$this->view->contracts = $contracts;
	
	$formTransaction->populate( $data );
	
	$this->view->form = $formTransaction;
	$this->view->receipt = $receipt;
	$this->_helper->viewRenderer->setRender( 'new-transaction' );
    }
    
    /**
     * 
     */
    public function contractControlAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$contract = $mapperContract->detail( $this->_getParam( 'id' ) );
	
	$this->view->contract = $contract;
    }
    
    /**
     * 
     */
    public function fundContractAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$formFundContract = new Fefop_Form_ContractFund();
	$formFundContract->setAction( $this->_helper->url( 'save-fund-contract' ) );
	$formFundContract->getElement( 'fk_id_fefop_contract' )->setValue( $id );

	// List all Funds
	$mapperFunds = new Fefop_Model_Mapper_Fund();
	$funds = $mapperFunds->fetchAll( array(), array( 'type' ) );
	
	// Fetch the totals by fund
	$reportFefop = new Report_Model_Mapper_Fefop();
	$filterReport = array(
	    'year' => date( 'Y' )
	);
	
	$fundsTotals = $reportFefop->donorContractCostReport();
	$donorsTotals = $fundsTotals['item']['donor'];
	
	$donorTotalsViews = array();
	foreach ( $donorsTotals as $typeDonor )
	    foreach ( $typeDonor as $idDonor => $donor )
		$donorTotalsViews[$idDonor] = $donor['balance'];
	
	$this->view->donor_totals = $donorTotalsViews;
	
	// List the funds already registered to the contract
	$fundsContract = $this->_mapper->listFundsContract( $id );
	
	// List all the components related to the contract
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$components = $mapperContract->listComponentsContract( $id );
	
	// Reimbursement by Component
	$reimbursement = $this->_mapper->listExpenseTypeTotalsByContract( $id, Fefop_Model_Mapper_Financial::TYPE_REIMBURSEMENT );
	
	$dataReimbursement = array();
	foreach( $reimbursement as $row )
	    $dataReimbursement[$row->fk_id_budget_category_type] = $row->total;
	
	$this->view->reimbursement = $dataReimbursement;
	$this->view->components = $components;
	$this->view->funds = $funds;
	$this->view->funds_contract = $fundsContract;
	$this->view->form = $formFundContract;
    }
    
    /**
     * 
     */
    public function saveFundContractAction()
    {
	$data = $this->_getAllParams();
	
	$return = $this->_mapper->setData( $data )->saveFundContract();	
	$message = $this->_mapper->getMessage()->toArray();

	$result = array(
	    'status'	    => (bool) $return,
	    'id'	    => $return,
	    'description'   => $message,
	    'data'	    => $data,
	    'fields'	    => $this->_mapper->getFieldsError()
	);

	$this->_helper->json( $result );
    }
    
     /**
     * 
     */
    public function saveAdditionalContractAction()
    {
	$data = $this->_getAllParams();
	
	$return = $this->_mapper->setData( $data )->saveAdditionalContract();	
	$message = $this->_mapper->getMessage()->toArray();

	$result = array(
	    'status'	    => (bool)$return,
	    'id'	    => $return,
	    'description'   => $message,
	    'data'	    => $data,
	    'fields'	    => $this->_mapper->getFieldsError()
	);

	$this->_helper->json( $result );
    }
    
    /**
     * 
     */
    public function expenseContractAction()
    {
	$this->_helper->layout()->disableLayout();
	$id = $this->_getParam( 'id' );
	
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$expenses = $mapperContract->listExpensesContract( $id );
	
	$rows = $this->_mapper->listExpenseTotalsByContract( $id );
	$expenseTotals = array();
	foreach ( $rows as $row )
	    $expenseTotals[$row->fk_id_budget_category] = $row->total;
	
	$rowsReimbursement = $this->_mapper->listExpenseTotalsByContract( $id, Fefop_Model_Mapper_Financial::TYPE_REIMBURSEMENT );
	$reimbursementTotals = array();
	foreach ( $rowsReimbursement as $row )
	    $reimbursementTotals[$row->fk_id_budget_category] = $row->total;
	
	$this->view->expense_totals = $expenseTotals;
	$this->view->reimbursement_total = $reimbursementTotals;
	$this->view->expenses = $expenses;
    }
    
    /**
     * 
     */
    public function additionalCostsAction()
    {
	$this->_helper->layout()->disableLayout();
	$id = $this->_getParam( 'id' );
	
	$additional = $this->_mapper->getExpensesAdditional( $id );
	$this->view->costs = $additional;
    }
    
    /**
     * 
     */
    public function transactionContractAction()
    {
	$this->_helper->layout()->disableLayout();
    }
    
    /**
     * 
     */
    public function newTransactionContractAction()
    {
	$this->_helper->layout()->disableLayout();
	$contract = $this->_getParam( 'id' );
	
	$formTransaction = new Fefop_Form_TransactionContract();
	$formTransaction->setAction( $this->_helper->url( 'save-transaction-contract' ) );
	$formTransaction->getElement( 'fk_id_fefop_contract' )->setValue( $contract );
	
	$this->_populateTypeBudgetForm( $formTransaction, $contract );
	
	$this->view->contract = $contract;
	$this->view->form = $formTransaction;
    }
    
    /**
     * 
     */
    public function editTransactionContractAction()
    {
	$this->_helper->layout()->disableLayout();
	$id = $this->_getParam( 'id' );
	
	$formTransaction = new Fefop_Form_TransactionContract();
	$formTransaction->setAction( $this->_helper->url( 'save-transaction-contract' ) );

	$transaction = $this->_mapper->detail( $id );
	$data = $transaction->toArray();
	
	$data['date_reference'] = $this->view->date( $data['date_reference'] );
	$data['total_contract'] = $this->getAmountExpenseContract( $data['fk_id_fefop_contract'], $data['fk_id_budget_category'] );
	
	$rows = $this->loadBudgetCategory( $data['fk_id_budget_category_type'], $data['fk_id_fefop_contract'] );
	$optBudgetCategory[''] = '';
	foreach ( $rows as $row )
	    $optBudgetCategory[$row->id_budget_category] = $row->description;
	
	$this->_populateTypeBudgetForm( $formTransaction, $data['fk_id_fefop_contract'] );
	
	$formTransaction->getElement( 'fk_id_budget_category' )->addMultiOptions( $optBudgetCategory );
	$formTransaction->populate( $data );
	
	$this->view->form = $formTransaction;
	$this->view->contract = $data['fk_id_fefop_contract'];
	$this->_helper->viewRenderer->setRender( 'new-transaction-contract' );
    }
    
    /**
     * 
     * @param Zend_Form $form
     * @param int $contract
     */
    protected function _populateTypeBudgetForm( $form, $contract )
    {
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$rows = $mapperContract->listComponentsContract( $contract );
	
	$optBudgetCategoryType[''] = '';
	foreach ( $rows as $row )
	    $optBudgetCategoryType[$row['id_budget_category_type']] = $row['description'];
	
	$mapperExpenseType = new Fefop_Model_Mapper_ExpenseType();
	$additionalCost = $mapperExpenseType->fetchRow( Fefop_Model_Mapper_ExpenseType::ADDITIONALS );
	
	$optBudgetCategoryType[Fefop_Model_Mapper_ExpenseType::ADDITIONALS] = $additionalCost->description;
	
	$form->getElement( 'fk_id_budget_category_type' )->addMultiOptions( $optBudgetCategoryType );
    }
    
    /**
     * 
     */
    public function saveTransactionContractAction()
    {
	$form = new Fefop_Form_TransactionContract();
	
	if ( $this->getRequest()->isPost() ) {

	    if ( $form->isValid( $this->getRequest()->getPost() ) ) {
	    	
		$this->_mapper->setData( $form->getValues() );
		$return = $this->_mapper->saveTransactionContract();	
		$message = $this->_mapper->getMessage()->toArray();

		$result = array(
		    'status'	=> (bool) $return,
		    'id'		=> $return,
		    'description'	=> $message,
		    'data'		=> $form->getValues(),
		    'fields'	=> $this->_mapper->getFieldsError()
		);

		$this->_helper->json( $result );
		    
	    } else {
		
		$message = new App_Message();
		$message->addMessage( $this->_config->messages->warning, App_Message::WARNING );

		$result = array(
		    'status'	    => false,
		    'description'   => $message->toArray(),
		    'errors'	    => $form->getMessages()
		);
		
		$this->_helper->json( $result );
	    }
	}
    }
    
    /**
     * 
     */
    public function loadBudgetCategoryAction()
    {
	$component = $this->_getParam( 'id' );
	$contract = $this->_getParam( 'contract' );
	
	$rows = $this->loadBudgetCategory( $component, $contract );
	
	$data = array();
	foreach ( $rows as $row )
	    $data[] = array( 'id' => $row->id_budget_category, 'name' => $row->description );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     * @param int $component
     * @param int $contract
     * @return Zend_Db_Table_Rowset
     */
    public function loadBudgetCategory( $component, $contract )
    {
	if ( Fefop_Model_Mapper_ExpenseType::ADDITIONALS == $component ) {
	    
	    $mapperExpense = new Fefop_Model_Mapper_Expense();
	    $rows = $mapperExpense->listAll( $component );
	    
	} else {
	    
	    $mapperContract = new Fefop_Model_Mapper_Contract();
	    $rows = $mapperContract->listExpensesContract( $contract, $component );
	}
	
	return $rows;
    }
    
    /**
     * 
     */
    public function contractAmountCategoryAction()
    {
	$contract = $this->_getParam( 'contract' );
	$category = $this->_getParam( 'category' );
	
	$json = array( 'amount' => $this->getAmountExpenseContract( $contract, $category ) );
	
	$this->_helper->json( $json );
    }
    
    /**
     * 
     * @param int $contract
     * @param int $category
     * @return float
     */
    public function getAmountExpenseContract( $contract, $category )
    {
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$expenses = $mapperContract->listExpensesContract( $contract );
	
	$total = 0;
	
	foreach ( $expenses as $expense )
	    if ( $expense->id_budget_category == $category )
		$total = (float)$expense->amount;
	    
	return $total;
    }
}