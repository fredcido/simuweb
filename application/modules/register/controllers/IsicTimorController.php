<?php

/**
 * 
 */
class Register_IsicTimorController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_IsicClassTimor
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_IsicTimor();
	
	$stepBreadCrumb = array(
	    'label' => 'Klase Index Timor - Setor Industria',
	    'url'   => 'register/isic-timor'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Klase Index Timor' )->setSubTitle( 'Setor Industria' );
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
     * @return Default_Form_IsicClass
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Register_Form_IsicTimor();
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
	
	$mapperIsicGroup = new Register_Model_Mapper_IsicGroup();
	$rows = $mapperIsicGroup->listAll( $this->view->data['fk_id_isicdivision'] );
	
	$opts = array( '' => '' );
	foreach( $rows as $row )
	    $opts[$row->id_isicgroup] = $row->name_group;
	
	$this->view->form->getElement( 'fk_id_isicgroup' )
			 ->addMultiOptions( $opts )
			 ->setAttrib( 'disabled', null );
	
	$mapperClass = new Register_Model_Mapper_IsicClass();
	$rows = $mapperClass->listAll( $this->view->data['fk_id_isicgroup'] );
	
	$opts = array( '' => '' );
	foreach( $rows as $row )
	    $opts[$row->id_isicclass] = $row->name_class;
	
	$this->view->form->getElement( 'fk_id_isicclass' )
			 ->addMultiOptions( $opts )
			 ->setAttrib( 'disabled', null );
        
        $this->view->form->populate( $this->view->data );
    }
    
    /**
     * 
     */
    public function searchGroupAction()
    {
	$mapperIsicGroup = new Register_Model_Mapper_IsicGroup();
	$rows = $mapperIsicGroup->listAll( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_isicgroup, 'name' => $row->name_group );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function searchClassAction()
    {
	$mapperIsicClass = new Register_Model_Mapper_IsicClass();
	$rows = $mapperIsicClass->listAll( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_isicclass, 'name' => $row->name_class );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function fetchClassAction()
    {
	$mapperIsicClass = new Register_Model_Mapper_IsicClass();
	$class = $mapperIsicClass->fetchRow( $this->_getParam( 'id' ) );
	
	$this->_helper->json( $class->toArray() );
    }
}