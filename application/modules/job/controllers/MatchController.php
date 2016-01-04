<?php

/**
 * 
 */
class Job_MatchController extends App_Controller_Default
{
    
    /**
     *
     * @var Job_Model_Mapper_JobMatch
     */
    protected $_mapper;
    
    /**
     *
     * @var Job_Model_Mapper_JobVacancy 
     */
    protected $_mapperVacancy;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Job_Model_Mapper_JobMatch();
	$this->_mapperVacancy = new Job_Model_Mapper_JobVacancy();
	
	$id = $this->_getParam( 'id' );
	
	$stepBreadCrumb = array(
	    'label' => 'Vaga Empregu',
	    'url'   => 'job/vacancy/view/id/' . $id
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Mediasaun' )->setSubTitle( 'Vaga Empregu' );
	
	// Set the path to be active in the menu
	$this->view->menu()->setActivePath( 'job/vacancy/list' );
	
	$this->view->jobActive( $id );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$id = $this->_getParam( 'id' );
	$vacancy = $this->_mapperVacancy->detailVacancy( $id );
	
	if ( empty( $vacancy ) )
	    $this->_helper->redirector->goToSimple( 'list', 'vacancy', 'job' );
	
	$this->view->vacancy = $vacancy;
	$this->view->id = $id;
    }
    
    /**
     * 
     */
    public function shortlistAction()
    {
	$form = new Job_Form_MatchShortlist();
	$form->setAction( $this->_helper->url( 'save-match' ) );
	
	$id = $this->_getParam( 'id' );
	
	$form->getElement( 'fk_id_jobvacancy' )->setValue( $id );
	
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function listShortlistAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$this->view->rows = $this->_mapper->listShortlist( $id );
	$this->view->user = Zend_Auth::getInstance()->getIdentity();
	$this->view->vacancy = $this->_mapperVacancy->detailVacancy( $id );
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$form = new Job_Form_MatchList();
	$form->setAction( $this->_helper->url( 'save-match' ) );
	
	$id = $this->_getParam( 'id' );
	
	$form->getElement( 'fk_id_jobvacancy' )->setValue( $id );
	
	$this->view->form = $form;
    }
    
     /**
     * 
     */
    public function listCandidateAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listCandidate( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function automaticAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$form = new Job_Form_MatchAutomatic();
	$form->setAction( $this->_helper->url( 'save' ) );
	
	$form->getElement( 'fk_id_jobvacancy' )->setValue( $this->_getParam( 'id' ) );
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function listAutomaticAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_helper->viewRenderer->setRender( 'client' );
	
	$this->view->rows = $this->_mapper->listAutomatic( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function manualAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$form = new Job_Form_MatchManual();
	$form->setAction( $this->_helper->url( 'list-manual' ) );
	
	$form->getElement( 'fk_id_jobvacancy' )->setValue( $this->_getParam( 'id' ) );
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function listManualAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_helper->viewRenderer->setRender( 'client' );
	
	$this->view->rows = $this->_mapper->listManual( $this->_getAllParams() );
    }
    
    
    /**
     * 
     */
    public function directAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$form = new Job_Form_MatchDirect();
	$form->setAction( $this->_helper->url( 'list-direct' ) );
	
	$form->getElement( 'fk_id_jobvacancy' )->setValue( $this->_getParam( 'id' ) );
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function listDirectAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_helper->viewRenderer->setRender( 'client' );
	
	$this->view->rows = $this->_mapper->listDirect( $this->_getAllParams() );
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function addListAction()
    {
	if ( $this->getRequest()->isPost() ) {
	    
	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->addList();
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
    public function removeClientAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteClient();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function searchSubDistrictAction()
    {
	$mapperSubDistrict = new Register_Model_Mapper_AddSubDistrict();
	$rows = $mapperSubDistrict->listAll( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_addsubdistrict, 'name' => $row->sub_district );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function printAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$id = $this->_getParam( 'id' );
	
	$vacancy = $this->_mapperVacancy->detailVacancy( $id );
	
	$this->view->vacancy = $vacancy;
	$this->view->shortlist = $this->_mapper->listShortlist( $id );
    }
    
    /**
     * 
     */
    public function searchScholarityAction()
    {
	$filters = array(
	    'category'	=> $this->_getParam( 'category' ),
	    'type'	=> $this->_getParam( 'type' )
	);
	
	$mapperScholarity =  new Register_Model_Mapper_PerScholarity();
	$scholarities = $mapperScholarity->listAll( $filters );

	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $scholarities as $row )
	    $opts[] = array( 'id' => $row->id_perscholarity, 'name' => $row->scholarity );
	
	$this->_helper->json( $opts );
    }
}