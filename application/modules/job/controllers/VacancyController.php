<?php

/**
 * 
 */
class Job_VacancyController extends App_Controller_Default
{
    
    /**
     *
     * @var Job_Model_Mapper_JobVacancy
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Job_Model_Mapper_JobVacancy();
	
	$stepBreadCrumb = array(
	    'label' => 'Vaga Empregu',
	    'url'   => 'job/vacancy'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Vaga Empregu' );
	
	$id = $this->_getParam( 'id' );
	$this->view->jobActive( $id );
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
		'url'	=> 'job/vacancy'
	    );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Vaga',
		'url'	=> 'job/vacancy/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->fetchRow( $id );
	    $this->view->title()->setSubTitle( $row->vacancy_titule );
	}

	$this->view->id = $id;
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Buka Vaga Empregu',
	    'url'   => 'job/vacancy/list'
	);
	
	$this->view->menu()->setActivePath( 'job/vacancy/list' );

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );

	$searchJobVacancy = new Job_Form_VacancySearch();
	$searchJobVacancy->setAction( $this->_helper->url( 'search-vacancy' ) );

	$this->view->form = $searchJobVacancy;
    }
    
    /**
     * 
     */
    public function searchVacancyAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listByFilters( $this->_getAllParams() );
	$this->view->listAjax = $this->_getParam( 'list-ajax' );
    }
    
    /**
     * 
     */
    public function informationAction()
    {
	// Form Information
	$formInformation = $this->_initForm( 'information' );

	$id = $this->_getParam( 'id' );
	$data = array();
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    
	    $date = new Zend_Date();
	    $data['registration_date'] = $date->set( $data['registration_date'] )->toString( 'dd/MM/yyyy' );
	    $data['open_date'] = $date->set( $data['open_date'] )->toString( 'dd/MM/yyyy' );
	    $data['close_date'] = $date->set( $data['close_date'] )->toString( 'dd/MM/yyyy' );
	    
	    $data['start_salary'] = number_format( $data['start_salary'], 2, '.', '' );
	    $data['finish_salary'] = number_format( $data['finish_salary'], 2, '.', '' );
	    $data['additional_salary'] = number_format( $data['additional_salary'], 2, '.', '' );
	    
	    if ( !empty( $data['start_job_date'] ) )
		$data['start_job_date'] = $date->set( $data['start_job_date'] )->toString( 'dd/MM/yyyy' );
	    
	    if ( !$this->view->jobActive()->hasAccessEdit() )
		$formInformation->getElement( 'save' )->setAttrib( 'disabled', true );
	} else
	    $data['fk_id_dec'] = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
	
	$formInformation->populate( $data );
	$this->view->form = $formInformation;
    }
    
     /**
     * 
     */
    public function addressAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Address
	$formAddress = $this->_initForm( 'address' );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    $data['fk_id_jobvacancy'] = $data['id_jobvacancy'];
	    
	    $formAddress->populate( $data );
	    
	    if ( !$this->view->jobActive()->hasAccessEdit() )
		$formAddress->getElement( 'save' )->setAttrib( 'disabled', true );
	}

	$this->view->form = $formAddress;
    }
    
    /**
     * 
     */
    public function scholarityAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Scholarity
	$formScholarity = $this->_initForm( 'scholarity' );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    $data['fk_id_jobvacancy'] = $data['id_jobvacancy'];
	    
	    $formScholarity->populate( $data );
	    
	    if ( !$this->view->jobActive()->hasAccessEdit() )
		$formScholarity->getElement( 'save' )->setAttrib( 'disabled', true );
	}

	$this->view->form = $formScholarity;
    }
    
    /**
     * 
     */
    public function trainingAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Training
	$formTraining = $this->_initForm( 'training' );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    $data['fk_id_jobvacancy'] = $data['id_jobvacancy'];
	    
	    $formTraining->populate( $data );
	    
	    if ( !$this->view->jobActive()->hasAccessEdit() )
		$formTraining->getElement( 'save' )->setAttrib( 'disabled', true );
	}

	$this->view->form = $formTraining;
    }
    
    /**
     * 
     */
    public function languageAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Language
	$formLanguage = $this->_initForm( 'language' );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    $data['fk_id_jobvacancy'] = $data['id_jobvacancy'];
	    
	    $formLanguage->populate( $data );
	    
	    if ( !$this->view->jobActive()->hasAccessEdit() )
		$formLanguage->getElement( 'save' )->setAttrib( 'disabled', true );
	}

	$this->view->form = $formLanguage;
    }
    
    /**
     * 
     */
    public function handicappedAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Handicapped
	$formHandicapped = $this->_initForm( 'handicapped' );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    $data['fk_id_jobvacancy'] = $data['id_jobvacancy'];
	    
	    $formHandicapped->populate( $data );
	    
	    if ( !$this->view->jobActive()->hasAccessEdit() )
		$formHandicapped->getElement( 'save' )->setAttrib( 'disabled', true );
	}

	$this->view->form = $formHandicapped;
    }
    
    /**
     * 
     */
    public function listAddressAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listAddress( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteAddressAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteAddress();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function listScholarityAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listScholarity( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteScholarityAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteScholarity();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function listTrainingAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listTraining( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteTrainingAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteTraining();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function listLanguageAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listLanguage( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteLanguageAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteLanguage();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
    }
    
     /**
     * 
     */
    public function listHandicappedAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listHandicapped( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteHandicappedAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteHandicapped();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function fetchHandicappedAction()
    {
	$contact = $this->_mapper->fetchHandicapped( $this->_getParam( 'id' ) );
	$this->_helper->json( $contact->toArray() );
    }
    
    /**
     *
     * @param string $formName
     * @return Zend_Form
     */
    protected function _initForm( $formName )
    {
	$className = 'Job_Form_Vacancy' . ucfirst( $formName );

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
    public function viewAction()
    {
	$vacancy = $this->_mapper->detailVacancy( $this->_getParam( 'id' ) );
	$this->view->vacancy = $vacancy;
    }
    
    /**
     * 
     */
    public function printAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$id = $this->_getParam( 'id' );
	
	$this->view->vacancy = $this->_mapper->detailVacancy( $id );
	$this->view->address = $this->_mapper->listAddress( $id );
	$this->view->scholarity = $this->_mapper->listScholarity( $id );
	$this->view->training = $this->_mapper->listTraining( $id );
	$this->view->language = $this->_mapper->listLanguagePrint( $id );
	$this->view->handicapped = $this->_mapper->listHandicapped( $id );
    }
    
    /**
     * 
     */
    public function closeAction()
    {
	// Form Close
	$formClose = $this->_initForm( 'close' );
	
	$this->view->title()->setSubTitle( 'Taka Vaga Emprego' );

	$id = $this->_getParam( 'id' );
	$vacancy = $this->_mapper->detailVacancy( $id );
	
	$data = array();
	$data['fk_id_jobvacancy'] = $id;
	$data['num_position'] = $vacancy->num_position;
	
	$formClose->populate( $data );

	$this->view->vacancy = $vacancy;
	$this->view->form = $formClose;
	$this->view->id = $id;
	
	$mapperJobMatch = new Job_Model_Mapper_JobMatch();
	$this->view->rows = $mapperJobMatch->listShortlist( $id );
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
	$data['fk_id_jobvacancy'] = $id;
	
	$formCancel->populate( $data );

	$this->view->form = $formCancel;
    }
    
    /**
     * 
     */
    public function searchClientAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Buka Kliente Sira',
	    'url'   => 'job/vacancy/search-client'
	);
	
	$this->view->title()->setSubTitle( 'Buka Kliente Sira' );

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );

	$searchClient = new Job_Form_SearchClient();
	$searchClient->setAction( $this->_helper->url( 'list-client' ) );
	
	// Set the path to be active in the menu
	$this->view->menu()->setActivePath( 'job/vacancy/search-client' );

	$this->view->form = $searchClient;
    }
    
    /**
     * 
     */
    public function listClientAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$mapperMatch = new Job_Model_Mapper_JobMatch();
	$this->view->rows = $mapperMatch->listManual( $this->_getAllParams() );
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
    public function searchScholarityAction()
    {
	$filters = array(
	    'category'	=> $this->_getParam( 'category' ),
	    'type'	=> $this->_getParam( 'type' )
	);
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$optScholarity = $mapperScholarity->getOptionsScholarity( $filters );

	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $optScholarity as $id => $value )
	    $opts[] = array( 'id' => $id, 'name' => $value );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function saveClientListAction()
    {
	if ( $this->getRequest()->isPost() ) {
	    
	    $this->_mapper->setData( $this->getRequest()->getPost() );
	    $return = $this->_mapper->saveClientList();
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
    public function printListAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$id = $this->_getParam( 'id' );
	
	$this->view->user = Zend_Auth::getInstance()->getIdentity();
	$this->view->quick = $this->_mapper->detailQuickList( $id );
	$this->view->list = $this->_mapper->listQuickList( $id );
    }
}