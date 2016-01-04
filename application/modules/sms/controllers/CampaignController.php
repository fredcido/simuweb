<?php

/**
 * 
 */
class Sms_CampaignController extends App_Controller_Default
{
    
    /**
     *
     * @var Sms_Model_Mapper_Campaign
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Sms_Model_Mapper_Campaign();
	
	$stepBreadCrumb = array(
	    'label' => 'Kampanha',
	    'url'   => 'sms/campaign'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Kampanha' );
	
	$id = $this->_getParam( 'id' );
	$this->view->campaign( $id );
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Buka Campanha',
	    'url'   => 'sms/campaign/list'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );

	$searchCampaign = new Sms_Form_CampaignSearch();
	$searchCampaign->setAction( $this->_helper->url( 'search-campaign' ) );
        
        $this->view->menu()->setActivePath( 'sms/campaign/list' );

	$this->view->form = $searchCampaign;
    }
    
    /**
     * 
     */
    public function searchCampaignAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$id = $this->_getParam( 'id' );

	if ( empty( $id ) ) {

	    $stepBreadCrumb = array(
		'label' => 'Rejistu Foun',
		'url'	=> 'sms/campaign'
	    );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Rejistu',
		'url'	=> 'sms/campaign/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->fetchRow( $id );
	    $this->view->title()->setSubTitle( $row->campaign_title );
	}

	$this->view->id = $id;
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
    }
    
    /**
     * 
     */
    public function informationAction()
    {
	// Form Information
	$form = $this->_getForm( $this->_helper->url( 'save' ) );
	
	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    
	    if ( !empty( $data['date_scheduled'] ) ) {
		
		$dateScheduled = new Zend_Date( $data['date_scheduled'] );
		$data['date_scheduled'] = $dateScheduled->toString( 'dd/MM/yyyy' );
	    }
	    
	    // Fetch the groups
	    $groups = $this->_mapper->listGroupsCampaign( $id );
	    $data['group'] = $groups;
	    
	    $form->populate( $data );
	}
	
	// Check if the user can create/edit campaign
	if ( !$this->view->campaign()->isEnabled() ) {
	    
	    $form->removeDisplayGroup(  'toolbar' );
	    foreach ( $form->getElements() as $element )
		$element->setAttrib( 'disabled', true );
	}
	
	$department = $this->view->campaign()->getDepartment();
	if ( !empty( $department ) )
	    $form->getElement( 'fk_id_department' )->setValue( $department['id_department'] );
	
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function departmentAction()
    {
	$department = $this->_getParam( 'department' );
	$this->view->department = $department;
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Sms_Form_Campaign();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function calcSendingAction()
    {
	$user = Zend_Auth::getInstance()->getIdentity();
	
	// Get the User department
	$mapperDepartment = new Admin_Model_Mapper_Department();
	$department = $mapperDepartment->getDepartmentByUser( $user->id_sysuser );
	
	// Get the groups with totals
	$mapperGroupsSms = new Sms_Model_Mapper_Group();
	$groups = $mapperGroupsSms->listGroupWithTotals();
	
	// Get the current sms config
	$mapperConfig = new Admin_Model_Mapper_SmsConfig();
	$config = $mapperConfig->getConfig();
	
	$groupsSelected = $this->_getParam( 'groups' );
	$total = 0;
	$percent = 100;
	
	if ( !empty( $groupsSelected ) ) {

	    foreach ( $groups as $group )
		if ( in_array( $group['id_sms_group'], $groupsSelected ) )
			$total += (int)$group['total'];
	}
	 
	$totalCurrency = $total * (float)$config->sms_unit_cost;
	
	if ( !empty( $department['balance'] ) )
	    $percent = ( $totalCurrency * 100 ) / $department['balance'];
	
	$return = array(
	    'total' => $total,
	    'release' => ( $totalCurrency <= $department['balance'] ),
	    'percent' => $percent
	);
	
	$this->_helper->json( $return );
    }
    
    /**
     * 
     */
    public function sentAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	$this->view->rows = $this->_mapper->listLastsSent( $id );
    }
    
    /**
     * 
     */
    public function incomingAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	$this->view->rows = $this->_mapper->listIncoming( $id );
    }
    
    
    /**
     * 
     */
    public function statisticsAction()
    {
	$this->_helper->layout()->disableLayout();
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
    public function viewAction()
    {
	$this->_forward( 'index' );
    }
    
     /**
     * 
     */
    public function listStatisticsAction()
    {
	$statistics = $this->_mapper->getStatistics( $this->_getAllParams() );
	$this->_helper->json( $statistics );
    }
    
    /**
     * 
     */
    public function chartSendingAction()
    {
	$chartSending = $this->_mapper->chartSending( $this->_getAllParams() );
	$this->_helper->json( $chartSending );
    }
    
    /**
     * 
     */
    public function chartSentDayAction()
    {
	$chartClient = $this->_mapper->chartSentDay( $this->_getAllParams() );
	$this->_helper->json( $chartClient );
    }
    
     /**
     * 
     */
    public function chartSentHourAction()
    {
	$chartClient = $this->_mapper->chartSentHour( $this->_getAllParams() );
	$this->_helper->json( $chartClient );
    }
    
    /**
     * 
     */
    public function chartSentGroupAction()
    {
	$chartClient = $this->_mapper->chartSentGroup( $this->_getAllParams() );
	$this->_helper->json( $chartClient );
    }
    
    /**
     * 
     */
    public function logAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listLogs( $this->_getParam( 'id' ) );
    }
}