<?php

/**
 * 
 */
class Fefop_BankStatementController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_BankStatement
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_BankStatement();
	
	$stepBreadCrumb = array(
	    'label' => 'Transasaun iha Banku',
	    'url'   => 'fefop/bank-statement'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Transasaun iha Banku' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$formSearch = new Fefop_Form_BankStatementSearch();
	$formSearch->setAction( $this->_helper->url( 'list-statement' ) );
	$this->view->formSearch = $formSearch;
	
	$today = new Zend_Date();
	$today->setDay(1)->subDay(1);
	
	$this->view->lastMonth = $today;
    }
    
    /**
     * 
     */
    public function listStatementAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$rows = $this->_mapper->listByFilters( $this->_getAllParams() );
	
	$this->view->rows = $rows;
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
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Fefop_Form_BankStatement();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function newStatementAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$formStatement = new Fefop_Form_BankStatement();
	$formStatement->setAction( $this->_helper->url( 'save' ) );
	
	$this->view->form = $formStatement;
    }
    
    /**
     * 
     */
    public function editStatementAction()
    {
	$this->_helper->layout()->disableLayout();
	$id = $this->_getParam( 'id' );
	
	$formStatement = new Fefop_Form_BankStatement();
	$formStatement->setAction( $this->_helper->url( 'save' ) );

	$transaction = $this->_mapper->detail( $id );
	$data = $transaction->toArray();
	
	$data['date_statement'] = $this->view->date( $data['date_statement'] );
	$formStatement->populate( $data );
	
	//$formStatement->getElement( 'fk_id_fefop_type_transaction' )->setAttrib( 'readonly', true );
	
	$dbTypeTransaction = App_Model_DbTable_Factory::get( 'FEFOPTypeTransaction' );
	$rows = $dbTypeTransaction->fetchAll( array( 'type = ?' => 'F' ) );
	
	$contractTransactions = array();
	foreach ( $rows as $row )
	    $contractTransactions[] = $row->id_fefop_type_transaction;
	
	// If the statement is related with contracts
	if ( in_array( $data['fk_id_fefop_type_transaction'], $contractTransactions ) ) {
	    
	    // Contracts
	    $contracts = $this->_mapper->groupContracts( $id );
	    $this->view->contracts = $contracts;
	    
	    $canEdit = true;
	    foreach ( $contracts as $contract ) {
		if ( !$contract['can-delete'] ) {
		    $canEdit = false;
		    break;
		}
	    }
	    
	    if ( !$canEdit ) {
		
		$formStatement->getElement( 'fk_id_fefop_type_transaction' )->setAttrib( 'readonly', true );
		$formStatement->getElement( 'operation' )->setAttrib( 'readonly', true );
	    }
	    
	    $formStatement->getElement( 'amount' )->setAttrib( 'readonly', true );
	}
	
	$this->view->form = $formStatement;
	$this->_helper->viewRenderer->setRender( 'new-statement' );
    }
    
    /**
     * 
     */
    public function searchContractAction()
    {
	$this->_forward( 'search-contract', 'financial' );
    }
    
    /**
     * 
     */
    public function listContractAction()
    {
	$this->_forward( 'list-contract', 'financial' );
    }
    
     /**
     * 
     */
    public function fetchContractAction()
    {
	$this->_forward( 'fetch-contract', 'financial' );
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
	    $optExpenses[$expense->id_budget_category] = $cont++ . ' - ' . $expense->description;
	
	
	$mapperExpense = new Fefop_Model_Mapper_Expense();
	$expensesAdditional = $mapperExpense->listAll( Fefop_Model_Mapper_ExpenseType::ADDITIONALS );
	
	foreach ( $expensesAdditional as $expense )
	    $optExpenses[$expense->id_budget_category] = $expense->description;
	
	$this->view->optionsBudgetCategory = $optExpenses;
    }
}