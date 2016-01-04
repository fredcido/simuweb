<?php

/**
 * 
 */
class Fefop_ExpenseController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_Expense
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_Expense();
	
	$stepBreadCrumb = array(
	    'label' => 'Rúbrica',
	    'url'   => 'fefop/expense'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Rúbrica' );
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

            $this->_form = new Fefop_Form_Expense();
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
    public function moduleAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$formModule = new Fefop_Form_ExpenseModule();
	$this->view->form = $formModule;
    }
    
    /**
     * 
     */
    public function configurationAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$formModule = new Fefop_Form_ExpenseModule();
	$this->view->form = $formModule;
    }
 
    /**
     * 
     */
    public function expensesItemAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->expensesInItem( $this->_getParam( 'id' ) );
	$this->view->expense_amount = true; 
	
	$this->_helper->viewRenderer->setRender( 'list-expense' );
    }
    
    /**
     * 
     */
    public function expensesNotItemAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->expensesNotInItem( $this->_getParam( 'id' ) );
	
	$this->_helper->viewRenderer->setRender( 'list-expense' );
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function saveExpensesItemAction()
    {
	$form = new Fefop_Form_ExpenseModule();
	
	if ( $form->isValid( $this->getRequest()->getPost() ) ) {

	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->saveExpensesItem();

	    $message = $this->_mapper->getMessage()->toArray();

	    $result = array(
		'status'	=> (bool) $return,
		'id'		=> $return,
		'description'	=> $message,
		'data'		=> $form->getValues(),
		'fields'	=> $this->_mapper->getFieldsError()
	    );

	    $this->_helper->json( $result );
	    
	} else {

	    $message = new App_Message();
	    $message->addMessage( $this->_config->messages->warning, App_Message::WARNING );

	    $result = array(
		'status' => false,
		'description' => $message->toArray(),
		'errors' => $form->getMessages()
	    );

	    $this->_helper->json( $result );
	}
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function removeExpensesItemAction()
    {
	$form = new Fefop_Form_ExpenseModule();
	
	if ( $form->isValid( $this->getRequest()->getPost() ) ) {

	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->removeExpensesItem();

	    $message = $this->_mapper->getMessage()->toArray();

	    $result = array(
		'status'	=> (bool) $return,
		'id'		=> $return,
		'description'	=> $message,
		'data'		=> $form->getValues(),
		'fields'	=> $this->_mapper->getFieldsError()
	    );

	    $this->_helper->json( $result );
	    
	} else {

	    $message = new App_Message();
	    $message->addMessage( $this->_config->messages->warning, App_Message::WARNING );

	    $result = array(
		'status' => false,
		'description' => $message->toArray(),
		'errors' => $form->getMessages()
	    );

	    $this->_helper->json( $result );
	}
    }
    
    /**
     * 
     */
    public function orderExpenseAction()
    {
	$json = $this->_mapper->orderExpense( $this->_getAllParams() );
	$this->_helper->json( $json );
    }
    
    /**
     * 
     */
    public function updateAmountAction()
    {
	$json = $this->_mapper->updateAmount( $this->_getAllParams() );
	$this->_helper->json( $json );
    }
    
    /**
     * 
     */
    public function removeExpenseAction()
    {
	$json = $this->_mapper->removeExpense( $this->_getAllParams() );
	$this->_helper->json( $json );
    }
}