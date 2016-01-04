<?php

/**
 * 
 */
class Admin_SmsCreditController extends App_Controller_Default
{
    
    /**
     *
     * @var Admin_Model_Mapper_SmsCredit
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Admin_Model_Mapper_SmsCredit();
	
	$stepBreadCrumb = array(
	    'label' => 'Sms Pulsa',
	    'url'   => 'admin/sms-credit'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Sms Pula' );
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

            $this->_form = new Admin_Form_SmsCredit();
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
	$this->view->rows = $this->_mapper->fetchAll();
    }
    /**
     * 
     */
    public function editPostHook()
    {
	$this->_helper->viewRenderer->setRender( 'index' );
	$this->view->id = $this->_getParam( 'id' );
    }
    
    /**
     * 
     */
    public function calcAction()
    {
	$smsConfigMapper = new Admin_Model_Mapper_SmsConfig();
	$config = $smsConfigMapper->getConfig();
	
	$value = $this->_getParam( 'value' );
	$type = $this->_getParam( 'type' );
	
	if ( $type == 'amount' )
	    $total = number_format( (int)$value * (float)$config['sms_unit_cost'], 2, '.', ',' );
	else
	    $total = floor( (float)$value / (float)$config['sms_unit_cost'] );
	
	$this->_helper->json( array( 'value' => $total ) );
    }
    
    /**
     * 
     */
    public function balanceAction()
    {
	$this->_helper->viewRenderer->setRender( 'balance-rows' );
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->getBalance();
    }
}