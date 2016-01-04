<?php

/**
 * 
 */
class Register_IsicClassController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_IsicClass
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_IsicClass();
	
	$stepBreadCrumb = array(
	    'label' => 'Klase - Setor Industria',
	    'url'   => 'register/isic-class'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Klase' )->setSubTitle( 'Setor Industria' );
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

            $this->_form = new Register_Form_IsicClass();
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
	
	$mapperDivision = new Register_Model_Mapper_IsicDivision();
	$rows = $mapperDivision->listAll( $this->view->data['fk_id_isicsection'] );
	
	$opts = array( '' => '' );
	foreach( $rows as $row )
	    $opts[$row->id_isicdivision] = $row->name_disivion;
	
	$this->view->form->getElement( 'fk_id_isicdivision' )
			 ->addMultiOptions( $opts )
			 ->setAttrib( 'disabled', null );
	
	$mapperIsicGroup = new Register_Model_Mapper_IsicGroup();
	$rows = $mapperIsicGroup->listAll( $this->view->data['fk_id_isicdivision'] );
	
	$opts = array( '' => '' );
	foreach( $rows as $row )
	    $opts[$row->id_isicgroup] = $row->name_group;
	
	$this->view->form->getElement( 'fk_id_isicgroup' )
			 ->addMultiOptions( $opts )
			 ->setAttrib( 'disabled', null );
	
	$acronym = substr( $this->view->data['acronym'], 3 , 1 );
	$this->view->form->getElement( 'acronym' )->setValue( $acronym );
    }
    
    /**
     * 
     */
    public function searchDivisionAction()
    {
	$mapperDivision = new Register_Model_Mapper_IsicDivision();
	$rows = $mapperDivision->listAll( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_isicdivision, 'name' => $row->name_disivion );
	
	$this->_helper->json( $opts );
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
}