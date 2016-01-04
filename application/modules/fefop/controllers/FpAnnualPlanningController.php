<?php

/**
 * 
 */
class Fefop_FpAnnualPlanningController extends App_Controller_Default
{
    
    /**
     *
     * @var Fefop_Model_Mapper_FPAnnualPlanning
     */
    protected $_mapper;

    /**
     * 
     * @access public
     * @return void
     */
    public function init()
    {
	$this->_mapper = new Fefop_Model_Mapper_FPAnnualPlanning();
	
	$stepBreadCrumb = array(
	    'label' => 'Planeamentu ba Tinan',
	    'url'   => 'fefop/fp-annual-planning'
	);
	
	$this->view->breadcrumb()->addStep( $stepBreadCrumb );
	$this->view->title( 'Planeamentu ba Tinan' );
    }

    /**
     * 
     */
    public function indexAction()
    {
	$this->view->form = new Fefop_Form_FPAnnualPlanning();
    }
    
    /**
     *
     * @param string $action
     * @return Default_Form_User
     */
    protected function _getForm( $action )
    {
        if ( null == $this->_form ) {

            $this->_form = new Fefop_Form_FPCoursePlanning();
            $this->_form->setAction( $action );
        }

        return $this->_form;
    }
    
    /**
     * 
     */
    public function searchInstituteAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'list', 'education-institution', 'register' );
    }
    
    /**
     * 
     */
    public function searchInstituteForwardAction()
    {
	$this->_helper->layout()->disableLayout();
	$this->_forward( 'search-institution', 'education-institution', 'register' );
    }
    
    /**
     * 
     */
    public function fetchInstituteAction()
    {
	$mapperInsitute = new Register_Model_Mapper_EducationInstitute();
	$institute = $mapperInsitute->detailEducationInstitution( $this->_getParam( 'id' ) );
	
	$data = array();
	$data['fk_id_fefpeduinstitution'] = $institute['id_fefpeduinstitution'];
	$data['institution'] = $institute['institution'];
	
	$this->_helper->json( $data );
    }
    
    /**
     * 
     */
    public function fetchEventsAction()
    {
	$params = $this->_getAllParams();
	$params['year_formation'] = $params['year_planning'];
	unset( $params['year_planning'] );
	
	$events = $this->_mapper->fetchEvents( $params );
	
	$json = array(
	    'planning'	=> null,
	    'students'	=> 0,
	    'cost'	=> 0
	);
	
	$data = array();
	foreach ( $events as $event ) {
	   $data[] = $this->_treatEvent( $event );
	   
	   if ( $event->year_planning == $params['year_formation'] && empty( $json['planning'] ) ) {
		$json = array(
		     'planning'  => $event->id_annual_planning,
		     'students'  => $event->total_students,
		     'cost'	    => number_format( $event->total_cost, 2, '.', ',' )
		 ); 
	   }
	}
	
	if ( $events->count() > 0 ) {
	    
	    $event = $events->getRow( 0 );
	    
	}
	
	$json['events'] = $data;
	
	$this->_helper->json( $json );
    }
    
     /**
     * 
     */
    public function fetchEventAction()
    {
	$event = $this->_mapper->fetchEvent( $this->_getParam( 'id' ) );
	$row = $this->_treatEvent( $event );
	$this->_helper->json( $row );
    }
    
    /**
     * 
     * @param type $event
     * @return type
     */
    protected function _treatEvent( $event )
    {
	$date = new Zend_Date();
	   
	$title = ( empty( $event->external_code ) ? '' : $event->external_code . ' - ' ) . $event->scholarity;
	$title .= ' - Partisipants: ' . $event->students_course;
	
	$row = array(
	    'id'		=> $event->id_planning_course,
	    'title'		=> $title,
	    'start'		=> (int)$date->setDate( $event->date_start, 'yyyy-MM-dd' )->get( Zend_Date::TIMESTAMP ),
	    'end'		=> (int)$date->setDate( $event->date_finish, 'yyyy-MM-dd' )->get( Zend_Date::TIMESTAMP ),
	    'scholarity'	=> $event->id_perscholarity
	);
	
	return $row;
    }
    
    /**
     * 
     */
    public function newFormationAction()
    {
	$this->_helper->layout()->disableLayout();
	
	$form = new Fefop_Form_FPCoursePlanning();
	$form->setAction( $this->_helper->url( 'save' ) );
	
	$data = $this->_getAllParams();
	$data['year'] = $data['year_planning'];
	$data['total_students'] = 0;
	$data['total_cost'] = 0;
	
	$id = $this->_getParam( 'id' );
	if ( !empty( $id ) ) {
	    
	    $event = $this->_mapper->fetchEvent( $id );
	    $eventData = $event->toArray();
	    $data += $eventData;
	    
	    $data['total_students'] = $eventData['students_course'];
	    $data['total_cost'] = number_format( $eventData['cost_course'], 2, '.', ',' );
	    $data['unit_cost'] = number_format( $eventData['unit_cost'], 2, '.', ',' );
	    $data['date_start'] = $this->view->date( $event->date_start );
	    $data['date_finish'] = $this->view->date( $event->date_finish );
	    
	    $mapperScholarity = new Register_Model_Mapper_PerScholarity();
	    
	    $data['category'] = $event->category;
	    $data['fk_id_perscholarity'] = $event->id_perscholarity;
	    
	    $filters = array(
		'type'		=> Register_Model_Mapper_PerTypeScholarity::NON_FORMAL,
		'category'	=> $event->category,
		'institution'   => $event->fk_id_fefpeduinstitution
	    );
	    $optScholarity = $mapperScholarity->getOptionsScholarity( $filters );

	    $opts = array();
	    foreach( $optScholarity as $id => $value )
		$opts[$id] = $value;
	    
	    $form->getElement( 'fk_id_perscholarity' )->addMultiOptions( $opts );
	   
	    if ( !empty( $event->fk_id_fefpstudentclass ) ) {
		
		foreach ( $form->getElements() as $element )
		    $element->setAttrib( 'disabled', true );
	    }
	}
	
	$form->populate( $data );
	$this->view->form = $form;
    }
    
    /**
     * 
     */
    public function searchCourseAction()
    {
	$category = $this->_getParam( 'category' );
	$institute = $this->_getParam( 'institute' );
	
	$filters = array(
	    'type'	    => Register_Model_Mapper_PerTypeScholarity::NON_FORMAL,
	    'category'	    => $category,
	    'institution'   => $institute
	);
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$optScholarity = $mapperScholarity->getOptionsScholarity( $filters );

	$opts = array();
	foreach( $optScholarity as $id => $value )
	    $opts[] = array( 'id' => $id, 'name' => $value );
	
	$this->_helper->json( $opts );
    }
    
    /**
     * 
     */
    public function deleteEventAction()
    {
	$result = $this->_mapper->deleteEvent( $this->_getParam( 'event' ) );
	$this->_helper->json( $result );
    }
    
    /**
     * 
     */
    public function fetchUnitCostAction()
    {
	$scholarity = $this->_getParam( 'scholarity' );
	
	$mapperUnitCost = new Fefop_Model_Mapper_UnitCost();
	$unitCost = $mapperUnitCost->getUnitCost( $scholarity );
	
	$json = array(
	    'id'    => null,
	    'cost'  => 0
	);
	
	if ( !empty( $unitCost ) ) {
	    
	    $json = array(
		'id'    => $unitCost->id_unit_cost,
		'cost'  => number_format( $unitCost->cost, 2, '.', '.' )
	    );
	}
	
	$this->_helper->json( $json );
    }
    
    /**
     * 
     */
    public function printAction()
    {
	$this->_helper->layout()->setLayout( 'print' );
	$id = $this->_getParam( 'id' );
	
	$planning = $this->_mapper->detailPlanning( $id );
	$this->view->planning = $planning;
	
	$filters = array(
	    'year_planning'		=> $planning->year_planning,
	    'fk_id_fefpeduinstitution'	=> $planning->id_fefpeduinstitution
	);
	
	$events = $this->_mapper->fetchEvents( $filters );
	$this->view->events = $events;
	
	$this->view->user = Zend_Auth::getInstance()->getIdentity();
    }
}