<?php

class Report_AdminController extends Zend_Controller_Action
{
    
    /**
     *
     * @var Report_Model_Mapper_Client
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
	$this->_mapper = new Report_Model_Mapper_Admin();
	$this->_config = Zend_Registry::get( 'config' );
	
	$stepBreadCrumb = array(
	    'label' => 'Admin',
	    'url'   => '/report/admin/user'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
    }
    
    /**
     * 
     */
    public function indexAction()
    {
	$this->_forward( 'user' );
    }
    
    /**
     * 
     */
    public function userAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Uzuariu',
	    'url'   => '/report/admin/user'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Relatorio: Uzuariu' );
	$this->view->menu()->setActivePath( 'report/admin/user' );
	
	$form = new Report_Form_User();
	$form->setAction( $this->_helper->url( 'output', 'general' ) );
	
	$this->view->form = $form;
    }
}