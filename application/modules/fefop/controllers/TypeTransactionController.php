<?php

/**
 * 
 */
class Fefop_TypeTransactionController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_TypeTransaction
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_TypeTransaction();
	
	$stepBreadCrumb = array(
	    'label' => 'Tipu Transasaun',
	    'url'   => 'fefop/type-transaction'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Tipu Transasaun' );
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

            $this->_form = new Fefop_Form_TypeTransaction();
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
    public function removeTransactionTypeAction()
    {
	$json = $this->_mapper->removeTransactionType( $this->_getAllParams() );
	$this->_helper->json( $json );
    }
}