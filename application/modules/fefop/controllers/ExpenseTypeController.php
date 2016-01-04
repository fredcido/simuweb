<?php

/**
 * 
 */
class Fefop_ExpenseTypeController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_ExpenseType
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_ExpenseType();
	
	$stepBreadCrumb = array(
	    'label' => 'Komponente',
	    'url'   => 'fefop/expense-type'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Komponente' );
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

            $this->_form = new Fefop_Form_ExpenseType();
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
	$this->view->id = $this->_getParam( 'id' );
    }
    
     /**
     * 
     */
    public function removeExpenseTypeAction()
    {
	$json = $this->_mapper->removeExpenseType( $this->_getAllParams() );
	$this->_helper->json( $json );
    }
}