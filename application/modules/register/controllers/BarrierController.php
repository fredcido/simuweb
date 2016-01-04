<?php

/**
 * 
 */
class Register_BarrierController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_Barrier
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_Barrier();
	
	$stepBreadCrumb = array(
	    'label' => 'Barreira',
	    'url'   => 'register/barrier'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Barreira' );
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
     * @return Register_Form_Barrier
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Register_Form_Barrier();
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
    }
}