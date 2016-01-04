<?php

/**
 * 
 */
class Register_IsicSectionController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_ProfIsicSection
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_IsicSection();
	
	$stepBreadCrumb = array(
	    'label' => 'Sesaun - Setor Industria',
	    'url'   => 'register/isic-section'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Sesaun' )->setSubTitle( 'Setor Industria' );
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
     * @return Default_Form_IsicSection
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Register_Form_IsicSection();
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
    }
}