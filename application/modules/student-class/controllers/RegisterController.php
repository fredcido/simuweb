<?php

/**
 * 
 */
class StudentClass_RegisterController extends App_Controller_Default
{
    
    /**
     *
     * @var Client_Model_Mapper_StudentClass
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new StudentClass_Model_Mapper_StudentClass();
	
	$stepBreadCrumb = array(
	    'label' => 'Klase Formasaun',
	    'url'   => 'student-class/register'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Klase Formasaun' );
	
	$id = $this->_getParam( 'id' );
	$this->view->studentClassActive( $id );
    }
    
    /*
     * 
     */
    public function indexAction()
    {
	$this->_forward( 'form' );
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Buka Klase',
	    'url'   => 'student-class/register/list'
	);
	
	$this->view->menu()->setActivePath( 'student-class/register/list' );

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );

	$searchClass = new StudentClass_Form_RegisterSearch();
	$searchClass->setAction( $this->_helper->url( 'search-class' ) );
	
	$ceop = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
	$searchClass->getElement( 'fk_id_dec' )->setValue( $ceop );

	$this->view->form = $searchClass;
    }
    
    /**
     * 
     */
    public function searchClassAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
	$this->view->listAjax = $this->_getParam( 'list-ajax' );
    }

    /**
     * 
     */
    public function formAction()
    {
	$id = $this->_getParam( 'id' );

	if ( empty( $id ) ) {

	    $stepBreadCrumb = array(
		'label' => 'Rejistu Foun',
		'url'	=> 'student-class/register'
	    );
	    
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Rejistu',
		'url'	=> 'student-class/register/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->fetchRow( $id );
	    if ( empty( $row ) )
		$this->_helper->redirector->goToSimple( 'index' );
		
	    $this->view->title()->setSubTitle( $row->class_name );
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

	    $row = $this->_mapper->fetchRow( $id );
	    $data = $row->toArray();
	    
	    $date = new Zend_Date();
	    $data['start_date'] = $date->set( $data['start_date'] )->toString( 'dd/MM/yyyy' );
	    $data['schedule_finish_date'] = $date->set( $data['schedule_finish_date'] )->toString( 'dd/MM/yyyy' );
	    
	    $data['student_payment'] = number_format( $data['student_payment'], 2, '.', '' );
	    $data['subsidy'] = number_format( $data['subsidy'], 2, '.', '' );
	    
	    if ( !empty( $data['real_finish_date'] ) )
		$data['real_finish_date'] = $date->set( $data['real_finish_date'] )->toString( 'dd/MM/yyyy' );
	    
	    $formInformation->getElement( 'fk_id_fefpeduinstitution' )->setAttrib( 'disabled', true );
	    $formInformation->getElement( 'fk_id_dec' )->setAttrib( 'disabled', true );
	    
	    $students = $this->_mapper->listClientClass( $id );
	    if ( $students->count() > 0 )
		$formInformation->getElement( 'fk_id_perscholarity' )->setAttrib( 'disabled', true );
	    
	    if ( !$this->view->studentClassActive()->hasAccessEdit() )
		$formInformation->getElement( 'save' )->setAttrib( 'disabled', true );
	    
	    $filters = array( 
		'institution'   => $data['fk_id_fefpeduinstitution'],
		'type'		=> Register_Model_Mapper_PerTypeScholarity::NON_FORMAL
	    );

	    $mapperScholarity = new Register_Model_Mapper_PerScholarity();
	    $optScholarity = $mapperScholarity->getOptionsScholarity( $filters );
	    $formInformation->getElement( 'fk_id_perscholarity' )->addMultiOptions( $optScholarity );
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
	if ( !empty( $id ) ) {

	    $data['fk_id_fefpstudentclass'] = $id;
	    $formCourse->populate( $data );
	    
	    if ( !$this->view->studentClassActive()->hasAccessEdit() )
		$formCourse->getElement( 'save' )->setAttrib( 'disabled', true );
	}

	$this->view->form = $formCourse;
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
	
	$form = new StudentClass_Form_RegisterMatch();
	$form->setAction( $this->_helper->url( 'search-client' ) );
	
	$form->getElement( 'fk_id_fefpstudentclass' )->setValue( $this->_getParam( 'id' ) );
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
    public function saveShortlistAction()
    {
	if ( $this->getRequest()->isPost() ) {
	    
	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->saveShortlist();
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
    public function saveClassAction()
    {
	if ( $this->getRequest()->isPost() ) {
	    
	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->saveClass();
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
    public function shortlistAction()
    {
	$this->_helper->layout()->disableLayout();
    }
    
    /**
     * 
     */
    public function clientAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Client
	$formClient = $this->_initForm( 'client' );

	$id = $this->_getParam( 'id' );

	$data['fk_id_fefpstudentclass'] = $id;
	$formClient->populate( $data );

	$this->view->form = $formClient;
	$this->view->competency = $this->_mapper->listCompetencyClass( $id );
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
	$this->view->itensFinish = $this->_mapper->checkFinishClass( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function finishClassAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->finishClass();
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
    public function listClientAction()
    {
	$this->_helper->layout()->disableLayout();
	$id = $this->_getParam( 'id' );
	$this->view->rows = $this->_mapper->listClientClass( $id );
	
	$this->view->optionsStatus = array(
	    StudentClass_Model_Mapper_StudentClass::ENROLLED	 => $this->view->nomenclature()->resultClass( StudentClass_Model_Mapper_StudentClass::ENROLLED ),
	    StudentClass_Model_Mapper_StudentClass::DROPPED_OUT  => $this->view->nomenclature()->resultClass( StudentClass_Model_Mapper_StudentClass::DROPPED_OUT ),
	    StudentClass_Model_Mapper_StudentClass::COMPLETED	 => $this->view->nomenclature()->resultClass( StudentClass_Model_Mapper_StudentClass::COMPLETED ),
	    StudentClass_Model_Mapper_StudentClass::GRADUATED	 => $this->view->nomenclature()->resultClass( StudentClass_Model_Mapper_StudentClass::GRADUATED ),
	    StudentClass_Model_Mapper_StudentClass::NO_MANDATORY => $this->view->nomenclature()->resultClass( StudentClass_Model_Mapper_StudentClass::NO_MANDATORY )
	);
	
	$this->view->competency = $this->_mapper->listCompetencyClass( $id );
    }
    
    /**
     * 
     */
    public function competenciesAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Competencies
	$formCompetencies = $this->_initForm( 'competencies' );

	$id = $this->_getParam( 'id' );
	$client = $this->_getParam( 'client' );

	$data['fk_id_fefpstudentclass'] = $id;
	$data['fk_id_perdata'] = $client;
	
	$dbStudentResult = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	$row = $dbStudentResult->fetchRow( array( 'fk_id_fefpstudentclass = ?' => $id, 'fk_id_perdata = ?' => $client ) );
	if ( !empty( $row ) && !empty( $row->date_drop_out ) ) {
	    
	    $data['date_drop_out'] = $this->view->date( $row->date_drop_out );
	    $formCompetencies->getElement( 'date_drop_out' )->setAttrib( 'disabled', null );
	}
	
	$formCompetencies->populate( $data );
	
	$mapperClient = new Client_Model_Mapper_Client();

	$this->view->form = $formCompetencies;
	$this->view->competency = $this->_mapper->listCompetencyClass( $id, $client );
	$this->view->client = $mapperClient->fetchRow( $client );
	
	$this->view->optionsStatus = array(
	    StudentClass_Model_Mapper_StudentClass::ENROLLED	=> $this->view->nomenclature()->resultClass( StudentClass_Model_Mapper_StudentClass::ENROLLED ),
	    StudentClass_Model_Mapper_StudentClass::DROPPED_OUT => $this->view->nomenclature()->resultClass( StudentClass_Model_Mapper_StudentClass::DROPPED_OUT ),
	    StudentClass_Model_Mapper_StudentClass::COMPLETED	=> $this->view->nomenclature()->resultClass( StudentClass_Model_Mapper_StudentClass::COMPLETED ),
	    StudentClass_Model_Mapper_StudentClass::GRADUATED	=> $this->view->nomenclature()->resultClass( StudentClass_Model_Mapper_StudentClass::GRADUATED ),
	    StudentClass_Model_Mapper_StudentClass::NO_MANDATORY => $this->view->nomenclature()->resultClass( StudentClass_Model_Mapper_StudentClass::NO_MANDATORY )
	);
    }
    
    /**
     * 
     */
    public function listShortlistAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listShortlist( $this->_getParam( 'id' ) );
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
    public function deleteShortlistAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteShortlist();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
    }
    
    /**
     *
     * @param string $formName
     * @return Zend_Form
     */
    protected function _initForm( $formName )
    {
	$className = 'StudentClass_Form_Register' . ucfirst( $formName );

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
	$this->_forward( 'form' );
    }
    
    /**
     * 
     */
    public function searchCourseAction()
    {
	$id = $this->_getParam( 'id' );
	
	$filters = array( 
	    'institution'   => $id,
	    'type'	    => Register_Model_Mapper_PerTypeScholarity::NON_FORMAL
	);
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$optScholarity = $mapperScholarity->getOptionsScholarity( $filters );

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
	
	$this->view->class = $this->_mapper->detailStudentClass( $id );
    }
    
     /**
     * 
     */
    public function printShortlistAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$id = $this->_getParam( 'id' );
	
	$this->view->class = $this->_mapper->detailStudentClass( $id );;
	$this->view->shortlist = $this->_mapper->listShortlist( $id );
    }
    
    /**
     * 
     */
    public function cancelAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Cancel
	$formCancel = $this->_initForm( 'cancel' );

	$id = $this->_getParam( 'id' );
	
	$data = array();
	$data['fk_id_fefpstudentclass'] = $id;
	
	$formCancel->populate( $data );

	$this->view->form = $formCancel;
    }
}