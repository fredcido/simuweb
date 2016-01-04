<?php

/**
 * 
 */
class Fefop_DrhTrainingPlanController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_DRHTrainingPlan
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_DRHTrainingPlan();
	
	$stepBreadCrumb = array(
	    'label' => 'DRH - Planu ba Formasaun',
	    'url'   => 'fefop/drh-training-plan'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'DRH - Planu ba Formasaun' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$id = $this->_getParam( 'id' );
	// Form
	$form = $this->_getForm( $this->_helper->url( 'save' ) );

	if ( empty( $id ) ) {

	    $stepBreadCrumb = array(
		'label' => 'DRH - Rejistu Planu ba Formasaun',
		'url'	=> 'fefop/drh-training-plan'
	    );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'DRH - Edita Planu ba Formasaun',
		'url'	=> 'fefop/drh-training-plan/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->detail( $id );
	    if ( empty( $row ) )
		$this->_helper->redirector->goToSimple( 'index' );
	    
	    $data = $row->toArray();
	    $data['date_start'] = $this->view->date( $data['date_start'] );
	    $data['date_finish'] = $this->view->date( $data['date_finish'] );
	    $data['country'] = $data['fk_id_addcountry'];
	    
	    $form->populate( $data );
	    
	    // List the beneficiaries
	    $this->view->beneficiaries = $this->_mapper->listBeneficiaries( $id );
	  
	    // List the Expenses
	    $this->view->expenses = $this->_mapper->listExpenses( $id );
	}

	$this->view->id = $id;
	$this->view->form = $form;
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
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Fefop_Form_DRHTrainingPlan();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$searchDRHTrainingPlan=  new Fefop_Form_DRHTrainingPlanSearch();
	$searchDRHTrainingPlan->setAction( $this->_helper->url( 'search-drh-training-plan' ) );
	
	$this->view->menu()->setActivePath( 'fefop/drh-training-plan/list' );
     
	$this->view->form = $searchDRHTrainingPlan;
    }
    
    /**
     * 
     */
    public function searchDrhTrainingPlanAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
	$this->view->listAjax = $this->_getParam( 'list-ajax' );
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
     */
    public function listStaffAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$mapperInstitute = new Register_Model_Mapper_EducationInstitute();
	$this->view->rows = $mapperInstitute->listStaff( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function addStaffAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$defaultValues = array(
	    'unit_cost'	    => 0,
	    'final_cost'    => 0,
	    'training_fund' => 0
	);
	
	$row = $this->_getParam( 'row' );
	
	if ( !empty( $row ) ) {
	  
	    $defaultValues = $row->toArray();
	    $this->view->staff = $row;
	    
	} else {
	    
	    $mapperInstitute = new Register_Model_Mapper_EducationInstitute();
	    $staff = $mapperInstitute->fetchStaff( $this->_getParam( 'id' ) );

	    $this->view->staff = $staff;
	}
	
	$this->view->defaultValues = $defaultValues;
    }
    
    /**
     * 
     */
    public function businessExpenseAction()
    {
	 $this->_helper->layout()->disableLayout();
	 
	 $mapperBusinessExpense = new Fefop_Model_Mapper_Expense();
	 $this->view->rows = $mapperBusinessExpense->expensesInItem( Fefop_Model_Mapper_Expense::CONFIG_PFPCI_DRH_PLAN );
    }
    
    /**
     * 
     */
    public function addExpenseAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$defaultValues = array(
	    'amount' => 0
	);
	
	$row = $this->_getParam( 'row' );
	
	if ( !empty( $row ) ) {
	    
	    $defaultValues = $row->toArray();
	    $this->view->expense = $row;
	    
	} else {
	    
	    $mapperBusinessExpense = new Fefop_Model_Mapper_Expense();
	    $expense = $mapperBusinessExpense->fetchRow( $this->_getParam( 'id' ) );

	    $this->view->expense = $expense;
	}
	
	$this->view->defaultValues = $defaultValues;
    }
    
    /**
     * 
     */
    public function calcTotalsAction()
    {
	$totals = $this->_mapper->calcTotals( $this->_getAllParams() );
	$this->_helper->json( $totals );
    }
    
    /**
     * 
     */
    public function fetchNumTrainingPlanAction()
    {
	$num = Fefop_Model_Mapper_DRHTrainingPlan::buildNum( $this->_getParam( 'id' ) );
	$this->_helper->json( array( 'num' => $num ) );
    }
    
    /**
     * 
     */
    public function listBeneficiaryAction()
    {
	$searchDRHBeneficiary =  new Fefop_Form_DRHBeneficiarySearch();
	$searchDRHBeneficiary ->setAction( $this->_helper->url( 'search-drh-beneficiary' ) );
	
	$this->view->menu()->setActivePath( 'fefop/drh-training-plan/list' );
	$this->view->form = $searchDRHBeneficiary ;
    }
    
    /**
     * 
     */
    public function searchBeneficiaryAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listBeneficiariesByFilter( $this->_getAllParams() );
    }
}