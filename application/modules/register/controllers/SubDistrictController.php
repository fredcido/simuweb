<?php

/**
 * 
 */
class Register_SubDistrictController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_AddSubDistrict
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_AddSubDistrict();
	
	$stepBreadCrumb = array(
	    'label' => 'Rejistu Sub-Distritu',
	    'url'   => 'register/sub-district'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Rejistu SubDistritu' );
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
     * @return Default_Form_SubDistrict
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Register_Form_SubDistrict();
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
	
	$opt[''] = '';
	foreach ( $rows as $row )
	    $opt[$row->id_adddistrict] = $row->District;
	
	$this->view->form->getElement( 'fk_id_adddistrict' )->addMultiOptions( $opt );
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
}