<?php

/**
 * 
 */
class Default_IndexController extends Zend_Controller_Action
{

    /**
     *
     * @var Default_Model_Mapper_Dashboard
     */
    protected $_mapper;
    
    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->view->title( 'Home' );
	$this->_mapper = new Default_Model_Mapper_Dashboard();
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
    public function headerReportAction()
    {
	if ( $this->getRequest()->isXMLHttpRequest() )
	    $this->_helper->layout()->disableLayout();
	
	$mapperCeop = new Register_Model_Mapper_Dec();
	$this->view->ceop = $mapperCeop->fetchRow( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function dashboardAction()
    {
	$dashboards = $this->_mapper->getDashboards( $this->_getAllParams() );
	$this->_helper->json( $dashboards );
    }
    
    /**
     * 
     */
    public function chartClientAction()
    {
	$chartClient = $this->_mapper->chartClient( $this->_getAllParams() );
	$this->_helper->json( $chartClient );
    }
    
    /**
     * 
     */
    public function chartVacancyAction()
    {
	$chartVacancy = $this->_mapper->chartVacancy( $this->_getAllParams() );
	$this->_helper->json( $chartVacancy );
    }
    
    /**
     * 
     */
    public function chartOccupationAction()
    {
	$chartOccupation = $this->_mapper->chartOccupation( $this->_getAllParams() );
	$this->_helper->json( $chartOccupation );
    }
    
     
    /**
     * 
     */
    public function chartGraduatedAction()
    {
	$chartGraduated = $this->_mapper->chartGraduated( $this->_getAllParams() );
	$this->_helper->json( $chartGraduated );
    }
}