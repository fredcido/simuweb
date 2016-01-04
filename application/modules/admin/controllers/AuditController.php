<?php

/**
 * 
 */
class Admin_AuditController extends App_Controller_Default
{
    
    /**
     *
     * @var Model_Mapper_SysAudit
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Model_Mapper_SysAudit();
	
	$stepBreadCrumb = array(
	    'label' => 'Atividades Usuarius',
	    'url'   => 'admin/audit'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Atividades Usuarius' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$this->view->form = $this->_getForm( $this->_helper->url( 'list' ) );
    }
    
    /**
     *
     * @param string $action
     * @return Default_Module_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Admin_Form_Audit();
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
	$this->view->rows = $this->_mapper->listByFilters(  $this->_getAllParams() );
    }
    
    /**
     * 
     */
    public function searchFormAction()
    {
	$mapperForm = new Admin_Model_Mapper_SysForm();
	$rows = $mapperForm->listAll( array( 'module' => $this->_getParam( 'id' ) ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_sysform, 'name' => $row->form );
	
	$this->_helper->json( $opts );
    }
}