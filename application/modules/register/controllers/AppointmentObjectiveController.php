<?php

/**
 * 
 */
class Register_AppointmentObjectiveController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_AppointmentObjective
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_AppointmentObjective();
	
	$stepBreadCrumb = array(
	    'label' => 'Intensaun ba Audiensia',
	    'url'   => 'register/appointament-objective'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Intensaun ba Audiensia' );
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
     * @return Register_Form_AppointmentObjective
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Register_Form_AppointmentObjective();
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