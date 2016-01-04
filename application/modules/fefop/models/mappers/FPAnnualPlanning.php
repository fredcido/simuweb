<?php

class Fefop_Model_Mapper_FPAnnualPlanning extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_FPAnnualPlanning
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FPAnnualPlanning();

	return $this->_dbTable;
    }

    /**
     * 
     * @return int|bool
     */
    public function save()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dataForm = $this->_data;
	    
	    $dateIni = new Zend_Date( $dataForm['date_start'] );
	    $dateFin = new Zend_Date( $dataForm['date_finish'] );
	    
	    // Check if the start date is later than finish date
	    if ( $dateIni->isLater( $dateFin ) ) {
		
		$this->_message->addMessage( 'Loron inisiu la bele liu loron remata.' );
		$this->addFieldError( 'date_start' )->addFieldError( 'date_finish' );
		return false;
	    }
	    
	    // Check to see if either start date or finish date are within the year defined
	    if ( !in_array( $dataForm['year_planning'], array( $dateIni->toString( 'yyyy' ) ) ) ) {
		
		$this->_message->addMessage( sprintf( 'Loron inisiu tenki iha laran Tinan: %s.', $dataForm['year'] ) );
		$this->addFieldError( 'date_start' );
		return false;
	    }
	    
	    $this->_data = $dataForm;
	    $this->_data['total_cost'] = App_General_String::toFloat( $this->_data['total_cost'] );
	    
	    // Save the annual planning
	    $this->_data['fk_id_annual_planning'] = $this->_saveAnnualPlanning( $this->_data );
	    
	    $this->_data['date_start'] = $dateIni->toString( 'yyyy-MM-dd' );
	    $this->_data['date_finish'] = $dateFin->toString( 'yyyy-MM-dd' );
	   
	    $dbPlanningCourse = App_Model_DbTable_Factory::get( 'FPPlanningCourse' );
	    $id = parent::_simpleSave( $dbPlanningCourse );
	    
	    // Update the totals
	    $dbAnnualPlanning = App_Model_DbTable_Factory::get( 'FPAnnualPlanning' );
	    $totals = $this->_getTotalsAnnualPlanning( $this->_data['fk_id_annual_planning'] );
	    $row = $dbAnnualPlanning->fetchRow( array( 'id_annual_planning = ?' => $this->_data['fk_id_annual_planning'] ) );
	    $row->total_students = $totals->total_students;
	    $row->total_cost = $totals->total_cost;
	    $row->save();
	    
	    $history = sprintf( 'REJISTU PLANU FORMASAUN: %s BA ANNUAL PLANNING: %s', $id, $this->_data['fk_id_annual_planning'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return $id;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @param array $data
     * @return int
     */
    protected function _saveAnnualPlanning( $data )
    {
	 $dbAnnualPlanning = App_Model_DbTable_Factory::get( 'FPAnnualPlanning' );
	 
	// If there is no annual planning
	if ( empty( $data['id_annual_planning'] ) ) {
	    
	    // Check to see if there is an annual planning to the current INSTITUTION/YEAR
	    $where = array(
		'year_planning = ?'		=> $data['year_planning'],
		'fk_id_fefpeduinstitution = ?'	=> $data['fk_id_fefpeduinstitution']
	    );
	    
	    // If there is already, get the id, otherwise, insert a new planning
	    $row = $dbAnnualPlanning->fetchRow( $where );
	    if ( !empty( $row ) )
		$id = $row->id_annual_planning;
	    else {
		
		$data['fk_id_sysuser'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$this->_data = $data;
		$id = parent::_simpleSave( $dbAnnualPlanning, false );
	    }
	    
	} else {
	    $id = $data['id_annual_planning'];
	}
	
	return $id;
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    protected function _getTotalsAnnualPlanning( $id )
    {
	$dbPlanningCourse = App_Model_DbTable_Factory::get( 'FPPlanningCourse' );
	
	$select = $dbPlanningCourse->select()
				   ->from( 
					array( 'pc' => $dbPlanningCourse ),
					array(
					    'total_students' => new Zend_Db_Expr( 'SUM(pc.total_students)' ),
					    'total_cost'     => new Zend_Db_Expr( 'SUM(pc.total_cost)' )
					)
				    )
				    ->where( 'pc.fk_id_annual_planning = ?', $id );
	
	return $dbPlanningCourse->fetchRow( $select );
    }
    
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function fetchEvents( $filters = array() )
    {
	$select = $this->getSelectEvent();
	$select->order( array( 'pc.date_start' ) );
	
	if ( !empty( $filters['fk_id_fefpeduinstitution'] ) )
	    $select->where( 'ap.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution'] );
	
	if ( !empty( $filters['year_planning'] ) )
	    $select->where( 'ap.year_planning = ?', $filters['year_planning'] );
	
	if ( !empty( $filters['year_formation'] ) ) {
	    
	    $select->where( '( YEAR(pc.date_start) = ?', $filters['year_formation'] );
	    $select->orWhere( 'YEAR(pc.date_finish) = ? )', $filters['year_formation'] );
	}
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detailPlanning( $id )
    {
	$select = $this->getSelect();
	$select->where( 'ap.id_annual_planning = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function fetchEvent( $id )
    {
	$select = $this->getSelectEvent();
	$select->where( 'pc.id_planning_course = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelectEvent()
    {
	$dbAnnualPlanning = App_Model_DbTable_Factory::get( 'FP_Annual_Planning' );
	$dbPlanningCourse = App_Model_DbTable_Factory::get( 'FP_Planning_Course' );
	$dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	
	$mapperEducationInstitute =  new Register_Model_Mapper_EducationInstitute();
	$select = $mapperEducationInstitute->getSelectEducationInstitute();
	
	$select->reset( Zend_Db_Select::GROUP );
	
	$select->join(
		    array( 'ap' => $dbAnnualPlanning ),
		    'ap.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution'
		)
	       ->join(
		    array( 'pc' => $dbPlanningCourse ),
		    'pc.fk_id_annual_planning = ap.id_annual_planning',
		    array(
			'id_planning_course',
			'date_start',
			'date_finish',
			'total_man',
			'total_woman',
			'students_course' => 'total_students',
			'cost_course' => 'total_cost',
			'unit_cost',
			'fk_id_fefpstudentclass'
		    )
	       )
	       ->join(
		    array( 'ps' => $dbPerScholarity ),
		    'pc.fk_id_perscholarity = ps.id_perscholarity',
		    array(
			'id_perscholarity',
			'scholarity',
			'external_code',
			'category'
		    )
		)
		->group( array( 'id_planning_course' ) );
	
	return $select;
    }
    
    /**
     * 
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function deleteEvent( $id )
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbPlanningCourse = App_Model_DbTable_Factory::get( 'FPPlanningCourse' );
	    $row = $dbPlanningCourse->fetchRow( array( 'id_planning_course = ?' => $id ) );
	    
	    if ( !empty( $row->fk_id_fefpstudentclass ) )
		throw new Exception( 'PLANU HO TURMA' );
	    
	    $idPlanning = $row->fk_id_annual_planning;
	    $idCourse = $row->id_planning_course;
	    $row->delete();
	  
	    // Update the totals
	    $dbAnnualPlanning = App_Model_DbTable_Factory::get( 'FPAnnualPlanning' );
	    $totals = $this->_getTotalsAnnualPlanning( $idPlanning );
	    $planning = $dbAnnualPlanning->fetchRow( array( 'id_annual_planning = ?' => $idPlanning ) );
	    $planning->total_students = $totals->total_students;
	    $planning->total_cost = $totals->total_cost;
	    $planning->save();
	    
	    $history = sprintf( 'REMOVE PLANU FORMASAUN: %s BA ANNUAL PLANNING: %s', $idCourse, $idPlanning );
	    $this->_sysAudit( $history, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
	    $dbAdapter->commit();
	    
	    return array( 'status' => true );
	    
	} catch ( Exception $ex ) {

	    $dbAdapter->rollBack();
	    return array( 'status' => false );
	}
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listInstitutes()
    {
	$mapperInstitute = new Register_Model_Mapper_EducationInstitute();
	$select = $mapperInstitute->getSelectEducationInstitute();
	
	$dbAnnualPlanning = App_Model_DbTable_Factory::get( 'FPAnnualPlanning' );
	
	$select->join(
		    array( 'ap' => $dbAnnualPlanning ),
		    'ap.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
		    array( 
			'id_annual_planning',
			'year_planning' 
		    )
		)
		->group( array( 'id_fefpeduinstitution' ) )
		->order( array( 'institution' ) );
	
	return $dbAnnualPlanning->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
	$dbAnnualPlanning = App_Model_DbTable_Factory::get( 'FPAnnualPlanning' );
	$dbPlanningCourse = App_Model_DbTable_Factory::get( 'FPPlanningCourse' );
	$dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	
	$mapperInstitute = new Register_Model_Mapper_EducationInstitute();
	$select = $mapperInstitute->getSelectEducationInstitute();
	
	$select->reset( Zend_Db_Select::GROUP );
	
	$select->join(
		    array( 'ap' => $dbAnnualPlanning ),
		    'ap.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
		    array(
			'id_annual_planning',
			'year_planning',
			'total_students',
			'total_cost'
		    )
		)
		->join(
		    array( 'pc' => $dbPlanningCourse ),
		    'pc.fk_id_annual_planning = ap.id_annual_planning',
		    array(
			'id_planning_course',
			'date_start',
			'date_finish',
			'total_man',
			'total_woman',
			'students_course' => 'total_students',
			'cost_course' => 'total_cost',
			'fk_id_fefpstudentclass'
		    )
		)
		->join(
		    array( 'ps' => $dbPerScholarity ),
		    'pc.fk_id_perscholarity = ps.id_perscholarity',
		    array(
			'id_perscholarity',
			'scholarity',
			'external_code',
			'category'
		    )
		)
		->group( array( 'pc.id_planning_course' ) )
		->order( array( 'ap.year_planning DESC', 'pc.date_start' ) );
	
	return $select;
    }
   
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listByFilters( $filters = array() )
    {
	$select = $this->getSelect();
	
	if ( !empty( $filters['fk_id_fefpeduinstitution'] ) )
	    $select->where( 'ap.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution'] );
	
	if ( !empty( $filters['year_planning'] ) )
	    $select->where( 'ap.year_planning = ?', $filters['year_planning'] );
	
	if ( !empty( $filters['category'] ) )
	    $select->where( 'ps.category = ?', $filters['category'] );
	
	if ( !empty( $filters['fk_id_perscholarity'] ) )
	    $select->where( 'pc.fk_id_perscholarity = ?', $filters['fk_id_perscholarity'] );
	
	if ( !empty( $filters['no_contract'] ) )
	    $select->where( 'pc.fk_id_fefpstudentclass IS NULL' );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'pc.date_start >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'pc.date_finish <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
   
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::FEFOP,
	    'fk_id_sysform'	    => Fefop_Form_FPAnnualPlanning::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}