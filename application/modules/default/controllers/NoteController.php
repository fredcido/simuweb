<?php

/**
 * 
 */
class Default_NoteController extends Zend_Controller_Action
{

    /**
     *
     * @var Default_Model_Mapper_Note
     */
    protected $_mapper;
    
    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_helper->layout()->disableLayout();
	$this->_mapper = new Default_Model_Mapper_Note();
    }

    /**
     * 
     * @access public
     * @return void
     */
    public function indexAction()
    {
    }
    
    /**
     * 
     */
    public function listNotesToUserAction()
    {
	$id = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	
	$notes = $this->_mapper->listNotesToUser( $id );
	
	$total = $notes->count();
	
	$notes = $notes->toArray();
	$notes = array_slice( $notes, 0, 10 );
	
	$data = array(
	    'notes' => $notes,
	    'total' => $total
	);
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function detailAction()
    {
	$this->view->note = $this->_mapper->detail( $this->_getParam( 'id' ) );
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function finishNoteAction()
    {
	if ( $this->getRequest()->isPost() ) {
	    
	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->finishNote();
	    $message = $this->_mapper->getMessage()->toArray();

	    $result = array(
		'status'	=> (bool) $return,
		'id'		=> $return,
		'description'	=> $message
	    );
		
	} else {

	    $message = new App_Message();
	    $message->addMessage( $this->_config->messages->warning, App_Message::WARNING );
	    
	    $result = array(
		'status'      => false,
		'description' => $message->toArray()
	    );
	}

	$this->_helper->json( $result );
    }
    
    /**
     * 
     */
    public function formAction()
    {
	$form = new Default_Form_Note();
	$form->setAction( $this->_helper->url( 'save' ) );
	
	$data = array();
	$data['fk_id_sysuser'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	$form->populate( $data );
	
	$this->view->form = $form;
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function saveAction()
    {
	if ( $this->getRequest()->isPost() ) {
	    
	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->save();
	    $message = $this->_mapper->getMessage()->toArray();

	    $result = array(
		'status'	=> (bool) $return,
		'id'		=> $return,
		'description'	=> $message
	    );
		
	} else {

	    $message = new App_Message();
	    $message->addMessage( $this->_config->messages->warning, App_Message::WARNING );
	    
	    $result = array(
		'status'      => false,
		'description' => $message->toArray()
	    );
	}

	$this->_helper->json( $result );
    }
    
    /**
     * 
     */
    public function listAllAction()
    {
    }
    
    /**
     * 
     */
    public function listToUserRowsAction()
    {
	$id = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	$this->view->rows = $this->_mapper->listAllNotesToUser( $id );
    }
    
    /**
     * 
     */
    public function listByUserRowsAction()
    {
	$id = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	$this->view->rows = $this->_mapper->listAllNotesByUser( $id );
    }
}