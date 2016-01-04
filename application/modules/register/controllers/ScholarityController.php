<?php

/**
 * 
 */
class Register_ScholarityController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_PerScholarity
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_PerScholarity();
	
	$stepBreadCrumb = array(
	    'label' => 'Kursu',
	    'url'   => 'register/scholarity'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Kursu' );
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
     * @return Default_Form_Group
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Register_Form_Scholarity();
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
	
	$data = $this->view->data;
	$form = $this->view->form;
	
	$categories = $this->_mapper->getOptionsCategory( $data['fk_id_pertypescholarity'] );
	$form->getElement( 'category' )->addMultiOptions( $categories )->setValue( $data['category'] );
	
	if ( $data['fk_id_pertypescholarity'] == Register_Model_Mapper_PerTypeScholarity::FORMAL || $data['category'] == 'V' ) {
	    
	    $dbLevelScholarity = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
	    $rows = $dbLevelScholarity->fetchAll( array( 'fk_id_pertypescholarity = ?' => $data['fk_id_pertypescholarity'] ), array( 'id_perlevelscholarity' ) );
	    
	    $opts = array( '' => '' );
	    foreach ( $rows as $row )
		$opts[$row->id_perlevelscholarity] = $row->level_scholarity;
	    
	    $this->view->showlevel = true;
	    $form->getElement( 'fk_id_perlevelscholarity' )
		 ->setRequired( true )
		 ->addMultiOptions( $opts )
		 ->setValue( $data['fk_id_perlevelscholarity'] );
	    
	    if ( $data['category'] == 'V' )
		$form->getElement( 'external_code' )->setRequired( true );
	    
	} else {
	    
	    $this->view->showlevel = false;
	    $form->getElement( 'fk_id_perlevelscholarity' )->setRequired( false );
	}
	
	if ( !empty( $data['remote_id'] ) )
	    foreach ( $form->getElements() as $element )
		$element->setAttrib( 'disabled', true );
	
	$this->view->competencies = $this->_mapper->listCompetencies( $this->_getParam( 'id' ) );
    }
    
    /**
     * 
     */
    public function searchCategoryAction()
    {
	$type = $this->_getParam( 'id' );
	
	$categories = $this->_mapper->getOptionsCategory( $type );
	
	unset( $categories['N'] );
	
	$opts = array();
	foreach( $categories as $id => $value )
	    $opts[] = array( 'id' => $id, 'name' => $value );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function searchLevelAction()
    {
	$type = $this->_getParam( 'id' );
	
	$dbLevelScholarity = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
	$rows = $dbLevelScholarity->fetchAll( array( 'fk_id_pertypescholarity = ?' => $type ), array( 'id_perlevelscholarity' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_perlevelscholarity, 'name' => $row->level_scholarity );
	
	$this->_helper->json( $opts );
    }
}