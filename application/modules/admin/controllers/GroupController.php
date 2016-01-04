<?php

/**
 * 
 */
class Admin_GroupController extends App_Controller_Default
{
    
    /**
     *
     * @var Admin_Model_Mapper_Group
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Admin_Model_Mapper_Group();
	
	$stepBreadCrumb = array(
	    'label' => 'Grupu',
	    'url'   => 'admin/group'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Grupu' );
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

            $this->_form = new Admin_Form_Group();
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
    
    /**
     * 
     */
    public function groupNoteAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->group = $this->_mapper->fetchRow( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function listUsersAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setRender( 'list-user' );
        
        $this->view->rows = $this->_mapper->listUsers( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function listUserGroupAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setRender( 'list-user' );
        
        $this->view->rows = $this->_mapper->listUserGroup( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function listTypesAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setRender( 'list-type' );
        
        $this->view->rows = $this->_mapper->listTypes( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function listTypesGroupAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setRender( 'list-type' );
        
        $this->view->rows = $this->_mapper->listTypesGroup( $this->_getParam( 'id' ) );
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function saveItensAction()
    {
	if ( $this->getRequest()->isPost() ) {
	    
	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->saveItens();
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
}