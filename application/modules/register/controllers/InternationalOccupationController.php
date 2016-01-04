<?php

/**
 * 
 */
class Register_InternationalOccupationController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_ProfOcupation
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_ProfOcupation();
	
	$stepBreadCrumb = array(
	    'label' => 'Okupasaun Internasional',
	    'url'   => 'register/international-occupation'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Okupasaun Internasional (ISCO - 08)' );
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
     * @return Default_Form_InternationalOccupation
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Register_Form_InternationalOccupation();
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
	
	$mapperSubGroup = new Register_Model_Mapper_ProfSubGroup();
	$rows = $mapperSubGroup->listAll( $this->view->data['fk_id_profgroup'] );
	
	$opts = array( '' => '' );
	foreach( $rows as $row )
	    $opts[$row->id_profsubgroup] = $row->sub_group;
	
	$this->view->form->getElement( 'fk_id_profsubgroup' )
			 ->addMultiOptions( $opts )
			 ->setAttrib( 'disabled', null );
	
	$mapperMiniGroup = new Register_Model_Mapper_ProfMiniGroup();
	$rows = $mapperMiniGroup->listAll( $this->view->data['fk_id_profsubgroup'] );
	
	$opts = array( '' => '' );
	foreach( $rows as $row )
	    $opts[$row->id_profminigroup] = $row->mini_group;
	
	$this->view->form->getElement( 'fk_id_profminigroup' )
			 ->addMultiOptions( $opts )
			 ->setAttrib( 'disabled', null );
	
	$acronym = substr( $this->view->data['acronym'], 3 , 1 );
	$this->view->form->getElement( 'acronym' )->setValue( $acronym );
    }
    
    /**
     * 
     */
    public function searchSubGroupAction()
    {
	$mapperSubGroup = new Register_Model_Mapper_ProfSubGroup();
	$rows = $mapperSubGroup->listAll( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_profsubgroup, 'name' => $row->acronym . ' - ' . $row->sub_group );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function searchMiniGroupAction()
    {
	$mapperMiniGroup = new Register_Model_Mapper_ProfMiniGroup();
	$rows = $mapperMiniGroup->listAll( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_profminigroup, 'name' => $row->acronym . ' - ' . $row->mini_group );
	
	$this->_helper->json( $opts );
    }
}