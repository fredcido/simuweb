<?php

/**
 * 
 */
class Register_MiniGroupController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_ProfMiniGroup
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_ProfMiniGroup();
	
	$stepBreadCrumb = array(
	    'label' => 'Mini-Grupu Okupasaun',
	    'url'   => 'register/mini-group'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Mini-Grupu Okupasaun' );
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
     * @return Default_Form_MiniGroup
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Register_Form_MiniGroup();
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
	
	$acronym = substr( $this->view->data['acronym'], 2 , 1 );
	
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
	    $opts[] = array( 'id' => $row->id_profsubgroup, 'name' => $row->sub_group );
	
	$this->_helper->json( $opts );
    }
}