<?php

/**
 * 
 */
class Register_EnterpriseController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_Enterprise
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_Enterprise();
	
	$stepBreadCrumb = array(
	    'label' => 'Empreza',
	    'url'   => 'register/enterprise'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Empreza' );
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Buka Empreza',
	    'url'   => 'register/enterprise/list'
	);

	$this->view->breadcrumb()->addStep( $stepBreadCrumb );

	$searchEnterprise = new Register_Form_EnterpriseSearch();
	$searchEnterprise->setAction( $this->_helper->url( 'search-enterprise' ) );
        
        $this->view->menu()->setActivePath( 'register/enterprise/list' );

	$this->view->form = $searchEnterprise;
    }
    
    /**
     * 
     */
    public function searchEnterpriseAction()
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
		'url'	=> 'register/enterprise'
	    );
	} else {

	    $stepBreadCrumb = array(
		'label' => 'Edita Rejistu',
		'url'	=> 'register/enterprise/edit/id/' . $id
	    );
	    
	    $row = $this->_mapper->fetchRow( $id );
	    $this->view->title()->setSubTitle( $row->enterprise_name );
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
	    $data['fk_id_fefpenterprise'] = $data['id_fefpenterprise'];
	    
	    $formContact->populate( $data );
	}

	$this->view->form = $formContact;
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
	    $data['fk_id_fefpenterprise'] = $data['id_fefpenterprise'];
	    
	    $formAddress->populate( $data );
	}

	$this->view->form = $formAddress;
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
	    $formStaff->populate( $data );
	}

	$this->view->form = $formStaff;
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
    public function listAddressAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->listAddress( $this->_getParam( 'id' ) );
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
    public function fetchContactAction()
    {
	$contact = $this->_mapper->fetchContact( $this->_getParam( 'id' ) );
	$this->_helper->json( $contact->toArray() );
    }
    
    /**
     * 
     */
    public function fetchAddressAction()
    {
	$address = $this->_mapper->fetchAddress( $this->_getParam( 'id' ) );
	
	$data = $address->toArray();
        if ( !empty( $data['start_date'] ) )
            $data['start_date'] = $this->view->date( $data['start_date'] );
        
        if ( !empty( $data['finish_date'] ) )
            $data['finish_date'] = $this->view->date( $data['finish_date'] );
	
	$this->_helper->json( $data );
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
    public function deleteAddressAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteAddress();
	$data = array( 'status'	=> (bool)$return );
	
	$this->_helper->json( $data );
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
    public function deleteStaffAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteStaff();
	
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
	$className = 'Register_Form_Enterprise' . ucfirst( $formName );

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
    public function editAction()
    {
	$this->_forward( 'index' );
    }
}