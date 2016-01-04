<?php

class Cron_ServiceController extends Zend_Controller_Action
{
    /**
    * (non-PHPdoc)
    * @see Zend_Controller_Action::init()
    */
    public function init ()
    {
	parent::init();
	
	$this->_helper->viewRenderer->setNoRender( true );
	$this->_helper->layout()->disableLayout();
	$this->_config = Zend_Registry::get( 'config' );
    }
    
    /**
     * 
     */
    public function sendclassAction()
    {
	$mapperStudentClass = new Cron_Model_Mapper_StudentClass();
	$mapperStudentClass->sendClassIndmo();
    }
    
    /**
     * 
     */
    public function sendqualificationAction()
    {
	$mapperStudentClass = new Cron_Model_Mapper_Qualification();
	$mapperStudentClass->sendQualification();
    }
    
    /**
     * 
     */
    public function notifysystemAction()
    {
	ini_set( 'max_execution_time', 0 );
	set_time_limit( 0 );
	
	$mapperNotification = new Cron_Model_Mapper_Notification();
	$mapperNotification->notifyAllMessages();
    }
    
    /**
     * 
     */
    public function fixphoneAction()
    {
	ini_set( 'max_execution_time', 0 );
	set_time_limit( 0 );
	
	$mapperFix = new Default_Model_Mapper_DataFixer();
	$mapperFix->fixPhones();
	exit;
    }
    
    /**
     * 
     */
    public function startsmsAction()
    {
	$mapperRobotCampaign = new Cron_Model_Mapper_CampaignSms();
	$mapperRobotCampaign->startSending();
    }
    
     /**
     * 
     */
    public function sendcampaignAction()
    {
	$mapperRobotCampaign = new Cron_Model_Mapper_CampaignSms();
	$mapperRobotCampaign->sendCampaign( $this->_getParam( 'idcampaign' ) );
    }
    
    /**
     * 
     */
    public function retrievesmsAction()
    {
	$mapperRobotCampaign = new Cron_Model_Mapper_CampaignSms();
	$mapperRobotCampaign->retrieveSms();
    }
}