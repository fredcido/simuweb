<?php

/**
 * 
 */
class Fefop_FundController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_Fund
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_Fund();
	
	$stepBreadCrumb = array(
	    'label' => 'Fundos e Doadores',
	    'url'   => 'fefop/fund'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Fundos e Doadores' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$this->view->form = $this->_getForm( $this->_helper->url( 'save' ) );
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Fefop_Form_Fund();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function listAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->rows = $this->_mapper->fetchAll();
    }
    
    /**
     * 
     */
    public function editPostHook()
    {
	$this->_helper->viewRenderer->setRender( 'index' );
	$this->view->id = $this->_getParam( 'id' );
    }
    
    /**
     * 
     */
    public function fundPlanningAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$form = new Fefop_Form_FundPlanning();
	$form->setAction( $this->_helper->url( 'save-planning' ) );
	$this->view->form = $form;
	
	$mapperModule = new Fefop_Model_Mapper_Module();
	$this->view->programs = $mapperModule->listModulesGrouped();
    }
    
    /**
     * 
     */
    public function fetchPlanningAction()
    {
	$rows = $this->_mapper->fetchPlanning( $this->_getParam( 'fund' ), $this->_getParam( 'year') );
	$this->_helper->json( $rows->toArray() );
    }
    
    /**
     * @access 	public
     * @return 	void
     */
    public function savePlanningAction()
    {
	$form = new Fefop_Form_FundPlanning();
	
	if ( $this->getRequest()->isPost() ) {

	    if ( $form->isValid( $this->getRequest()->getPost() ) ) {
	    	
		$this->_mapper->setData( $form->getValues() );

		$return = $this->_mapper->savePlanning();
		$message = $this->_mapper->getMessage()->toArray();

		    $result = array(
			'status'	=> (bool) $return,
			'id'		=> $return,
			'description'	=> $message,
			'data'		=> $form->getValues(),
			'fields'	=> $this->_mapper->getFieldsError()
		    );
		    
	    } else {
		
		$message = new App_Message();
		$message->addMessage( $this->_config->messages->warning, App_Message::WARNING );

		$result = array(
		    'status'	    => false,
		    'description'   => $message->toArray(),
		    'errors'	    => $form->getMessages()
		);
	    }
	}
	
	$this->_helper->json( $result );
    }
    
    /**
     * 
     */
    public function totalsAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->view->totals = $this->_mapper->listTotals();
    }
}