<?php

class Report_Model_Mapper_Job extends App_Model_Mapper_Abstract
{
    
    /**
     *
     * @var array
     */
    protected $_graphs = array();
    
    /**
     *
     * @return \ArrayObject 
     */
    public function placementReport()
    {
	$filters = $this->_data;
	
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	$dbHired = App_Model_DbTable_Factory::get( 'Hired' );
	$dbJobVacancy = App_Model_DbTable_Factory::get( 'JOBVacancy' );
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	$dbIsicTimor = App_Model_DbTable_Factory::get( 'ISICClassTimor' );
	$dbOcupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$select->join(
		    array( 'h' => $dbHired ),
		    'h.fk_id_perdata = c.id_perdata',
		    array()
		)
		->join(
		    array( 'jv' => $dbJobVacancy ),
		    'jv.id_jobvacancy = h.fk_id_jobvacancy',
		    array()
		)
		->join(
		    array( 'oc' => $dbOcupationTimor ),
		    'jv.fk_id_profocupation = oc.id_profocupationtimor',
		    array( 'ocupation_name_timor' )
		)
		->join(
		    array( 'e' => $dbEnterprise ),
		    'jv.fk_id_fefpenterprise = e.id_fefpenterprise',
		    array()
		)
		->join(
		    array( 'st' => $dbIsicTimor ),
		    'e.fk_id_sectorindustry = st.id_isicclasstimor',
		    array( 'name_classtimor' )
		)
		->order( array( 'name_classtimor' ) );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'h.result_date >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'h.result_date <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'jv.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	if ( empty( $filters['consolidated'] ) ) {
	    
	    if ( !empty( $filters['overseas'] ) ) {
	    
		if ( !empty( $filters['fk_id_addcountry'] ) )
		    $select->where( 'jv.fk_location_overseas = ?', $filters['fk_id_addcountry'] );
		else
		    $select->where( 'IFNULL(jv.fk_location_overseas, 0) NOT IN (?)', array( 0, 1, 19 ) );
	    
	    } else
		$select->where( 'IFNULL(jv.fk_location_overseas, 0) IN (?)', array( 0, 1, 19 ) );
	    
	}
	
	$rows = $dbPerData->fetchAll( $select );
	
	$data = new ArrayObject( 
		    array(
			'sector'    => $this->jobSectorIndustry( $rows ),
			'occupation'=> $this->jobOccupation( $rows ),
			'total'	    => $this->jobHiredTotal( $rows ),
			'school'    => $this->jobSchoolLevel( $rows ),
			'age'	    => $this->jobByAge( $rows ),
			'graph'	    => $this->_graphs
		    ) 
		);
	
	return $data;
    }
    
     /**
     *
     * @param array $rows
     * @return array 
     */
    public function jobSectorIndustry( $rows )
    {
	$data = array(
		    'MANE'	    => 0,
		    'FETO'	    => 0,
		    'total'	    => 0
		);
	
	$sectorData = array(
	    'total'	=> 0,
	    'MANE'	=> 0,
	    'FETO'	=> 0,
	    'porcent'	=> 0
	);
	
	$sectorIndustry = array();
	foreach ( $rows as $row ) {
	 
	    $gender = trim( $row['gender'] );
	    $sector = $row['name_classtimor'];
	    
	    if ( !array_key_exists( $sector, $sectorIndustry ) )
		$sectorIndustry[$sector] = $sectorData;
	    
	    $sectorIndustry[$sector][$gender]++;
	    $sectorIndustry[$sector]['total']++;
	    
	    $data['total']++;
	    $data[$gender]++;
	}
	
	foreach ( $sectorIndustry as $sector => $row )
	    $sectorIndustry[$sector]['porcent'] = round( ( 100 * $row['total'] ) / $data['total'], 2 );
	
	$data['rows'] = $sectorIndustry;
	
	if ( !empty( $data['rows'] ) ) {
	    
	    $column = array(
		'series' => array(),
		'labels' => array(),
		'names'  => array()
	    );

	    $man = array();
	    $woman = array();

	    $view = Zend_Layout::getMvcInstance()->getView();

	    foreach ( $data['rows'] as $sector => $row ) {

		$man[] = $row['MANE'];
		$woman[] = $row['FETO'];
		$column['labels'][] = App_General_String::addBreakLine( $view->truncate( ucfirst( strtolower( $sector ) ), 20 ), 1 );
	    }

	    $column['series'] = array( $man, $woman );
	    $column['names'] = array( 'MANE', 'FETO' );

	    $scaleConfig = array( 'LabelRotation' => 90 );

	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Hetan Servisu Sektor Industria', $scaleConfig );
	}
	
	return $data;
    }
    
     /**
     *
     * @param array $rows
     * @return array 
     */
    public function jobOccupation( $rows )
    {
	$data = array(
		    'MANE'	    => 0,
		    'FETO'	    => 0,
		    'total'	    => 0
		);
	
	$occupationData = array(
	    'total'	=> 0,
	    'MANE'	=> 0,
	    'FETO'	=> 0,
	    'porcent'	=> 0
	);
	
	$occupations = array();
	foreach ( $rows as $row ) {
	 
	    $gender = trim( $row['gender'] );
	    $occupation = $row['ocupation_name_timor'];
	    
	    if ( !array_key_exists( $occupation, $occupations ) )
		$occupations[$occupation] = $occupationData;
	    
	    $occupations[$occupation][$gender]++;
	    $occupations[$occupation]['total']++;
	    
	    $data['total']++;
	    $data[$gender]++;
	}
	
	foreach ( $occupations as $occupation => $row )
	    $occupations[$occupation]['porcent'] = round( ( 100 * $row['total'] ) / $data['total'], 2 );
	
	$data['rows'] = $occupations;
	
	if ( !empty( $data['rows'] ) ) {
	    
	    $column = array(
		'series' => array(),
		'labels' => array(),
		'names'  => array()
	    );

	    $man = array();
	    $woman = array();

	    $view = Zend_Layout::getMvcInstance()->getView();

	    foreach ( $data['rows'] as $ocupation => $row ) {

		$man[] = $row['MANE'];
		$woman[] = $row['FETO'];
		$column['labels'][] = App_General_String::addBreakLine( $view->truncate( ucfirst( strtolower( $ocupation ) ), 15 ), 1 );
	    }

	    $column['series'] = array( $man, $woman );
	    $column['names'] = array( 'MANE', 'FETO' );

	    $scaleConfig = array( 'LabelRotation' => 90 );

	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Hetan Servisu Okupasaun', $scaleConfig );
	}
	
	return $data;
    }
    
    /**
     *
     * @return type 
     */
    public function jobHiredTotal( $rows )
    {
	$data = array(
			'total_man'		=> 0,
			'total_man_porcent'	=> 0,
			'total_woman'		=> 0,
			'total_woman_porcent'	=> 0,
			'total'			=> $rows->count()
		    );
	
	foreach ( $rows as $row ) {
	    
	    if ( $row->gender == 'MANE' )
		$data['total_man']++;
	    else
		$data['total_woman']++;
	}
	
	if ( !empty( $data['total'] ) ) {
	    
	    $data['total_man_porcent'] = round( ( 100 * $data['total_man'] ) / $data['total'], 2 );
	    $data['total_woman_porcent'] = round( ( 100 * $data['total_woman'] ) / $data['total'], 2 );
	    
	    $graph = array(
		'series' => array( $data['total_man'], $data['total_woman'] ),
		'labels' => array( 'MANE', 'FETO' )
	    );
	    
	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::pieChart( $graph, 'Hetan Servisu MANE no FETO' );
	}
	
	return $data;
    }
    
    /**
     *
     * @return type 
     */
    public function jobByAge( $rows )
    {
	$data = array(
		    'rows'		    => array(),
		    'total_man'		    => 0,
		    'total_man_porcent'	    => 0,
		    'total_woman'	    => 0,
		    'total_woman_porcent'   => 0,
		    'total'		    => $rows->count()
		); 
	
	$ages = array();
	foreach ( $rows as $row ) {
	    
	    switch ( $row['age'] ) {
		case $row['age'] < 15:
			@$ages['< 15'][ trim( $row['gender'] ) ]++;
		    break;
		case $row['age'] >= 15 && $row['age'] <= 24:
			@$ages['15 - 24'][ trim( $row['gender'] ) ]++;
		    break;
		case $row['age'] >= 25 && $row['age'] <= 39:
			@$ages['25 - 39'][ trim( $row['gender'] ) ]++;
		    break;
		case $row['age'] >= 40 && $row['age'] <= 54:
			@$ages['40 - 54'][ trim( $row['gender'] ) ]++;
		    break;
		case $row['age'] >= 55:
			@$ages['55+'][ trim( $row['gender'] ) ]++;
		    break;
	    }
	}
	
	ksort( $ages );
	
	foreach ( $ages as $key => $row ) {
	    
	    $row['man'] = empty( $row['MANE'] ) ? 0 : (int)$row['MANE'];
	    $row['woman'] = empty( $row['FETO'] ) ? 0 : (int)$row['FETO'];
	    $total = $row['man'] + $row['woman'];
	    $percentWoman = round( ( 100 * $row['woman'] ) / $total, 2 );
	    $percentMan = round( ( 100 * $row['man'] ) / $total, 2 );
	    
	    $data['rows'][] = array(
				'age'			  =>  $key,
				'total_man'		  =>  $row['man'],
				'total_man_porcent'	  =>  $percentMan,
				'total_woman'		  =>  $row['woman'],
				'total_woman_porcent'	  =>  $percentWoman,
				'total'			  =>  $total
			    );
	    
	    $data['total_man'] += $row['man'];
	    $data['total_woman'] += $row['woman'];
	}
	
	foreach ( $data['rows'] as $key => $value ) {
	    
	    $percent = round( ( 100 * $value['total'] ) / $data['total'], 2 );
	    $data['rows'][$key]['total_porcent'] = $percent;
	}
	
	if ( !empty( $data['rows'] ) ) {
	    
	    $data['total_man_porcent'] = round( ( 100 * $data['total_man'] ) / $data['total'], 2 );
	    $data['total_woman_porcent'] = round( ( 100 * $data['total_woman'] ) / $data['total'], 2 );
	    
	    $man = array();
	    $woman = array();
	    
	    foreach ( $data['rows'] as $row ) {

		$man[] = $row['total_man'];
		$woman[] = $row['total_woman'];
		$column['labels'][] = $row['age'];
	    }

	    $column['series'] = array( $man, $woman );
	    $column['names'] = array( 'MANE', 'FETO' );

	    $scaleConfig = array( 'LabelRotation' => 90 );

	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Hetan Servisu Grupu Idade', $scaleConfig );
	
	}
	
	return $data;
    }
    
    /**
     *
     * @param array $rows
     * @return array 
     */
    public function jobSchoolLevel( $rows )
    {
	$data = array(
		    'MANE'	    => 0,
		    'FETO'	    => 0,
		    'total'	    => 0
		);
	
	$schoolData = array(
	    'total'	=> 0,
	    'MANE'	=> 0,
	    'FETO'	=> 0,
	    'porcent'	=> 0
	);
	
	$schoolLevel = array();
	foreach ( $rows as $row ) {
	 
	    $gender = trim( $row['gender'] );
	    $school = empty( $row['max_level_scholarity'] ) ? 'LA IHA' : $row['max_level_scholarity'];
	    
	    if ( !array_key_exists( $school, $schoolLevel ) )
		$schoolLevel[$school] = $schoolData;
	    
	    $schoolLevel[$school][$gender]++;
	    $schoolLevel[$school]['total']++;
	    
	    $data['total']++;
	    $data[$gender]++;
	}
	
	foreach ( $schoolLevel as $school => $row )
	    $schoolLevel[$school]['porcent'] = round( ( 100 * $row['total'] ) / $data['total'], 2 );
	
	$data['rows'] = $schoolLevel;
	
	if ( !empty( $data['rows'] ) ) {
	    
	    $man = array();
	    $woman = array();

	    foreach ( $data['rows'] as $school => $row ) {

		$man[] = $row['MANE'];
		$woman[] = $row['FETO'];
		$column['labels'][] = App_General_String::addBreakLine( $school, 1);
	    }

	    $column['series'] = array( $man, $woman );
	    $column['names'] = array( 'MANE', 'FETO' );

	    $scaleConfig = array( 'LabelRotation' => 90 );

	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Hetan Servisu Nivel Eskola', $scaleConfig );
	
	}
	
	return $data;
    }
    
    /**
     *
     * @return array
     */
    public function shortlistedReport()
    {
	$mapperVacancy = new Job_Model_Mapper_JobVacancy();
	$select = $mapperVacancy->getSelectVacancy();
	
	$dbShortlist = App_Model_DbTable_Factory::get( 'ShortlistVacancy' );
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	
	$select->join(
		    array( 's' => $dbShortlist ),
		    's.fk_id_jobvacancy = jv.id_jobvacancy',
		    array(
			'count' => new Zend_Db_Expr( 'COUNT(1)' )
		    )
		)
		->join(
		    array( 'c' => $dbPerData ),
		    's.fk_id_perdata = c.id_perdata',
		    array( 'client_gender' => 'gender' )
		)
		->order( array( 'vacancy_titule' ) )
		->group( array( 'id_jobvacancy', 'c.gender' ) );
	
	$date = new Zend_Date();
	
	$filters = $this->_data;
	
	$select->where( 'jv.active = ?', (int)$filters['active'] );
	
	if ( !empty( $filters['fk_id_profocupation'] ) )
	    $select->where( 'jv.fk_id_profocupation = ?', $filters['fk_id_profocupation'] );
	
	if ( !empty( $filters['fk_id_fefpenterprise'] ) )
	    $select->where( 'jv.fk_id_fefpenterprise = ?', $filters['fk_id_fefpenterprise'] );
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'jv.open_date >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'jv.close_date <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'jv.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	$rows = $dbShortlist->fetchAll( $select );
	
	$data = array(
		    'MANE'	    => 0,
		    'FETO'	    => 0,
		    'total'	    => 0
		);
	
	$vacancyData = array(
	    'total'	=> 0,
	    'MANE'	=> 0,
	    'FETO'	=> 0,
	);
	
	$vacancies = array();
	foreach ( $rows as $row ) {
	 
	    $row = $row->toArray();
	    $gender = trim( $row['client_gender'] );
	    $vacancy = $row['id_jobvacancy'];
	    
	    if ( !array_key_exists( $vacancy, $vacancies ) )
		$vacancies[$vacancy] = $row + $vacancyData;
	    
	    $vacancies[$vacancy][$gender] += $row['count'];
	    $vacancies[$vacancy]['total'] += $row['count'];
	    
	    $data['total'] += $row['count'];
	    $data[$gender] += $row['count'];
	}
	
	$data['rows'] = $vacancies;
	$data['graph'] = array();
	
	if ( !empty( $data['rows'] ) ) {
	    
	    $graph = array(
		'series' => array( $data['MANE'], $data['FETO'] ),
		'labels' => array( 'MANE', 'FETO' )
	    );
	    
	    $data['graph'][App_General_String::randomHash()] = App_Util_Chart::pieChart( $graph, 'Refere Shortlist MANE no FETO' );  
	}
	
	return $data;
    }
    
    /**
     *
     * @return array
     */
    public function listShortlistReport()
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$dbShortlist = App_Model_DbTable_Factory::get( 'ShortlistVacancy' );
	$dbListCandidates = App_Model_DbTable_Factory::get( 'JOBVacancy_Candidates' );
	$dbHired = App_Model_DbTable_Factory::get( 'Hired' );
	$dbJobVacancy = App_Model_DbTable_Factory::get( 'JOBVacancy' );
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	$dbOcupation = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	
	$select->join(
		    array( 's' => $dbShortlist ),
		    's.fk_id_perdata = c.id_perdata',
		    array()
		)
		->join(
		    array( 'jv' => $dbJobVacancy ),
		    's.fk_id_jobvacancy = jv.id_jobvacancy',
		    array( 'vacancy_titule' )
		)
		->joinLeft(
		    array( 'lc' => $dbListCandidates ),
		    'lc.fk_id_jobvacancy = jv.id_jobvacancy AND lc.fk_id_perdata = c.id_perdata',
		    array()
		)
		->join(
		    array( 'e' => $dbEnterprise ),
		    'e.id_fefpenterprise = jv.fk_id_fefpenterprise',
		    array( 'enterprise_name' )
		)
		->join(
		    array( 'o' => $dbOcupation ),
		    'o.id_profocupationtimor = jv.fk_id_profocupation',
		    array( 'ocupation_name_timor' )
		)
		->joinLeft(
		    array( 'h' => $dbHired ),
		    'h.fk_id_perdata = c.id_perdata AND h.fk_id_jobvacancy = jv.id_jobvacancy',
		    array( 'hired' => 'id_relationship' )
		);
	
	$date = new Zend_Date();
	
	$filters = $this->_data;
	
	$select->where( 'jv.active = ?', (int)$filters['active'] );
	
	if ( (int)$filters['active'] == 1 ) {
	    
	    if ( !empty( $filters['date_start'] ) )
		$select->where( 'jv.open_date >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );

	    if ( !empty( $filters['date_finish'] ) )
		$select->where( 'jv.open_date <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	    
	} else {
	    
	    if ( !empty( $filters['date_start'] ) )
		$select->where( 'jv.close_date >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	    if ( !empty( $filters['date_finish'] ) )
		$select->where( 'jv.close_date <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	}
	
	if ( !empty( $filters['fk_id_profocupation'] ) )
	    $select->where( 'jv.fk_id_profocupation = ?', $filters['fk_id_profocupation'] );
	
	if ( !empty( $filters['fk_id_sysuser'] ) )
	    $select->where( 'lc.fk_id_sysuser = ?', $filters['fk_id_sysuser'] );
	
	if ( !empty( $filters['fk_id_fefpenterprise'] ) )
	    $select->where( 'jv.fk_id_fefpenterprise = ?', $filters['fk_id_fefpenterprise'] );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'jv.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	if ( !empty( $filters['hired'] ) )
	    $select->where( 'IF( h.id_relationship, 1, 0 ) = ?', ( $filters['hired'] == 'S' ? 1 : 0 ) );
	
	$rows = $dbShortlist->fetchAll( $select );
	
	return array( 'rows' => $rows );
    }
    
    /**
     *
     * @return array
     */
    public function registerReport()
    {
	$mapperVacancy = new Job_Model_Mapper_JobVacancy();
	$select = $mapperVacancy->getSelectVacancy();
	
	$dbVacancy = App_Model_DbTable_Factory::get( 'JOBVacancy' );
	
	$date = new Zend_Date();
	$filters = $this->_data;
	
	$select->where( 'jv.active = ?', (int)$filters['active'] );
	
	if ( !empty( $filters['fk_id_profocupation'] ) )
	    $select->where( 'jv.fk_id_profocupation = ?', $filters['fk_id_profocupation'] );
	
	if ( !empty( $filters['fk_id_fefpenterprise'] ) )
	    $select->where( 'jv.fk_id_fefpenterprise = ?', $filters['fk_id_fefpenterprise'] );
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'jv.open_date >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'jv.close_date <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'jv.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	$rows = $dbVacancy->fetchAll( $select );
	
	return array( 'rows' => $rows );
    }
    
    /**
     *
     * @return array 
     */
    public function youthIndicatorReport()
    {
	$data = array();
	
	$data += $this->youthJobSearch();
	$data += $this->youthJobPlacement();
	$data += $this->youthFormation();
	
	$this->_graphs = array();
	
	// Graph Youth Seek Job Level School
	if ( !empty( $data['school'] ) ) {
	    
	    $man = array();
	    $woman = array();

	    foreach ( $data['school']['rows'] as $school => $row ) {

		$man[] = $row['MANE'];
		$woman[] = $row['FETO'];
		$column['labels'][] = App_General_String::addBreakLine( $school, 1);
	    }

	    $column['series'] = array( $man, $woman );
	    $column['names'] = array( 'MANE', 'FETO' );

	    $scaleConfig = array( 'LabelRotation' => 90 );

	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Buka Servisu Nivel Eskola', $scaleConfig );
	}
	
	// Graph Youth Seek Job District
	if ( !empty( $data['district'] ) ) {
	    
	    $man = array();
	    $woman = array();

	    foreach ( $data['district']['rows'] as $district => $row ) {

		$man[] = $row['MANE'];
		$woman[] = $row['FETO'];
		$column['labels'][] = App_General_String::addBreakLine( $district, 1);
	    }

	    $column['series'] = array( $man, $woman );
	    $column['names'] = array( 'MANE', 'FETO' );

	    $scaleConfig = array( 'LabelRotation' => 90 );

	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Buka Servisu Distrito', $scaleConfig );
	}
	
	// Graph Youth Got Job Level School
	if ( !empty( $data['school_job'] ) ) {
	    
	    $man = array();
	    $woman = array();

	    foreach ( $data['school_job']['rows'] as $school => $row ) {

		$man[] = $row['MANE'];
		$woman[] = $row['FETO'];
		$column['labels'][] = App_General_String::addBreakLine( $school, 1);
	    }

	    $column['series'] = array( $man, $woman );
	    $column['names'] = array( 'MANE', 'FETO' );

	    $scaleConfig = array( 'LabelRotation' => 90 );

	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Hetan Servisu Nivel Eskola', $scaleConfig );
	}
	
	// Graph Youth Got Job District
	if ( !empty( $data['district_job'] ) ) {
	    
	    $man = array();
	    $woman = array();

	    foreach ( $data['district_job']['rows'] as $district => $row ) {

		$man[] = $row['MANE'];
		$woman[] = $row['FETO'];
		$column['labels'][] = App_General_String::addBreakLine( $district, 1);
	    }

	    $column['series'] = array( $man, $woman );
	    $column['names'] = array( 'MANE', 'FETO' );

	    $scaleConfig = array( 'LabelRotation' => 90 );

	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Hetan Servisu Distrito', $scaleConfig );
	}
	
	// Graph Youth Got Job Occupation
	if ( !empty( $data['occupation'] ) ) {
	    
	    $column = array(
		'series' => array(),
		'labels' => array(),
		'names'  => array()
	    );

	    $man = array();
	    $woman = array();

	    $view = Zend_Layout::getMvcInstance()->getView();

	    foreach ( $data['occupation']['rows'] as $ocupation => $row ) {

		$man[] = $row['MANE'];
		$woman[] = $row['FETO'];
		$column['labels'][] = App_General_String::addBreakLine( $view->truncate( ucfirst( strtolower( $ocupation ) ), 15 ), 1 );
	    }

	    $column['series'] = array( $man, $woman );
	    $column['names'] = array( 'MANE', 'FETO' );

	    $scaleConfig = array( 'LabelRotation' => 90 );

	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Hetan Servisu Okupasaun', $scaleConfig );
	}
	
	if ( !empty( $data['sector'] ) ) {
	    
	    $column = array(
		'series' => array(),
		'labels' => array(),
		'names'  => array()
	    );

	    $man = array();
	    $woman = array();

	    $view = Zend_Layout::getMvcInstance()->getView();

	    foreach ( $data['sector']['rows'] as $sector => $row ) {

		$man[] = $row['MANE'];
		$woman[] = $row['FETO'];
		$column['labels'][] = App_General_String::addBreakLine( $view->truncate( ucfirst( strtolower( $sector ) ), 20 ), 1 );
	    }

	    $column['series'] = array( $man, $woman );
	    $column['names'] = array( 'MANE', 'FETO' );

	    $scaleConfig = array( 'LabelRotation' => 90 );

	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Hetan Servisu Sektor Industria', $scaleConfig );
	}
	
	$data['graph'] = $this->_graphs;
	
	return $data;
    }
    
    /**
     *
     * @return array 
     */
    public function youthFormation()
    {
	$data = array(
	    'national_certificate' => $this->_youthFormation( 'N' ),
	    'vocational_training' => $this->_youthFormation( 'V' ),
	    'community_training' => $this->_youthFormation( 'C' )
	);
	
	return $data;
    }
    
    /**
     *
     * @param string $category
     * @return array 
     */
    protected function _youthFormation( $category )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$dbStudentClass = App_Model_DbTable_Factory::get( 'FEFPStudentClass' );
	$dbStudentClassPerData = App_Model_DbTable_Factory::get( 'FEFEPStudentClass_has_PerData' );
	$dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbScholarityArea = App_Model_DbTable_Factory::get( 'ScholarityArea' );
	$dbTrainingProvider = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	
	$select->join(
		    array( 'scp' => $dbStudentClassPerData ),
		    'scp.fk_id_perdata = c.id_perdata',
		    array()
		)
		->join(
		    array( 'sc' => $dbStudentClass ),
		    'scp.fk_id_fefpstudentclass = sc.id_fefpstudentclass',
		    array()
		)
		->join(
		    array( 's' => $dbScholarity ),
		    'sc.fk_id_perscholarity = s.id_perscholarity',
		    array( 'external_code', 'scholarity' )
		)
		->join(
		    array( 'sa' => $dbScholarityArea ),
		    'sa.id_scholarity_area = s.fk_id_scholarity_area',
		    array( 'scholarity_area' )
		)
		->join(
		    array( 'tp' => $dbTrainingProvider ),
		    'tp.id_fefpeduinstitution = sc.fk_id_fefpeduinstitution',
		    array( 'institution' )
		)
		->where( 'scp.status = ?', StudentClass_Model_Mapper_StudentClass::GRADUATED )
		->where( 's.category = ?', $category )
		->group( 'id_perdata' )
		->having( 'age >= ?', 15 )->having( 'age <= ?', 29 );
	
	$date = new Zend_Date();
	
	if ( !empty( $this->_data['date_start'] ) )
	    $select->where( 'sc.real_finish_date >= ?', $date->set( $this->_data['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $this->_data['date_finish'] ) )
	    $select->where( 'sc.real_finish_date <= ?', $date->set( $this->_data['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	$rows = $dbStudentClass->fetchAll( $select );
	
	return array(
	    'area_course'	=> $this->youthAreaCourse( $rows ),
	    'course'		=> $this->youthCourse( $rows ),
	    'training_provider' => $this->youthTrainingProvider( $rows ),
	);
    }
    
    /**
     *
     * @param array $data
     * @return array
     */
    public function youthAreaCourse( $rows )
    {
	$data = array(
		    'MANE'	    => 0,
		    'FETO'	    => 0,
		    'total'	    => 0
		);
	
	$schoolData = array(
	    'total'	=> 0,
	    'MANE'	=> 0,
	    'FETO'	=> 0,
	    'porcent'	=> 0
	);
	
	$areaScholarity = array();
	foreach ( $rows as $row ) {
	 
	    $gender = trim( $row['gender'] );
	    $area = trim( $row['scholarity_area'] );
	    
	    if ( !array_key_exists( $area, $areaScholarity ) )
		$areaScholarity[$area] = $schoolData;
	    
	    $areaScholarity[$area][$gender]++;
	    $areaScholarity[$area]['total']++;
	    
	    $data['total']++;
	    $data[$gender]++;
	}
	
	foreach ( $areaScholarity as $area => $row )
	    $areaScholarity[$area]['porcent'] = round( ( 100 * $row['total'] ) / $data['total'], 2 );
	
	$data['rows'] = $areaScholarity;
	return $data;
    }
    
    /**
     *
     * @param array $rows
     * @return array
     */
    public function youthCourse( $rows )
    {
	$data = array(
		    'MANE'	    => 0,
		    'FETO'	    => 0,
		    'total'	    => 0
		);
	
	$schoolData = array(
	    'total'	=> 0,
	    'MANE'	=> 0,
	    'FETO'	=> 0,
	    'porcent'	=> 0
	);
	
	$courses = array();
	foreach ( $rows as $row ) {
	 
	    $gender = trim( $row['gender'] );
	    $course = trim( $row['scholarity'] );
	    
	    if ( !array_key_exists( $course, $courses ) )
		$courses[$course] = $schoolData;
	    
	    $courses[$course][$gender]++;
	    $courses[$course]['total']++;
	    
	    $data['total']++;
	    $data[$gender]++;
	}
	
	foreach ( $courses as $course => $row )
	    $courses[$course]['porcent'] = round( ( 100 * $row['total'] ) / $data['total'], 2 );
	
	$data['rows'] = $courses;
	
	return $data;
    }
    
    /**
     *
     * @param array $data
     * @return array
     */
    public function youthTrainingProvider( $rows )
    {
	$data = array(
		    'MANE'	    => 0,
		    'FETO'	    => 0,
		    'total'	    => 0
		);
	
	$schoolData = array(
	    'total'	=> 0,
	    'MANE'	=> 0,
	    'FETO'	=> 0,
	    'porcent'	=> 0
	);
	
	$trainingProvider = array();
	foreach ( $rows as $row ) {
	 
	    $gender = trim( $row['gender'] );
	    $institution = trim( $row['institution'] );
	    
	    if ( !array_key_exists( $institution, $trainingProvider ) )
		$trainingProvider[$institution] = $schoolData;
	    
	    $trainingProvider[$institution][$gender]++;
	    $trainingProvider[$institution]['total']++;
	    
	    $data['total']++;
	    $data[$gender]++;
	}
	
	foreach ( $trainingProvider as $institution => $row )
	    $trainingProvider[$institution]['porcent'] = round( ( 100 * $row['total'] ) / $data['total'], 2 );
	
	$data['rows'] = $trainingProvider;
	return $data;
    }
    
    /**
     *
     * @return \ArrayObject 
     */
    public function youthJobSearch()
    {
	$filters = $this->_data;
	
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$select->having( 'age >= ?', 15 )->having( 'age <= ?', 29 );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'c.date_registration >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'c.date_registration <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'c.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	$rows = $dbPerData->fetchAll( $select );

	$data = array(
			'school'    => $this->jobSchoolLevel( $rows ),
			'district'  => $this->jobDistrict( $rows )
		    );
	
	return $data;
    }
    
    /**
     *
     * @param array $rows
     * @return array 
     */
    public function jobDistrict( $rows )
    {
	$data = array(
		    'MANE'	    => 0,
		    'FETO'	    => 0,
		    'total'	    => 0
		);
	
	$schoolData = array(
	    'total'	=> 0,
	    'MANE'	=> 0,
	    'FETO'	=> 0,
	    'porcent'	=> 0
	);
	
	$nameDec = array();
	foreach ( $rows as $row ) {
	 
	    $gender = trim( $row['gender'] );
	    $dec = $row['name_dec'];
	    
	    if ( !array_key_exists( $dec, $nameDec ) )
		$nameDec[$dec] = $schoolData;
	    
	    $nameDec[$dec][$gender]++;
	    $nameDec[$dec]['total']++;
	    
	    $data['total']++;
	    $data[$gender]++;
	}
	
	foreach ( $nameDec as $dec => $row )
	    $nameDec[$dec]['porcent'] = round( ( 100 * $row['total'] ) / $data['total'], 2 );
	
	$data['rows'] = $nameDec;
	
	return $data;
    }
    
    /**
     *
     * @return \ArrayObject 
     */
    public function youthJobPlacement()
    {
	$filters = $this->_data;
	
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	$dbHired = App_Model_DbTable_Factory::get( 'Hired' );
	$dbJobVacancy = App_Model_DbTable_Factory::get( 'JOBVacancy' );
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	$dbIsicTimor = App_Model_DbTable_Factory::get( 'ISICClassTimor' );
	$dbOcupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$select->having( 'age >= ?', 15 )->having( 'age <= ?', 29 );
	
	$select->join(
		    array( 'h' => $dbHired ),
		    'h.fk_id_perdata = c.id_perdata',
		    array()
		)
		->join(
		    array( 'jv' => $dbJobVacancy ),
		    'jv.id_jobvacancy = h.fk_id_jobvacancy',
		    array()
		)
		->join(
		    array( 'oc' => $dbOcupationTimor ),
		    'jv.fk_id_profocupation = oc.id_profocupationtimor',
		    array( 'ocupation_name_timor' )
		)
		->join(
		    array( 'e' => $dbEnterprise ),
		    'jv.fk_id_fefpenterprise = e.id_fefpenterprise',
		    array()
		)
		->join(
		    array( 'st' => $dbIsicTimor ),
		    'e.fk_id_sectorindustry = st.id_isicclasstimor',
		    array( 'name_classtimor' )
		)
		->order( array( 'name_classtimor' ) );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'h.result_date >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'h.result_date <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'jv.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	$rows = $dbPerData->fetchAll( $select );
	
	$data = array(
		    'sector'	=> $this->jobSectorIndustry( $rows ),
		    'occupation'	=> $this->jobOccupation( $rows ),
		    'school_job'	=> $this->jobSchoolLevel( $rows ),
		    'district_job'  => $this->jobDistrict( $rows ) 
		);
	
	return $data;
    }
    
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function filterAppliedJob( $filters )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$dbShortlist = App_Model_DbTable_Factory::get( 'ShortlistVacancy' );
	$dbJobVacancy = App_Model_DbTable_Factory::get( 'JOBVacancy' );
	
	$select->join(
		    array( 'sv' => $dbShortlist ),
		    'sv.fk_id_perdata = c.id_perdata',
		    array()
		)
		->join(
		    array( 'jv' => $dbJobVacancy ),
		    'jv.id_jobvacancy = sv.fk_id_jobvacancy',
		    array()
		);
	
	if ( !empty( $filters['year'] ) )
	    $select->where( 'YEAR( jv.registration_date ) = ?', $filters['year'] );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'jv.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	$rows = $dbShortlist->fetchAll( $select );
	return $rows;
    }
    
    /**
     *
     * @return array
     */
    public function educationReport()
    {
	$rows = $this->filterAppliedJob( $this->_data );
	
	$mapperClient = new Report_Model_Mapper_Client();
	$data = $mapperClient->registerSchoolLevel( $rows );
	
	$column = array(
	    'series' => array(),
	    'labels' => array(),
	    'names'  => array()
	);

	$man = array();
	$woman = array();

	foreach ( $data['rows'] as $school => $row ) {

	    $man[] = $row['MANE'];
	    $woman[] = $row['FETO'];
	    $column['labels'][] = $school;
	}

	$column['series'] = array( $man, $woman );
	$column['names'] = array( 'MANE', 'FETO' );

	$data['graph'][App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Nivel Eskola / Tinan ' . $this->_data['year'] );
	return $data;
    }
}