<?php

class Report_JobController extends Zend_Controller_Action
{
    
    /**
     *
     * @var Report_Model_Mapper_Job
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
	$this->_mapper = new Report_Model_Mapper_Job();
	$this->_config = Zend_Registry::get( 'config' );
	
	$stepBreadCrumb = array(
	    'label' => 'Vaga Empregu',
	    'url'   => '/report/job/placement'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
    }
    
    /**
     * 
     */
    public function indexAction()
    {
	$this->_forward( 'placement' );
    }
    
    /**
     * 
     */
    public function placementAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Job Placements',
	    'url'   => '/report/job/placement'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Job Placements' );
	$this->view->menu()->setActivePath( 'report/job/placement' );
	
	$form = new Report_Form_JobPlacement();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function placementOverseasAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Job Placements Overseas',
	    'url'   => '/report/job/placement-overseas'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Job Placements Overseas' );
	$this->view->menu()->setActivePath( 'report/job/placement-overseas' );
	
	$form = new Report_Form_JobPlacementOverseas();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
    
     /**
     * 
     */
    public function placementConsolidatedAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Job Placements Consolidated',
	    'url'   => '/report/job/placement-consolidated'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Job Placements Consolidated' );
	$this->view->menu()->setActivePath( 'report/job/placement-consolidated' );
	
	$form = new Report_Form_JobPlacementConsolidated();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
    
     /**
     * 
     */
    public function shortlistedAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Refere Shortlist',
	    'url'   => '/report/job/shortlisted'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Refere Shorlist' );
	$this->view->menu()->setActivePath( 'report/job/shortlisted' );
	
	$form = new Report_Form_JobShortlisted();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function listShortlistAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'List Shortlist',
	    'url'   => '/report/job/list-shortlist'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: List Shortlist' );
	$this->view->menu()->setActivePath( 'report/job/list-shortlist' );
	
	$form = new Report_Form_JobListShortlist();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function registerAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'List Vaga Rejista',
	    'url'   => '/report/job/register'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: List Vaga Rejista' );
	$this->view->menu()->setActivePath( 'report/job/register' );
	
	$form = new Report_Form_JobRegister();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function youthIndicatorAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Indikador juventude nian',
	    'url'   => '/report/job/youth-indicator'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Indikador juventude nian' );
	$this->view->menu()->setActivePath( 'report/job/youth-indicator' );
	
	$form = new Report_Form_JobYouthIndicator();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function educationAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Buka servisu / Nivel Edukasaun',
	    'url'   => '/report/job/education'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Buka servisu / Nivel Edukasaun' );
	$this->view->menu()->setActivePath( 'report/job/education' );
	
	$form = new Report_Form_JobEducation();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
}