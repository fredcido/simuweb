<?php

/**
 * 
 */
class Fefop_DocumentController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_Document
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_Document();
	$this->_helper->layout()->disableLayout();
    }
    
    /**
     * 
     */
    public function indexAction()
    {
	$this->view->id = $this->_getParam( 'id' );
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
    }
    
    /**
     * 
     */
    public function listFilesRowsAction()
    {
	$this->view->files = $this->_mapper->setData( $this->_getAllParams() )->listFiles();
    }
}