<?php

class Report_SmsController extends Zend_Controller_Action
{
    
    /**
     *
     * @var Report_Model_Mapper_Sms
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
	$this->_mapper = new Report_Model_Mapper_Sms();
	$this->_config = Zend_Registry::get( 'config' );
	
	$stepBreadCrumb = array(
	    'label' => 'Sms',
	    'url'   => '/report/sms/campaign'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
    }
    
    /**
     * 
     */
    public function indexAction()
    {
	$this->_forward( 'campaign' );
    }
    
    /**
     * 
     */
    public function campaignAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Campaign',
	    'url'   => '/report/sms/campaign'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Kampanha' );
	$this->view->menu()->setActivePath( 'report/sms/campaign' );
	
	$form = new Report_Form_Campaign();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function balanceAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Total Pulsa',
	    'url'   => '/report/sms/balance'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Total Pulsa Departamentu' );
	$this->view->menu()->setActivePath( 'report/sms/balance' );
	
	$form = new Report_Form_SmsBalance();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function creditAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Pulsa',
	    'url'   => '/report/sms/credit'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Pulsa Departamentu' );
	$this->view->menu()->setActivePath( 'report/sms/credit' );
	
	$form = new Report_Form_SmsCredit();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function sendingAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Enviu sira',
	    'url'   => '/report/sms/sending'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Enviu Sira' );
	$this->view->menu()->setActivePath( 'report/sms/sending' );
	
	$form = new Report_Form_SmsSending();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function incomingAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Simu hotu',
	    'url'   => '/report/sms/incoming'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Simu hotu' );
	$this->view->menu()->setActivePath( 'report/sms/incoming' );
	
	$form = new Report_Form_SmsIncoming();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
}