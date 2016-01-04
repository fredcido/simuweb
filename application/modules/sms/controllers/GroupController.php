<?php

/**
 * 
 */
class Sms_GroupController extends App_Controller_Default
{
    
    /**
     *
     * @var Sms_Model_Mapper_Group
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Sms_Model_Mapper_Group();
	
	$stepBreadCrumb = array(
	    'label' => 'Grupu',
	    'url'   => 'sms/group'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Grupu' );
	
	$id = $this->_getParam( 'id' );
	$this->view->groupSms( $id );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$form = $this->_getForm( $this->_helper->url( 'save' ) );
	
	$this->view->form = $form;
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Sms_Form_Group();
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
	
	$form = $this->view->form;
	$data = $this->view->data;
	
	if ( !$this->view->groupSms()->isEnabled() ) {
	    
	    foreach ( $form->getElements() as $element )
		$element->setAttrib( 'disabled', true );
	}
	
	$this->view->title()->setSubTitle( $data['sms_group_name'] );
    }
    
     /**
     * 
     */
    public function clientAction()
    {
	$this->_helper->layout()->disableLayout();
    }
    
    /**
     * 
     */
    public function listClientAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listClient( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function searchClientAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'client', 'client' );
    }
    
    /**
     * 
     */
    public function searchClientForwardAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'search-client', 'client', 'client' );
    }
    
    /**
     * 
     */
    public function saveClientAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->saveClient();
	$message = $this->_mapper->getMessage()->toArray();

	$result = array(
	    'status'	    => (bool) $return,
	    'description'   => empty( $message ) ? null : $message[0]
	);

	$this->_helper->json( $result );
    }
    
     /**
     * 
     */
    public function enterpriseAction()
    {
	$this->_helper->layout()->disableLayout();
    }
    
    /**
     * 
     */
    public function listEnterpriseAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listEnterprise( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function searchEnterpriseAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'enterprise', 'register' );
    }
    
    /**
     * 
     */
    public function searchEnterpriseForwardAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'search-enterprise', 'enterprise', 'register' );
    }
    
    /**
     * 
     */
    public function saveEnterpriseAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->saveEnterprise();
	$message = $this->_mapper->getMessage()->toArray();

	$result = array(
	    'status'	    => (bool) $return,
	    'description'   => empty( $message ) ? null : $message[0]
	);

	$this->_helper->json( $result );
    }
    
     /**
     * 
     */
    public function instituteAction()
    {
	$this->_helper->layout()->disableLayout();
    }
    
    /**
     * 
     */
    public function listInstituteAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listInstitute( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function searchInstituteAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'education-institution', 'register' );
    }
    
    /**
     * 
     */
    public function searchInstituteForwardAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'search-institution', 'education-institution', 'register' );
    }
    
    /**
     * 
     */
    public function saveInstituteAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->saveInstitute();
	$message = $this->_mapper->getMessage()->toArray();

	$result = array(
	    'status'	    => (bool) $return,
	    'description'   => empty( $message ) ? null : $message[0]
	);

	$this->_helper->json( $result );
    }
    
    /**
     * 
     */
    public function removeItemAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->removeItem();
	$message = $this->_mapper->getMessage()->toArray();

	$result = array(
	    'status'	    => (bool) $return,
	    'description'   => empty( $message ) ? null : $message[0]
	);

	$this->_helper->json( $result );
    }
}