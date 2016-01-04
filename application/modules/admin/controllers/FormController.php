<?php

/**
 * 
 */
class Admin_FormController extends App_Controller_Default
{
    
    /**
     *
     * @var Admin_Model_Mapper_SysForm
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Admin_Model_Mapper_SysForm();
	
	$stepBreadCrumb = array(
	    'label' => 'Formulariu',
	    'url'   => 'admin/form'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Formulariu' );
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

            $this->_form = new Admin_Form_Form();
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
	
	$id = $this->_getParam( 'id' );
	
	$dbFormOperations = App_Model_DbTable_Factory::get( 'SysFormHasSysOperations' );
	$rows = $dbFormOperations->fetchAll( array( 'fk_id_sysform = ?' => $id ) );
	
	$operations = array();
	foreach ( $rows as $row )
	    $operations[] = $row->fk_id_sysoperation;
	
	$this->view->form->getElement( 'operations' )->setValue( $operations );
    }
}