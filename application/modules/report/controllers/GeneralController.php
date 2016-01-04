<?php

class Report_GeneralController extends Zend_Controller_Action
{
    
    /**
     *
     * @var Report_Model_Mapper_Report
     */
    protected $_mapper;
    
    /**
     *
     * @var Zend_Config
     */
    protected $_config;
    
    /**
     *
     * @var Zend_Session_Namespace
     */
    protected $_session;
    
    /**
     * 
     */
    public function init()
    {
	$this->_mapper = new Report_Model_Mapper_Report();
	$this->_config = Zend_Registry::get( 'config' );
	$this->_session = new Zend_Session_Namespace( $this->_config->general->appid );
    }
    
    /**
     * 
     * @return Zend_Form
     */
    protected function _initForm()
    {
	$formStep = $this->_getParam( 'form' );
	if ( empty( $formStep ) )
	    $form = new Report_Form_StandardSearch();
	else
	    $form = new $formStep();
	
	return $form;
    }
    
    /**
     * 
     */
    public function validateAction()
    {
	if ( !$this->getRequest()->isPost() ) {
	 
	    $result = array( 'status' => false );
	    $this->_helper->json( $result );
	}
	    
	$form = $this->_initForm();
	
	if ( !$form->isValid( $this->getRequest()->getPost() ) ) {
	    
	    $message = new App_Message();
	    $message->addMessage( $this->_config->messages->warning, App_Message::WARNING );

	    $result = array(
		'status'	=> false,
		'description'   => $message->toArray(),
		'errors'	=> $form->getMessages()
	    );
	    
	} else {
	    $result = array( 'status' => true );
	}
	
	$this->_helper->json( $result );
    }
    
    /**
     * 
     */
    public function outputAction()
    {
	if ( !$this->getRequest()->isPost() )
	    $this->_helper->redirector->goToSimple( 'index' );
	
	$dataReport = $this->_mapper->setData( $this->_getAllParams() )->report();
	
	$this->_helper->layout()->disableLayout();
	$this->_helper->viewRenderer->setRender( 'templates/output', null, true );
	
	$layoutPath = APPLICATION_PATH . '/modules/report/views/scripts/';

	$viewSpec = new Zend_View();
	$viewSpec->setScriptPath( $layoutPath );
	$viewSpec->addHelperPath( 'App/View/Helpers/', 'App_View_Helper' );
	
	$layoutView = new Zend_View();
	$layoutView->setScriptPath( $layoutPath );
	$layoutView->addHelperPath( 'App/View/Helpers/', 'App_View_Helper' );
	
	$viewSpec->data = $dataReport;
	
	$path = $this->_getParam( 'path' );
	$title = $this->_getParam( 'title' );
	
	require_once APPLICATION_PATH . '/../library/HTMLPurifier/HTMLPurifier.auto.php';
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier( $config );
	
	$layoutView->title = $title;
	$layoutView->path = $this->view->baseUrl();
	$layoutView->content = $purifier->purify( $viewSpec->render( $path . '.phtml' ) );
	//$layoutView->content = $viewSpec->render( $path . '.phtml' );
	
	if ( preg_match( '/fefop/i', $path ) )
	    $layoutView->department = 'SECRETARIA DE ESTADO PARA A POLÍTICA DE FORMAÇÃO PROFISSIONAL E EMPREGO - FEFOP';
	
	$reportOutput = $layoutView->render( 'templates/report.phtml' );
	$id = App_General_String::randomHash();
	
	if ( empty( $this->_session->reportOutput ) ) {
	    $this->_session->reportOutput = array();
	}
	
	$this->_session->reportOutput[$id] = $reportOutput;
	$this->_session->dataReport = $dataReport;
	
	$this->view->content = $reportOutput;
	$this->view->id = $id;
    }
    
    /**
     * 
     * @return array
     */
    protected function _parseExportParams()
    {
	$id = $this->_getParam( 'id-report' );
	
	$params = array(
	    'content'	    => $this->_session->reportOutput[$id],
	    'title'	    => preg_replace( '/[^\w]/', '_', $this->_getParam( 'title') ),
	    'orientation'   =>	$this->_getParam( 'orientation' )
	);
	
	return $params;
    }
    
    /**
     * 
     */
    public function toPdfAction()
    {
	$this->_session->download = true;
	
	App_Util_Export::toPdf( $this->_parseExportParams() );
	
	$this->_session->download = false;
	
	$this->_helper->layout()->disableLayout();
	$this->_helper->viewRenderer->setNoRender( true );
    }
    
    /**
     * 
     */
    public function toDocAction()
    {
	$this->_session->download = true;
				
	App_Util_Export::toDoc( $this->_parseExportParams() );
	
	$this->_session->download = false;
	
	$this->_helper->layout()->disableLayout();
	$this->_helper->viewRenderer->setNoRender( true );
    }
    
    /**
     * 
     */
    public function toExcelAction()
    {
	$this->_session->download = true;
	
	App_Util_Export::toExcel( $this->_parseExportParams() );
	
	$this->_session->download = false;
	
	$this->_helper->layout()->disableLayout();
	$this->_helper->viewRenderer->setNoRender( true );
    }
    
    /**
     * 
     */
    public function checkDownloadAction()
    {
	$download = $this->_session->download;
	
	$this->_helper->json( array( 'status' => $download ) );
    }
    
    /**
     * 
     */
    public function imageAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_helper->viewRenderer->setNoRender( true );
	
	$id = $this->_getParam( 'id' );
	
	$dataReport = $this->_session->dataReport;
	
	if ( !empty( $dataReport ) ) {
	 
	    $imageData = $dataReport['graph'][$id];
	    App_Cache::save( $imageData, $id );
	    
	} else {
	    
	    $imageData = App_Cache::load( $id );
	}
			
	$resource = imagecreatefromstring( $imageData );
	
	header( 'Content-type: image/png' );
	imagepng( $resource );
    }
}