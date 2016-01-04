<?php

/**
 * 
 */
class External_PceController extends App_Controller_Default
{
    
    /**
     *
     * @var External_Model_Mapper_Pce
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new External_Model_Mapper_Pce();
    }
    
    /**
     * 
     */
    public function indexAction()
    {
	$module = $this->_getParam( 'module' );
	
	if ( empty( $module ) )
	    $this->_helper->redirector->goToSimple( 'index', 'index', 'external' );
	
	$client = $this->view->session->client->id_perdata;
	$businessPlan = $this->_mapper->fetchBusinessPlanByClient( $client, $module );
	    
	if ( $businessPlan ) {
	    
	    $this->view->businessPlan = $businessPlan;
	    $this->view->date_ini = $this->view->date( $businessPlan->date_inserted );
	    $this->view->hasBudgetCategory = $this->_mapper->hasBudgetCategory( $businessPlan->id_businessplan );
	    
	    if ( !$businessPlan->submitted )
		$this->view->revision = $this->_mapper->getLastRevision( $businessPlan->id_businessplan );
	    
	    if ( !empty( $businessPlan->business_group ) )
		$this->view->is_group = true;
	    
	    if ( !empty( $businessPlan->fk_id_fefop_contract ) ) {
		
		$mapperContract = new Fefop_Model_Mapper_Contract();
		$this->view->contract = $mapperContract->detail( $businessPlan->fk_id_fefop_contract );
	    }
	    
	} else {
	    
	    $this->view->date_ini = Zend_Date::now()->toString( 'dd/MM/yyyy' );
	}
	
	$this->view->can_create = $this->_mapper->canCreateBusinessPlan( $client, $module );
	$this->view->module = $module;
    }
    
    /**
     * 
     */
    public function informationAction()
    {
	$form = $this->_getForm( $this->_helper->url( 'save' ) );
	
	$module = $this->_getParam( 'module' );
	$id = $this->_getParam( 'id' );
	
	if ( empty( $id )  ) {
	    
	    $client = $this->view->session->client->id_perdata;
	    $businessPlan = $this->_mapper->fetchBusinessPlanByClient( $client, $module );
	    
	} else
	    $businessPlan = $this->_mapper->fetchBusinessPlan ( $id );
	
	if ( empty( $businessPlan ) ) {
	    
	    $data = array(
		'fk_id_perdata' => $this->view->session->client->id_perdata,
		'module'	=> $this->_getParam( 'module' )
	    );
	    
	} else {
	    
	    $data = $businessPlan->toArray();
	    
	    $mapperIsicTimor = new Register_Model_Mapper_IsicTimor();
	    $classTimor = $mapperIsicTimor->listClassByDisivion( $data['fk_id_isicdivision'] );

	    $classes = array();
	    if ( !empty( $classTimor[$data['fk_id_isicdivision']]['classes'] ) )
		$classes = $classTimor[$data['fk_id_isicdivision']]['classes'];

	    $opt = array( '' => '' );
	    foreach ( $classes as $class )
		$opt[$class->id_isicclasstimor] = $class->name_classtimor;
	    
	    $form->getElement('fk_id_isicclasstimor')->addMultiOptions( $opt );
	    
	    $fieldsReadonly = array( 'fk_id_adddistrict' );
	    foreach ( $fieldsReadonly as $field )
		$form->getElement( $field )->setAttrib( 'readonly', true );
	    
	    // List the clients to the business plan
	    $clientsBusinessPlan = $this->_mapper->listClientBusinessPlan( $businessPlan->id_businessplan );
	    $this->view->clientsBusinessPlan = $clientsBusinessPlan;
	    
	    $this->view->businessPlan = $businessPlan;
	    
	    if ( !empty( $data['submitted'] ) || !empty( $businessPlan->business_group ) ) {
		
		foreach ( $form->getElements() as $element )
		    $element->setAttrib( 'disabled', true );
		
		$this->view->no_edit = true;
	    }
	    
	    $data['module'] = $data['fk_id_fefop_modules'];
	}
	
	$form->populate( $data );
	
	$can_create = $this->_mapper->canCreateBusinessPlan( $data['fk_id_perdata'], $data['module'] );
	if ( !$can_create ) {

	    foreach ( $form->getElements() as $element )
		$element->setAttrib( 'disabled', true );
	}

	$this->view->can_create = $can_create;
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function businessPlanAction()
    {
	$id = $this->_getParam( 'id' );
	if ( empty( $id ) ) 
	    $this->_helper->redirector->goToSimple( 'index', 'index', 'external' );
	
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$formBusinessPlan = new External_Form_BusinessPlan();
	$formBusinessPlan->setAction( $this->_helper->url( 'save-business-plan' ) );
	
	$businessPlan = $this->_mapper->fetchBusinessPlan( $id );
	
	// Prepare Subdistrict SELECT Option
	$mapperSubDistrict = new Register_Model_Mapper_AddSubDistrict();
	$rowsSubdistrict = $mapperSubDistrict->listAll( $businessPlan->fk_id_adddistrict );
	
	$rows = array( '' => '' );
	foreach ( $rowsSubdistrict as $subDistrict )
	    $rows[$subDistrict->id_addsubdistrict] = $subDistrict->sub_district;
	
	$formBusinessPlan->getElement( 'fk_id_addsubdistrict' )->addMultiOptions( $rows );
	
	// Prepare Business Plan Owner Select
	$rowOwner = array( $businessPlan->fk_id_perdata => Client_Model_Mapper_Client::buildNameById( $businessPlan->fk_id_perdata ) );
	$clientsBusinessPlan = $this->_mapper->listClientBusinessPlan( $id );
	
	foreach ( $clientsBusinessPlan as $client )
	    $rowOwner[$client->id_perdata] = Client_Model_Mapper_Client::buildName(  $client );
	
	$formBusinessPlan->getElement( 'bussines_plan_developer' )->addMultiOptions( $rowOwner );
	
	$data = $businessPlan->toArray();
	
	$formBusinessPlan->populate( $data );
	$this->view->formBusiness = $formBusinessPlan;
	$this->view->businessPlan = $businessPlan;
	
	$mapperBudgetCategory = new Fefop_Model_Mapper_Expense();
	
	$expenses = $mapperBudgetCategory->expensesInModule( $businessPlan->fk_id_fefop_modules );
	$initial_expense = $mapperBudgetCategory->expensesInItem( Fefop_Model_Mapper_Expense::CONFIG_PCE_INITIAL );
	$annual_expense = $mapperBudgetCategory->expensesInItem( Fefop_Model_Mapper_Expense::CONFIG_PCE_ANNUAL );
	$revenue_expense = $mapperBudgetCategory->expensesInItem( Fefop_Model_Mapper_Expense::CONFIG_PCE_REVENUE );
	
	$this->view->expenses = $this->_mapper->aggregateExpenses( $expenses, $businessPlan, $mapperBudgetCategory->getModuleToItem( $businessPlan->fk_id_fefop_modules ) );
	$this->view->initial_expense = $this->_mapper->aggregateExpenses( $initial_expense, $businessPlan, Fefop_Model_Mapper_Expense::CONFIG_PCE_INITIAL );
	$this->view->annual_expense = $this->_mapper->aggregateExpenses( $annual_expense, $businessPlan, Fefop_Model_Mapper_Expense::CONFIG_PCE_ANNUAL );
	$this->view->revenue_expense = $this->_mapper->aggregateExpenses( $revenue_expense, $businessPlan, Fefop_Model_Mapper_Expense::CONFIG_PCE_REVENUE );
	
	// Populate form
	$this->_populateFormBusinessPlan( $formBusinessPlan, $businessPlan );
	
	if ( !empty( $data['submitted'] ) || !empty( $businessPlan->business_group ) ) {
		
	    foreach ( $formBusinessPlan->getElements() as $element )
		$element->setAttrib( 'disabled', true );

	    $this->view->no_edit = true;
	}
	
	$can_create = $this->_mapper->canCreateBusinessPlan( $data['fk_id_perdata'], $data['fk_id_fefop_modules'] );
	if ( !$can_create ) {

	    foreach ( $formBusinessPlan->getElements() as $element )
		$element->setAttrib( 'disabled', true );
	}
	
	$this->view->can_create = $can_create;
    }
    
    /**
     * 
     * @param Zend_Form $form
     * @param Zend_Db_Table_Row $businessPlan
     */
    protected function _populateFormBusinessPlan( $form, $businessPlan )
    {
	$dataForm = array();
	
	// Fetch description fields
	$fieldsBusinessPlan = $this->_mapper->groupFieldsBusinessPlan( $businessPlan->id_businessplan );
	
	// Fetch totals
	$totalsFields = $this->_mapper->groupTotals( $businessPlan->id_businessplan );
	
	if ( !empty( $businessPlan->fk_id_addsucu ) ) {
	    
	    $mapperSuku = new Register_Model_Mapper_AddSuku();
	    $suku = $mapperSuku->fetchRow( $businessPlan->fk_id_addsucu );
	    
	    $dataForm['fk_id_addsubdistrict'] = $suku->fk_id_addsubdistrict;
	    
	    $sukuRows = $mapperSuku->listAll( $suku->fk_id_addsubdistrict );
	    
	    $rows = array( '' => '' );
	    foreach ( $sukuRows as $suku )
		$rows[$suku->id_addsucu] = $suku->sucu;
	    
	    $form->getElement( 'fk_id_addsucu' )->addMultiOptions( $rows );
	}
	
	// List the items expense detailed
	$itensExpense = $this->_mapper->listItemExpenses( $businessPlan->id_businessplan );

	$dataItensExpense = array();
	foreach ( $itensExpense as $item ) {

	    if ( !array_key_exists( $item->fk_id_budget_category, $dataItensExpense ) )
		$dataItensExpense[$item->fk_id_budget_category] = array();

	    $dataItensExpense[$item->fk_id_budget_category][] = $item;
	}
	
	// Check if the business plan has Budget Category defined
	if ( $this->_mapper->hasBudgetCategory( $businessPlan->id_businessplan ) ) {
	
	    $idBusinessPlan = $businessPlan->id_businessplan;
	    
	    $mapperBudgetCategory = new Fefop_Model_Mapper_Expense();
	    $itemConfig = $mapperBudgetCategory->getModuleToItem( $businessPlan->fk_id_fefop_modules );
	    
	    $this->view->expenses = $this->_mapper->listExpenses( $idBusinessPlan, $itemConfig );
	    $this->view->initial_expense = $this->_mapper->listExpenses( $idBusinessPlan, Fefop_Model_Mapper_Expense::CONFIG_PCE_INITIAL );
	    $this->view->annual_expense = $this->_mapper->listExpenses( $idBusinessPlan, Fefop_Model_Mapper_Expense::CONFIG_PCE_ANNUAL );
	    $this->view->revenue_expense = $this->_mapper->listExpenses( $idBusinessPlan, Fefop_Model_Mapper_Expense::CONFIG_PCE_REVENUE );
	    
	} else
	    $dataItensExpense = $this->_mapper->aggregateItemsExpense( $dataItensExpense, $businessPlan );
	    

	$this->view->itemsExpense = $dataItensExpense;
	
	$dataForm += $fieldsBusinessPlan;
	$dataForm += $totalsFields;
	
	$form->populate( $dataForm );
    }
    
    /**
     * 
     */
    public function finishPlanAction()
    {
	$id = $this->_getParam( 'id' );
	if ( empty( $id ) ) 
	    $this->_helper->redirector->goToSimple( 'index', 'index', 'external' );
	
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$formFinishPlan=  new External_Form_FinishPlan();
	$formFinishPlan->setAction( $this->_helper->url( 'finish-business-plan' ) );
	
	$businessPlan = $this->_mapper->fetchBusinessPlan( $id );
	
	$data = $businessPlan->toArray();
	if ( $data['date_sumitted'] )
	    $data['date_sumitted'] = $this->view->date( $data['date_sumitted'] );
	
	$formFinishPlan->populate( $data );
	
	$this->view->finishForm = $formFinishPlan;
	$this->view->no_edit = !empty( $data['submitted'] ) || !empty( $businessPlan->business_group );
    }
    
    /**
     * 
     */
    public function addDetailedExpenseAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$this->view->expense = $this->_getParam( 'expense' );
	
	$defaultValues = array(
	    'description' => null,
	    'quantity'	  => 1,
	    'amount_unit' => 0,
	    'amount_total'=> 0,
	    'comments'	  => ''
	);
	
	$row = $this->_getParam( 'row' );
	
	if ( !empty( $row ) )
	    $defaultValues = $row->toArray();
	
	$this->view->defaultValues = $defaultValues;
	$this->view->no_edit = $this->_getParam( 'no_edit' );
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new External_Form_Pce();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function cegAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Criação de Emprego para Graduados (CEG)',
	    'url'   => 'external/pce/ceg'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Criação de Emprego para Graduados (CEG)' );
	$this->_setParam( 'module', Fefop_Model_Mapper_Module::CEG );
	
	$this->_forward( 'index', 'pce' );
    }
    
    /**
     * 
     */
    public function cecAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Criação de Emprego para Comunidade (CEC)',
	    'url'   => 'external/pce/cec'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Criação de Emprego para Comunidade (CEC)' );
	$this->_setParam( 'module', Fefop_Model_Mapper_Module::CEC );
	
	$this->_forward( 'index', 'pce' );
    }
    
    /**
     * 
     */
    public function cedAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Criação de Emprego para Portadores de Deficiência (CED)',
	    'url'   => 'external/pce/cec'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Criação de Emprego para Portadores de Deficiência (CED)' );
	$this->_setParam( 'module', Fefop_Model_Mapper_Module::CED );
	
	$this->_forward( 'index', 'pce' );
    }
    
    /**
     * 
     */
    public function searchIsicClassAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$division = $this->_getParam( 'id' );
	
	$mapperIsicTimor = new Register_Model_Mapper_IsicTimor();
	$classTimor = $mapperIsicTimor->listClassByDisivion( $division );
	
	$classes = array();
	if ( !empty( $classTimor[$division]['classes'] ) )
	    $classes = $classTimor[$division]['classes'];
	
	$opt = array( array( 'id' => '', 'name' => '' ) );
	foreach ( $classes as $class )
	    $opt[] = array( 'id' => $class->id_isicclasstimor, 'name' => $class->name_classtimor );
	
	$this->_helper->json( $opt );
    }
    
    /**
     * 
     */
    public function searchClientAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'client', 'client' );
    }
    
    /**
     * 
     */
    public function searchClientForwardAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'search-client', 'client', 'client' );
    }
    
    /**
     * 
     */
    public function addClientAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	$row = $this->_getParam( 'row' );
	$no_edit = $this->_getParam( 'no_edit' );
	
	if ( empty( $row ) ) {
	    
	    $mapperClient = new Client_Model_Mapper_Client();
	    $row = $mapperClient->detailClient( $id );
	    
	}
	
	$this->view->no_edit = $no_edit;
	$this->view->client = $row;
    }
    
    /**
     * 
     */
    public function searchSukuAction()
    {
	$mapperSuku = new Register_Model_Mapper_AddSuku();
	$rows = $mapperSuku->listAll( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_addsucu, 'name' => $row->sucu );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function saveBusinessPlanAction()
    {
	$form = new External_Form_BusinessPlan();
	
	if ( $this->getRequest()->isPost() ) {

	    if ( $form->isValid( $this->getRequest()->getPost() ) ) {
	    	
		$this->_mapper->setData( $form->getValues() );
		$return = $this->_mapper->saveBusinessPlan();
		$message = $this->_mapper->getMessage()->toArray();

		$result = array(
		    'status'	    => (bool) $return,
		    'id'	    => $return,
		    'description'   => $message,
		    'data'	    => $form->getValues(),
		    'fields'	    => $this->_mapper->getFieldsError()
		);

		$this->_helper->json( $result );
		
	    } else {
		
		$message = new App_Message();
		$message->addMessage( $this->_config->messages->warning, App_Message::WARNING );

		$result = array(
		    'status'	  => false,
		    'description' => $message->toArray(),
		    'errors'	  => $form->getMessages()
		);

		$this->_helper->json( $result );
	    }
	}
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function finishBusinessPlanAction()
    {
	$form = new External_Form_FinishPlan();
	
	if ( $this->getRequest()->isPost() ) {

	    if ( $form->isValid( $this->getRequest()->getPost() ) ) {
	    	
		$this->_mapper->setData( $form->getValues() );
		$return = $this->_mapper->saveFinishPlan();
		$message = $this->_mapper->getMessage()->toArray();

		$result = array(
		    'status'	    => (bool) $return,
		    'id'	    => $return,
		    'description'   => $message,
		    'data'	    => $form->getValues(),
		    'fields'	    => $this->_mapper->getFieldsError()
		);

		$this->_helper->json( $result );
		
	    } else {
		
		$message = new App_Message();
		$message->addMessage( $this->_config->messages->warning, App_Message::WARNING );

		$result = array(
		    'status' => false,
		    'description' => $message->toArray(),
		    'errors' => $form->getMessages()
		);

		$this->_helper->json( $result );
	    }
	}
    }
    
    /**
     * 
     */
    public function fetchFinancialAnalysisAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	     
	$id = $this->_getParam( 'id' );
	
	$businessPlan = $this->_mapper->fetchBusinessPlan( $id );
	
	$this->_setParam( 'year', $businessPlan->year_activity );
	
	$totals = $this->_mapper->groupTotals( $id );
	foreach ( $totals as $total => $amount )
	    $this->_setParam( $total, $amount );
	
	$mapperBudgetCategory = new Fefop_Model_Mapper_Expense();
	
	if ( $this->_mapper->hasBudgetCategory( $id ) ) {
	    
	    $expenses = $this->_mapper->listExpenses( $id, Fefop_Model_Mapper_Expense::CONFIG_PCE_ANNUAL );
	    $annualSales = $this->_mapper->listExpenses($id, Fefop_Model_Mapper_Expense::CONFIG_PCE_REVENUE );
	    
	} else {
	    
	    $expenses = $mapperBudgetCategory->expensesInItem( Fefop_Model_Mapper_Expense::CONFIG_PCE_ANNUAL );
	    $annualSales = $mapperBudgetCategory->expensesInItem( Fefop_Model_Mapper_Expense::CONFIG_PCE_REVENUE );
	}
	
	$totalAnnualSales = 0;
	if ( $annualSales->count() > 0 )
	    $totalAnnualSales = $annualSales->current()->amount;
	
	$this->_setParam( 'expenses', $expenses );
	$this->_setParam( 'annual', $totalAnnualSales );
	$this->_setParam( 'submitted', $businessPlan->submitted );
	
	$this->_forward( 'financial-analysis' );
    }
    
    /**
     * 
     */
    public function financialAnalysisAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$financialAnalysis = $this->_mapper->financialAnalysis( $this->_getAllParams() );
	
	$this->view->financial_analysis = $financialAnalysis;
	$this->view->submitted = $this->_getParam( 'submitted' );
    }
}