<?php

/**
 * 
 */
class Admin_ModuleController extends App_Controller_Default
{
    
    /**
     *
     * @var Admin_Model_Mapper_SysModule
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Admin_Model_Mapper_SysModule();
	
	$stepBreadCrumb = array(
	    'label' => 'Modulu',
	    'url'   => 'admin/module'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Modulu' );
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
     * @return Default_Module_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Admin_Form_Module();
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
    }
}