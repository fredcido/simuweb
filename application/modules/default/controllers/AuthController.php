<?php

class Default_AuthController extends Zend_Controller_Action
{

    /**
     * 
     */
    public function init()
    {
	$this->_helper->layout()->disableLayout();
    }

    /**
     * 
     * Enter description here ...
     */
    public function indexAction()
    {
	Zend_Auth::getInstance()->clearIdentity();
	$config = Zend_Registry::get( 'config' );
	$session = new Zend_Session_Namespace( $config->general->appid );
	
	unset( $session->client );
    }

    /**
     * 
     * Enter description here ...
     */
    public function loginAction()
    {
	if ( 
		Zend_Auth::getInstance()->hasIdentity() && 
		!$this->getRequest()->isXmlHttpRequest()
	) {
	    $this->_helper->redirector->goToSimple( 'index', 'index' );
	    return;
	}

	$config = Zend_Registry::get( 'config' );
	$session = new Zend_Session_Namespace( $config->general->appid );
	
	$rota = empty( $session->triedroute ) ?
		$this->_helper->url( 'index', 'index' ) :
		$session->triedroute;
	
	$session->triedroute = null;
	unset( $session->triedroute );
	
	$result = array(
	    'redirect'  => $rota,
	    'valid'	=> false
	);
	
	if ( Zend_Auth::getInstance()->hasIdentity() ) {
	    
	    $result['valid'] = true;
	    
	} else {

	    if ( $this->getRequest()->isPost() ) {

		$data = $this->getRequest()->getPost();

		$mapperSysUser = new Admin_Model_Mapper_SysUser();
		$mapperSysUser->setData( $data );

		$result['valid'] = $mapperSysUser->login();
	    }
	}

	$this->_helper->json( $result );
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function loginExternalAction()
    {
	$config = Zend_Registry::get( 'config' );
	$session = new Zend_Session_Namespace( $config->general->appid );
	
	$rota = $this->_helper->url( 'index', 'index', 'external' );
	
	$session->triedroute = null;
	unset( $session->triedroute );
	
	$result = array(
	    'redirect'  => $rota,
	    'valid'	=> false
	);
	
	if ( $this->getRequest()->isPost() ) {

	    $data = $this->getRequest()->getPost();

	    $mapperSysUser = new Admin_Model_Mapper_SysUser();
	    $mapperSysUser->setData( $data );

	    $result['valid'] = $mapperSysUser->loginExternal();
	}

	$this->_helper->json( $result );
    }

    /**
     * 
     * Enter description here ...
     */
    public function logoutAction()
    {
	Zend_Auth::getInstance()->clearIdentity();
	$config = Zend_Registry::get( 'config' );
	$session = new Zend_Session_Namespace( $config->general->appid );
	
	unset( $session->client );
	
	$this->_helper->redirector->gotoSimple( 'index', 'index' );
    }
    
    /**
     * 
     * Enter description here ...
     */
    public function logoutExternalAction()
    {
	Zend_Auth::getInstance()->clearIdentity();
	$config = Zend_Registry::get( 'config' );
	$session = new Zend_Session_Namespace( $config->general->appid );
	
	unset( $session->client );
	
	$this->_helper->redirector->gotoSimple( 'index', 'index' );
    }
    
    /**
     * 
     */
    public function profileAction()
    {
	$stepBreadCrumb = array(
	    'label' => 'Profile',
	    'url'   => 'auth/profile'
	);
	
	$this->view->title( 'Profile' );
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->_helper->layout()->enableLayout();
	
	$user = Zend_Auth::getInstance()->getIdentity();
	
	$form = new Default_Form_Profile();
        $form->setAction( $this->_helper->url( 'edit-profile' ) );
	    
	$form->populate( $user->toArray() );
	
	$this->view->form = $form;
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function editProfileAction()
    {
	$form = new Default_Form_Profile();
	$mapperUser = new Admin_Model_Mapper_SysUser();
	
	if ( $this->getRequest()->isPost() ) {

	    if ( $form->isValid( $this->getRequest()->getPost() ) ) {
	    	
		$mapperUser->setData( $form->getValues() );

		$return = $mapperUser->editProfile();
		$message = $mapperUser->getMessage()->toArray();

		$result = array(
		    'status'	    => (bool) $return,
		    'id'	    => $return,
		    'description'   => $message,
		    'data'	    => $form->getValues()
		);

	    } else {
		
		$config = Zend_Registry::get( 'config' );
		
		$message = new App_Message();
		$message->addMessage( $config->messages->warning, App_Message::WARNING );

		$result = array(
		    'status'	    => false,
		    'description'   => $message->toArray(),
		    'errors'	    => $form->getMessages()
		);
	    }
	} else
	    $this->_helper->redirector->goToSimple( 'index', 'index' );
	
	$this->_helper->json( $result );
    }
    
    /**
     * 
     */
    public function dashboardAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
    }
    
    /**
     * 
     */
    public function jobListAction()
    {
	$mapperJob = new Job_Model_Mapper_JobVacancy();
	$filters = array( 
	    'active' => 1 
	);
	
	$this->view->rows = $mapperJob->listByFilters( $filters );
    }
    
    /**
     * 
     */
    public function printJobAction()
    {
	$this->_forward( 'print', 'vacancy', 'job' );
    }
    
    /**
     * 
     */
    public function trainingListAction()
    {
	$mapperStudentClass = new StudentClass_Model_Mapper_StudentClass();
	$filters = array( 
	    'active' => 1 
	);
	
	$this->view->rows = $mapperStudentClass->listByFilters( $filters );
    }
    
    /**
     * 
     */
    public function printClassAction()
    {
	$this->_forward( 'print', 'register', 'student-class' );
    }
    
    /**
     * 
     */
    public function ceopListAction()
    {
	$mapperCEOP = new Register_Model_Mapper_Dec();
	$this->view->rows = $mapperCEOP->fetchAll();
    }
}