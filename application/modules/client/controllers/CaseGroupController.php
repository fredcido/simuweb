<?php

/**
 * 
 */
class Client_CaseGroupController extends App_Controller_Default
{
    
    /**
     *
     * @var Client_Model_Mapper_CaseGroup
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Client_Model_Mapper_CaseGroup();
	
	$stepBreadCrumb = array(
	    'label' => 'Kazu Grupu',
	    'url'   => 'client/case-group'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Kazu Grupu' );
	
	$id = $this->_getParam( 'id' );
	$this->view->caseActiveGroup( $id );
    }
    
    /**
     * 
     */
    public function indexAction()
    {
	$id = $this->_getParam( 'id' );

	if ( empty( $id ) ) {

	    $stepBreadCrumb = array(
		'label' => 'Rejistu Foun',
		'url'	=> 'case/case-group'
	    );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Rejistu',
		'url'	=> 'client/case-group/edit/id/' . $id
	    );
	}

	$this->view->id = $id;
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
    }
    
     /**
     *
     * @param string $formName
     * @return Zend_Form
     */
    protected function _initForm( $formName )
    {
	$className = 'Client_Form_' . ucfirst( $formName );

	$form = new $className();
	$form->setAction( $this->_helper->url( 'save-abstract' ) );

	return $form;
    }
    
    /**
     * 
     */
    public function saveAbstractAction()
    {
	$step = $this->_getParam( 'step' );
	$form = $this->_initForm( $step );

	if ( $this->getRequest()->isPost() ) {

	    if ( $form->isValid( $this->getRequest()->getPost() ) ) {

		$this->_mapper->setData( $form->getValues() );

		$method = 'save' . ucfirst( $step );
		$return = call_user_func( array($this->_mapper, $method) );
		$message = $this->_mapper->getMessage()->toArray();

		$result = array(
		    'status'	    => (bool)$return,
		    'id'	    => $return,
		    'description'   => $message,
		    'data'	    => $form->getValues(),
		    'fields'	    => $this->_mapper->getFieldsError()
		);

		$this->_helper->json( $result );
	    } else {

		$message = new App_Message();
		$message->addMessage( $this->_config->messages->warning, App_Message::WARNING );

		$result = array(
		    'status'	    => false,
		    'description'   => $message->toArray(),
		    'errors'	    => $form->getMessages()
		);

		$this->_helper->json( $result );
	    }
	}
	else
	    $this->_helper->redirector->goToSimple( 'index' );
    }
    
    /**
     * 
     */
    public function informationAction()
    {
	if ( $this->getRequest()->isXMLHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	// Form Case Group
	$form = $this->_initForm( 'caseGroup' );
	
	$data = array();
	$data['fk_id_counselor'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	
	$id = $this->_getParam( 'id' );
	
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    $data = $row->toArray();
	    
	    $form->getElement( 'fk_id_counselor' )->setAttrib( 'disabled', true );
	    
	    if ( !$this->view->caseActiveGroup()->hasAccessEdit() )
		$form->getElement( 'save' )->setAttrib( 'disabled', true );
	}
	
	$form->populate( $data );
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function clientAction()
    {
	if ( $this->getRequest()->isXMLHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	// Form Client Group
	$form = $this->_initForm( 'clientGroup' );
	$data = array();
	
	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $data['fk_id_action_plan_group'] = $id;
	    
	    if ( !$this->view->caseActiveGroup()->hasAccessEdit() )
		$form->getElement( 'save' )->setAttrib( 'disabled', true );
	}
	
	$form->populate( $data );
	$this->view->form = $form;
	
	$searchClient = new Client_Form_ClientSearch();
	$searchClient->setAction( $this->_helper->url( 'search-client' ) );
	$this->view->searchClient = $searchClient;
    }
    
    /**
     * 
     */
    public function actionPlanAction()
    {
	if ( $this->getRequest()->isXMLHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	// Form Action Plan Group
	$form = $this->_initForm( 'actionPlanGroup' );
	$data = array();
	
	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $data['fk_id_action_plan_group'] = $id;
	    
	    if ( !$this->view->caseActiveGroup()->hasAccessEdit() )
		$form->getElement( 'save' )->setAttrib( 'disabled', true );
	}
	
	$form->populate( $data );
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function searchClientAction()
    {
	$this->_helper->layout()->disableLayout();
	$mapperClient = new Client_Model_Mapper_Client();
	$this->view->rows = $mapperClient->listByFilters( $this->_getAllParams() );
    }
    
    /**
     * 
     */
    public function editAction()
    {
	$this->_forward( 'index' );
    }
    
     /**
     * @access 	public
     * @return 	void
     */
    public function addClientAction()
    {
	if ( $this->getRequest()->isPost() ) {
	    
	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->addClient();
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
    public function listClientGroupAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listClientGroup( $this->_getParam( 'id' ) );
    }
    
     /**
     * 
     */
    public function listBarriersAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listBarriers( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function resultBarrierAction()
    {
	$this->_helper->layout()->disableLayout();
	$data = $this->_getAllParams();
	
	$mapperIntervention = new Register_Model_Mapper_BarrierIntervention();
	$intervention = $mapperIntervention->detail( $data['fk_id_barrier_intervention'] );
	
	$form = $this->_initForm( 'caseGroupResult' );
	$form->populate( $data );
	
	$this->view->caseActiveGroup()->setCase( $data['fk_id_action_plan_group'] );
	
	if ( !$this->view->caseActiveGroup()->hasAccessEdit() )
	    $form->getElement( 'save' )->setAttrib( 'disabled', true );
	
	$this->view->form = $form;
	$this->view->barrier = $intervention;
    }
    
    /**
     * 
     */
    public function listCaseResultAction()
    {
	$this->_helper->layout()->disableLayout();
	$data = $this->_getAllParams();
	
	$this->view->rows = $this->_mapper->listCasesResult( $data );
	
	$this->view->caseActiveGroup()->setCase( $data['fk_id_action_plan_group'] );
	
	$mapperCase = new Client_Model_Mapper_Case();
	$this->view->optStatus = $mapperCase->getOptionsStatus();
    }
    
    /**
     * 
     */
    public function jobBarrierAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->id = $this->_getParam( 'barrier' );
    }
    
    /**
     * 
     */
    public function listJobBarrierAction()
    {
	$this->_helper->layout()->disableLayout();
    }
    
    /**
     * 
     */
    public function listJobBarrierRowsAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listJobIntervention( $this->_getParam( 'intervention' ), $this->_getParam( 'case' ) );
    }
    
    /**
     * 
     */
    public function searchJobAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'vacancy', 'job' );
    }
    
    /**
     * 
     */
    public function jobClientAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$mapperVacancy = new Job_Model_Mapper_JobVacancy();
	$vacancy = $mapperVacancy->detailVacancy( $this->_getParam( 'vacancy' ) );
	$this->view->vacancy = $vacancy;
    }
    
    /**
     * 
     */
    public function listClientVacancyAction()
    {
	$this->_helper->layout()->disableLayout();
	$case = $this->_getParam( 'case' );
	$vacancy = $this->_getParam( 'vacancy' );
	
	$this->view->rows = $this->_mapper->listClientVacancy( $case, $vacancy );
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function clientToVacancyAction()
    {
	if ( $this->getRequest()->isPost() ) {
	    
	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->clientToVacancy();
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
    public function classBarrierAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->id = $this->_getParam( 'barrier' );
    }
    
    /**
     * 
     */
    public function listClassBarrierAction()
    {
	$this->_helper->layout()->disableLayout();
    }
    
    /**
     * 
     */
    public function listClassBarrierRowsAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listClassIntervention( $this->_getParam( 'intervention' ), $this->_getParam( 'case' ) );
    }
    
    /**
     * 
     */
    public function searchClassAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'register', 'student-class' );
    }
    
    /**
     * 
     */
    public function classClientAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	$class = $mapperStudentClass->detailStudentClass( $this->_getParam( 'class' ) );
	$this->view->class = $class;
    }
    
    /**
     * 
     */
    public function listClientClassAction()
    {
	$this->_helper->layout()->disableLayout();
	$case = $this->_getParam( 'case' );
	$class = $this->_getParam( 'class' );
	
	$this->view->rows = $this->_mapper->listClientClass( $case, $class );
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function clientToClassAction()
    {
	if ( $this->getRequest()->isPost() ) {
	    
	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->clientToClass();
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
    public function finishAction()
    {
	$this->_helper->layout()->disableLayout();
    }
    
    /**
     * 
     */
    public function listFinishAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->itensFinish = $this->_mapper->checkFinishCase( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function finishCaseAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->finishCase();
	$message = $this->_mapper->getMessage()->toArray();

	$result = array(
	    'status'	    => (bool)$return,
	    'id'	    => $return,
	    'message'	    => $message
	);

	$this->_helper->json( $result );
    }
    
    /**
     * 
     */
    public function jobTrainingBarrierAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->id = $this->_getParam( 'barrier' );
    }
    
    /**
     * 
     */
    public function listJobTrainingBarrierAction()
    {
	$this->_helper->layout()->disableLayout();
    }
    
    /**
     * 
     */
    public function listJobTrainingBarrierRowsAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listJobTrainingIntervention( $this->_getParam( 'intervention' ), $this->_getParam( 'case' ) );
    }
    
    /**
     * 
     */
    public function searchJobTrainingAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'job-training', 'student-class' );
    }
    
    /**
     * 
     */
    public function jobTrainingClientAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$mapperJobTraining = new StudentClass_Model_Mapper_JobTraining();
	$jobTraining = $mapperJobTraining->detailJobTraining( $this->_getParam( 'job-training' ) );
	$this->view->jobTraining = $jobTraining;
    }
    
    /**
     * 
     */
    public function listClientJobTrainingAction()
    {
	$this->_helper->layout()->disableLayout();
	$case = $this->_getParam( 'case' );
	$jobTraining = $this->_getParam( 'job-training' );
	
	$this->view->rows = $this->_mapper->listClientJobTraining( $case, $jobTraining );
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function clientToJobTrainingAction()
    {
	if ( $this->getRequest()->isPost() ) {
	    
	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->clientToJobTraining();
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
    public function printAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$id = $this->_getParam( 'id' );
	
	$this->view->case = $this->_mapper->detailCase( $id );
	$this->view->barriers = $this->_mapper->listBarriers( $id );
	$this->view->clients = $this->_mapper->listClientGroup( $id );
	
	$this->view->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
    }
    
    /**
     * 
     */
    public function printBarrierAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$id = $this->_getParam( 'id' );
	
	$this->view->case = $this->_mapper->detailCase( $id );
	$this->view->all = $this->_getParam( 'all' );
	$this->view->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
	
	$data = $this->_getAllParams();
	
	$mapperIntervention = new Register_Model_Mapper_BarrierIntervention();
	$intervention = $mapperIntervention->detail( $data['fk_id_barrier_intervention'] );
	$this->view->barrier = $intervention;
	
	$this->view->clients = $this->_mapper->listCasesResult( $data );
    }
}