<?php

/**
 * 
 */
class Client_CaseController extends App_Controller_Default
{
    
    /**
     *
     * @var Client_Model_Mapper_Case
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Client_Model_Mapper_Case();
	
	$stepBreadCrumb = array(
	    'label' => 'Kazu',
	    'url'   => 'client/case'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Kazu' );
	
	$id = $this->_getParam( 'id' );
	$this->view->caseActive( $id );
    }
    
    /**
     * 
     */
    public function indexAction()
    {
	$this->_helper->redirector->goToSimple( 'index', 'case-group' );
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Buka Kazu',
	    'url'   => 'client/case/list'
	);
	
	$this->view->menu()->setActivePath( 'client/case/list' );

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );

	$searchCase = new Client_Form_CaseSearch();
	$searchCase->setAction( $this->_helper->url( 'search-case' ) );

	$this->view->form = $searchCase;
    }
    
    /**
     * 
     */
    public function searchCaseAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
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
    public function formAction()
    {
	$id = $this->_getParam( 'id' );
	
	$mapperClient = new Client_Model_Mapper_Client();

	if ( empty( $id ) ) {
	    
	    $idClient = $this->_getParam( 'client' );
	    if ( empty( $idClient ) )
		$this->_helper->redirector->goToSimple( 'list', 'client' );
	    
	    $client = $mapperClient->detailClient( $idClient );
	    
	    if ( !empty( $client->case ) )
		$this->_helper->redirector->goToSimple( 'form', 'case', 'client', array( 'id' => $client->id_action_plan ) );

	    $stepBreadCrumb = array(
		'label' => 'Rejistu Foun',
		'url'	=> 'client/case'
	    );
	    
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Rejistu Kazu',
		'url'	=> 'client/case/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->fetchRow( $id );
	    if ( empty( $row ) )
		$this->_helper->redirector->goToSimple( 'list', 'client' );
	    
	    $client = $mapperClient->detailClient( $row->fk_id_perdata );
	}
	
	$this->view->title()->setSubTitle( Client_Model_Mapper_Client::buildName( $client ) );
	
	$this->view->client = $client;
	$this->view->id = $id;
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
    }
    
    /**
     * 
     */
    public function informationAction()
    {
	if ( $this->getRequest()->isXMLHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	// Form Information
	$form = $this->_initForm( 'actionPlan' );
	
	$data = array();
	$data['fk_id_counselor'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	$data['fk_id_perdata'] = $this->_getParam( 'client' );
	
	$id = $this->_getParam( 'id' );
	
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    $data = $row->toArray();
	    
	    $form->getElement( 'fk_id_counselor' )->setAttrib( 'disabled', true );
	    
	    if ( !$this->view->caseActive()->hasAccessEdit() )
		$form->getElement( 'save' )->setAttrib( 'disabled', true );
	    
	    $form->getElement( 'print_case' )->setAttrib( 'disabled', null );
	}
	
	$form->populate( $data );
	$this->view->form = $form;
    }
    
    
    /**
     * 
     */
    public function developmentAction()
    {
	if ( $this->getRequest()->isXMLHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	// Form Case Development
	$form = $this->_initForm( 'caseDevelopment' );
	
	$data = array();
	
	$id = $this->_getParam( 'id' );
	
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    $data = $row->toArray();
	    $data['fk_id_action_plan'] = $id;
	    
	    if ( !$this->view->caseActive()->hasAccessEdit() )
		$form->getElement( 'save' )->setAttrib( 'disabled', true );
	}
	
	$form->populate( $data );
	
	$this->view->id = $id;
	$this->view->case = $this->_mapper->detailCase( $id );
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function newCaseAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$mapperClient = new Client_Model_Mapper_Client();
	
	$client = $mapperClient->detailClient( $id );
	
	$this->view->client = $client;
	$this->view->data = $this->_mapper->checkClientData( $client );
    }
    
    /**
     * 
     */
    public function addBarrierAction()
    {
	if ( $this->getRequest()->isXMLHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$optType = array( '' => '' );
	$optBarrier = array();
	$optIntervention = array();
	$user = Zend_Auth::getInstance()->getIdentity()->name;
	
	$dbBarrierType = App_Model_DbTable_Factory::get( 'BarrierType' );
	$rows = $dbBarrierType->fetchAll(array(), array( 'barrier_type_name' ) );
	
	foreach ( $rows as $row )
	    $optType[$row->id_barrier_type] = $row->barrier_type_name;
	
	$this->view->user = $user;
	
	$row = $this->_getParam( 'row' );
	if ( !empty( $row ) ) {
	    
	    // Look up for the barriers
	    $dbBarrier = App_Model_DbTable_Factory::get( 'BarrierName' );
	    $barriers = $dbBarrier->fetchAll( array( 'fk_id_barrier_type = ?' => $row->fk_id_barrier_type ), array( 'barrier_name' ) );
	    
	    $optBarrier = array( '' => '' );
	    foreach ( $barriers as $barrier )
		$optBarrier[$barrier->id_barrier_name] = $barrier->barrier_name;
	    
	    // Look up for the interventions
	    $dbIntervention = App_Model_DbTable_Factory::get( 'BarrierIntervention' );
	    $interventions = $dbIntervention->fetchAll( array( 'fk_id_barrier_name = ?' => $row->fk_id_barrier_name ), array( 'barrier_Intervention_name' ) );
	    
	    $optIntervention = array( '' => '' );
	    foreach ( $interventions as $intervention )
		$optIntervention[$intervention->id_barrier_intervention] = $intervention->barrier_Intervention_name;
	    
	    $this->view->id_action_barrier = $row->id_action_barrier;
	    $this->view->fk_id_barrier_type = $row->fk_id_barrier_type;
	    $this->view->fk_id_barrier_name = $row->fk_id_barrier_name;
	    $this->view->fk_id_barrier_intervention = $row->fk_id_barrier_intervention;
	    $this->view->user = $row->user;
	}
	
	$this->view->optType = $optType;
	$this->view->optBarrier = $optBarrier;
	$this->view->optIntervention = $optIntervention;
    }
    
    /**
     * 
     */
    public function searchBarriersAction()
    {
	$dbBarrier = App_Model_DbTable_Factory::get( 'BarrierName' );
	$rows = $dbBarrier->fetchAll( array( 'fk_id_barrier_type = ?' => $this->_getParam( 'id' ) ), array( 'barrier_name' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_barrier_name, 'name' => $row->barrier_name );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function searchInterventionsAction()
    {
	$dbIntervention = App_Model_DbTable_Factory::get( 'BarrierIntervention' );
	$rows = $dbIntervention->fetchAll( array( 'fk_id_barrier_name = ?' => $this->_getParam( 'id' ) ), array( 'barrier_Intervention_name' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_barrier_intervention, 'name' => $row->barrier_Intervention_name );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function editAction()
    {
	$this->view->menu()->setActivePath( 'client/case-group' );
	$this->_forward( 'form' );
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
    public function listActionBarriersAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listBarriers( $this->_getParam( 'id' ) );
	$this->view->optStatus = $this->_mapper->getOptionsStatus();
    }
    
    /**
     * 
     */
    public function deleteBarrierAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteBarrier();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
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
	
	$mapperClient = new Client_Model_Mapper_Client();
	$this->view->client = $mapperClient->detailClient( $this->view->case->fk_id_perdata );
	
	$this->view->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
    }
    
    /**
     * 
     */
    public function caseNoteAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$formCaseNote = new Client_Form_CaseNote();
	$formCaseNote->setAction( $this->_helper->url( 'save-note' ) ); 
	$formCaseNote->getElement( 'fk_id_action_plan' )->setValue( $id );
	
	if ( !$this->view->caseActive()->hasAccessEdit() )
	    $formCaseNote->getElement( 'save' )->setAttrib( 'disabled', true );
	
	$case = $this->_mapper->detailCase( $id );
	// Search Client
	$mapperClient = new Client_Model_Mapper_Client();
	$this->view->client = $mapperClient->detailClient( $case->fk_id_perdata );
	
	$this->view->form = $formCaseNote;
    }
    
    /**
     * 
     */
    public function listNoteRowsAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$mapperNote = new Client_Model_Mapper_CaseNote();
	
	$this->view->rows = $mapperNote->listNotes( $id );
	$this->view->logged_user = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
    }
    
    /**
     * 
     */
    public function saveNoteAction()
    {
	$form = new Client_Form_CaseNote();
	$mapperNote = new Client_Model_Mapper_CaseNote();

	if ( $this->getRequest()->isPost() ) {

	    if ( $form->isValid( $this->getRequest()->getPost() ) ) {

		$mapperNote->setData( $form->getValues() );

		$return = $mapperNote->save();
		$message = $mapperNote->getMessage()->toArray();

		$result = array(
		    'status'	    => (bool)$return,
		    'id'	    => $return,
		    'description'   => $message,
		    'data'	    => $form->getValues(),
		    'fields'	    => $mapperNote->getFieldsError()
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
    public function caseNoteDetailAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$mapperCaseNote = new Client_Model_Mapper_CaseNote();
	$this->view->note = $mapperCaseNote->detailNote( $id );
    }
    
    /**
     * 
     */
    public function fetchCaseNoteAction()
    {
	$mapperCaseNote = new Client_Model_Mapper_CaseNote();
	$caseNote = $mapperCaseNote->detailNote( $this->_getParam( 'id' ) );
	$this->_helper->json( $caseNote->toArray() );
    }
    
     /**
     * 
     */
    public function deleteCaseNoteAction()
    {
	$mapperCaseNote = new Client_Model_Mapper_CaseNote();
	$return = $mapperCaseNote->setData( $this->_getAllParams() )->delete();
	
	$data = array( 'status'	=> (bool)$return );
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function appointmentAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$auth = Zend_Auth::getInstance()->getIdentity();
	$case = $this->_mapper->detailCase( $id );
	
	$data = array(
	    'fk_id_action_plan'	=> $id,
	    'fk_id_dec'		=> $auth->fk_id_dec,
	    'fk_id_sysuser'	=> $auth->id_sysuser,
	    'fk_id_counselor'	=> $case->fk_id_counselor
	);
	
	$formAppointment = new Client_Form_Appointment();
	$formAppointment->setAction( $this->_helper->url( 'save-appointment' ) ); 
	$formAppointment->populate( $data );
	
	if ( !$this->view->caseActive()->hasAccessEdit() )
	    $formAppointment->getElement( 'save' )->setAttrib( 'disabled', true );
	
	$case = $this->_mapper->detailCase( $id );
	// Search Client
	$mapperClient = new Client_Model_Mapper_Client();
	$this->view->client = $mapperClient->detailClient( $case->fk_id_perdata );
	
	$formAppointment->getElement( 'appointment_filled' )->setAttrib( 'disabled', true );
	
	$this->view->form = $formAppointment;
    }
    
     /**
     * 
     */
    public function listAppointmentRowsAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$mapperAppointment = new Client_Model_Mapper_Appointment();
	$this->view->rows = $mapperAppointment->listAppointments( $id );
    }
    
    /**
     * 
     */
    public function saveAppointmentAction()
    {
	$form = new Client_Form_Appointment();
	$mapperAppointment = new Client_Model_Mapper_Appointment();

	if ( $this->getRequest()->isPost() ) {

	    if ( $form->isValid( $this->getRequest()->getPost() ) ) {

		$mapperAppointment->setData( $form->getValues() );

		$return = $mapperAppointment->save();
		$message = $mapperAppointment->getMessage()->toArray();

		$result = array(
		    'status'	    => (bool)$return,
		    'id'	    => $return,
		    'description'   => $message,
		    'data'	    => $form->getValues(),
		    'fields'	    => $mapperAppointment->getFieldsError()
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
    public function addAppointmentObjectiveAction()
    {
	if ( $this->getRequest()->isXMLHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$optObjective = array( '' => '' );
	
	$dbAppointmentObjective = App_Model_DbTable_Factory::get( 'AppointmentObjective' );
	$rows = $dbAppointmentObjective->fetchAll(array(), array( 'objective_desc' ) );
	
	foreach ( $rows as $row )
	    $optObjective[$row->id_appointment_objective] = $row->objective_desc;
	
	$row = $this->_getParam( 'row' );
	if ( !empty( $row ) )
	    $this->view->fk_id_appointment_objective = $row->id_appointment_objective;
	
	$this->view->optObjective = $optObjective;
    }
    
    /**
     * 
     */
    public function printAppointmentAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$id = $this->_getParam( 'id' );
	
	$mapperAppointment = new Client_Model_Mapper_Appointment();
	
	$this->view->appointment = $mapperAppointment->detailAppointment( $id );
	$this->view->objectives = $mapperAppointment->listObjectives( $id );
	
	$mapperClient = new Client_Model_Mapper_Client();
	$this->view->client = $mapperClient->detailClient( $this->view->appointment->fk_id_perdata );
    }
    
    /**
     * 
     */
    public function fetchAppointmentAction()
    {
	$mapperAppointment = new Client_Model_Mapper_Appointment();
	$appointment = $mapperAppointment->detailAppointment( $this->_getParam( 'id' ) );
	
	$data = $appointment->toArray();
	
	$date = new Zend_Date( $data['date_appointment'] );
	$data['date_appointment'] = $date->toString( 'dd/MM/yyyy' );
	$data['time_appointment'] = $date->toString( 'HH:mm' );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function listAppointmentObjectiveAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$mapperAppointment = new Client_Model_Mapper_Appointment();
	
	$id = $this->_getParam( 'id' );
	$this->view->rows = $mapperAppointment->listObjectives( $id );
    }
    
    /**
     * 
     */
    public function deleteAppointmentObjectiveAction()
    {
	$mapperAppointment = new Client_Model_Mapper_Appointment();
	
	$return = $mapperAppointment->setData( $this->_getAllParams() )->deleteAppointmentObjective();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function finishAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->id = $this->_getParam( 'id' );
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
	$this->view->rows = $this->_mapper->listJobBarriers( $this->_getParam( 'barrier' ) );
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
    public function clientVacancyAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->clientVacancy();
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
	$this->view->rows = $this->_mapper->listClassBarriers( $this->_getParam( 'barrier' ) );
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
    public function clientClassAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->clientClass();
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
    public function cancelAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Cancel
	$formCancel = $this->_initForm( 'caseCancel' );

	$id = $this->_getParam( 'id' );
	
	$data = array();
	$data['fk_id_action_plan'] = $id;
	
	$formCancel->populate( $data );

	$this->view->form = $formCancel;
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
	$this->view->rows = $this->_mapper->listJobTrainingBarriers( $this->_getParam( 'barrier' ) );
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
    public function clientJobTrainingAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->clientJobTraining();
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
    public function certificateAction()
    {
	$id = $this->_getParam( 'id' );
	
	$case = $this->_mapper->detailCase($id);
	
	$data = array(
	    'beneficiary'   => Client_Model_Mapper_Client::buildNameById( $case->fk_id_perdata ),
	    'evidence'	    => Client_Model_Mapper_Client::buildNumById( $case->fk_id_perdata )
	);
	
	$file = APPLICATION_PATH . '/../public/forms/Kazu/Sertifikadu_Atendimentu.rtf';
	App_Util_Export::toRtf( $file, $data );
    }
}