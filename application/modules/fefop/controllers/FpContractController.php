<?php

/**
 * 
 */
class Fefop_FpContractController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_FPContract
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_FPContract();
	
	$stepBreadCrumb = array(
	    'label' => 'Kontratu Formasaun Profisional',
	    'url'   => 'fefop/fp-contract'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Kontratu Formasaun Profisional' );
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
		'url'	=> 'fefop/fp-contract'
	    );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Kontraktu',
		'url'	=> 'fefop/fp-contract/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->detail( $id );
	    $this->view->id_contract = $row->fk_id_fefop_contract;
	}

	$this->view->id = $id;
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
	    
	    $data['start_date'] = $this->view->date( $data['date_start'] );
	    $data['finish_date'] = $this->view->date( $data['date_finish'] );
	    $data['scholarity'] = ( empty( $row->external_code ) ? '' : $row->external_code . ' - ' ) . $row->scholarity;
	    
	    $budget = $this->_mapper->getBudgetCategory( $id );
	    $data['id_budget_category'] = $budget->fk_id_budget_category;
	    
	    $formInformation->populate( $data );
	    $formInformation->getElement('fk_id_adddistrict')->setAttrib('readonly', true);
	    
	    if ( !$this->view->fefopContract( $data['id_fefop_contract'] )->isEditable() )
		$formInformation->removeDisplayGroup( 'toolbar' );
	    
	    $this->view->id_planning_course = $row->fk_id_planning_course;
	    
	} else {
	    
	    $mapperBudgetCategory = new Fefop_Model_Mapper_Expense();
	    $expenseModule = $mapperBudgetCategory->expensesInItem( Fefop_Model_Mapper_Expense::CONFIG_PFPCI_FP );
	    
	    // Check if the module has Budget Category Defined
	    if ( $expenseModule->count() < 1 ) {
		
		foreach ( $formInformation->getElements() as $element )
		    $element->setAttrib( 'disabled', true );
		
		$this->view->fefopContract()->addMessage( 'Seidauk iha Rubrica ba Modulu FP.' );
		
	    } else {
		$formInformation->getElement( 'id_budget_category' )->setValue( $expenseModule->current()->id_budget_category );
	    }
	}

	$this->view->form = $formInformation;
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Fefop_Form_FPContract();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$searchFPContract = new Fefop_Form_FPContractSearch();
	$searchFPContract->setAction( $this->_helper->url( 'search-fp-contract' ) );
	
	$this->view->menu()->setActivePath( 'fefop/fp-contract/list' );
     
	$this->view->form = $searchFPContract;
    }
    
    /**
     * 
     */
    public function searchFpContractAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
    }
    
    /**
     * 
     */
    public function searchAnnualPlanningAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$searchAnnualPlanning = new Fefop_Form_FPAnnualPlanningSearch();
	$searchAnnualPlanning->setAction( $this->_helper->url( 'list-annual-planning' ) );
     
	$this->view->form = $searchAnnualPlanning;
    }
    
    /**
     * 
     */
    public function listAnnualPlanningAction()
    {
	$this->_helper->layout()->disableLayout();
	$mapperAnnualPlanning = new Fefop_Model_Mapper_FPAnnualPlanning();
	
	$filters = $this->_getAllParams();
	$filters['no_contract'] = true;
	
	$this->view->rows = $mapperAnnualPlanning->listByFilters( $filters );
    }
    
    /**
     * 
     */
    public function detailPlanningAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$mapperEvent = new Fefop_Model_Mapper_FPAnnualPlanning();
	$this->view->event = $mapperEvent->fetchEvent( $id );
    }
    
    /**
     * 
     */
    public function searchCourseAction()
    {
	$category = $this->_getParam( 'category' );
	
	$filters = array(
	    'type'	=> Register_Model_Mapper_PerTypeScholarity::NON_FORMAL,
	    'category'	=> $category
	);
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$optScholarity = $mapperScholarity->getOptionsScholarity( $filters );

	$opts = array();
	foreach( $optScholarity as $id => $value )
	    $opts[] = array( 'id' => $id, 'name' => $value );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function fetchAnnualPlanningAction()
    {
	$id = $this->_getParam( 'id' );
	
	$mapperAnnualPlanning = new Fefop_Model_Mapper_FPAnnualPlanning();
	$event = $mapperAnnualPlanning->fetchEvent( $id );
	
	$data = $event->toArray();
	
	$data['institute'] = $data['institution'];
	$data['fk_id_annual_planning'] = $data['id_annual_planning'];
	$data['fk_id_planning_course'] = $data['id_planning_course'];
	$data['fk_id_perscholarity'] = $data['id_perscholarity'];
	$data['start_date'] = $this->view->date( $data['date_start'] );
	$data['finish_date'] = $this->view->date( $data['date_finish'] );
	$data['scholarity'] = ( empty( $event->external_code ) ? '' : $event->external_code . ' - ' ) . $event->scholarity;
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function fetchUnitCostAction()
    {
	$mapperUnitCost = new Fefop_Model_Mapper_UnitCost();
	$unitCost = $mapperUnitCost->getUnitCost( $this->_getParam( 'scholarity')  );
	
	$data = array();
	
	if ( !empty( $unitCost ) ) {
	    
	    $data = array(
		'id' => $unitCost->id_unit_cost,
		'cost' => number_format( $unitCost->cost, 2, '.', ',' )
	    );
	}
		
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function classPlanningAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'register', 'student-class' );
    }
    
    /**
     * 
     */
    public function classClientAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	$class = $mapperStudentClass->detailStudentClass( $this->_getParam( 'class' ) );
	$this->view->class = $class;
    }
    
    /**
     * 
     */
    public function fetchClassAction()
    {
	$mapperClass = new StudentClass_Model_Mapper_StudentClass();
	$class = $mapperClass->detailStudentClass( $this->_getParam( 'id_class' ) );
	
	$data = array();
	
	if ( !empty( $class ) ) {
	    
	    $data = array(
		'status'	=> $class->fk_id_perscholarity == $this->_getParam( 'scholarity' ) && $class->fk_id_fefpeduinstitution == $this->_getParam( 'institute' ),
		'id'		=> $class->id_fefpstudentclass,
		'class'		=> str_pad( $class->id_fefpstudentclass, 5, '0', STR_PAD_LEFT ) . ' - ' . $class->class_name,
		'date_start'	=> $this->view->date( $class->start_date ),
		'date_finish'	=> $this->view->date( $class->real_finish_date )
	    );
	}
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function listClientAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	$this->view->rows = $mapperStudentClass->listClientClass( $id );
    }
    
    /**
     * 
     */
    public function listBeneficiariesAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	$this->view->rows = $this->_mapper->listBeneficiaries( $id );
	
	$this->_helper->viewRenderer->setRender( 'list-client' );
    }
    
    /**
     * 
     */
    public function calcUnitCostAction()
    {
	$cost = App_General_String::toFloat( $this->_getParam( 'cost' ) );
	$idClass = $this->_getParam( 'id_class' );
	
	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	$clients = $mapperStudentClass->listClientClass( $idClass );
	
	$data = array(
	    'total' => 0,
	    'costs' => array(),
	    'man'   => 0,
	    'woman' => 0
	);
	
	if ( $clients->count() ) {
	    
	    foreach ( $clients as $client ) {

		$value = $cost;

		if ( !empty( $client->id_handicapped ) )
		    $value = $value * 1.25;

		$data['costs'][$client->id_perdata] = number_format( $value, 2, '.', ',' );
		$data['total'] += $value;

		if ( 'MANE' == $client->gender )
		    $data['man']++;
		else
		    $data['woman']++;
	    }

	    $womanPercentage = floor( ( $data['woman'] * 100 ) / $clients->count() );
	    if ( $womanPercentage >= 40 && $womanPercentage <= 70 )
		$data['total'] = $data['total'] * 1.10;
	}
	
	$data['total'] = number_format( $data['total'], 2, '.', ',' );
	
	$this->_helper->json( $data );
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
	$contract = $this->_mapper->detail( $id );
	
	$data = $contract->toArray();
	$data['contract'] = Fefop_Model_Mapper_Contract::buildNumById( $contract->fk_id_fefop_contract );
	$data['date_start'] = $this->view->date( $data['date_start'] );
	$data['date_finish'] = $this->view->date( $data['date_finish'] );
	$data['date_inserted'] = $this->view->date( $data['date_inserted'] );
	
	$mapperInstitute = new Register_Model_Mapper_EducationInstitute();
	$contacts = $mapperInstitute->listContacts( $contract->fk_id_fefpeduinstitution )->toArray();
	
	$beneficiaries = $this->_mapper->listBeneficiaries( $id )->toArray();
	foreach ( $beneficiaries as $key => $beneficiary ) {
	 
	    $beneficiaries[$key]['complete_nane'] = Client_Model_Mapper_Client::buildName( $beneficiary );
	    $beneficiaries[$key]['evidence'] = Client_Model_Mapper_Client::buildNumRow( $beneficiary );
	}
	
	$excelPath = APPLICATION_PATH . '/../library/PHPExcel/';
	require_once( $excelPath . 'PHPExcel/IOFactory.php' );
	
	$objReader = PHPExcel_IOFactory::createReader( 'Excel2007' );
	$objPHPExcel = $objReader->load( APPLICATION_PATH . '/../public/forms/FEFOP/Contrato_FP_tet.xlsx' );
	$activeSheet = $objPHPExcel->getActiveSheet();
	
	$activeSheet->setCellValue( 'S14', $data['contract'] );
	$activeSheet->setCellValue( 'W12', $data['date_inserted'] );
	$activeSheet->setCellValue( 'E17', $data['scholarity'] );
	$activeSheet->setCellValue( 'O17', $data['level_scholarity'] );
	$activeSheet->setCellValue( 'S17', $data['class_name'] );
	$activeSheet->setCellValue( 'E20', $data['district'] );
	$activeSheet->setCellValue( 'Q20', $data['date_start'] );
	$activeSheet->setCellValue( 'Q21', $data['date_finish'] );
	#$activeSheet->setCellValue( 'F10', $data['institute'] );
	
	if ( !empty( $contacts ) ) {
	    
	    $activeSheet->setCellValue( 'G24', $contacts[0]['contact_name'] );
	    $activeSheet->setCellValue( 'S24', $contacts[0]['cell_fone'] );
	    $activeSheet->setCellValue( 'S25', $contacts[0]['email'] );
	}
	
	$activeSheet->setCellValue( 'R31', $data['unit_cost'] );
	$activeSheet->setCellValue( 'J29', count( $beneficiaries ) );
	$activeSheet->setCellValue( 'R29', $data['amount'] );
	
	$startRow = 34;
	foreach ( $beneficiaries as $beneficiary ) {
	    
	    $activeSheet->setCellValue( 'F' . $startRow, $beneficiary['complete_nane'] );
	    $activeSheet->setCellValue( 'L' . $startRow, $beneficiary['evidence'] );
	    $activeSheet->setCellValue( 'N' . $startRow, $beneficiary['electoral'] );
	    $activeSheet->setCellValue( 'P' . $startRow, $beneficiary['gender'] );
	    $activeSheet->setCellValue( 'Q' . $startRow, empty( $beneficiary['id_handicapped'] ) ? 'Lae' : 'Sin' );
	    $activeSheet->setCellValue( 'R' . $startRow, $beneficiary['amount'] );
	    
	    $startRow++;
	}
	
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	$file = sprintf( 'Contract_%s.xlsx', $data['contract'] );
	header(sprintf('Content-Disposition: attachment;filename="%s"', $file));
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel2007' );
	$objWriter->save( 'php://output' );
	exit;
    }
}