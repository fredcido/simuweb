<?php

/**
 * 
 */
class Fefop_PceContractController extends App_Controller_Default
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
	
	$stepBreadCrumb = array(
	    'label' => 'Kontratu Programa de Kriasaun de Empreza',
	    'url'   => 'fefop/pce-contract'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Kontratu Programa de Kriasaun de Empreza' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$this->_forward( 'list' );
    }
    
     /**
     * 
     */
    public function contractAction()
    {
	$id = $this->_getParam( 'id' );
	$this->view->menu()->setActivePath( 'fefop/pce-contract/list' );

	if ( empty( $id ) ) {
	    $this->_helper->redirector->goToSimple( 'list' );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Haree Kontraktu',
		'url'	=> 'fefop/pce-contract/contract/id/' . $id
	    );
	    
	    $row = $this->_mapper->fetchBusinessPlan( $id );
	    if ( empty( $row ) )
		$this->_helper->redirector->goToSimple( 'index' );
	    
	    $this->view->id_contract = $row->fk_id_fefop_contract;
	    $this->view->id_businessplan = $row->id_businessplan;
	}
	
	$this->view->id = $id;
	$this->view->contract = $row;
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
    }
    
    /**
     * 
     */
    public function informationAction()
    {
	$id = $this->_getParam( 'id' );
	
	$row = $this->_mapper->fetchBusinessPlan( $id );
	
	$mapperClient = new Client_Model_Mapper_Client();
	$client = $mapperClient->detailClient( $row->fk_id_perdata );
	
	$clientsBusinessPlan = $this->_mapper->listClientBusinessPlan( $id );
	$this->view->clientsBusinessPlan = $clientsBusinessPlan;
	
	// Fetch description fields
	$fieldsBusinessPlan = $this->_mapper->groupFieldsBusinessPlan( $id );
	
	// Fetch totals
	$totalsFields = $this->_mapper->groupTotals( $id );
	
	$mapperBudgetCategory = new Fefop_Model_Mapper_Expense();
	$itemConfig = $mapperBudgetCategory->getModuleToItem( $row->fk_id_fefop_modules );
	
	$this->view->expenses = $this->_mapper->listExpenses( $id, $itemConfig );
	$this->view->initial_expense = $this->_mapper->listExpenses( $id, Fefop_Model_Mapper_Expense::CONFIG_PCE_INITIAL );
	$this->view->annual_expense = $this->_mapper->listExpenses( $id, Fefop_Model_Mapper_Expense::CONFIG_PCE_ANNUAL );
	$this->view->revenue_expense = $this->_mapper->listExpenses( $id, Fefop_Model_Mapper_Expense::CONFIG_PCE_REVENUE );
	
	// List the items expense detailed
	$itensExpense = $this->_mapper->listItemExpenses( $id );

	$dataItensExpense = array();
	foreach ( $itensExpense as $item ) {

	    if ( !array_key_exists( $item->fk_id_budget_category, $dataItensExpense ) )
		$dataItensExpense[$item->fk_id_budget_category] = array();

	    $dataItensExpense[$item->fk_id_budget_category][] = $item;
	}

	$this->view->itemsExpense = $dataItensExpense;
	$this->view->id = $id;
	$this->view->contract = $row;
	$this->view->totals_fields = $totalsFields;
	$this->view->values_description_fields = $fieldsBusinessPlan;
	$this->view->description_fields = $this->_mapper->getDescriptionFields();
	$this->view->client = $client;
	$this->view->no_edit = true;
    }
    
    public function fetchFinancialAnalysisAction()
    {
	$this->_forward( 'fetch-financial-analysis', 'pce', 'external' );
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$searchPCESearch = new Fefop_Form_PCEContractSearch();
	$searchPCESearch->setAction( $this->_helper->url( 'search-pce-contract' ) );
	
	$this->view->menu()->setActivePath( 'fefop/pce-contract/list' );
     
	$this->view->form = $searchPCESearch;
    }
    
    /**
     * 
     */
    public function searchPceContractAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
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
    public function printAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$id = $this->_getParam( 'id' );
	
	$this->view->id = $id;
	$this->view->contract = $this->_mapper->fetchBusinessPlan( $id );
	$this->view->user = Zend_Auth::getInstance()->getIdentity();
	$this->view->print = true;
	
	$this->view->financial_analysis = $this->view->action( 'fetch-financial-analysis', 'pce', 'external', array( 'id' => $id ) );
    }
    
    /**
     * 
     */
    public function technicalFeedbackAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$this->view->feedbackFields = $this->_mapper->getTechnicalFeedback( $id );
	$businessPlan = $this->_mapper->fetchBusinessPlan( $id );
	
	$row = $this->_mapper->fetchTechnicalFeedback( $id );
	if ( !empty( $row ) )
	    $data = $row->toArray();
	else
	    $data = array( 'fk_id_businessplan' => $id );
	
	$formTecnhicalFeedback = new Fefop_Form_TechnicalFeedback();
	$formTecnhicalFeedback->setAction( $this->_helper->url( 'save-technical-feedback' ) );
	//$formTecnhicalFeedback->addAutomaticFields( $tecnhicalFeedback );
	$formTecnhicalFeedback->populate( $data );
	
	if ( !empty( $businessPlan->fk_id_fefop_contract ) && !empty( $businessPlan->submitted ) ) {
	    
	    foreach ( $formTecnhicalFeedback as $element )
		$element->setAttrib( 'disabled', true );
	    
	    $formTecnhicalFeedback->removeDisplayGroup( 'toolbar' );
	}
	
	$this->view->amount_contract = $this->_mapper->getTotal( $id, 'total_expense' );
	$this->view->amount_max = $this->_mapper->getMaxContractAmount( $id );
	
	if ( $this->view->amount_contract <= $this->view->amount_max )
	    $formTecnhicalFeedback->getElement('amount')->setValue(1);
	
	$this->view->form = $formTecnhicalFeedback;
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function saveTechnicalFeedbackAction()
    {
	$this->_helper->viewRenderer->setRender( 'form' );

	$form = new Fefop_Form_TechnicalFeedback();
	
	if ( $this->getRequest()->isPost() ) {

	    if ( $form->isValid( $this->getRequest()->getPost() ) ) {
	    	
		$this->_mapper->setData( $form->getValues() );

		$return = $this->_mapper->saveTechnicalFeedback();
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
    public function revisionAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$formRevision = new Fefop_Form_PceRevision();
	$formRevision->setAction( $this->_helper->url( 'save-revision' ) );
	$formRevision->getElement( 'fk_id_businessplan' )->setValue( $id );
	
	$businessPlan = $this->_mapper->fetchBusinessPlan( $id );
	if ( !empty( $businessPlan->fk_id_fefop_contract ) ) {
	    
	    foreach ( $formRevision as $element )
		$element->setAttrib( 'disabled', true );
	    
	    $formRevision->removeElement( 'save' );
	}
	
	$this->view->form = $formRevision;
    }
    
    /**
     * 
     */
    public function listRevisionAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$this->view->rows = $this->_mapper->listRevisions( $this->_getParam( 'id' ) );
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function saveRevisionAction()
    {
	$this->_helper->viewRenderer->setRender( 'form' );

	$form = new Fefop_Form_PceRevision();
	
	if ( $this->getRequest()->isPost() ) {

	    if ( $form->isValid( $this->getRequest()->getPost() ) ) {
	    	
		$this->_mapper->setData( $form->getValues() );

		$return = $this->_mapper->saveRevision();
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
    public function councilDecisionAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$data = array(
	    'fk_id_businessplan' => $id,
	    'council_negative' => $this->_mapper->getDescription( $id, 'council_negative' )
	);
	
	$businessPlan = $this->_mapper->fetchBusinessPlan( $id );
	$formCouncilDecision = new Fefop_Form_CouncilDecision();
	
	if ( !empty( $businessPlan->fk_id_fefop_contract ) ) {
	    
	    $mapperContract = new Fefop_Model_Mapper_Contract();
	    $contract = $mapperContract->detail( $businessPlan->fk_id_fefop_contract );
	    
	    $data['date_contract'] = $this->view->date( $contract['date_inserted'] );
	    $data['approved'] = 1;
	    
	    foreach ( $formCouncilDecision as $element )
		$element->setAttrib( 'disabled', true );
	    
	    $formCouncilDecision->removeDisplayGroup( 'toolbar' );
	}
	
	$formCouncilDecision->setAction( $this->_helper->url( 'save-council-decision' ) );
	$formCouncilDecision->populate( $data );
	
	$this->view->form = $formCouncilDecision;
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function saveCouncilDecisionAction()
    {
	$this->_helper->viewRenderer->setRender( 'form' );

	$form = new Fefop_Form_CouncilDecision();
	
	if ( $this->getRequest()->isPost() ) {

	    if ( $form->isValid( $this->getRequest()->getPost() ) ) {
	    	
		$this->_mapper->setData( $form->getValues() );

		$return = $this->_mapper->saveCouncilDecision();
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
    public function exportAction()
    {
	$id = $this->_getParam( 'id' );
	$row = $this->_mapper->fetchBusinessPlan( $id );
	
	$contractFiles = array(
	    'CEG' => 'Contrato_CEG_tet.xlsx',
	    'CEC' => 'Contrato_CEC_II_tet.xlsx',
	    'CED' => 'Contrato_CED_II_tet.xlsx'
	);
	
	// Fetch Contract
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$contract = $mapperContract->detail( $row->fk_id_fefop_contract );
	
	$data = $row->toArray();
	
	$data += $contract->toArray();
	
	$reserve_fund = $this->_mapper->getTotal( $id, 'reserve_fund' );
	$reserve_fund = $reserve_fund < 1 ? 600 : $reserve_fund;
	
	$data['contract'] = Fefop_Model_Mapper_Contract::buildNumById( $row->fk_id_fefop_contract );
	$data['business_plan'] = External_Model_Mapper_Pce::buildNumRow( $row );
	$data['date_inserted'] = $this->view->date( $data['date_inserted'] );
	
	$dateInsert = new Zend_Date( $data['date_inserted'] );
	$data['date_finish'] = $dateInsert->addYear( 2 )->toString( 'dd/MM/yyyy' );
	
	$mapperClient = new Client_Model_Mapper_Client();
	$client = $mapperClient->detailClient( $row->fk_id_perdata );
	
	$data['evidence_card'] = Client_Model_Mapper_Client::buildNumRow( $client );
	$data['client_name'] = Client_Model_Mapper_Client::buildName( $client );
	$data['is_handicapped'] = Client_Model_Mapper_Client::isHandicapped( $row->fk_id_perdata );
	$data['electoral'] = $client->electoral;
	$data['gender'] = $client->gender;
	$data['client_fone'] = $client->client_fone;
	
	$group = array();
	$rowsGroup = $this->_mapper->listClientBusinessPlan( $id );
	foreach ( $rowsGroup as $rowGroup ) {
	    
	    $group[] = array(
		'name'		    => Client_Model_Mapper_Client::buildName( $rowGroup ),
		'evidence_card'	    => Client_Model_Mapper_Client::buildNumRow( $rowGroup ),
		'electoral'	    => $client->electoral,
		'gender'	    => $client->gender,
		'is_handicapped'    => Client_Model_Mapper_Client::isHandicapped( $client->id_perdata ),
	    );
	}
	
	$expensesRows = $mapperContract->listExpensesContract( $row->fk_id_fefop_contract );
	
	$expenses = array();
	$total = 0;
	
	foreach ( $expensesRows as $expense ) {
	    
	    $expenses[] = array(
		'name'	 => $expense->description,
		'amount' => (float)$expense->amount
	    );
	    
	    $total += (float)$expense->amount;
	}
	
	$excelPath = APPLICATION_PATH . '/../library/PHPExcel/';
	require_once( $excelPath . 'PHPExcel/IOFactory.php' );
	
	$objReader = PHPExcel_IOFactory::createReader( 'Excel2007' );
	$objPHPExcel = $objReader->load( APPLICATION_PATH . '/../public/forms/FEFOP/' . $contractFiles[$data['num_module']]  );
	$activeSheet = $objPHPExcel->getActiveSheet();
	
	
	$activeSheet->setCellValue( 'P12', $data['contract'] );
	$activeSheet->setCellValue( 'Q40', $data['business_plan'] );
	$activeSheet->setCellValue( 'T8', $data['date_inserted'] );
	$activeSheet->setCellValue( 'F45', $data['date_inserted'] );
	$activeSheet->setCellValue( 'F46', $data['date_finish'] );
	$activeSheet->setCellValue( 'F16', $data['evidence_card'] );
	$activeSheet->setCellValue( 'F17', $data['electoral'] );
	$activeSheet->setCellValue( 'E19', $data['client_name'] );
	$activeSheet->setCellValue( 'D117', $data['client_name'] );
	$activeSheet->setCellValue( 'Q19', $data['client_fone'] );
	$activeSheet->setCellValue( 'O20', $data['email'] );
	
	$activeSheet->setCellValue( 'H25', $data['total_partisipants'] );
	
	
	// Responsible
	$activeSheet->setCellValue( 'E28', $data['client_name'] );
	$activeSheet->setCellValue( 'M28', $data['evidence_card'] );
	$activeSheet->setCellValue( 'O28', $data['electoral'] );
	$activeSheet->setCellValue( 'Q28', $data['gender'] );
	$activeSheet->setCellValue( 'R28', $data['is_handicapped'] ? 'Sin' : 'Lae' );
	
	$startPerson = 29;
	foreach ( $group as $person ) {
	    
	    $activeSheet->setCellValue( 'E' . $startPerson, $person['name'] );
	    $activeSheet->setCellValue( 'M' . $startPerson, $person['evidence_card'] );
	    $activeSheet->setCellValue( 'O' . $startPerson, $person['electoral'] );
	    $activeSheet->setCellValue( 'Q' . $startPerson, $person['gender'] );
	    $activeSheet->setCellValue( 'R' . $startPerson, $person['is_handicapped'] ? 'Sin' : 'Lae' );
	    
	    $startPerson++;
	}
	
	$activeSheet->setCellValue( 'F38', $data['name_disivion'] );
	$activeSheet->setCellValue( 'L38', $data['name_classtimor'] );
	$activeSheet->setCellValue( 'F40', $data['project_name'] );
	
	$activeSheet->setCellValue( 'P45', $data['district'] );
	$activeSheet->setCellValue( 'P46', $data['sub_district'] );
	$activeSheet->setCellValue( 'P47', $data['sucu'] );
	
	$startExpense = 52;
	$count = 'A';
	foreach ( $expenses as $expense ) {
	    
	    $activeSheet->setCellValue( 'B' . $startExpense, $count++ );
	    $activeSheet->setCellValue( 'C' . $startExpense, $expense['name'] );
	    $activeSheet->setCellValue( 'S' . $startExpense, $expense['amount'] );
	    
	    $startExpense++;
	}
	
	$activeSheet->setCellValue( 'B' . $startExpense, $count++ );
	$activeSheet->setCellValue( 'C' . $startExpense, 'Fundu Maneiu (600 USD + 10% rubrika sira iha leten)' );
	$activeSheet->setCellValue( 'S' . $startExpense, $reserve_fund );
	
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	$file = sprintf( 'Contract_%s.xlsx', $data['contract'] );
	header(sprintf('Content-Disposition: attachment;filename="%s"', $file));
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel2007' );
	$objWriter->save( 'php://output' );
	exit;
    }
}