<?php

/**
 * 
 */
class Admin_SmsConfigController extends App_Controller_Default
{
    
    /**
     *
     * @var Admin_Model_Mapper_SmsConfig
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Admin_Model_Mapper_SmsConfig();
	
	$stepBreadCrumb = array(
	    'label' => 'Sms Config',
	    'url'   => 'admin/sms-config'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Sms Config' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$this->view->form = $this->_getForm( $this->_helper->url( 'save' ) );
	
	$config = $this->_mapper->getConfig();
	if ( !empty( $config ) )
	    $this->view->form->populate( $config->toArray() );
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Admin_Form_SmsConfig();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
}