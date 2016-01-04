<?php

/**
 * 
 */
class Fefop_DrhContractController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_DRHContract
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_DRHContract();
	
	$stepBreadCrumb = array(
	    'label' => 'Kontratu DRH',
	    'url'   => 'fefop/drh-contract'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Kontratu DRH' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$id = $this->_getParam( 'id' );

	if ( empty( $id ) ) {

	    $stepBreadCrumb = array(
		'label' => 'Rejistu Kontratu',
		'url'	=> 'fefop/drh-contract'
	    );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Kontratu',
		'url'	=> 'fefop/drh-contract/edit/id/' . $id
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
	    $data['num_training_plan'] = Fefop_Model_Mapper_DRHTrainingPlan::buildNum( $data['fk_id_drh_trainingplan'] );
	    $data['beneficiary'] = $data['staff_name'];
	    $data['training_provider'] = $data['institution'];
	    $data['modality'] = $this->view->nomenclature()->drhModality( $data['modality'] );
	    
	    $formInformation->populate( $data );
	    $formInformation->getElement('fk_id_adddistrict')->setAttrib( 'readonly', true ); 
	    
	    if ( !$this->view->fefopContract( $data['id_fefop_contract'] )->isEditable() )
		$formInformation->removeDisplayGroup( 'toolbar' );
	    
	    // List the expenses related to the contract
	    $this->view->expenses = $this->_mapper->listExpenses( $id );
	    
	} else {
	    
	    // Fetch the Expenses related to the DRH module
	    $mapperBudgetCategory = new Fefop_Model_Mapper_Expense();
	    $this->view->expenses = $mapperBudgetCategory->expensesInItem( Fefop_Model_Mapper_Expense::CONFIG_PFPCI_DRH );
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

            $this->_form = new Fefop_Form_DRHContract();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$searchDRHContract = new Fefop_Form_DRHContractSearch();
	$searchDRHContract->setAction( $this->_helper->url( 'search-drh-contract' ) );
	
	$this->view->menu()->setActivePath( 'fefop/drh-contract/list' );
     
	$this->view->form = $searchDRHContract;
    }
    
    /**
     * 
     */
    public function searchDrhContractAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
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
    public function searchPlanningAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list-beneficiary', 'drh-training-plan', 'fefop' );
    }
    
    /**
     * 
     */
    public function searchPlanningForwardAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'search-beneficiary', 'drh-training-plan', 'fefop' );
    }
    
     /**
     * 
     */
    public function fetchStaffAction()
    {
	$id = $this->_getParam( 'id' );
	
	$mapperDRHTrainingPlan = new Fefop_Model_Mapper_DRHTrainingPlan();
	$beneficiary = $mapperDRHTrainingPlan->fetchBeneficiary( $id );
	
	$data = array();
	
	if ( !empty( $beneficiary->id_drh_contract ) ) {
	    
	    $data['valid'] = false;
	    
	} else {
	    
	    $data['valid'] = true;
	    $data['fk_id_drh_trainingplan'] = $beneficiary->id_drh_trainingplan;
	    $data['fk_id_perdata'] = $beneficiary->fk_id_perdata;
	    $data['id_staff'] = $beneficiary->id_staff;
	    $data['fk_id_drh_beneficiary'] = $beneficiary->id_drh_beneficiary;
	    $data['training_provider'] = $beneficiary->institution;
	    $data['modality'] = $this->view->nomenclature()->drhModality( $beneficiary->modality );
	    $data['scholarity_area'] = $beneficiary->scholarity_area;
	    $data['ocupation_name_timor'] = $beneficiary->ocupation_name_timor;
	    $data['country'] = $beneficiary->country;
	    $data['city'] = $beneficiary->city;
	    $data['date_start'] = $this->view->date( $beneficiary->date_start );
	    $data['date_finish'] = $this->view->date( $beneficiary->date_finish );
	    $data['entity'] = $beneficiary->entity;
	    $data['beneficiary'] = $beneficiary->staff_name;
	    $data['duration_days'] = $beneficiary->duration_days;
	    $data['num_training_plan'] = Fefop_Model_Mapper_DRHTrainingPlan::buildNum( $beneficiary->id_drh_trainingplan );
	    $data['unit_cost'] = (float)$beneficiary->unit_cost;
	    $data['final_cost'] = (float)$beneficiary->final_cost;
	    $data['training_fund'] = (float)$beneficiary->training_fund;
	}
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function calcDiffDateAction()
    {
	$dateInit = new Zend_Date( $this->_getParam( 'date_start' ) );
	$dateFinish = new Zend_Date( $this->_getParam( 'date_finish' ) );
	
	$diff = $dateFinish->sub( $dateInit );
	
	$measure = new Zend_Measure_Time( $diff->toValue(), Zend_Measure_Time::SECOND );
	$diffMonths = $measure->convertTo( Zend_Measure_Time::DAY, 0 );
	
	$this->_helper->json( array( 'diff' => preg_replace( '/[^0-9]/i', '', $diffMonths ) + 1 ) );
    }
    
    /**
     * 
     * @param int $id
     * @return array
     */
    protected function _contractToExport( $id )
    {
	$contract = $this->_mapper->detail( $id );
	
	$data = $contract->toArray();
	$data['contract'] = Fefop_Model_Mapper_Contract::buildNumById( $contract->fk_id_fefop_contract );
	$data['evidence'] = Client_Model_Mapper_Client::buildNumById( $contract->fk_id_perdata );
	$data['formation_plan'] = Fefop_Model_Mapper_DRHTrainingPlan::buildNum( $contract->fk_id_drh_trainingplan );
	$data['date_start'] = $this->view->date( $data['date_start'] );
	$data['date_finish'] = $this->view->date( $data['date_finish'] );
	$data['date_inserted'] = $this->view->date( $data['date_inserted'] );
	$data['modality'] = $this->view->nomenclature()->drhModality( $data['modality'] );
	
	$expenses = $this->_mapper->listExpenses( $id );
	$data['expenses'] = $expenses->toArray();
	
	return $data;
    }
    
    protected function _contractToExcel( $data )
    {
	$excelPath = APPLICATION_PATH . '/../library/PHPExcel/';
	require_once( $excelPath . 'PHPExcel/IOFactory.php' );
	
	$objReader = PHPExcel_IOFactory::createReader( 'Excel2007' );
	$objPHPExcel = $objReader->load( APPLICATION_PATH . '/../public/forms/FEFOP/Contrato_DRH_tet.xlsx' );
	$activeSheet = $objPHPExcel->getActiveSheet();
	
	$activeSheet->setCellValue( 'S10', $data['contract'] );
	$activeSheet->setCellValue( 'W8', $data['date_inserted'] );
	$activeSheet->setCellValue( 'G13', $data['formation_plan'] );
	$activeSheet->setCellValue( 'S13', $data['modality'] );
	$activeSheet->setCellValue( 'F17', $data['scholarity_area'] );
	$activeSheet->setCellValue( 'F18', $data['ocupation_name_timor'] );
	$activeSheet->setCellValue( 'G23', $data['date_start'] );
	$activeSheet->setCellValue( 'G24', $data['date_finish'] );
	$activeSheet->setCellValue( 'K23', $data['duration_days'] );
	$activeSheet->setCellValue( 'R23', $data['country'] );
	$activeSheet->setCellValue( 'R24', $data['city'] );
	$activeSheet->setCellValue( 'I27', $data['entity'] );
	$activeSheet->setCellValue( 'H32', $data['institution'] );
	$activeSheet->setCellValue( 'H33', $data['staff_name'] );
	$activeSheet->setCellValue( 'E115', $data['staff_name'] );
	$activeSheet->setCellValue( 'T33', $data['evidence'] );
	
	// Expenses
	$activeSheet->setCellValue( 'D38', $data['expenses'][0]['description'] );
	$activeSheet->setCellValue( 'T38', $data['expenses'][0]['amount'] );
	$activeSheet->setCellValue( 'D39', $data['expenses'][1]['description'] );
	$activeSheet->setCellValue( 'T39', $data['expenses'][1]['amount'] );
	
	return $objPHPExcel;
    }
    
    /**
     * 
     */
    public function exportAction()
    {
	$id = $this->_getParam( 'id' );
	$data = $this->_contractToExport( $id );
	
	$objPHPExcel = $this->_contractToExcel( $data );
	
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	$file = sprintf( 'Contract_%s.xlsx', $data['contract'] );
	header(sprintf('Content-Disposition: attachment;filename="%s"', $file));
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel2007' );
	$objWriter->save( 'php://output' );
	exit;
    }
    
    /**
     * 
     */
    public function contractsAction()
    {
	$this->view->menu()->setActivePath( 'fefop/drh-contract/contracts' );
	
	$stepBreadCrumb = array(
	    'label' => 'Kontratu Sira DRH',
	    'url'   => 'fefop/drh-contract/contracts'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Kontratu Sira DRH' );
	
	$formBulk = new Fefop_Form_DRHBulkContract();
	$formBulk->setAction( $this->_helper->url( 'save-contracts' ) );
	
	$this->view->form = $formBulk;
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function saveContractsAction()
    {
	$form = new Fefop_Form_DRHBulkContract();
	
	if ( $this->getRequest()->isPost() ) {

	    if ( $form->isValid( $this->getRequest()->getPost() ) ) {
	    	
		$this->_mapper->setData( $form->getValues() );
		$return = $this->_mapper->saveContracts();
			
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
		    'status' => false,
		    'description' => $message->toArray(),
		    'errors' => $form->getMessages()
		);
	    }
	}

	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function planningBulkAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'drh-training-plan', 'fefop' );
    }
    
    /**
     * 
     */
    public function searchPlanningBulkAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'search-drh-training-plan', 'drh-training-plan', 'fefop' );
    }
    
    /**
     * 
     */
    public function fetchPlanningAction()
    {
	$id = $this->_getParam( 'id' );
	
	$mapperDRHTrainingPlan = new Fefop_Model_Mapper_DRHTrainingPlan();
	$beneficiary = $mapperDRHTrainingPlan->detail( $id );
	
	$data = array();
	$data['fk_id_drh_trainingplan'] = $beneficiary->id_drh_trainingplan;
	$data['modality'] = $this->view->nomenclature()->drhModality( $beneficiary->modality );
	$data['scholarity_area'] = $beneficiary->scholarity_area;
	$data['ocupation_name_timor'] = $beneficiary->ocupation_name_timor;
	$data['country'] = $beneficiary->country;
	$data['city'] = $beneficiary->city;
	$data['date_start'] = $this->view->date( $beneficiary->date_start );
	$data['date_finish'] = $this->view->date( $beneficiary->date_finish );
	$data['entity'] = $beneficiary->entity;
	$data['num_training_plan'] = Fefop_Model_Mapper_DRHTrainingPlan::buildNum( $beneficiary->id_drh_trainingplan );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function listContractsAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	// Fetch the Expenses related to the DRH module
	$mapperBudgetCategory = new Fefop_Model_Mapper_Expense();
	$this->view->expenses = $mapperBudgetCategory->expensesInItem( Fefop_Model_Mapper_Expense::CONFIG_PFPCI_DRH );
	
	$mapperDRHTrainingPlan = new Fefop_Model_Mapper_DRHTrainingPlan();
	$filters = array( 'no_contract' => true, 'training_plan' => $id );
	
	$this->view->beneficiaries = $mapperDRHTrainingPlan->listBeneficiariesByFilter( $filters );
	$this->view->training_plan = $mapperDRHTrainingPlan->detail($id);
    }
    
    public function exportContractsAction()
    {
	$ids = $this->_getParam( 'ids' );
	$ids = explode( ',', $ids );
	
	$contractFiles = array();
	foreach( $ids as $id ) {
	    $data = $this->_contractToExport( $id );
	    $objExcel = $this->_contractToExcel( $data );
	    
	    $file = sprintf( 'Contract_%s.xlsx', $data['contract'] );
	    $tmpName = tempnam( sys_get_temp_dir(), $data['contract'] );
	    
	    $objWriter = PHPExcel_IOFactory::createWriter( $objExcel, 'Excel2007' );
	    $objWriter->save( $tmpName );
	    
	    $contractFiles[$tmpName] = $file;
	}
	
	$zip = new ZipArchive();
	$destination = tempnam( sys_get_temp_dir(), 'Contracts' );
	$zip->open( $destination, ZIPARCHIVE::OVERWRITE );
	
	foreach( $contractFiles as $file => $name )
	    $zip->addFile( $file, $name );
	
	$zip->close();
	
	header("Content-Type: application/zip");
	$nameZip = sprintf('Contracts_DRH_%s.zip', date('Y_m_d_H_i') );
	header("Content-Disposition: attachment; filename=$nameZip");
	header("Content-Length: " . filesize( $destination ) );
	
	readfile( $destination );
	exit;
    }
}