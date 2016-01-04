<?php

/**
 * 
 */
class Fefop_BeneficiaryBlacklistController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_BeneficiaryBlacklist
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_BeneficiaryBlacklist();
	
	$stepBreadCrumb = array(
	    'label' => 'Lista benefisariu la kumpridor',
	    'url'   => 'fefop/beneficiary-blacklist'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Lista benefisariu la kumpridor' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$this->view->form = $this->_getForm( $this->_helper->url( 'save' ) );
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Fefop_Form_BeneficiaryBlacklist();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$beneficiaryBlacklistSearch = new Fefop_Form_BeneficiaryBlacklistSearch();
	$beneficiaryBlacklistSearch->setAction( $this->_helper->url( 'search-beneficiary-blacklist' ) );
     
	$this->view->form = $beneficiaryBlacklistSearch;
    }
    
    /**
     * 
     */
    public function searchBeneficiaryBlacklistAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
	$this->view->listAjax = $this->_getParam( 'list-ajax' );
    }
    
     /**
     * 
     */
    public function searchModulesAction()
    {
	$dbFefopModules = App_Model_DbTable_Factory::get( 'FEFOPModules' );
	$rows = $dbFefopModules->fetchAll( array( 'fk_id_fefop_programs = ?' => $this->_getParam( 'id' ) ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_fefop_modules, 'name' => $row->acronym . ' - ' . $row->description );
	
	$this->_helper->json( $opts );
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
	$data['beneficiary'] = $institute['institution'];
	
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
	$data['beneficiary'] = $enterprise['enterprise_name'];
	
	$this->_helper->json( $data );
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
    public function fetchStaffAction()
    {
	$mapperInstitute = new Register_Model_Mapper_EducationInstitute();
	$staff = $mapperInstitute->fetchStaff( $this->_getParam( 'id' ) );
	
	$data = array();
	$data['fk_id_staff'] = $staff['id_staff'];
	$data['beneficiary'] = $staff['staff_name'];
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function disableAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$form = new Fefop_Form_RemoveBeneficiaryBlacklist();
	$form->setAction( $this->_helper->url( 'save-disable' ) );
	
	$row = $this->_mapper->detail( $this->_getParam( 'id' ) );
	$form->populate( $row->toArray() ); 
	
	$this->view->form = $form;
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function saveDisableAction()
    {
	$form = new Fefop_Form_RemoveBeneficiaryBlacklist();
	
	if ( $form->isValid( $this->getRequest()->getPost() ) ) {

	    $this->_mapper->setData( $form->getValues() );
	    $return = $this->_mapper->saveDisableBlacklist();

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

	    $this->_helper->json( $result );
	}
    }
    
    /**
     * 
     */
    public function detailAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->row = $this->_mapper->detail( $this->_getParam( 'id' ) );
    }
}