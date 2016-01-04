<?php

/**
 * 
 */
class Register_OccupationTimorController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_ProfOcupationTimor
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_ProfOcupationTimor();
	
	$stepBreadCrumb = array(
	    'label' => 'Okupasaun Timor-Leste',
	    'url'   => 'register/occupation-timor'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Okupasaun Timor-Leste' );
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
     * @return Default_Form_OccupationTimor
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Register_Form_OccupationTimor();
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
	$this->view->rows = $this->_mapper->listAll();
    }
    
    /**
     * 
     */
    public function editPostHook()
    {
	$this->_helper->viewRenderer->setRender( 'index' );
    }
    
    /**
     * 
     */
    public function searchOccupationAction()
    {
	$mapperOccupation = new Register_Model_Mapper_ProfOcupation();
	$occupation = $mapperOccupation->fetchRow( $this->_getParam( 'id' ) );
	
	$this->_helper->json( $occupation->toArray() );
    }
}