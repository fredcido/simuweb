<?php

/**
 * 
 */
class Fefop_RiContractController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_RIContract
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_RIContract();
	
	$stepBreadCrumb = array(
	    'label' => 'Kontratu Reforço Institucional',
	    'url'   => 'fefop/ri-contract'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Kontratu Reforço Institucional' );
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
		'url'	=> 'fefop/ri-contract'
	    );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Kontraktu',
		'url'	=> 'fefop/ri-contract/edit/id/' . $id
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
	    
	    $mapperSubDistrict = new Register_Model_Mapper_AddSubDistrict();
	    $rows = $mapperSubDistrict->listAll( $data['fk_id_adddistrict'] );

	    $opts = array( '' => '' );
	    foreach( $rows as $row )
		$opts[$row->id_addsubdistrict] = $row->sub_district;
	    
	    $formInformation->getElement( 'fk_id_addsubdistrict' )->addMultiOptions( $opts );
	    
	    $formInformation->populate( $data );
	    $formInformation->getElement('fk_id_adddistrict')->setAttrib( 'readonly', true ); 
	    
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
	    
	    if ( !$this->view->fefopContract( $data['id_fefop_contract'] )->isEditable() )
		$formInformation->removeDisplayGroup( 'toolbar' );
	    
	    $this->view->itemsExpense = $dataItensExpense;
	    
	} else {
	    
	    // Fetch the Expenses related to the RI module
	    $mapperBudgetCategory = new Fefop_Model_Mapper_Expense();
	    $this->view->expenses = $mapperBudgetCategory->expensesInItem( Fefop_Model_Mapper_Expense::CONFIG_PFPCI_RI );
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

            $this->_form = new Fefop_Form_RIContract();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$searchRIContract = new Fefop_Form_RIContractSearch();
	$searchRIContract->setAction( $this->_helper->url( 'search-ri-contract' ) );
	
	$this->view->menu()->setActivePath( 'fefop/ri-contract/list' );
     
	$this->view->form = $searchRIContract;
    }
    
    /**
     * 
     */
    public function searchRiContractAction()
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
	$id = $this->_getParam( 'id' );
	$institute = $mapperInsitute->detailEducationInstitution( $id );
	
	$data = array();
	$data['fk_id_fefpeduinstitution'] = $institute['id_fefpeduinstitution'];
	$data['institute'] = $institute['institution'];
	
	$addresses = $mapperInsitute->listAddress( $id );
	if ( $addresses->count() > 0 ) {
	    
	    $data['district'] = $addresses[0]['fk_id_adddistrict'];
	    $data['sub_district'] = $addresses[0]['fk_id_addsubdistrict'];
	}
	
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
    public function exportAction()
    {
	$id = $this->_getParam( 'id' );
	$contract = $this->_mapper->detail( $id );
	
	$data = $contract->toArray();
	
	$data['contract'] = Fefop_Model_Mapper_Contract::buildNumById( $contract->fk_id_fefop_contract );
	$data['date_start'] = $this->view->date( $data['date_start'], 'MM/dd/yyyy' );
	$data['date_finish'] = $this->view->date( $data['date_finish'], 'MM/dd/yyyy' );
	$data['date_inserted'] = $this->view->date( $data['date_inserted'] );
	
	$mapperInstitute = new Register_Model_Mapper_EducationInstitute();
	$contacts = $mapperInstitute->listContacts( $data['fk_id_fefpeduinstitution'] )->toArray();
	
	$expenses = $this->_mapper->listExpenses( $id )->toArray();
	$itensExpense = $this->_mapper->listItemExpenses( $id )->toArray();
	
	$dataItensExpense = array();
	foreach ( $itensExpense as $item ) {

	    if ( !array_key_exists( $item['fk_id_budget_category'], $dataItensExpense ) )
		$dataItensExpense[$item['fk_id_budget_category']] = array();

	    $dataItensExpense[$item['fk_id_budget_category']][] = $item;
	}
	
	$excelPath = APPLICATION_PATH . '/../library/PHPExcel/';
	require_once( $excelPath . 'PHPExcel/IOFactory.php' );
	
	$objReader = PHPExcel_IOFactory::createReader( 'Excel2007' );
	$objPHPExcel = $objReader->load( APPLICATION_PATH . '/../public/forms/FEFOP/Contrato_RI_tet.xlsx' );
	$activeSheet = $objPHPExcel->getActiveSheet();

	$activeSheet->setCellValue( 'S10', $data['contract'] );
	$activeSheet->setCellValue( 'W8', $data['date_inserted'] );
	$activeSheet->setCellValue( 'F12', $data['institute'] );
	$activeSheet->setCellValue( 'F14', $data['district'] );
	$activeSheet->setCellValue( 'F15', $data['sub_district'] );

	if ( !empty( $contacts ) ) {
	    
	    $activeSheet->setCellValue( 'G18', $contacts[0]['contact_name'] );
	    $activeSheet->setCellValue( 'S18', $contacts[0]['cell_fone'] );
	    $activeSheet->setCellValue( 'S19', $contacts[0]['email'] );
	}
	
	$startRow = 24;
	$startSubRow = 35;
	$count = 'A';
	
	$styleCell = new PHPExcel_Style();
	$borders = array(
	    'allborders' => array(
		'style' => PHPExcel_Style_Border::BORDER_THIN,
		'color'	=>  array( 'argb' => '0000000' )
	   )
	);
	$styleCell->applyFromArray(
			    array( 
				'fill' => array(
				    'type'  => PHPExcel_Style_Fill::FILL_SOLID,
				    'color' => array( 'argb' => 'FFFFFFFF' )
				),
				'borders' => $borders
			    )
			);
	
	$ranges = array();
	
	foreach ( $expenses as $expense ) {
	    
	    $marker = $count++  . '.';
	    $expenseName = ucwords( strtolower( $expense['description'] ) );
	    
	    $activeSheet->setCellValue( 'C' . $startRow, $marker );
	    $activeSheet->setCellValue( 'D' . $startRow, $expenseName );
	    $activeSheet->setCellValueExplicit( 'U' . $startRow, $expense['amount'], PHPExcel_Cell_DataType::TYPE_NUMERIC );
	    
	    $activeSheet->mergeCells( 'U' . $startRow . ':V' . $startRow );
	    
	    $activeSheet->setCellValue( 'C' . $startSubRow, $marker );
	    $activeSheet->setCellValue( 'D' . $startSubRow, $expenseName );
	    
	    $subExpenseRow = $startSubRow + 1;
	    $subRowCount = 0;
	    if ( !empty( $dataItensExpense[$expense['id_budget_category']] ) ) {
		
		foreach ( $dataItensExpense[$expense['id_budget_category']] as $subExpense ) {
		    
		    $activeSheet->setCellValue( 'D' . $subExpenseRow, $subExpense['description'] );
		    $activeSheet->setCellValue( 'O' . $subExpenseRow, $subExpense['quantity'] );
		    $activeSheet->setCellValue( 'P' . $subExpenseRow, number_format( (float)$subExpense['amount_unit'], 2, '.', ',' ) );
		    $activeSheet->setCellValue( 'Q' . $subExpenseRow, number_format( (float)$subExpense['amount_total'], 2, '.', ',' ) );
		    $activeSheet->setCellValue( 'S' . $subExpenseRow, $subExpense['comments'] );
		    
		    $activeSheet->mergeCells( 'Q' . $subExpenseRow . ':R' . $subExpenseRow );
		    $activeSheet->mergeCells( 'S' . $subExpenseRow . ':V' . $subExpenseRow );
		    
		    $subRowCount++;
		    $subExpenseRow++;
		    
		    $activeSheet->insertNewRowBefore( $subExpenseRow + 1, 1 );
		}
		
		$ranges[] = 'O' . ( $startSubRow + 2 ) . ':V' . ( $subExpenseRow );
	    }
	    
	    $activeSheet->insertNewRowBefore( $subExpenseRow, 3 );
	    $startSubRow = $subExpenseRow + 2;
	    
	    $startRow++;
		
	    if ( $startRow > 28 )
		$activeSheet->insertNewRowBefore( $startRow + 1, 1 );
	}
	
	$activeSheet->removeRow( $startSubRow, 4 );
	
	foreach ( $ranges as $range )
	    $activeSheet->setSharedStyle( $styleCell, $range );
	
	$dateStartCell = 'G' .( $startSubRow + 2 );
	$dateFinishCell = 'G' .( $startSubRow + 3 );
	$formulaDate = $activeSheet->getCell( 'L' .( $startSubRow + 2 ) )->getValue();
	
	$formulaDate = str_replace( '98', $dateStartCell, $formulaDate );
	$formulaDate = str_replace( '99', $dateFinishCell, $formulaDate );
	$activeSheet->setCellValueExplicit( 'L' .( $startSubRow + 2 ), $formulaDate, PHPExcel_Cell_DataType::TYPE_FORMULA );
	
	$activeSheet->setCellValue( $dateStartCell, $data['date_start'] );
	$activeSheet->setCellValue( $dateFinishCell, $data['date_finish'] );
	
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	$file = sprintf( 'Contract_%s.xlsx', $data['contract'] );
	header(sprintf('Content-Disposition: attachment;filename="%s"', $file));
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel2007' );
	$objWriter->save( 'php://output' );
	exit;
    }
}