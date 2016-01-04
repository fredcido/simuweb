<?php

/**
 * 
 */
class Client_ClientController extends App_Controller_Default
{
    
    /**
     *
     * @var Client_Model_Mapper_Client
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Client_Model_Mapper_Client();
	
	$stepBreadCrumb = array(
	    'label' => 'Kliente',
	    'url'   => 'client/client'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Kliente' );
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Buka Kliente',
	    'url'   => 'client/client/list'
	);
	
	$this->view->menu()->setActivePath( 'client/client/list' );

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );

	$searchClient = new Client_Form_ClientSearch();
	$searchClient->setAction( $this->_helper->url( 'search-client' ) );

	$this->view->form = $searchClient;
    }
    
    /**
     * 
     */
    public function searchClientAction()
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
		'url'	=> 'client/client'
	    );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Rejistu',
		'url'	=> 'client/client/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->detailClient( $id );
	    if ( empty( $row ) )
		$this->_helper->redirector->goToSimple( 'index' );
		
	    $this->view->client = $row;
	    $this->view->title()->setSubTitle( Client_Model_Mapper_Client::buildName( $row ) );
	}

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
	$formInformation = $this->_initForm( 'information' );
	
	$data = array();
	$data['fk_id_dec'] = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    
	    $data['birth_date'] = $data['birth_date_format'];
	    $data['date_registration'] = $data['date_registration_format'];
	    $data['age'] = App_General_Date::getAge( new Zend_Date( $data['birth_date'] ) );
	    
	    // Disable the fields
	    $formInformation->getElement( 'date_registration' )->setAttrib( 'disabled', true );
	    $formInformation->getElement( 'fk_id_adddistrict' )->setAttrib( 'disabled', true );
	    
	    // Search the district by acronym
	    $mapperDistrict = new Register_Model_Mapper_AddDistrict();
	    $district = $mapperDistrict->fetchByAcronym( $data['num_district'] );
	    
	    $data['fk_id_adddistrict'] = $district->id_adddistrict;
	    
	    $mapperSubDistrict = new Register_Model_Mapper_AddSubDistrict();
	    $rows = $mapperSubDistrict->listAll( $district->id_adddistrict );

	    $optsSub[''] = '';
	    foreach( $rows as $row )
		$optsSub[$row->acronym] = $row->sub_district;
	    
	    $formInformation->getElement( 'num_subdistrict' )->setAttrib( 'disabled', true )->addMultiOptions( $optsSub );
	}
	
	$formInformation->populate( $data );
	$this->view->form = $formInformation;
    }
    
    /**
     * 
     */
    public function documentAction()
    {
	if ( $this->getRequest()->isXMLHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	// Form Document
	$formDocument = $this->_initForm( 'document' );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    $data['fk_id_perdata'] = $data['id_perdata'];
	    $formDocument->populate( $data );
	}

	$this->view->form = $formDocument;
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
	    $data['fk_id_perdata'] = $data['id_perdata'];
	    
	    $formAddress->populate( $data );
	}

	$this->view->form = $formAddress;
    }
    
    
    /**
     * 
     */
    public function visitAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Visit
	$formVisit = $this->_initForm( 'visit' );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    $data['fk_id_perdata'] = $data['id_perdata'];
	    
	    $formVisit->populate( $data );
	}

	$this->view->form = $formVisit;
    }
    
    /**
     * 
     */
    public function scholarityAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->id = $this->_getParam( 'id' );
    }
    
    /**
     * 
     */
    public function formalScholarityAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Formal Scholarity
	$formFormalScholarity = $this->_initForm( 'formalScholarity' );

	$id = $this->_getParam( 'id' );

	$data['fk_id_perdata'] = $id;
	$formFormalScholarity->populate( $data );

	$this->view->form = $formFormalScholarity;
    }
    
    /**
     * 
     */
    public function nonFormalScholarityAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Non Form Formal Scholarity
	$formNonFormalScholarity = $this->_initForm( 'nonFormalScholarity' );

	$id = $this->_getParam( 'id' );

	$data['fk_id_perdata'] = $id;
	$formNonFormalScholarity->populate( $data );

	$this->view->form = $formNonFormalScholarity;
    }
    
    /**
     * 
     */
    public function competencyAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->id = $this->_getParam( 'id' );
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

	    $data['fk_id_perdata'] = $id;
	    $formLanguage->populate( $data );
	}

	$this->view->form = $formLanguage;
    }
    
    /**
     * 
     */
    public function knowledgeAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Knowledge
	$formKnowledge = $this->_initForm( 'knowledge' );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $data['fk_id_perdata'] = $id;
	    $formKnowledge->populate( $data );
	}

	$this->view->form = $formKnowledge;
    }
    
    /**
     * 
     */
    public function experienceAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Experience
	$formExperience = $this->_initForm( 'experience' );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $data['fk_id_perdata'] = $id;
	    $formExperience->populate( $data );
	}

	$this->view->form = $formExperience;
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
	$data['fk_id_perdata'] = $id;
	$formHandicapped->populate( $data );

	$this->view->form = $formHandicapped;
    }
    
     /**
     * 
     */
    public function dependentAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Dependent
	$formDepedendent = $this->_initForm( 'dependent' );

	$id = $this->_getParam( 'id' );
	$data['fk_id_perdata'] = $id;
	$formDepedendent->populate( $data );

	$this->view->form = $formDepedendent;
    }
    
     /**
     * 
     */
    public function contactAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->id = $this->_getParam( 'id' );
    }
    
     /**
     * 
     */
    public function contactClientAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Contact
	$formContact = $this->_initForm( 'contact' );

	$id = $this->_getParam( 'id' );
	$data['fk_id_perdata'] = $id;
	$formContact->populate( $data );

	$this->view->form = $formContact;
    }
    
    /**
     * 
     */
    public function aboutAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form About
	$formAbout = $this->_initForm( 'about' );

	$id = $this->_getParam( 'id' );
	$data['fk_id_perdata'] = $id;
	$formAbout->populate( $data );

	$this->view->form = $formAbout;
    }
    
    /**
     * 
     */
    public function bankAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Bank
	$formBank = $this->_initForm( 'bank' );

	$id = $this->_getParam( 'id' );
	$data['fk_id_perdata'] = $id;
	$formBank->populate( $data );

	$this->view->form = $formBank;
    }
    
    /**
     * 
     */
    public function listBankAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listBank( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteBankAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteBank();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function listAboutAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listAbout( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteAboutAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteAbout();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function listContactAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listContact( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteContactAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteContact();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function listDependentAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listDependent( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteDependentAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteDependent();
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
    public function listKnowledgeAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listKnowledge( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteKnowledgeAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteKnowledge();
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
	$className = 'Client_Form_Client' . ucfirst( $formName );

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
    public function listDocumentAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listDocument( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteDocumentAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteDocument();
	
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
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
    public function listVisitAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listVisit( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteVisitAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteVisit();
	
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
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
    public function listFormalScholarityAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listScholarity( $this->_getParam( 'id' ), Register_Model_Mapper_PerTypeScholarity::FORMAL );
    }
    
    /**
     * 
     */
    public function listNonFormalScholarityAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listScholarity( $this->_getParam( 'id' ), Register_Model_Mapper_PerTypeScholarity::NON_FORMAL );
    }
    
    /**
     * 
     */
    public function listExperienceAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listExperience( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function deleteExperienceAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteExperience();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
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
    public function calcAgeAction()
    {
	$birth = $this->_getParam( 'birth' );
	$date = new Zend_Date( $birth );
	
	$now = $this->_getParam( 'finish' );
	if ( !empty( $now ) )
	    $now = new Zend_Date( $now );
	
	$data = array( 'age' => App_General_Date::getAge( $date, $now ) );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function informationDocumentAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$clientDocument = $this->_initForm( 'document' );
	$this->view->form = $clientDocument;
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
	    $opts[] = array( 'id' => $row->acronym, 'name' => $row->sub_district );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function sameNameAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	$this->view->client = $this->_mapper->fetchRow( $id );
	$this->view->flag = $this->_getParam( 'flag' );
	$this->view->document = $this->_mapper->getEleitoralDocument( $id );
    }
    
    /**
     * 
     */
    public function fetchNumClientAction()
    {
	$num = Client_Model_Mapper_Client::buildNumById( $this->_getParam( 'id' ) );
	$this->_helper->json( array( 'num' => $num ) );
    }
    
    /**
     * 
     */
    public function searchDistrictAction()
    {
	$mapperDistrict = new Register_Model_Mapper_AddDistrict();
	$rows = $mapperDistrict->listAll( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_adddistrict, 'name' => $row->District );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function searchSubDistrictAddressAction()
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
    public function searchSukuAction()
    {
	$mapperSuku = new Register_Model_Mapper_AddSuku();
	$rows = $mapperSuku->listAll( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_addsucu, 'name' => $row->sucu );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function viewAction()
    {
	$id = $this->_getParam( 'id' );
	
	$client = $this->_mapper->detailClient( $id );
	
	$this->view->client = $client;
	$this->view->id = $id;
    }
    
    /**
     * 
     */
    public function historyAction()
    {
	$id = $this->_getParam( 'id' );
	
	$client = $this->_mapper->detailClient( $id );
	
	$this->view->client = $client;
	$this->view->id = $id;
	$this->view->history = $this->_mapper->listHistory( $id );
    }
    
    /**
     * 
     */
    public function printAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$id = $this->_getParam( 'id' );
	
	$this->view->client = $this->_mapper->detailClient( $id );
	$this->view->address = $this->_mapper->listAddress( $id );
	$this->view->language = $this->_mapper->listLanguagePrint( $id );
	$this->view->knowledge = $this->_mapper->listKnowledgePrint( $id );
	$this->view->formalScholarity = $this->_mapper->listScholarity( $id, 1 );
	$this->view->nonFormalScholarity = $this->_mapper->listScholarity( $id, 2 );
	$this->view->experience = $this->_mapper->listExperience( $id );
	$this->view->contact = $this->_mapper->listContact( $id );
    }
    
    /**
     * 
     */
    public function evidenceAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_helper->viewRenderer->setNoRender( true );
	
	$id = $this->_getParam( 'id' );
	$client = $this->_mapper->detailClient( $id );
	
	if ( !empty( $client->id_action_plan ) )
	    App_General_EvidenceCard::generate( $client );
	
	throw new Exception( "Kliente ne'e seidauk iha Kazu Akonsellamentu" );
    }
    
    /**
     * 
     */
    public function searchCourseAction()
    {
	$category = $this->_getParam( 'category' );
	$type = $this->_getParam( 'type' );
	
	$filters = array(
	    'category'	=> $category,
	    'type'	=> $type
	);
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$optScholarity = $mapperScholarity->getOptionsScholarity( $filters );

	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $optScholarity as $id => $value )
	    $opts[] = array( 'id' => $id, 'name' => $value );
	
	$this->_helper->json( $opts );
    }
}