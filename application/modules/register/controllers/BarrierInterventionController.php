<?php

/**
 * 
 */
class Register_BarrierInterventionController extends App_Controller_Default
{
    
    /**
     *
     * @var Register_Model_Mapper_BarrierIntervention
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Register_Model_Mapper_BarrierIntervention();
	
	$stepBreadCrumb = array(
	    'label' => 'Intervensaun',
	    'url'   => 'register/barrier-intervention'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	
	$this->view->title( 'Intervensaun' );
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
     * @return Register_Form_BarrierIntervention
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Register_Form_BarrierIntervention();
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
	
	$mapperBarrier = new Register_Model_Mapper_Barrier();
	$rows = $mapperBarrier->listAll( $this->view->data['fk_id_barrier_type'] );
	
	$opt[''] = '';
	foreach ( $rows as $row )
	    $opt[$row->id_barrier_name] = $row->barrier_name;
	
	$this->view->form->getElement( 'fk_id_barrier_name' )->addMultiOptions( $opt );
    }
    
    /**
     * 
     */
    public function searchBarrierAction()
    {
	$mapperBarrier = new Register_Model_Mapper_Barrier();
	$rows = $mapperBarrier->listAll( $this->_getParam( 'id' ) );
	
	$opts = array( array( 'id' => '', 'name' => '' ) );
	foreach( $rows as $row )
	    $opts[] = array( 'id' => $row->id_barrier_name, 'name' => $row->barrier_name );
	
	$this->_helper->json( $opts );
    }
}