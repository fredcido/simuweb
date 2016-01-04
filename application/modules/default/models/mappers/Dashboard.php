<?php

class Default_Model_Mapper_Dashboard extends App_Model_Abstract
{

    /**
     *
     * @param array $filters
     * @return array
     */
    public function getDashboards( $filters = array() )
    {
	$dashboards = array(
	    'clients'	    =>  $this->getTotalClients( $filters ),
	    'graduated'	    =>  $this->getGraduated( $filters ),
	    'job-vacancy'   =>  $this->getJobVacancys( $filters ),
	    'find-job'	    =>  $this->getHireds( $filters )
	);
	
	return $dashboards;
    }
    
    /**
     *
     * @param array $filters
     * @return int 
     */
    public function getTotalClients( $filters = array() )
    {
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	
	$select = $dbPerData->select()
			    ->from( 
				array( 'c' => $dbPerData ),
				array( 'total' => new Zend_Db_Expr( 'COUNT(1)' ) )
			    )
			    ->setIntegrityCheck( false );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['data_ini'] ) )
	    $select->where( 'c.date_registration >= ?', $date->set( $filters['data_ini'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['data_fim'] ) )
	    $select->where( 'c.date_registration <= ?', $date->set( $filters['data_fim'] )->toString( 'yyyy-MM-dd' ) );
	
	$row = $dbPerData->fetchRow( $select );
	return $row->total;
    }
    
    /**
     *
     * @param array $filters
     * @return int 
     */
    public function getGraduated( $filters = array() )
    {
	$dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	$dbStudentClass = App_Model_DbTable_Factory::get( 'FEFPStudentClass' );
	
	$select = $dbStudentClassPerData->select()
			    ->from( 
				array( 'sc' => $dbStudentClassPerData ),
				array( 'total' => new Zend_Db_Expr( 'COUNT(1)' ) )
			    )
			    ->join(
				array( 'c' => $dbStudentClass ),
				'c.id_fefpstudentclass = sc.fk_id_fefpstudentclass',
				array()
			    )
			    ->setIntegrityCheck( false )
			    ->where( 'sc.status = ?', StudentClass_Model_Mapper_StudentClass::GRADUATED );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['data_ini'] ) )
	    $select->where( 'c.real_finish_date >= ?', $date->set( $filters['data_ini'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['data_fim'] ) )
	    $select->where( 'c.real_finish_date <= ?', $date->set( $filters['data_fim'] )->toString( 'yyyy-MM-dd' ) );
	
	$row = $dbStudentClassPerData->fetchRow( $select );
	return $row->total;
    }
    
    /**
     *
     * @param array $filters
     * @return int 
     */
    public function getJobVacancys( $filters = array() )
    {
	$dbJobVacancy = App_Model_DbTable_Factory::get( 'JOBVacancy' );
	$select = $dbJobVacancy->select()
			    ->from( 
				array( 'jv' => $dbJobVacancy ),
				array( 'total' => new Zend_Db_Expr( 'SUM(num_position)' ) )
			    )
			    ->setIntegrityCheck( false );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['data_ini'] ) )
	    $select->where( 'jv.registration_date >= ?', $date->set( $filters['data_ini'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['data_fim'] ) )
	    $select->where( 'jv.registration_date <= ?', $date->set( $filters['data_fim'] )->toString( 'yyyy-MM-dd' ) );
	
	$row = $dbJobVacancy->fetchRow( $select );
	return (int)$row->total;
    }
    
    /**
     *
     * @param array $filters
     * @return int 
     */
    public function getHireds( $filters = array() )
    {
	$dbJobHired = App_Model_DbTable_Factory::get( 'Hired' );
	$select = $dbJobHired->select()
			    ->from( 
				array( 'jh' => $dbJobHired ),
				array( 'total' => new Zend_Db_Expr( 'COUNT(1)' ) )
			    )
			    ->setIntegrityCheck( false );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['data_ini'] ) )
	    $select->where( 'jh.result_date >= ?', $date->set( $filters['data_ini'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['data_fim'] ) )
	    $select->where( 'jh.result_date <= ?', $date->set( $filters['data_fim'] )->toString( 'yyyy-MM-dd' ) );
	
	$row = $dbJobHired->fetchRow( $select );
	return $row->total;
    }
    
    /**
     *
     * @param array $filters
     * @return array 
     */
    public function chartClient( $filters = array() )
    {
	$clients = $this->clientByCeop( $filters );
	
	$rows = array();
	foreach ( $clients as $client )
	    $rows[$client->name_dec][$client->gender] = $client->total;
	
	$data = array(
	    'categories' => array(),
	    'men'	 => array(),
	    'women'	 => array()
	);
	
	foreach ( $rows as $ceop => $total ) {
	    
	    $data['categories'][] = $ceop;
	    
	    $data['men'][] = (int)( empty( $total['MANE'] ) ? 0 : $total['MANE'] );
	    $data['women'][] = (int)( empty( $total['FETO'] ) ? 0 : $total['FETO'] );
	}
	
	return $data;
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset 
     */
    public function clientByCeop( $filters = array() )
    {
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	
	$select = $dbPerData->select()
			    ->from( 
				array( 'c' => $dbPerData ),
				array( 'total' => new Zend_Db_Expr( 'COUNT(1)' ), 'gender' )
			    )
			    ->setIntegrityCheck( false )
			    ->join(
				array( 'ce' => $dbDec ),
				'ce.id_dec = c.fk_id_dec',
				array( 'name_dec' )
			    )
			    ->group( array( 'id_dec', 'gender' ) )
			    ->order( array( 'total' ) );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['data_ini'] ) )
	    $select->where( 'c.date_registration >= ?', $date->set( $filters['data_ini'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['data_fim'] ) )
	    $select->where( 'c.date_registration <= ?', $date->set( $filters['data_fim'] )->toString( 'yyyy-MM-dd' ) );
	
	return $dbPerData->fetchAll( $select );
    }
    
    /**
     *
     * @param array $filters
     * @return array 
     */
    public function chartVacancy( $filters = array() )
    {
	$vacancies = $this->vacancyByCeop( $filters );
	
	$data = array(
	    'categories' => array(),
	    'data'	 => array()
	);
	
	foreach ( $vacancies as $vacancy ) {
	    
	    $data['categories'][] = $vacancy->name_dec;
	    $data['data'][] = (int)$vacancy->total;
	}
	
	return $data;
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset 
     */
    public function vacancyByCeop( $filters = array() )
    {
	$dbJobVacancy = App_Model_DbTable_Factory::get( 'JOBVacancy' );
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	
	$select = $dbJobVacancy->select()
			    ->from( 
				array( 'jv' => $dbJobVacancy ),
				array( 'total' => new Zend_Db_Expr( 'SUM(num_position)' ) )
			    )
			    ->setIntegrityCheck( false )
			    ->join(
				array( 'ce' => $dbDec ),
				'ce.id_dec = jv.fk_id_dec',
				array( 'name_dec' )
			    )
			    ->group( array( 'id_dec' ) )
			    ->order( array( 'total' ) );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['data_ini'] ) )
	    $select->where( 'jv.registration_date >= ?', $date->set( $filters['data_ini'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['data_fim'] ) )
	    $select->where( 'jv.registration_date <= ?', $date->set( $filters['data_fim'] )->toString( 'yyyy-MM-dd' ) );
	
	return $dbJobVacancy->fetchAll( $select );
    }
    
    /**
     *
     * @param array $filters
     * @return array 
     */
    public function chartOccupation( $filters = array() )
    {
	$vacancies = $this->vacancyByOccupation( $filters );
	
	$data = array(
	    'data'	 => array()
	);
	
	$view = Zend_Layout::getMvcInstance()->getView();
	
	foreach ( $vacancies as $vacancy ) {
	    
	    $data['categories'][] = $view->truncate( ucfirst( strtolower( $vacancy->ocupation_name_timor ) ) );
	    $data['data'][] = (int)$vacancy->total;
	}
	
	return $data;
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset 
     */
    public function vacancyByOccupation( $filters = array() )
    {
	$dbJobVacancy = App_Model_DbTable_Factory::get( 'JOBVacancy' );
	$dbProfOccupation = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	
	$select = $dbJobVacancy->select()
			    ->from( 
				array( 'jv' => $dbJobVacancy ),
				array( 'total' => new Zend_Db_Expr( 'SUM(num_position)' ) )
			    )
			    ->setIntegrityCheck( false )
			    ->join(
				array( 'po' => $dbProfOccupation ),
				'po.id_profocupationtimor = jv.fk_id_profocupation',
				array( 'ocupation_name_timor' )
			    )
			    ->group( array( 'id_profocupationtimor' ) )
			    ->limit( 10 )
			    ->order( array( 'total DESC' ) );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['data_ini'] ) )
	    $select->where( 'jv.registration_date >= ?', $date->set( $filters['data_ini'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['data_fim'] ) )
	    $select->where( 'jv.registration_date <= ?', $date->set( $filters['data_fim'] )->toString( 'yyyy-MM-dd' ) );
	
	return $dbJobVacancy->fetchAll( $select );
    }
    
    /**
     *
     * @param array $filters
     * @return array 
     */
    public function chartGraduated( $filters = array() )
    {
	$courses = $this->graduatedByCourse( $filters );
	
	$data = array(
	    'data'   => array()
	);
	
	$view = Zend_Layout::getMvcInstance()->getView();
	
	foreach ( $courses as $course )
	    $data['data'][] = array( $view->truncate( ucfirst( strtolower( $course->scholarity ) ) ) , (int)$course->total );
	
	return $data;
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset 
     */
    public function graduatedByCourse( $filters = array() )
    {
	$dbStudentClass = App_Model_DbTable_Factory::get( 'FEFPStudentClass' );
	$dbStudentClassScholarity = App_Model_DbTable_Factory::get( 'FEFPStudentClass_has_PerScholarity' );
	$dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	
	$select = $dbStudentClass->select()
			    ->from( 
				array( 'sc' => $dbStudentClass ),
				array( 'total' => new Zend_Db_Expr( 'COUNT(1)' ) )
			    )
			    ->setIntegrityCheck( false )
			    ->join(
				array( 's' => $dbPerScholarity ),
				'sc.fk_id_perscholarity = s.id_perscholarity',
				array( 'scholarity' )
			    )
			    ->join(
				array( 'scp' => $dbStudentClassPerData ),
				'scp.fk_id_fefpstudentclass = sc.id_fefpstudentclass',
				array()
			    )
			    ->where( 'sc.active = ?', 0 )
			    ->where( 'scp.status = ?', StudentClass_Model_Mapper_StudentClass::GRADUATED )
			    ->group( array( 'id_perscholarity' ) )
			    ->limit( 10 )
			    ->order( array( 'total DESC' ) );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['data_ini'] ) )
	    $select->where( 'sc.real_finish_date >= ?', $date->set( $filters['data_ini'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['data_fim'] ) )
	    $select->where( 'sc.real_finish_date <= ?', $date->set( $filters['data_fim'] )->toString( 'yyyy-MM-dd' ) );
	
	return $dbStudentClass->fetchAll( $select );
    }
}