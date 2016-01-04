<?php

class Cron_ApiController extends Zend_Controller_Action
{
    
    /**
    * (non-PHPdoc)
    * @see Zend_Controller_Action::init()
    */
    public function init ()
    {
	$token  = $this->_getParam( 'access' );
	if ( empty( $token ) || $token != App_Util_Indmo::TOKEN ) {
	    
	    header( 'HTTP/1.1 403 Forbidden' );
	    exit;
	}
	
	parent::init();
	
	$this->_helper->viewRenderer->setNoRender( true );
	$this->_helper->layout()->disableLayout();
	$this->_config = Zend_Registry::get( 'config' );
    }
    
     /**
     * 
     */
    public function qualificationAction()
    {
	$mapperQualification = new Cron_Model_Mapper_Qualification();
	$response = $mapperQualification->setData( $this->_getAllParams() )->importQualification();
	
	$this->_helper->json( $response );
    }
    
    /**
     * 
     */
    public function testSendSmsAction()
    {
	$mapperCampaignSms = new Cron_Model_Mapper_CampaignSms();
	$response = $mapperCampaignSms->testSmsSending( $this->_getAllParams() );
	
	$this->_helper->json( $response );
    }
}