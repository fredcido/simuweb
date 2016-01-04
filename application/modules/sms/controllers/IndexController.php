<?php

/**
 * 
 */
class Sms_IndexController extends Zend_Controller_Action
{

    /**
     *
     * @var Sms_Model_Mapper_Dashboard
     */
    protected $_mapper;
    
    /**
     *
     * @var Sms_Model_Mapper_Campaign
     */
    protected $_mapperCampaign;
    
    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->view->title( 'Home SMS' );
	$this->_mapper = new Sms_Model_Mapper_Dashboard();
	$this->_mapperCampaign = new Sms_Model_Mapper_Campaign();
    }

    /**
     * 
     * @access public
     * @return void
     */
    public function indexAction()
    {
    }
    
    /**
     * 
     */
    public function listStatisticsAction()
    {
	$statistics = $this->_mapperCampaign->getStatistics( $this->_getAllParams() );
	$this->_helper->json( $statistics );
    }
    
    /**
     * 
     */
    public function chartSendingAction()
    {
	$chartSending = $this->_mapperCampaign->chartSending( $this->_getAllParams() );
	$this->_helper->json( $chartSending );
    }
    
    /**
     * 
     */
    public function chartSentDayAction()
    {
	$chartClient = $this->_mapperCampaign->chartSentDay( $this->_getAllParams() );
	$this->_helper->json( $chartClient );
    }
    
     /**
     * 
     */
    public function chartSentHourAction()
    {
	$chartClient = $this->_mapperCampaign->chartSentHour( $this->_getAllParams() );
	$this->_helper->json( $chartClient );
    }
    
    /**
     * 
     */
    public function chartSentGroupAction()
    {
	$chartClient = $this->_mapperCampaign->chartSentGroup( $this->_getAllParams() );
	$this->_helper->json( $chartClient );
    }
    
     /**
     * 
     */
    public function lastSentAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapperCampaign->listLastsSent();
    }
}