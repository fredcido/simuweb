<?php

/**
 * 
 */
class Fefop_IndexController extends Zend_Controller_Action
{

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
    }

    /**
     * 
     * @access public
     * @return void
     */
    public function indexAction()
    {
	$this->view->title( 'Módulo FEFOP' );
    }
    
    /**
     * 
     */
    public function contractAction()
    {
	if ( $this->getRequest()->isXMLHTTPRequest() ) 
	    $this->_helper->layout()->disableLayout();
	
	$id = $this->_getParam( 'id' );
	
	if ( !empty( $id ) ) {
	    
	    $mapperContract = new Fefop_Model_Mapper_Contract();
	    $this->view->contract = $mapperContract->detail( $id );
	}
    }
    
    /**
     * 
     */
    public function checkBlacklistAction()
    {
	$blacklist = new Fefop_Model_Mapper_BeneficiaryBlacklist();
	$response = $blacklist->checkBlacklist( $this->_getAllParams() );
	
	$this->_helper->json( $response );
    }
    
    /**
     * 
     */
    public function printGridAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$this->view->user = Zend_Auth::getInstance()->getIdentity();
    }
}