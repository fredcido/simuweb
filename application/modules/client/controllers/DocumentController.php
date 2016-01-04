<?php

/**
 * 
 */
class Client_DocumentController extends App_Controller_Default
{
    
    /**
     *
     * @var Client_Model_Mapper_Document
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Client_Model_Mapper_Document();
	$this->_helper->layout()->disableLayout();
    }
    
    /**
     * 
     */
    public function indexAction()
    {
	$this->view->client = $this->_getParam( 'client' );
	$this->view->case = $this->_getParam( 'case' );
	
	// Search Client
	$mapperClient = new Client_Model_Mapper_Client();
	$this->view->clientRow = $mapperClient->detailClient( $this->_getParam( 'client' ) );
    }
    
    /**
     * 
     */
    public function saveAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->uploadFiles();
	$this->_helper->json( $return );
    }
    
    /**
     * 
     */
    public function deleteAction()
    {
	$return = $this->_mapper->setData( $this->_getAllParams() )->deleteFile();
	$this->_helper->json( $return );
    }
    
    /**
     * 
     */
    public function listFilesAction()
    {
	$this->view->files = $this->_mapper->setData( $this->_getAllParams() )->listFiles();
	$this->_helper->viewRenderer->setRender( 'list-files-rows' );
    }
}