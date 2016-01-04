<?php

class Report_RegisterController extends Zend_Controller_Action
{
    
    /**
     *
     * @var Report_Model_Mapper_Register
     */
    protected $_mapper;
    
    /**
     *
     * @var Zend_Config
     */
    protected $_config;
    
    /**
     * 
     */
    public function init()
    {
	$this->_mapper = new Report_Model_Mapper_Register();
	$this->_config = Zend_Registry::get( 'config' );
	
	$stepBreadCrumb = array(
	    'label' => 'Rejistu Jeral',
	    'url'   => '/report/register/course'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
    }
    
    /**
     * 
     */
    public function indexAction()
    {
	$this->_forward( 'course' );
    }
    
    /**
     * 
     */
    public function courseAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Course',
	    'url'   => '/report/register/course'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Kursu' );
	$this->view->menu()->setActivePath( 'report/register/course' );
	
	$form = new Report_Form_Course();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function institutionAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Inst. Ensinu',
	    'url'   => '/report/register/institution'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Inst. Ensinu' );
	$this->view->menu()->setActivePath( 'report/register/institution' );
	
	$form = new Report_Form_Institution();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function enterpriseAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Empreza',
	    'url'   => '/report/register/enterprise'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Empreza' );
	$this->view->menu()->setActivePath( 'report/register/enterprise' );
	
	$form = new Report_Form_Enterprise();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
}