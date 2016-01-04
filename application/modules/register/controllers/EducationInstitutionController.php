<?php

/**
 * 
 */
class Register_EducationInstitutionController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_EducationInstitute
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_EducationInstitute();
	
	$stepBreadCrumb = array(
	    'label' => 'Instituisaun Ensinu',
	    'url'   => 'register/education-institution'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Instituisaun Ensinu' );
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Buka Instituisaun Ensinu',
	    'url'   => 'register/education-institution/list'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );

	$searchInstitution = new Register_Form_EducationInstitutionSearch();
	$searchInstitution->setAction( $this->_helper->url( 'search-institution' ) );
        
        $this->view->menu()->setActivePath( 'register/education-institution/list' );

	$this->view->form = $searchInstitution;
    }
    
    /**
     * 
     */
    public function searchInstitutionAction()
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
		'url'	=> 'register/education-institution'
	    );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Rejistu',
		'url'	=> 'register/education-institution/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->fetchRow( $id );
	    $this->view->title()->setSubTitle( $row->institution );
	}

	$this->view->id = $id;
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
    }
    
    /**
     * 
     */
    public function informationAction()
    {
	// Form Information
	$formInformation = $this->_initForm( 'information' );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    $data['date_visit'] = $this->view->date( $data['date_visit'] );
	    $data['date_registration'] = $this->view->date( $data['date_registration'] );
	    
	    $formInformation->getElement( 'fk_id_dec' )->setAttrib( 'disabled', true );
	    
	    $formInformation->populate( $data );
	}

	$this->view->form = $formInformation;
    }
    
     /**
     * 
     */
    public function contactAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Contact
	$formContact = $this->_initForm( 'contact' );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    $data['fk_id_fefpeduinstitution'] = $data['id_fefpeduinstitution'];
	    
	    $formContact->populate( $data );
	}

	$this->view->form = $formContact;
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

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    $data['fk_id_fefpeduinstitution'] = $data['id_fefpeduinstitution'];
	    
	    $formCourse->populate( $data );
	}

	$this->view->form = $formCourse;
    }
    
    /**
     * 
     */
    public function staffAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Staff
	$formStaff = $this->_initForm( 'staff' );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $row = $this->_mapper->fetchRow( $id );
	    
	    $data = $row->toArray();
	    $data['fk_id_fefpeduinstitution'] = $data['id_fefpeduinstitution'];
	    
	    $formStaff->populate( $data );
	}

	$this->view->form = $formStaff;
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
	    $data['fk_id_fefpeduinstitution'] = $data['id_fefpeduinstitution'];
	    
	    $formAddress->populate( $data );
	}

	$this->view->form = $formAddress;
    }
    
    /**
     * 
     */
    public function qualificationAction()
    {
	$this->_helper->layout()->disableLayout();
	
	// Form Qualification
	$formQualification = $this->_initForm( 'qualification' );

	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {

	    $data = array();
	    $data['fk_id_fefpeduinstitution'] = $id;
	    
	    $formQualification->populate( $data );
	}

	$this->view->form = $formQualification;
    }
    
    /**
     * 
     */
    public function listContactsAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listContacts( $this->_getParam( 'id' ) );
    }
    
     /**
     * 
     */
    public function listCoursesAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listCourses( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function listStaffAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listStaff( $this->_getParam( 'id' ) );
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
    public function listQualificationAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listQualification( $this->_getParam( 'id' ) );
    }
    
    /**
     *
     * @param string $formName
     * @return Zend_Form
     */
    protected function _initForm( $formName )
    {
	$className = 'Register_Form_EducationInstitution' . ucfirst( $formName );

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
    public function deleteContactAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteContact();
	
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
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
    public function deleteStaffAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteStaff();
	
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function deleteQualificationAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteQualification();
	
	$data = array( 'status'	=> (bool)$return );
	$this->_helper->json( $data );
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
    public function fetchContactAction()
    {
	$contact = $this->_mapper->fetchContact( $this->_getParam( 'id' ) );
	$this->_helper->json( $contact->toArray() );
    }
    
    /**
     * 
     */
    public function searchCourseAction()
    {
	$id = $this->_getParam( 'id' );
	$category = $this->_getParam( 'category' );
	
	$filters = array(
	    'type'	=> $id,
	    'category' => $category
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
    public function fetchCourseAction()
    {
	$course = $this->_mapper->fetchCourse( $this->_getParam( 'id' ) );
	$this->_helper->json( $course->toArray() );
    }
    
     /**
     * 
     */
    public function fetchStaffAction()
    {
	$staff = $this->_mapper->fetchStaff( $this->_getParam( 'id' ) );
	
	$data = $staff->toArray();
	$data['birth_date'] = $this->view->date( $data['birth_date'] );
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function fetchAddressAction()
    {
	$address = $this->_mapper->fetchAddress( $this->_getParam( 'id' ) );
	
	$data = $address->toArray();
	$data['start_date'] = $this->view->date( $data['start_date'] );
	$data['finish_date'] = $this->view->date( $data['finish_date'] );
	
	$this->_helper->json( $data );
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
    public function searchCategoryAction()
    {
	$type = $this->_getParam( 'id' );
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$categories = $mapperScholarity->getOptionsCategory( $type );
	
	$opts = array();
	foreach( $categories as $id => $value )
	    $opts[] = array( 'id' => $id, 'name' => $value );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function searchStaffAction()
    {
	$rows = $this->_mapper->listStaff( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $value )
	    $opts[] = array( 'id' => $value->id_staff, 'name' => $value->staff_name );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function detailCoursesAction()
    {
	$this->_helper->layout()->disableLayout();
	$id = $this->_getParam( 'id' );
	
	$this->view->row = $this->_mapper->detailEducationInstitution( $id );
	$this->view->courses = $this->_mapper->listCourses( $id );
	$this->view->contact = $this->_mapper->listContacts( $id );
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
    public function fetchClientAction()
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$client = $mapperClient->detailClient( $this->_getParam( 'id' ) );
	
	$data = $client->toArray();
	
	$data['fk_id_perdata'] = $data['id_perdata'];
	$data['staff_name'] = Client_Model_Mapper_Client::buildName( $client );
	$data['birth_date'] = $data['birth_date_format'];
	$data['gender'] = substr( $data['gender'], 0, 1);
	
	$this->_helper->json( $data );
    }
}