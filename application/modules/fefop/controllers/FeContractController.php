<?php

/**
 * 
 */
class Fefop_FeContractController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_FEContract
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_FEContract();
	
	$stepBreadCrumb = array(
	    'label' => 'Kontratu Formasaun Iha Servisu Fatin',
	    'url'   => 'fefop/fe-contract'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Kontratu Formasaun Iha Servisu Fatin' );
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
		'url'	=> 'fefop/fe-contract'
	    );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Kontraktu',
		'url'	=> 'fefop/fe-contract/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->detail( $id );
	    if ( empty( $row ) )
		$this->_helper->redirector->goToSimple( 'index' );
	    
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
	    
	    $data['date_start'] = $this->view->date( $data['date_start'] );
	    $data['date_finish'] = $this->view->date( $data['date_finish'] );
	    $data['date_formation'] = $this->view->date( $data['date_formation'] );
	    
	    $mapperSubDistrict = new Register_Model_Mapper_AddSubDistrict();
	    $rows = $mapperSubDistrict->listAll( $data['fk_id_adddistrict'] );

	    $opts = array( array( '' => '' ) );
	    foreach( $rows as $row )
		$opts[$row->id_addsubdistrict] = $row->sub_district;
	    
	    $formInformation->getElement( 'fk_id_addsubdistrict' )->addMultiOptions( $opts );
	    $formInformation->populate( $data );
	    $formInformation->getElement('fk_id_adddistrict')->setAttrib( 'readonly', true ); 
	    
	    // List the expenses related to the contract
	    $this->view->expenses = $this->_mapper->listExpenses( $id );
	    
	    if ( !$this->view->fefopContract( $data['id_fefop_contract'] )->isEditable() )
		$formInformation->removeDisplayGroup( 'toolbar' );
	    
	} else {
	    
	    // Fetch the Expenses related to the FE module
	    $mapperBudgetCategory = new Fefop_Model_Mapper_Expense();
	    $this->view->expenses = $mapperBudgetCategory->expensesInItem( Fefop_Model_Mapper_Expense::CONFIG_PISE_FE );
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

            $this->_form = new Fefop_Form_FEContract();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$searchFEContract = new Fefop_Form_FEContractSearch();
	$searchFEContract->setAction( $this->_helper->url( 'search-fe-contract' ) );
	
	$this->view->menu()->setActivePath( 'fefop/fe-contract/list' );
     
	$this->view->form = $searchFEContract;
    }
    
    /**
     * 
     */
    public function searchFeContractAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
    }
    
    /**
     * 
     */
    public function searchInstituteAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'education-institution', 'register' );
    }
    
    /**
     * 
     */
    public function searchInstituteForwardAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'search-institution', 'education-institution', 'register' );
    }
    
    /**
     * 
     */
    public function fetchInstituteAction()
    {
	$mapperInsitute = new Register_Model_Mapper_EducationInstitute();
	$institute = $mapperInsitute->detailEducationInstitution( $this->_getParam( 'id' ) );
	
	$data = array();
	$data['fk_id_fefpeduinstitution'] = $institute['id_fefpeduinstitution'];
	$data['entity'] = $institute['institution'];
	
	$this->_helper->json( $data );
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
	$data['entity'] = $enterprise['enterprise_name'];
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function searchRegistrationAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'fe-registration', 'fefop' );
    }
    
    /**
     * 
     */
    public function searchRegistrationForwardAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'search-fe-registration', 'fe-registration', 'fefop' );
    }
    
    /**
     * 
     */
    public function fetchRegistrationAction()
    {
	$id = $this->_getParam( 'id' );
	
	$mapperFeRegistration = new Fefop_Model_Mapper_FERegistration();
	$registration = $mapperFeRegistration->detail( $id );
	
	$data = $registration->toArray();
	$data['fk_id_perdata'] = $data['id_perdata'];
	$data['fk_id_fe_registration'] = $data['id_fe_registration'];
	$data['beneficiary'] = Client_Model_Mapper_Client::buildName( $registration );
	
	$formation = $mapperFeRegistration->groupFormation( $id );
	$data['fk_id_scholarity_area'] = $formation['selected']['area'];
	$data['fk_id_profocupationtimor'] = $formation['selected']['occupation'];
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function selectEntityAction()
    {
	$data = $this->_getAllParams();
	
	$this->_helper->layout()->disableLayout();
	$this->view->registration = $data;
	
	$mapperFeRegistration = new Fefop_Model_Mapper_FERegistration();
	$this->view->entities = $mapperFeRegistration->listEntities( $data['id_fe_registration'] );
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
    public function searchSubDistrictAction()
    {
	$mapperSubDistrict = new Register_Model_Mapper_AddSubDistrict();
	$rows = $mapperSubDistrict->listAll( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_addsubdistrict, 'name' => $row->sub_district );
	
	$this->_helper->json( $opts );
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
    public function calcDiffMonthAction()
    {
	$dateInit = new Zend_Date( $this->_getParam( 'date_start' ) );
	$dateFinish = new Zend_Date( $this->_getParam( 'date_finish' ) );
	
	$diff = $dateFinish->sub( $dateInit );
	
	$measure = new Zend_Measure_Time( $diff->toValue(), Zend_Measure_Time::SECOND );
	$diffMonths = $measure->convertTo( Zend_Measure_Time::MONTH, 0 );
	
	$this->_helper->json( array( 'diff' => preg_replace( '/[^0-9]/i', '', $diffMonths ) ) );
    }
    
    /**
     * 
     */
    public function searchCourseAction()
    {
	$category = $this->_getParam( 'category' );
	$institute = $this->_getParam( 'institute' );
	
	$filters = array(
	    'type'	    => Register_Model_Mapper_PerTypeScholarity::NON_FORMAL,
	    'category'	    => $category,
	    'institution'   => $institute
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
    public function searchTraineeAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list-trainee', 'job-training', 'student-class' );
    }
    
    /**
     * 
     */
    public function searchTraineeForwardAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'search-job-training-trainee', 'job-training', 'student-class' );
    }
    
     /**
     * 
     */
    public function fetchTraineeAction()
    {
	$id = $this->_getParam( 'id' );
	
	$mapperJobTraining = new StudentClass_Model_Mapper_JobTraining();
	$trainee = $mapperJobTraining->fetchTrainee( $id );
	
	$data = array();
	
	$mapperClient = new Client_Model_Mapper_Client();
	$client = $mapperClient->detailClient( $trainee->fk_id_perdata );
	
	$contract = $this->_mapper->getContractByTrainee( $id );
	
	if ( !empty( $contract ) ) {
	    
	    $data['valid'] = false;
	    
	} else {
	
	    $data['valid'] = true;
	    $data['fk_id_trainee'] = $trainee->id_trainee;
	    $data['fk_id_fefpenterprise'] = $trainee->fk_id_fefpenterprise;
	    $data['fk_id_fefpeduinstitution'] = $trainee->fk_id_fefpeduinstitution;
	    $data['entity'] = $trainee->entity;
	    $data['fk_id_scholarity_area'] = $trainee->fk_id_scholarity_area;
	    $data['beneficiary'] = Client_Model_Mapper_Client::buildName( $client );
	    $data['fk_id_perdata'] = $trainee->fk_id_perdata;
	    $data['date_start'] = $trainee->date_start_formated;
	    $data['date_finish'] = $trainee->date_finish_formated;
	    $data['duration_month'] = $trainee->duration;
	}
	
	$this->_helper->json( $data );
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
	$data['evidence'] = Client_Model_Mapper_Client::buildNumById( $contract->fk_id_perdata );
	$data['date_start'] = $this->view->date( $data['date_start'] );
	$data['date_finish'] = $this->view->date( $data['date_finish'] );
	$data['date_formation'] = $this->view->date( $data['date_formation'] );
	$data['date_inserted'] = $this->view->date( $data['date_inserted'] );
	
	$expenses = $this->_mapper->listExpenses( $id );
	$data['expenses'] = $expenses->toArray();
	
	$excelPath = APPLICATION_PATH . '/../library/PHPExcel/';
	require_once( $excelPath . 'PHPExcel/IOFactory.php' );
	
	$objReader = PHPExcel_IOFactory::createReader( 'Excel2007' );
	$objPHPExcel = $objReader->load( APPLICATION_PATH . '/../public/forms/FEFOP/Contrato_FE_tet.xlsx' );
	$activeSheet = $objPHPExcel->getActiveSheet();
	
	$activeSheet->setCellValue( 'R10', $data['contract'] );
	$activeSheet->setCellValue( 'V8', $data['date_inserted'] );
	$activeSheet->setCellValue( 'F15', $data['scholarity_area'] );
	$activeSheet->setCellValue( 'F16', $data['ocupation_name_timor'] );
	$activeSheet->setCellValue( 'G21', $data['date_start'] );
	$activeSheet->setCellValue( 'G22', $data['date_finish'] );
	$activeSheet->setCellValue( 'K21', (int)$data['duration_month'] );
	$activeSheet->setCellValue( 'Q21', $data['district'] );
	$activeSheet->setCellValue( 'Q22', $data['sub_district'] );
	$activeSheet->setCellValue( 'H28', $data['entity'] );
	$activeSheet->setCellValue( 'H29', $data['beneficiary'] );
	$activeSheet->setCellValue( 'E117', $data['beneficiary'] );
	$activeSheet->setCellValue( 'S29', $data['evidence'] );
	//$activeSheet->setCellValue( 'U28', $data['date_formation'] );
	
	// Expenses
	$startRow = 34;
	foreach ( $data['expenses'] as $expense ) {
	    $activeSheet->setCellValue( 'D' . $startRow, $expense['description'] );
	    $activeSheet->setCellValue( 'U' . $startRow, $expense['amount'] );
	    
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