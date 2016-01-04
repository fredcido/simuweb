<?php

/**
 * 
 */
class Fefop_EtcContractController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_PERContract
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_PERContract();
	
	$stepBreadCrumb = array(
	    'label' => 'Kontratu Empregu no Turismu ba Komunidade',
	    'url'   => 'fefop/etc-contract'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Kontratu Empregu no Turismu ba Komunidade' );
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
		'url'	=> 'fefop/etc-contract'
	    );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Kontraktu',
		'url'	=> 'fefop/etc-contract/edit/id/' . $id
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
	    
	    $mapperSuku = new Register_Model_Mapper_AddSuku();
	    $sukus = $mapperSuku->listAll( $data['fk_id_addsubdistrict'] );
	    
	    $opts = array( '' => '' );
	    foreach( $sukus as $row )
		$opts[$row->id_addsucu] = $row->sucu;
	    
	    $formInformation->getElement( 'fk_id_addsucu' )->addMultiOptions( $opts );
	    
	    $formInformation->populate( $data );
	    $formInformation->getElement('fk_id_adddistrict')->setAttrib( 'readonly', true ); 
	    
	     // List the expenses related to the contract
	    $expenses = $this->_mapper->listExpenses( $id );
	    $this->view->expense_detailed = $this->_mapper->expensesDetailed( $id, $expenses );
	    
	    $this->view->expenses = $expenses;
	    
	    if ( !$this->view->fefopContract( $data['id_fefop_contract'] )->isEditable() )
		$formInformation->removeDisplayGroup( 'toolbar' );
	    
	} else {
	    
	    // Fetch the Expenses related to the ETC module
	    $mapperBudgetCategory = new Fefop_Model_Mapper_Expense();
	    $this->view->expenses = $mapperBudgetCategory->expensesInItem( Fefop_Model_Mapper_Expense::CONFIG_PER_ETC );
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

            $this->_form = new Fefop_Form_ETCContract();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$searchETCContract = new Fefop_Form_ETCContractSearch();
	$searchETCContract->setAction( $this->_helper->url( 'search-etc-contract' ) );
	
	$this->view->menu()->setActivePath( 'fefop/etc-contract/list' );
     
	$this->view->form = $searchETCContract;
    }
    
    /**
     * 
     */
    public function searchEtcContractAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
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
	$data['enterprise'] = $enterprise['enterprise_name'];
	
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
     * 
     */
    public function detailEnterpriseAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$mapperEnterprise = new Register_Model_Mapper_Enterprise();
	$enterprise = $mapperEnterprise->detail( $id );
	
	$address = $mapperEnterprise->listAddress( $id )->current();
	$staff = $mapperEnterprise->listStaff( $id )->current();
	
	$this->view->enterprise = $enterprise;
	$this->view->address = $address;
	$this->view->staff = $staff;
    }
    
    /**
     * 
     */
    public function headerItemAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$this->view->items = $this->_getParam( 'items' );
    }
    
     /**
     * 
     */
    public function headerEmploymentAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$this->view->items = $this->_getParam( 'items' );
    }
    
    /**
     * 
     */
    public function headerFormationAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$this->view->items = $this->_getParam( 'items' );
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
	if ( !empty( $row ) ) {
	    
	    $defaultValues = $row->toArray();
	    $this->view->expense = $row->fk_id_budget_category;
	}
	
	$this->view->defaultValues = $defaultValues;
    }
    
    /**
     * 
     */
    public function addDetailedEmploymentAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$this->view->expense = $this->_getParam( 'expense' );
	
	$defaultValues = array(
	    'beneficiaries' => null,
	    'date_start'    => null,
	    'date_finish'   => null,
	    'duration_days' => null,
	    'quantity'	    => null,
	    'amount_unit'   => 0,
	    'amount_total'  => 0,
	    'comments'	    => ''
	);
	
	$row = $this->_getParam( 'row' );
	if ( !empty( $row ) ) {
	    
	    $defaultValues = $row->toArray();
	    $this->view->expense = $row->fk_id_budget_category;
	}
	
	$this->view->defaultValues = $defaultValues;
    }
    
    /**
     * 
     */
    public function addDetailedFormationAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$this->view->expense = $this->_getParam( 'expense' );
	
	$defaultValues = array(
	    'beneficiaries' => null,
	    'fk_id_perscholarity'    => null,
	    'level'	    => null,
	    'quantity'	    => null,
	    'amount_unit'   => 0,
	    'amount_total'  => 0,
	    'comments'	    => ''
	);
	
	$row = $this->_getParam( 'row' );
	if ( !empty( $row ) ) {
	    
	    $defaultValues = $row->toArray();
	    $this->view->expense = $row->fk_id_budget_category;
	}
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$this->view->scholarityOptions = $mapperScholarity->getOptionsScholarity();
	
	$this->view->defaultValues = $defaultValues;
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
	$diffDays = $measure->convertTo( Zend_Measure_Time::MONTH, 0 );
	
	$this->_helper->json( array( 'diff' => preg_replace( '/[^0-9]/i', '', $diffDays ) + 1 ) );
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
    public function searchScholarityAction()
    {
	$id = $this->_getParam( 'id' );
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$scholarity = $mapperScholarity->detail( $id );
	
	$this->_helper->json( $scholarity->toArray() );
    }
}