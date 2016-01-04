<?php

/**
 * 
 */
class Sms_CampaignTypeController extends App_Controller_Default
{
    
    /**
     *
     * @var Sms_Model_Mapper_CampaignType
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Sms_Model_Mapper_CampaignType();
	
	$stepBreadCrumb = array(
	    'label' => 'Tipu Kampanha',
	    'url'   => 'sms/campaign-type'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Tipu Kampanha' );
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

            $this->_form = new Sms_Form_CampaignType();
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
}