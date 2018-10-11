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
        $this->view->title('Home');
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
        if ($this->getRequest()->isXMLHttpRequest()) {
            $this->_helper->layout()->disableLayout();
        }
    
        $mapperCeop = new Register_Model_Mapper_Dec();
        $this->view->ceop = $mapperCeop->fetchRow($this->_getParam('id'));
    }
    
    /**
     *
     */
    public function dashboardAction()
    {
        $dashboards = $this->_mapper->getDashboards($this->_getAllParams());
        $this->_helper->json($dashboards);
    }
    
    /**
     *
     */
    public function chartClientAction()
    {
        $chartClient = $this->_mapper->chartClient($this->_getAllParams());
        $this->_helper->json($chartClient);
    }
    
    /**
     *
     */
    public function chartVacancyAction()
    {
        $chartVacancy = $this->_mapper->chartVacancy($this->_getAllParams());
        $this->_helper->json($chartVacancy);
    }
    
    /**
     *
     */
    public function chartOccupationAction()
    {
        $chartOccupation = $this->_mapper->chartOccupation($this->_getAllParams());
        $this->_helper->json($chartOccupation);
    }
    
     
    /**
     *
     */
    public function chartGraduatedAction()
    {
        $chartGraduated = $this->_mapper->chartGraduated($this->_getAllParams());
        $this->_helper->json($chartGraduated);
    }

    public function listActivitiesAction()
    {
        $this->_helper->layout()->disableLayout();
        $user = Zend_Auth::getInstance()->getIdentity()->id_sysuser;

        $activities = $this->_mapper->listUserActivities($user);
        $this->view->activities = $activities;
    }

    public function allActivitiesAction()
    {
        $user = Zend_Auth::getInstance()->getIdentity()->id_sysuser;

        $activities = $this->_mapper->listUserActivities($user, false);
        $rows = $activities->toArray();

        $data = array();
        foreach ($rows as $row) {
            $row['start'] = $row['date'];
            $data[] = $row;
        }
        
        $this->_helper->json($data);
    }
}
