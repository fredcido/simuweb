<?php

/**
 * 
 */
class Fefop_FeRegistrationController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_FERegistration
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_FERegistration();
	
	$stepBreadCrumb = array(
	    'label' => 'Fixa Inskrisaun Formasaun iha Servisu Fatin',
	    'url'   => 'fefop/fe-registration'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Fixa Inskrisaun Formasaun iha Servisu Fatin' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$id = $this->_getParam( 'id' );
	
	// Form Information
	$form = $this->_getForm( $this->_helper->url( 'save' ) );

	if ( empty( $id ) ) {

	    $stepBreadCrumb = array(
		'label' => 'Rejistu Fixa Inskrisaun',
		'url'	=> 'fefop/fe-registration'
	    );
	    
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Fixa Inskrisaun',
		'url'	=> 'fefop/fe-registration/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->detail( $id );
	    if ( empty( $row ) )
		$this->_helper->redirector->goToSimple( 'index' );
	    
	    $data = $row->toArray();
	    $groups = $this->_mapper->groupFormation( $id );
	    
	    $data += $groups;
	    $data['client_name'] = Client_Model_Mapper_Client::buildName($row);
	    $form->populate( $data );
	    
	    $this->view->entities = $this->_mapper->listEntities( $id );
	    $this->view->client = $data['fk_id_perdata'];
	    
	    if ( !empty( $data['id_fe_contract'] ) ) {
		
		$form->removeDisplayGroup( 'toolbar' );
		foreach ( $form->getElements() as $element )
		    $element->setAttrib( 'disabled', true );
		
		$this->view->has_contract = true;
	    }
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

            $this->_form = new Fefop_Form_FERegistration();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$searchFEContract = new Fefop_Form_FERegistrationSearch();
	$searchFEContract->setAction( $this->_helper->url( 'search-fe-registration' ) );
	
	$this->view->menu()->setActivePath( 'fefop/fe-registration/list' );
     
	$this->view->form = $searchFEContract;
    }
    
    /**
     * 
     */
    public function searchFeRegistrationAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
	$this->view->listAjax = $this->_getParam( 'list-ajax' );
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
	
	$data = $client->toArray();
	$data['fk_id_perdata'] = $data['id_perdata'];
	$data['client_name'] = Client_Model_Mapper_Client::buildName( $client );
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
	$data['id'] = $institute['id_fefpeduinstitution'];
	$data['name'] = $institute['institution'];
	
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
	$data['id'] = $enterprise['id_fefpenterprise'];
	$data['name'] = $enterprise['enterprise_name'];
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function addEntityAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$this->view->row = $this->_getParam( 'row' );
	$this->view->has_contract = $this->_getParam( 'has_contract' );
    }
    
    /**
     * 
     */
    public function listScholarityAction()
    {
	if ( $this->getRequest()->isXmlHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	$type = $this->_getParam( 'type' );
	
	$mapperClient = new Client_Model_Mapper_Client();
	$this->view->rows = $mapperClient->listScholarity( $id, $type );
    }
    
    /**
     * 
     */
    public function printAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$id = $this->_getParam( 'id' );
	
	$registration = $this->_mapper->detail( $id );
	$this->view->registration = $registration;
	
	$this->view->user = Zend_Auth::getInstance()->getIdentity();
	
	$mapperClient = new Client_Model_Mapper_Client();
	
	$this->view->non_formal_scholarity = $mapperClient->listScholarity( $registration->fk_id_perdata, Register_Model_Mapper_PerTypeScholarity::NON_FORMAL );
	$this->view->formal_scholarity = $mapperClient->listScholarity( $registration->fk_id_perdata, Register_Model_Mapper_PerTypeScholarity::FORMAL );
	
	$this->view->entities = $this->_mapper->listEntities( $id );
	
	$groups = $this->_mapper->groupFormation( $id );
	$this->view->groups = $groups;
    }
}