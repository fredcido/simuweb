<?php

/**
 * 
 */
class Fefop_BankConsolidateController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_BankConsolidate
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_BankConsolidate();
	
	$stepBreadCrumb = array(
	    'label' => 'Konsolida Banku',
	    'url'   => 'fefop/bank-consolidate'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Konsolida Banku' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$form = $this->_getForm( $this->_helper->url( 'save' ) );
	
	// Get items to consolidate
	$itemsToConsolidate = $this->_mapper->listExpensesToConsolidate();
	
	$this->view->items = $itemsToConsolidate;
	$this->view->form = $form;
	
	$today = new Zend_Date();
	$today->setDay(1)->subDay(1);
	
	$this->view->lastMonth = $today;
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Fefop_Form_BankConsolidate();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function listConsolidateAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$formSearch = new Fefop_Form_BankConsolidateSearch();
	$formSearch->setAction( $this->_helper->url( 'search-consolidate' ) );
	$this->view->formSearch = $formSearch;
    }
    
    /**
     * 
     */
    public function searchConsolidateAction()
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
	$this->_forward( 'calc-totals', 'bank-statement' );
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
     * @throws Exception
     */
    public function redirectDetailSessionAction()
    {
	$items = $this->_session->bank_to_consolidate;
	$id = $this->_getParam( 'id' );
	
	if ( empty( $items[$id] ) )
	    throw new Exception( 'Not item found to be consolidated' );
	
	$item = $items[$id];
	foreach ( $item as $k => $v )
	    $this->_setParam ( $k, $v );
	
	$bankStatements = array();
	$financialTransactions = array();
	
	if ( !empty( $item['bank_rows'] ) )
	    $bankStatements = $this->_mapper->listBankStatementsIn(  $item['bank_rows'] );
	
	if ( !empty( $item['financial_rows'] ) )
	    $financialTransactions = $this->_mapper->listFinancialTransactionsIn(  $item['financial_rows'] );
	
	$this->_setParam( 'bankStatements', $bankStatements );
	$this->_setParam( 'financialTransactions', $financialTransactions );
	
	$this->_forward( 'detail-consolidate' );
    }
    
    public function detailConsolidateRowAction()
    {
	$id = $this->_getParam( 'id' );
	
	$bankStatements = $this->_mapper->listStatementsBankConsolidated(  $id );
	$financialTransactions = $this->_mapper->listFinancialConsolidated(  $id );
	
	$firstTransaction = $financialTransactions->current();
	
	$this->_setParam( 'contract', $firstTransaction->code_contract );
	$this->_setParam( 'component', $firstTransaction->component );
	$this->_setParam( 'expense', $firstTransaction->expense );
	
	$totalBank = 0;
	$totalFinancial = 0;
	
	foreach ( $bankStatements as $bank )
	    $totalBank += $bank->amount_contract;
	
	foreach ( $financialTransactions as $financial )
	    $totalFinancial += $financial->amount;
	
	$this->_setParam( 'bank_amount', $totalBank );
	$this->_setParam( 'total_financial', $totalFinancial );
	$this->_setParam( 'bankStatements', $bankStatements );
	$this->_setParam( 'financialTransactions', $financialTransactions );
	
	$this->_forward( 'detail-consolidate' );
    }
    
    /**
     * 
     */
    public function detailConsolidateAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$params = $this->_getAllParams();
	$this->view->params = $params;
    }
    
    /**
     * 
     */
    public function removeAction()
    {
	$id = $this->_getParam( 'id' );
	$result = $this->_mapper->removeConsolidate( $id );
	
	$this->_helper->json( array( 'status' => (bool)$result ) );
    }
}