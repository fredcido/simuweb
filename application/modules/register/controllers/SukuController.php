<?php

/**
 * 
 */
class Register_SukuController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_AddSuku
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_AddSuku();
	
	$stepBreadCrumb = array(
	    'label' => 'Rejistu Suku',
	    'url'   => 'register/suku'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Rejistu Suku' );
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
     * @return Default_Form_Suku
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Register_Form_Suku();
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
	
	$mapperDistrict = new Register_Model_Mapper_AddDistrict();
	$rows = $mapperDistrict->listAll( $this->view->data['fk_id_addcountry'] );
	
	$opts = array( '' => '' );
	foreach( $rows as $row )
	    $opts[$row->id_adddistrict] = $row->District;
	
	$this->view->form->getElement( 'fk_id_adddistrict' )->addMultiOptions( $opts );
	
	$mapperSubDistrict = new Register_Model_Mapper_AddSubDistrict();
	$rows = $mapperSubDistrict->listAll( $this->view->data['fk_id_adddistrict'] );
	
	$opts = array( '' => '' );
	foreach( $rows as $row )
	    $opts[$row->id_addsubdistrict] = $row->sub_district;
	
	$this->view->form->getElement( 'fk_id_addsubdistrict' )->addMultiOptions( $opts );
    }
    
    /**
     * 
     */
    public function searchDistrictAction()
    {
	$mapperDistrict = new Register_Model_Mapper_AddDistrict();
	$rows = $mapperDistrict->listAll( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_adddistrict, 'name' => $row->District );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function searchSubDistrictAction()
    {
	$mapperSubDistrict = new Register_Model_Mapper_AddSubDistrict();
	$rows = $mapperSubDistrict->listAll( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_addsubdistrict, 'name' => $row->sub_district );
	
	$this->_helper->json( $opts );
    }
}