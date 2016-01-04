<?php

/**
 * 
 */
class StudentClass_JobTrainingController extends App_Controller_Default
{
    
    /**
     *
     * @var StudentClass_Model_Mapper_JobTraining
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new StudentClass_Model_Mapper_JobTraining();
	
	$stepBreadCrumb = array(
	    'label' => 'Job Training',
	    'url'   => 'student-class/job-training'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Job Training' );
	
	$id = $this->_getParam( 'id' );
	$this->view->jobTrainingActive( $id );
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Buka Job Training',
	    'url'   => 'student-class/job-training/list'
	);
	
	$this->view->menu()->setActivePath( 'student-class/job-training/list' );

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );

	$searchClass = new StudentClass_Form_JobTrainingSearch();
	$searchClass->setAction( $this->_helper->url( 'search-job-training' ) );
	
	$ceop = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
	$searchClass->getElement( 'fk_id_dec' )->setValue( $ceop );

	$this->view->form = $searchClass;
    }
    
    /**
     * 
     */
    public function searchJobTrainingAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
	$this->view->listAjax = $this->_getParam( 'list-ajax' );
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
		'url'	=> 'student-class/job-training'
	    );
	    
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Rejistu',
		'url'	=> 'student-class/job-training/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->fetchRow( $id );
	    if ( empty( $row ) )
		$this->_helper->redirector->goToSimple( 'index' );
		
	    $this->view->title()->setSubTitle( $row->title );
	}

	$this->view->id = $id;
	$this->view->user = Zend_Auth::getInstance()->getIdentity();
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
	$formInformation = $this->_initForm( 'information' );
	
	$data = array();
	$data['fk_id_dec'] = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->detailJobTraining( $id );
	    $data = $row->toArray();
	    
	    $data['salary'] = number_format( $data['salary'], 2, '.', '' );
	    
	    $date = new Zend_Date();
	    
	    if ( !empty( $data['date_start'] ) )
		$data['date_start'] = $date->set( $data['date_start'] )->toString( 'dd/MM/yyyy' );
	    
	    if ( !empty( $data['date_finish'] ) )
		$data['date_finish'] = $date->set( $data['date_finish'] )->toString( 'dd/MM/yyyy' );
	    
	    $formInformation->getElement( 'fk_id_dec' )->setAttrib( 'disabled', true );
	    
	    if ( !$this->view->jobTrainingActive()->hasAccessEdit() )
		$formInformation->getElement( 'save' )->setAttrib( 'disabled', true );
	    
	    $this->view->editing = true;
	}
	
	$formInformation->populate( $data );
	$this->view->form = $formInformation;
    }
    
    /**
     * 
     */
    public function courseAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Course
	$formCourse = $this->_initForm( 'course' );

	$id = $this->_getParam( 'id' );
	$data = array();
	if ( !empty( $id ) ) {

	    $data['fk_id_jobtraining'] = $id;
	    $formCourse->populate( $data );
	    
	    if ( !$this->view->jobTrainingActive()->hasAccessEdit() )
		$formCourse->getElement( 'save' )->setAttrib( 'disabled', true );
	}

	$this->view->form = $formCourse;
    }
    
     /**
     * 
     */
    public function editTraineeAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Edit Trainee
	$formEditTrainee = $this->_initForm( 'editTrainee' );

	$idTrainee = $this->_getParam( 'trainee' );
	$trainee = $this->_mapper->fetchTrainee( $idTrainee );
	
	$data = $trainee->toArray();
	
	$date = new Zend_Date();
	if ( !empty( $data['date_start'] ) )
	    $data['date_start'] = $date->set( $data['date_start'] )->toString( 'dd/MM/yyyy' );

	if ( !empty( $data['date_finish'] ) )
	    $data['date_finish'] = $date->set( $data['date_finish'] )->toString( 'dd/MM/yyyy' );
	
	$formEditTrainee->populate( $data );
	
	// Fetch the client
	$mapperClient = new Client_Model_Mapper_Client();
	$this->view->client = $mapperClient->detailClient( $trainee->fk_id_perdata );
	
	if ( !$this->view->jobTrainingActive()->hasAccessEdit() )
	    $formEditTrainee->getElement( 'save' )->setAttrib( 'disabled', true );
	
	$mapperFeContract = new Fefop_Model_Mapper_FEContract();
	$contract = $mapperFeContract->getContractByTrainee( $idTrainee );
	
	if ( !empty( $contract ) ) {
	    
	    $formEditTrainee->getElement( 'date_start' )
			    ->setAttrib( 'readonly', true )
			    ->setAttrib( 'class', 'm-wrap span12');
	    
	    $formEditTrainee->getElement( 'date_finish' )
			    ->setAttrib( 'readonly', true )
			    ->setAttrib( 'class', 'm-wrap span12 ' );
	    
	    $this->view->contract = $contract->fk_id_fefop_contract;
	}
	
	$this->view->form = $formEditTrainee;
	$this->view->trainee = $trainee;
    }
    
    /**
     * 
     */
    public function candidateAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->id = $this->_getParam( 'id' );
    }
    
     /**
     * 
     */
    public function matchAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$form = new StudentClass_Form_JobTrainingMatch();
	$form->setAction( $this->_helper->url( 'search-client' ) );
	
	$form->getElement( 'fk_id_jobtraining' )->setValue( $this->_getParam( 'id' ) );
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function candidatesAction()
    {
	$this->_helper->layout()->disableLayout();
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
    public function searchClientAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$this->view->rows = $this->_mapper->listClient( $this->_getAllParams() );
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
    public function saveTraineeAction()
    {
	if ( $this->getRequest()->isPost() ) {
	    
	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->saveTrainee();
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
	$this->view->itensFinish = $this->_mapper->checkFinishJobTraining( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function finishJobTrainingAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->finishJobTraining();
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
    public function listCourseAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listCourse( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteCourseAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteCourse();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
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
	$id = $this->_getParam( 'id' );
	$this->view->rows = $this->_mapper->listClientJobTraining( $id );
    }
    
    /**
     *
     * @param string $formName
     * @return Zend_Form
     */
    protected function _initForm( $formName )
    {
	$className = 'StudentClass_Form_JobTraining' . ucfirst( $formName );

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
    public function editAction()
    {
	$this->_forward( 'index' );
    }
    
    /**
     * 
     */
    public function searchCourseAction()
    {
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$optScholarity = $mapperScholarity->getOptionsScholarity( $this->_getAllParams() );

	$opts = array();
	foreach( $optScholarity as $id => $value )
	    $opts[] = array( 'id' => $id, 'name' => $value );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function printAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$id = $this->_getParam( 'id' );
	
	$this->view->jobtraining = $this->_mapper->detailJobTraining( $id );
	$this->view->courses = $this->_mapper->listCourse( $id );
	$this->view->clients = $this->_mapper->listClientJobTraining( $id );
    }
    
    /**
     * 
     */
    public function calculateMonthAction()
    {
	$dateIni = new Zend_Date( $this->_getParam( 'data_ini' ) );
	$dateFin = new Zend_Date( $this->_getParam( 'date_fim' ) );
	
	$diff = App_General_Date::getMonth( $dateIni, $dateFin );
	
	$this->_helper->json( array( 'duration' => $diff ) );
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
    public function fetchInstituteAction()
    {
	$mapperInsitute = new Register_Model_Mapper_EducationInstitute();
	$institute = $mapperInsitute->detailEducationInstitution( $this->_getParam( 'id' ) );
	
	$data = array();
	$data['fk_id_fefpeduinstitution'] = $institute['id_fefpeduinstitution'];
	$data['entity'] = $institute['institution'];
	
	$this->_helper->json( $data );
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
    public function fetchEnterpriseAction()
    {
	$mapperEnterpise = new Register_Model_Mapper_Enterprise();
	$enterprise = $mapperEnterpise->fetchRow( $this->_getParam( 'id' ) );
	
	$data = array();
	$data['fk_id_fefpenterprise'] = $enterprise['id_fefpenterprise'];
	$data['entity'] = $enterprise['enterprise_name'];
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function listTraineeAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Buka Job Training - Trainee',
	    'url'   => 'student-class/job-training/list-trainee'
	);
	
	$this->view->menu()->setActivePath( 'student-class/job-training/list-trainee' );

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );

	$searchClass = new StudentClass_Form_JobTrainingTraineeSearch();
	$searchClass->setAction( $this->_helper->url( 'search-job-training-trainee' ) );
	
	$ceop = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
	$searchClass->getElement( 'fk_id_dec' )->setValue( $ceop );

	$this->view->form = $searchClass;
    }
    
    /**
     * 
     */
    public function searchJobTrainingTraineeAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listTraineeByFilters( $this->_getAllParams() );
	$this->view->listAjax = $this->_getParam( 'list-ajax' );
    }
}