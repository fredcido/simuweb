<?php

/**
 * 
 */
class Admin_UserController extends App_Controller_Default
{
    
    /**
     *
     * @var Admin_Model_Mapper_SysUser
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Admin_Model_Mapper_SysUser();
	
	$stepBreadCrumb = array(
	    'label' => 'Usuariu',
	    'url'   => 'admin/user'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Usuariu' );
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

            $this->_form = new Admin_Form_User();
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
	$this->view->rows = $this->_mapper->listAll();
    }
    
    /**
     * 
     */
    public function editPostHook()
    {
	$this->_helper->viewRenderer->setRender( 'index' );
	$this->view->form->getElement( 'login' )->setAttrib( 'readonly', true );
	$this->view->form->getElement( 'password' )->setRequired( false );
	$this->view->form->getElement( 'confirm_password' )->setRequired( false );
    }
}