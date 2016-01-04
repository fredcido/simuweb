<?php

/**
 * 
 */
class Register_SubGroupController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_ProfSubGroup
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_ProfSubGroup();
	
	$stepBreadCrumb = array(
	    'label' => 'Sub-Grupu Okupasaun',
	    'url'   => 'register/sub-group'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Sub-Grupu Okupasaun' );
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
     * @return Default_Form_SubGroup
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Register_Form_SubGroup();
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
	
	$acronym = substr( $this->view->data['acronym'], 1 , 1 );
	$this->view->form->getElement( 'acronym' )->setValue( $acronym );
    }
}