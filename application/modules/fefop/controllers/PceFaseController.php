<?php

/**
 * 
 */
class Fefop_PceFaseController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_PCEContract
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_PCEContract();
	
	$stepBreadCrumb = array(
	    'label' => 'Kontratu PCE - Formasaun Profisional - Faze I',
	    'url'   => 'fefop/pce-fase'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Kontratu PCE - Formasaun Profisional - Faze I' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$id = $this->_getParam( 'id' );

	if ( empty( $id ) ) {

	    $stepBreadCrumb = array(
		'label' => 'Rejistu Kontraktu',
		'url'	=> 'fefop/pce-fase'
	    );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Kontraktu',
		'url'	=> 'fefop/pce-fase/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->detail( $id );
	    if ( empty( $row ) )
		$this->_helper->redirector->goToSimple( 'index' );
	    
	    $this->view->id_contract = $row->fk_id_fefop_contract;
	}

	$this->view->id = $id;
	$this->view->module = $this->_getParam( 'm' );
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
    }
    
     /**
     * 
     */
    public function editAction()
    {
	$this->_forward( 'index' );
    }
    
    /**
     * 
     */
    public function informationAction()
    {
	// Form Information
	$formInformation = $this->_getForm( $this->_helper->url( 'save' ) );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->detail( $id );
	    
	    $data = $row->toArray();
	    
	    $data['date_start'] = $this->view->date( $data['date_start'] );
	    $data['date_finish'] = $this->view->date( $data['date_finish'] );
	    
	    $mapperIsicTimor = new Register_Model_Mapper_IsicTimor();
	    $classTimor = $mapperIsicTimor->listClassByDisivion( $data['fk_id_isicdivision'] );

	    $classes = array();
	    if ( !empty( $classTimor[$data['fk_id_isicdivision']]['classes'] ) )
		$classes = $classTimor[$data['fk_id_isicdivision']]['classes'];

	    $opt = array( '' => '' );
	    foreach ( $classes as $class )
		$opt[$class->id_isicclasstimor] = $class->name_classtimor;
	    
	    $formInformation->getElement('fk_id_isicclasstimor')->addMultiOptions( $opt );
	    
	    $formInformation->populate( $data );
	    
	    $formInformation->getElement('fk_id_adddistrict')->setAttrib( 'readonly', true ); 
	    $formInformation->getElement('fk_id_fefop_modules')->setAttrib( 'readonly', true ); 
	    
	    // List the expenses related to the contract
	    $this->view->expenses = $this->_mapper->listExpenses( $id );
	    
	    // List the items expense detailed
	    $itensExpense = $this->_mapper->listItemExpenses( $id );
	    
	    $dataItensExpense = array();
	    foreach ( $itensExpense as $item ) {
		
		if ( !array_key_exists( $item->fk_id_budget_category, $dataItensExpense ) )
		    $dataItensExpense[$item->fk_id_budget_category] = array();
		
		$dataItensExpense[$item->fk_id_budget_category][] = $item;
	    }
	    
	    $this->view->itemsExpense = $dataItensExpense;
	    
	    if ( !$this->view->fefopContract( $data['id_fefop_contract'] )->isEditable() )
		$formInformation->removeDisplayGroup( 'toolbar' );
	    
	} else {
	    
	    $module = $this->_getParam( 'module' );
	    if ( !empty( $module ) ) {
		
		$formInformation->getElement( 'fk_id_fefop_modules' )->setValue( $module );
		
		$constant = Fefop_Model_Mapper_Expense::CONFIG_PCE_CEC_FASE_I;
		if ( Fefop_Model_Mapper_Module::CED == $module )
		    $constant = Fefop_Model_Mapper_Expense::CONFIG_PCE_CED_FASE_I;
	    
		// Fetch the Expenses related to the module
		$mapperBudgetCategory = new Fefop_Model_Mapper_Expense();
		$this->view->expenses = $mapperBudgetCategory->expensesInItem( $constant );
		
	    } else {
		
		foreach ( $formInformation->getElements() as $element )
		    $element->setAttrib( 'disabled', true );
		
		$formInformation->getElement( 'fk_id_fefop_modules' )->setAttrib( 'disabled', null );
	    }
	}
	
	$this->view->form = $formInformation;
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
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Fefop_Form_PceFaseContract();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$searchPseFase = new Fefop_Form_PceFaseSearch();
	$searchPseFase->setAction( $this->_helper->url( 'search-pce-fase' ) );
	
	$this->view->menu()->setActivePath( 'fefop/pce-fase/list' );
     
	$this->view->form = $searchPseFase;
    }
    
    /**
     * 
     */
    public function searchPceFaseAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
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
    public function fetchClientAction()
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$client = $mapperClient->detailClient( $this->_getParam( 'id' ) );
	
	$data = array();
	$data['fk_id_perdata'] = $client['id_perdata'];
	$data['beneficiary'] = Client_Model_Mapper_Client::buildName( $client );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function searchClassAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'register', 'student-class' );
    }
    
    /**
     * 
     */
    public function searchClassForwardAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'search-class', 'register', 'student-class' );
    }
    
    /**
     * 
     */
    public function fetchClassAction()
    {
	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	$studentClass = $mapperStudentClass->detailStudentClass( $this->_getParam( 'id' ) );
	
	$data = array();
	$data['fk_id_fefpstudentclass'] = $studentClass['id_fefpstudentclass'];
	$data['class_name'] = $studentClass['class_name'];
	$data['date_start'] = $this->view->date( $studentClass['start_date'] );
	$data['date_finish'] = $this->view->date( $studentClass['schedule_finish_date'] );
	
	$dateInit = new Zend_Date( $studentClass['start_date'] );
	$dateFinish = new Zend_Date( $studentClass['schedule_finish_date'] );
	
	$diff = $dateFinish->sub( $dateInit );
	
	$measure = new Zend_Measure_Time( $diff->toValue(), Zend_Measure_Time::SECOND );
	$diffDays = $measure->convertTo( Zend_Measure_Time::DAY, 0 );
	
	$data['duration'] = preg_replace( '/[^0-9]/i', '', $diffDays );
	
	$this->_helper->json( $data );
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
    }
    
    /**
     * 
     */
    public function fetchContractAction()
    {
	$id = $this->_getParam( 'id' );
	$row = $this->_mapper->detail( $id );
	
	$this->_helper->json( $row->toArray() );
    }
    
    
    /**
     * 
     */
    public function exportAction()
    {
	$id = $this->_getParam( 'id' );
	$row = $this->_mapper->detail( $id );
	
	$contractFiles = array(
	    'CEC' => 'Contrato_CEC_I_tet.xlsx',
	    'CED' => 'Contrato_CED_I_tet.xlsx'
	);
	
	// Fetch Contract
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$contract = $mapperContract->detail( $row->fk_id_fefop_contract );
	
	$data = $row->toArray();
	
	$data += $contract->toArray();
	
	$data['contract'] = Fefop_Model_Mapper_Contract::buildNumById( $row->fk_id_fefop_contract );
	$data['date_inserted'] = $this->view->date( $data['date_inserted'] );
	$data['date_start'] = $this->view->date( $data['date_start'], 'yyyy-M-dd' );
	$data['date_finish'] = $this->view->date( $data['date_finish'], 'yyyy-M-dd' );
	
	$mapperClient = new Client_Model_Mapper_Client();
	$client = $mapperClient->detailClient( $row->fk_id_perdata );
	
	$data['evidence_card'] = Client_Model_Mapper_Client::buildNumRow( $client );
	$data['client_name'] = Client_Model_Mapper_Client::buildName( $client );
	$data['electoral'] = $client->electoral;
	$data['gender'] = $client->gender;
	$data['client_fone'] = $client->client_fone;
	$data['email'] = $client->email;
	
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
	$activeSheet->setCellValue( 'T8', $data['date_inserted'] );
	$activeSheet->setCellValue( 'F16', $data['evidence_card'] );
	$activeSheet->setCellValue( 'F17', $data['electoral'] );
	$activeSheet->setCellValue( 'E19', $data['client_name'] );
	$activeSheet->setCellValue( 'D100', $data['client_name'] );
	$activeSheet->setCellValue( 'Q19', $data['client_fone'] );
	$activeSheet->setCellValue( 'O20', $data['email'] );
	
	$activeSheet->setCellValue( 'F25', $data['name_disivion'] );
	$activeSheet->setCellValue( 'L25', $data['name_classtimor'] );
	$activeSheet->setCellValue( 'F27', $data['scholarity'] );
	$activeSheet->setCellValue( 'Q27', $data['external_code'] );
	$activeSheet->setCellValue( 'F29', $data['class_name'] );
	//$activeSheet->setCellValue( 'Q23', (float)$data['amount_training'] );
	
	$activeSheet->setCellValue( 'F33', $data['date_start'] );
	$activeSheet->setCellValue( 'F34', $data['date_finish'] );
	//$activeSheet->setCellValue( 'J27', $data['duration'] . ' days' );
	$activeSheet->setCellValue( 'P33', $data['district_course'] );
	$activeSheet->setCellValue( 'P34', $data['sub_district'] );
	
	$startExpense = 40;
	$count = 'A';
	foreach ( $expenses as $expense ) {
	    
	    $activeSheet->setCellValue( 'B' . $startExpense, $count++ );
	    $activeSheet->setCellValue( 'C' . $startExpense, $expense['name'] );
	    $activeSheet->setCellValue( 'S' . $startExpense, $expense['amount'] );
	    
	    $startExpense++;
	}
	
	//$activeSheet->setCellValue( 'S' . $startExpense, $total );
	
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	$file = sprintf( 'Contract_%s.xlsx', $data['contract'] );
	header(sprintf('Content-Disposition: attachment;filename="%s"', $file));
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel2007' );
	$objWriter->save( 'php://output' );
	exit;
    }
}