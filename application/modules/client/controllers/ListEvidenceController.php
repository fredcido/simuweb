<?php

/**
 * 
 */
class Client_ListEvidenceController extends App_Controller_Default
{
    
    /**
     *
     * @var Client_Model_Mapper_ListEvidence
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Client_Model_Mapper_ListEvidence();
	
	$stepBreadCrumb = array(
	    'label' => 'Lista Kartaun Evidensia',
	    'url'   => 'client/list-evidence'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Lista Kartaun Evidensia' );
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Buka Lista',
	    'url'   => 'client/list-evidence/list'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );

	$searchListEvidence = new Client_Form_ListEvidenceSearch();
	$searchListEvidence->setAction( $this->_helper->url( 'search-list-evidence' ) );
        
        $this->view->menu()->setActivePath( 'client/list-evidence/list' );

	$this->view->form = $searchListEvidence;
    }
    
    /**
     * 
     */
    public function searchListEvidenceAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$id = $this->_getParam( 'id' );
	$form = $this->_getForm( $this->_helper->url( 'save' ) );
	$this->_session->client_list = array();

	if ( empty( $id ) ) {

	    $stepBreadCrumb = array(
		'label' => 'Rejistu Foun',
		'url'	=> 'client/list-evidence'
	    );
	    
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Rejistu',
		'url'	=> 'client/list-evidence/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    
	    $form->getElement( 'fk_id_dec' )->setAttrib( 'disabled', true );
	    $form->getElement( 'fk_id_user' )->setAttrib( 'disabled', true );
	    $form->populate( $data );
	    $form->setPrintMode();
	    
	    $this->view->data = $data;
	    $this->view->title()->setSubTitle( $row->list_name );
	}

	$this->view->form = $form;
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_Group
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Client_Form_ListEvidence();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function editAction()
    {
	$this->_forward( 'index' );
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
	$this->_setParam( 'has_case', 1 );
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
    public function listClientAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listClient( $this->_getParam( 'id' ) );
    }
    
     /**
     * 
     */
    public function removeClientAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->removeClient();
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
    public function printAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_helper->viewRenderer->setNoRender( true );
	
	$id = $this->_getParam( 'id' );
	$clients = $this->_getParam( 'clients' );
	
	$clients = explode( ',', $clients );
	
	$this->_mapper->savePrint( $id, $clients );
	
	$mapperClient = new Client_Model_Mapper_Client();
	$dataClient = array();
	foreach ( $clients as $client )
	    $dataClient[] = $mapperClient->detailClient( $client );
	
	App_General_EvidenceCard::generate( $dataClient );
    }
}