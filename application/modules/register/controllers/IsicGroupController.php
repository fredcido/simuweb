<?php

/**
 * 
 */
class Register_IsicGroupController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_ProfIsicGroup
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_IsicGroup();
	
	$stepBreadCrumb = array(
	    'label' => 'Grupu - Setor Industria',
	    'url'   => 'register/isic-group'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Grupu' )->setSubTitle( 'Setor Industria' );
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
     * @return Default_Form_IsicGroup
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Register_Form_IsicGroup();
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
	
	$acronym = substr( $this->view->data['acronym'], 2 , 1 );
	
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
}