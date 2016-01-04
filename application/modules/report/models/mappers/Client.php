<?php

class Report_Model_Mapper_Client extends App_Model_Mapper_Abstract
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
    public function registerReport()
    {
	$rows = $this->getFilteredRows( $this->_data );
	
	$data = new ArrayObject( 
		    array(
			'total'	    => $this->totalRegister( $rows ),
			'age'	    => $this->registerByAge( $rows ),
			'school'    => $this->registerSchoolLevel( $rows ),
			'graph'    => $this->_graphs
		    ) 
		);
	
	return $data;
    }
    
    /**
     *
     * @param array $rows
     * @return Zend_Db_Table_Rowset
     */
    public function getFilteredRows( $filters =  array() )
    {
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$select->join(
		    array( 'ds' => $dbDistrict ),
		    'ds.acronym = c.num_district',
		    array( 'District' )
		);
	
	$filters = $this->_data;
	$date = new Zend_Date();
	
	if ( !empty( $filters['year'] ) )
	    $select->where( 'YEAR( c.date_registration ) = ?', $filters['year'] );
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'c.date_registration >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'c.date_registration <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'c.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	$rows = $dbPerData->fetchAll( $select );
	return $rows;
    }
    
    /**
     *
     * @return type 
     */
    public function totalRegister( $rows )
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
	    
	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::pieChart( $graph, 'Rejistu MANE no FETO' );
	}
	
	return $data;
    }
    
    /**
     *
     * @return type 
     */
    public function registerByAge( $rows )
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
	    
	    $pie = array(
		'series' => array(),
		'labels'  => array()
	    );
	    
	    $column = array(
		'series' => array(),
		'labels' => array(),
		'names'  => array()
	    );

	    $man = array();
	    $woman = array();
	    
	    foreach ( $data['rows'] as $row ) {

		$pie['series'][] = $row['total'];
		$pie['labels'][] = $row['age'];
		
		$man[] = $row['total_man'];
		$woman[] = $row['total_woman'];
		$column['labels'][] = $row['age'];
	    }
	    
	    $column['series'] = array( $man, $woman );
	    $column['names'] = array( 'MANE', 'FETO' );
	
	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::pieChart( $pie, 'Grupu Idade' );
	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Grupu Idade / Seksu' );
	}
	
	return $data;
    }
    
    /**
     *
     * @param array $rows
     * @return array 
     */
    public function registerSchoolLevel( $rows )
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
	    
	    $pie = array(
		'series' => array(),
		'labels'  => array()
	    );
	    
	    $column = array(
		'series' => array(),
		'labels' => array(),
		'names'  => array()
	    );

	    $man = array();
	    $woman = array();
	    
	    foreach ( $data['rows'] as $school => $row ) {

		$pie['series'][] = $row['total'];
		$pie['labels'][] = $school;
		
		$man[] = $row['MANE'];
		$woman[] = $row['FETO'];
		$column['labels'][] = $school;
	    }
	    
	    $column['series'] = array( $man, $woman );
	    $column['names'] = array( 'MANE', 'FETO' );
	
	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::pieChart( $pie, 'Nivel Edukasaun' );
	    $scaleConfig = array( 'LabelRotation' => 20 );
	    $this->_graphs[App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Nivel Edukasaun / Seksu', $scaleConfig );
	}
	
	return $data;
    }
    
    /**
     *
     * @return array
     */
    public function ceopYearReport()
    {
	$data = $this->clientByCeop( $this->getFilteredRows( $this->_data ) );
	
	$column = array(
	    'series' => array(),
	    'labels' => array(),
	    'names'  => array()
	);

	$man = array();
	$woman = array();

	foreach ( $data['rows'] as $row ) {

	    $man[] = $row['total_man'];
	    $woman[] = $row['total_woman'];
	    $column['labels'][] = $row['ceop'];
	}

	$column['series'] = array( $man, $woman );
	$column['names'] = array( 'MANE', 'FETO' );

	$data['graph'][App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'CEOP / Tinan ' . $this->_data['year'] );
	return $data;
    }
    
    /**
     *
     * @return array
     */
    public function ageGroupYearReport()
    {
	$rows = $this->getFilteredRows( $this->_data );
	$data = $this->registerByAge( $rows );
	
	$column = array(
	    'series' => array(),
	    'labels' => array(),
	    'names'  => array()
	);

	$man = array();
	$woman = array();

	foreach ( $data['rows'] as $row ) {

	    $man[] = $row['total_man'];
	    $woman[] = $row['total_woman'];
	    $column['labels'][] = $row['age'];
	}

	$column['series'] = array( $man, $woman );
	$column['names'] = array( 'MANE', 'FETO' );

	$data['graph'][App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Grupu idade / Tinan ' . $this->_data['year'] );
	return $data;
    }
    
    /**
     *
     * @return array
     */
    public function schoolYearReport()
    {
	$rows = $this->getFilteredRows( $this->_data );
	$data = $this->registerSchoolLevel( $rows );
	
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
    
    /**
     *
     * @return array
     */
    public function districtReport()
    {
	$rows = $this->getFilteredRows( $this->_data );
	$data = $this->clientByDistrict( $rows );
	
	$column = array(
	    'series' => array(),
	    'labels' => array(),
	    'names'  => array()
	);

	$man = array();
	$woman = array();

	foreach ( $data['rows'] as $row ) {

	    $man[] = $row['total_man'];
	    $woman[] = $row['total_woman'];
	    $column['labels'][] = $row['district'];
	}

	$column['series'] = array( $man, $woman );
	$column['names'] = array( 'MANE', 'FETO' );

	$data['graph'][App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Kliente husi Distritu' );
	
	return $data;
    }
    
     /**
     *
     * @return array
     */
    public function visitPurposeReport()
    {
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );
	$dbPerVisitPurpose = App_Model_DbTable_Factory::get( 'PerVisitPurpose' );
	$dbVisitPurpose = App_Model_DbTable_Factory::get( 'VisitPurpose' );

	$select = $dbPerData->select()
			    ->setIntegrityCheck( false )
			    ->from(
				array( 'c' => $dbPerData ),
				array( 'gender' )
			    )
			    ->join(
				array( 'pvp' => $dbPerVisitPurpose ),
				'pvp.fk_id_perdata = c.id_perdata',
				array()
			    )
			    ->join(
				array( 'vp' => $dbVisitPurpose ),
				'vp.id_visitpurpose = pvp.fk_id_visitpurpose',
				array( 'purpose' )
			    );
	
	$filters = $this->_data;
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'pvp.visit_date >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'pvp.visit_date <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'c.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	$rows = $dbPerData->fetchAll( $select );
	
	$data = $this->visitPurpose( $rows );
	
	$column = array(
	    'series' => array(),
	    'labels' => array(),
	    'names'  => array()
	);

	$man = array();
	$woman = array();
	
	$view = Zend_Layout::getMvcInstance()->getView();

	foreach ( $data['rows'] as $row ) {

	    $man[] = $row['total_man'];
	    $woman[] = $row['total_woman'];
	    $column['labels'][] = App_General_String::addBreakLine( $view->truncate( $row['purpose'] ), 2 );
	}

	$column['series'] = array( $man, $woman );
	$column['names'] = array( 'MANE', 'FETO' );

	$data['graph'][App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Kliente husi Distritu' );
	
	return $data;
    }
    
    /**
     *
     * @param Zend_Db_Table_Rowset $rows
     * @return array
     */
    public function clientByCeop( $rows )
    {
	$ceops = array();
	foreach ( $rows as $row ) {
	    
	    if ( !array_key_exists( $row['name_dec'], $ceops ) )
		$ceops[$row['name_dec']] = array();
	    
	    $gender = trim( $row['gender'] );
	    
	    if ( !array_key_exists( $gender, $ceops[$row['name_dec']] ) )
		$ceops[$row['name_dec']][$gender] = 0;
	    
	    $ceops[$row['name_dec']][$gender]++;
	}
	
	ksort( $ceops );
		
	$data = new ArrayObject( 
		    array(
			'rows'			=> array(),
			'total_man'		=> 0,
			'total_man_porcent'	=> 0,
			'total_woman'		=> 0,
			'total_woman_porcent'	=> 0,
			'total'			=> 0,
			'graph'			=>  array(),
			'filters'		=> $this->_data
		    ) 
		);
	
	foreach ( $ceops as $key => $row ) {
	    
	    $row['man'] = empty( $row['MANE'] ) ? 0 : (int)$row['MANE'];
	    $row['woman'] = empty( $row['FETO'] ) ? 0 : (int)$row['FETO'];
	    $total = $row['man'] + $row['woman'];
	    
	    $data['rows'][] = array(
				'ceop'		  =>  $key,
				'total_man'	  =>  $row['man'],
				'total_woman'	  =>  $row['woman'],
				'total'		  =>  $total
			    );
	    
	    $data['total'] += $total;
	    $data['total_man'] += $row['man'];
	    $data['total_woman'] += $row['woman'];
	}
	
	if ( !empty( $data['total'] ) ) {
	    
	    $data['total_man_porcent'] = round( ( 100 * $data['total_man'] ) / $data['total'], 2 );
	    $data['total_woman_porcent'] = round( ( 100 * $data['total_woman'] ) / $data['total'], 2 );
	}
	
	return $data;
    }
    
    /**
     *
     * @param Zend_Db_Table_Rowset $rows
     * @return array
     */
    public function clientByDistrict( $rows )
    {
	$districts = array();
	foreach ( $rows as $row ) {
	    
	    if ( !array_key_exists( $row['District'], $districts ) )
		$districts[$row['District']] = array();
	    
	    $gender = trim( $row['gender'] );
	    
	    if ( !array_key_exists( $gender, $districts[$row['District']] ) )
		$districts[$row['District']][$gender] = 0;
	    
	    $districts[$row['District']][$gender]++;
	}
	
	ksort( $districts );
		
	$data = new ArrayObject( 
		    array(
			'rows'			=> array(),
			'total_man'		=> 0,
			'total_man_porcent'	=> 0,
			'total_woman'		=> 0,
			'total_woman_porcent'	=> 0,
			'total'			=> 0,
			'graph'			=>  array(),
			'filters'		=> $this->_data
		    ) 
		);
	
	foreach ( $districts as $key => $row ) {
	    
	    $row['man'] = empty( $row['MANE'] ) ? 0 : (int)$row['MANE'];
	    $row['woman'] = empty( $row['FETO'] ) ? 0 : (int)$row['FETO'];
	    $total = $row['man'] + $row['woman'];
	    
	    $data['rows'][] = array(
				'district'	  =>  $key,
				'total_man'	  =>  $row['man'],
				'total_woman'	  =>  $row['woman'],
				'total'		  =>  $total
			    );
	    
	    $data['total'] += $total;
	    $data['total_man'] += $row['man'];
	    $data['total_woman'] += $row['woman'];
	}
	
	if ( !empty( $data['total'] ) ) {
	    
	    $data['total_man_porcent'] = round( ( 100 * $data['total_man'] ) / $data['total'], 2 );
	    $data['total_woman_porcent'] = round( ( 100 * $data['total_woman'] ) / $data['total'], 2 );
	}
	
	return $data;
    }
    
    /**
     *
     * @param Zend_Db_Table_Rowset $rows
     * @return array
     */
    public function visitPurpose( $rows )
    {
	$purposes = array();
	foreach ( $rows as $row ) {
	    
	    if ( !array_key_exists( $row['purpose'], $purposes ) )
		$purposes[$row['purpose']] = array();
	    
	    $gender = trim( $row['gender'] );
	    
	    if ( !array_key_exists( $gender, $purposes[$row['purpose']] ) )
		$purposes[$row['purpose']][$gender] = 0;
	    
	    $purposes[$row['purpose']][$gender]++;
	}
	
	ksort( $purposes );
		
	$data = new ArrayObject( 
		    array(
			'rows'			=> array(),
			'total_man'		=> 0,
			'total_man_porcent'	=> 0,
			'total_woman'		=> 0,
			'total_woman_porcent'	=> 0,
			'total'			=> 0,
			'graph'			=>  array(),
			'filters'		=> $this->_data
		    ) 
		);
	
	foreach ( $purposes as $key => $row ) {
	    
	    $row['man'] = empty( $row['MANE'] ) ? 0 : (int)$row['MANE'];
	    $row['woman'] = empty( $row['FETO'] ) ? 0 : (int)$row['FETO'];
	    $total = $row['man'] + $row['woman'];
	    
	    $data['rows'][] = array(
				'purpose'	  =>  $key,
				'total_man'	  =>  $row['man'],
				'total_woman'	  =>  $row['woman'],
				'total'		  =>  $total
			    );
	    
	    $data['total'] += $total;
	    $data['total_man'] += $row['man'];
	    $data['total_woman'] += $row['woman'];
	}
	
	if ( !empty( $data['total'] ) ) {
	    
	    $data['total_man_porcent'] = round( ( 100 * $data['total_man'] ) / $data['total'], 2 );
	    $data['total_woman_porcent'] = round( ( 100 * $data['total_woman'] ) / $data['total'], 2 );
	}
	
	return $data;
    }
    
    /**
     * 
     * @return array
     */
    public function ceopQuarterReport()
    {
	$rows = $this->getFilteredRows( $this->_data );
	
	$gender = array(
	    'mane'  =>	0,
	    'feto'  =>	0
	);
	
	$quarters = array_combine( range( 1, 4 ), array_fill( 0, 4, $gender ) );
	
	$data = array(
	    'total'	=> 0,
	    'totals'	=> $quarters,
	    'rows'	=> array()
	);
	
	foreach ( $rows as $row ) {
	    
	    if ( !array_key_exists( $row['name_dec'], $data['rows'] ) ) {
		
		$data['rows'][$row['name_dec']] = array(
		    'quarters'	=> $quarters,
		    'total'	=> 0
		);
	    }
	    
	    $quarter = ceil( $row['month_registration'] / 3 );
	    $gender = strtolower( trim( $row['gender'] ) );
	    
	    $data['total']++;
	    $data['totals'][$quarter][$gender]++;
	    
	    $data['rows'][$row['name_dec']]['total']++;
	    $data['rows'][$row['name_dec']]['quarters'][$quarter][$gender]++;
	}
	
	$column = array(
	    'series' => array(),
	    'labels' => array(),
	    'names'  => array()
	);

	$quarters = array(
	    1 => array(),
	    2 => array(),
	    3 => array(),
	    4 => array(),
	);

	foreach ( $data['rows'] as $ceop => $row ) {

	    $column['labels'][] = $ceop;
	    foreach ( $row['quarters'] as $q => $value )
		$quarters[$q][] = $value['mane'] + $value['feto'];
	}

	$column['series'] = $quarters;
	$column['names'] = array( 1 => 'QTR1', 'QTR2', 'QTR3', 'QTR4' );

	$data['graph'][App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'CEOP / Quarter ' . $this->_data['year'] );
	
	return $data;
    }
    
    /**
     * 
     * @return array
     */
    public function ageGroupQuarterReport()
    {
	$rows = $this->getFilteredRows( $this->_data );
	
	$genders = array(
	    'mane'   => 0,
	    'feto' => 0
	);
	
	$ages = array(
	    'total'	=> 0,
	    'genders'	=> $genders
	);
	
	$quarter = array(
	    'mane'	    => 0,
	    'feto'	    => 0,
	    'ages'	    => array(
		'15 - 24'   => $ages,
		'25 - 39'   => $ages,
		'40 - 54'   => $ages,
		'55+'	    => $ages
	    )
	);
	
	$data = array(
		    'total'	    => 0,
		    'quarters'	    => array(),
		    'name_quarters' => array_keys( $quarter['ages'] ),
		    'subtotal'	    => array()
		);
	
	for ( $i = 1; $i <= 4; $i++ ) {
	 
	    $data['quarters'][$i] = $quarter;
	    $data['subtotal'][$i] = $genders;
	}
	
	foreach ( $rows as $row ) {
	    
	    $quarter = ceil( $row['month_registration'] / 3 );
	    $gender = strtolower( trim( $row['gender'] ) );
	    
	    $age = null;
	    switch ( $row['age'] ) {
		case $row['age'] >= 15 && $row['age'] <= 24:
			$age = '15 - 24';
		    break;
		case $row['age'] >= 25 && $row['age'] <= 39:
			$age = '25 - 39';
		    break;
		case $row['age'] >= 40 && $row['age'] <= 54:
			$age = '40 - 54';
		    break;
		case $row['age'] >= 55:
			$age = '55+';
		    break;
	    }
	    
	    if ( empty( $age ) ) continue;
	    
	    $data['quarters'][$quarter]['ages'][$age]['genders'][$gender]++;
	    $data['quarters'][$quarter]['ages'][$age]['total']++;
	    $data['quarters'][$quarter][$gender]++;
	    $data['subtotal'][$quarter][$gender]++;
	    $data['total']++;
	}
	
	$column = array(
	    'series' => array(),
	    'labels' => $data['name_quarters'],
	    'names'  => array()
	);

	$quarters = array(
	    1 => array(),
	    2 => array(),
	    3 => array(),
	    4 => array(),
	);

	foreach ( $data['name_quarters'] as $key => $quarter ) {
	    $key++;
	    foreach ( $data['quarters'][$key]['ages'] as $age => $total )
		$quarters[$key][] = $total['total'];
	}

	$column['series'] = $quarters;
	$column['names'] = array( 1 => 'QTR1', 'QTR2', 'QTR3', 'QTR4' );
	
	$data['graph'][App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Grupu Idade / Quarter ' . $this->_data['year'] );
	
	return $data;
    }
    
    /**
     * 
     * @return array
     */
    public function schoolQuarterReport()
    {
	$rows = $this->getFilteredRows( $this->_data );
	
	$gender = array(
	    'mane'  =>	0,
	    'feto'  =>	0
	);
	
	$quarters = array_combine( range( 1, 4 ), array_fill( 0, 4, $gender ) );
	
	$data = array(
	    'total'	=> 0,
	    'totals'	=> $quarters,
	    'rows'	=> array()
	);
	
	foreach ( $rows as $row ) {
	    
	    $school = empty( $row['max_level_scholarity'] ) ? 'LA IHA' : $row['max_level_scholarity'];
	    if ( !array_key_exists( $school, $data['rows'] ) ) {
		
		$data['rows'][$school] = array(
		    'quarters'	=> $quarters,
		    'total'	=> 0
		);
	    }
	    
	    $quarter = ceil( $row['month_registration'] / 3 );
	    $gender = strtolower( trim( $row['gender'] ) );
	    
	    $data['total']++;
	    $data['totals'][$quarter][$gender]++;
	    
	    $data['rows'][$school]['total']++;
	    $data['rows'][$school]['quarters'][$quarter][$gender]++;
	}
	
	$column = array(
	    'series' => array(),
	    'labels' => array(),
	    'names'  => array()
	);

	$quarters = array(
	    1 => array(),
	    2 => array(),
	    3 => array(),
	    4 => array(),
	);

	foreach ( $data['rows'] as $level => $row ) {

	    $column['labels'][] = $level;
	    foreach ( $row['quarters'] as $q => $value )
		$quarters[$q][] = $value['mane'] + $value['feto'];
	}

	$column['series'] = $quarters;
	$column['names'] = array( 1 => 'QTR1', 'QTR2', 'QTR3', 'QTR4' );

	$data['graph'][App_General_String::randomHash()] = App_Util_Chart::columnChart( $column, 'Nivel Edukasaun / Tinan ' . $this->_data['year'] );
	
	return $data;
    }
	
	/**
	 * 
	 * @return array
	 */
	public function kasuAkonsellamentuTuirKonselleiruReport()
	{
	    $dbAppointment = App_Model_DbTable_Factory::get('Appointment');
	    $dbActionPlan  = App_Model_DbTable_Factory::get('ActionPlan');
	    $dbPerData     = App_Model_DbTable_Factory::get('PerData');
	    $dbDec         = App_Model_DbTable_Factory::get('Dec');
	    $dbSysUser     = App_Model_DbTable_Factory::get('SysUser');
	    
	    $subSelect = $dbSysUser->select()
            ->from(
                $dbSysUser->__toString(),
                array('name')
            )
            ->where('SysUser.id_sysuser = Appointment.fk_id_counselor');
	    
	    $select = $dbAppointment->select()
    	    ->setIntegrityCheck(false)
    	    ->from(
        		$dbAppointment->__toString(),
        		array(
        		    'fk_id_counselor', 
        		    'konselleiru' => new Zend_Db_Expr('(' . $subSelect . ')')
	           )
    	    )
    	    ->join(
                $dbDec->__toString(),
    	        'Dec.id_dec = Appointment.fk_id_dec',
    	        array('id_dec', 'name_dec')
            )
    	    ->join(
        		$dbActionPlan->__toString(),
        		'Action_Plan.id_action_plan = Appointment.fk_id_action_plan',
        		array('statuto' => new Zend_Db_Expr("CASE Action_Plan.active WHEN 1 THEN 'LOKE' ELSE 'TAKA' END"))
    	    )
    	    ->join(
        		$dbPerData->__toString(),
        		'PerData.id_perdata = Appointment.fk_id_perdata',
        		array(
        		    'naran_kliente' => new Zend_Db_Expr("CONCAT(PerData.first_name, ' ', IF(PerData.medium_name, CONCAT(PerData.medium_name, ' '), ''), PerData.last_name)"),
        		    'kartaun_evidensia' => new Zend_Db_Expr("CONCAT(PerData.num_district, '-', PerData.num_subdistrict, '-', PerData.num_servicecode, '-', PerData.num_year, '-', PerData.num_sequence)"),
	           )
    	    )
            ->where("DATE(Action_Plan.date_insert) >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'])
            ->where("DATE(Action_Plan.date_insert) <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish']);
	    
	    if (!empty($this->_data['fk_id_dec'])) {
	       $select->where('Dec.id_dec IN(?)', $this->_data['fk_id_dec']);
	    }
	    
	    if (!empty($this->_data['fk_id_counselor'])) {
	       $select->where('Appointment.fk_id_counselor IN(?)', $this->_data['fk_id_counselor']);
	    }
	    
	    $dbBarrierIntervention = App_Model_DbTable_Factory::get('BarrierIntervention');
	    $dbActionPlanBarrier = App_Model_DbTable_Factory::get('ActionPlanBarrier');
	    
	    $subSelect = $dbBarrierIntervention->select()
            ->from(
                $dbBarrierIntervention->__toString(),
                array(new Zend_Db_Expr('COUNT(1)'))
            )
            ->join(
                $dbActionPlanBarrier->__toString(),
                'Action_Plan_Barrier.fk_id_barrier_intervention = Barrier_Intervention.id_barrier_intervention',
                array()
            )
            ->where('Action_Plan_Barrier.fk_id_action_plan = Action_Plan.id_action_plan');
	    
	    $select->columns(array(
	        'intervensaun_identifika' => new Zend_Db_Expr('(' . $subSelect . ')'),
	        'intervensaun_kompletu' => new Zend_Db_Expr('(' . $subSelect->where('Action_Plan_Barrier.status = ?', 'C') . ')'),
	        'intervensaun_mati' => new Zend_Db_Expr('(' . $subSelect->where('Action_Plan_Barrier.status <> ?', 'C') . ')'),
	    ));
	    
	    $rows = $dbAppointment->fetchAll($select);
	    
	    if (0 == $rows->count()) {
	        return array('item' => null);
	    }
	    
	    $ceop = array();
	    $konselleiru = array();
	    $data = array();
	    $count = array();
	    
	    foreach ($rows as $row) {
	        
	        $ceop[$row->id_dec] = $row->name_dec;
	        
	        $konselleiru[$row->id_dec][$row->fk_id_counselor] = $row->konselleiru;
	        
	        $data[$row->id_dec][$row->fk_id_counselor][] = array(
	        	'naran_kliente' => $row->naran_kliente,
	            'kartaun_evidensia' => $row->kartaun_evidensia,
	            'statuto' => $row->statuto,
	            'intervensaun_identifika' => $row->intervensaun_identifika,
	            'intervensaun_kompletu' => $row->intervensaun_kompletu,
	            'intervensaun_mati' => $row->intervensaun_mati,
	        );
	        
	        //Contador
	        if (empty($count[$row->id_dec]['intervensaun_identifika'])) {
	            $count[$row->id_dec]['intervensaun_identifika'] = 0;
	        }
	        
	        if (empty($count[$row->id_dec]['intervensaun_kompletu'])) {
	        	$count[$row->id_dec]['intervensaun_kompletu'] = 0;
	        }
	        
	        if (empty($count[$row->id_dec]['intervensaun_mati'])) {
	        	$count[$row->id_dec]['intervensaun_mati'] = 0;
	        }
	        
	        $count[$row->id_dec]['intervensaun_identifika'] += $row->intervensaun_identifika;
	        $count[$row->id_dec]['intervensaun_kompletu'] += $row->intervensaun_kompletu;
	        $count[$row->id_dec]['intervensaun_mati'] += $row->intervensaun_mati;
	        
	    }
	    	    	    
	    return array(
	        'item' => array(
                'ceop' => $ceop,
	            'konselleiru' => $konselleiru,
	            'data' => $data,
	            'count' => $count,
            )
	    );
	}
	
	/**
	 * 
	 * @return array
	 */
	public function audiensiaAkonsellamentuTuirKonselleiruReport()
	{
	    $dbAppointment = App_Model_DbTable_Factory::get('Appointment');
	    $dbActionPlan  = App_Model_DbTable_Factory::get('ActionPlan');
	    $dbPerData     = App_Model_DbTable_Factory::get('PerData');
	    $dbDec         = App_Model_DbTable_Factory::get('Dec');
	    $dbSysUser     = App_Model_DbTable_Factory::get('SysUser');
	     
	    $subSelect = $dbSysUser->select()
    	    ->from(
        		$dbSysUser->__toString(),
        		array('name')
    	    )
    	    ->where('SysUser.id_sysuser = Appointment.fk_id_counselor');
	     
	    $select = $dbAppointment->select()
    	    ->setIntegrityCheck(false)
    	    ->from(
	    		$dbAppointment->__toString(),
	    		array(
    				'fk_id_counselor',
	    		    'date_appointment' => new Zend_Db_Expr("DATE_FORMAT(Appointment.date_appointment, '%Y-%m-%d %H:%i:%s')"),
	    		    'appointment_filled',
	    		    'kliente_mai' => new Zend_Db_Expr("CASE Appointment.appointment_filled WHEN 1 THEN 'SIN' ELSE 'LAE' END"),
    				'konselleiru' => new Zend_Db_Expr('(' . $subSelect . ')'),
	    		)
    	    )
    	    ->join(
	    		$dbDec->__toString(),
	    		'Dec.id_dec = Appointment.fk_id_dec',
	    		array('id_dec', 'name_dec')
    	    )
    	    ->join(
	    		$dbActionPlan->__toString(),
	    		'Action_Plan.id_action_plan = Appointment.fk_id_action_plan',
	    		array()
    	    )
    	    ->join(
	    		$dbPerData->__toString(),
	    		'PerData.id_perdata = Appointment.fk_id_perdata',
	    		array(
    				'naran_kliente' => new Zend_Db_Expr("CONCAT(PerData.first_name, ' ', IF(PerData.medium_name, CONCAT(PerData.medium_name, ' '), ''), PerData.last_name)"),
    				'kartaun_evidensia' => new Zend_Db_Expr("CONCAT(PerData.num_district, '-', PerData.num_subdistrict, '-', PerData.num_servicecode, '-', PerData.num_year, '-', PerData.num_sequence)"),
	    		)
    	    )
    	    ->where("DATE(Action_Plan.date_insert) >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'])
    	    ->where("DATE(Action_Plan.date_insert) <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish']);
	     
	    if (!empty($this->_data['fk_id_dec'])) {
	    	$select->where('Dec.id_dec IN(?)', $this->_data['fk_id_dec']);
	    }
	     
	    if (!empty($this->_data['fk_id_counselor'])) {
	    	$select->where('Appointment.fk_id_counselor IN(?)', $this->_data['fk_id_counselor']);
	    }
	     
	    $rows = $dbAppointment->fetchAll($select);
	     
	    if (0 == $rows->count()) {
	    	return array('item' => null);
	    }
	    
	    $ceop = array();
	    $konselleiru = array();
	    $data = array();

        $locale = new Zend_Locale('pt_BR.UTF-8');
	    
	    foreach ($rows as $row) {
	    	 
	    	$ceop[$row->id_dec] = $row->name_dec;
	    	 
	    	$konselleiru[$row->id_dec][$row->fk_id_counselor] = $row->konselleiru;
	    	
	    	$dateAppointment = new Zend_Date($row->date_appointment, 'yyyy-MM-dd HH:mm:ss', $locale);
	    	
	    	$data[$row->id_dec][$row->fk_id_counselor][] = array(
	    	    'data' => $dateAppointment->get('EEEE dd/MM/yyyy'),
	    	    'oras' => $dateAppointment->get('HH:mm'),
    			'naran_kliente' => $row->naran_kliente,
    			'kartaun_evidensia' => $row->kartaun_evidensia,
	    	    'kliente_mai' => $row->kliente_mai,
	    	);
	    	 
	    }
	     
	    return array(
    		'item' => array(
				'ceop' => $ceop,
				'konselleiru' => $konselleiru,
				'data' => $data,
    		)
	    );
	}
	
	/**
	 *
	 * @return array
	 */
	public function numeruPlanuTuirAsaunCeopReport()
	{
		$dbAppointment = App_Model_DbTable_Factory::get('Appointment');
		$dbSysUser     = App_Model_DbTable_Factory::get('SysUser');
		$dbDec         = App_Model_DbTable_Factory::get('Dec');
		$dbActionPlan  = App_Model_DbTable_Factory::get('ActionPlan');
	
		$subSelect = $dbActionPlan->select()
            ->from(
                array('ap' => $dbActionPlan),
                array(new Zend_Db_Expr('COUNT(1)'))
            )
            ->where('ap.fk_id_counselor = Action_Plan.fk_id_counselor')
            ->where('ap.active = ?', 0);
		
		$select = $dbAppointment->select()
    		->setIntegrityCheck(false)
    		->from(
				$dbAppointment->__toString(),
				array()
    		)
    		->join(
                $dbSysUser->__toString(),
    		    'SysUser.id_sysuser = Appointment.fk_id_counselor',
    		    array('konselleiru' => 'name')
            )
    		->join(
				$dbDec->__toString(),
				'Dec.id_dec = Appointment.fk_id_dec',
				array('id_dec', 'name_dec')
    		)
    		->join(
				$dbActionPlan->__toString(),
				'Action_Plan.id_action_plan = Appointment.fk_id_action_plan',
				array(
                    'rejistu' => new Zend_Db_Expr('COUNT(Action_Plan.fk_id_counselor)'),
				    'remata' => new Zend_Db_Expr('(' . $subSelect . ')'),
                )
    		)
    		->where("DATE(Action_Plan.date_insert) >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'])
    		->where("DATE(Action_Plan.date_insert) <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish']);
		
		if (!empty($this->_data['fk_id_dec'])) {
			$select->where('Dec.id_dec IN(?)', $this->_data['fk_id_dec']);
		}
		
		$select->group(array('Action_Plan.fk_id_counselor'));
			
		$rows = $dbAppointment->fetchAll($select);
	
		if (0 == $rows->count()) {
			return array('item' => null);
		}
		 
		$ceop = array();
		$data = array();
		$count = array();
	
		foreach ($rows as $row) {
			 
			$ceop[$row->id_dec] = $row->name_dec;
			 
			$data[$row->id_dec][] = array(
			    'konselleiru' => $row->konselleiru,
				'rejistu' => $row->rejistu,
				'remata' => $row->remata,
			);
			
			//Contador
	        if (empty($count[$row->id_dec]['rejistu'])) {
	            $count[$row->id_dec]['rejistu'] = 0;
	        }
	        
	        if (empty($count[$row->id_dec]['remata'])) {
	        	$count[$row->id_dec]['remata'] = 0;
	        }
	        
	        $count[$row->id_dec]['rejistu'] += $row->rejistu;
	        $count[$row->id_dec]['remata'] += $row->remata;
			 
		}
	
		return array(
			'item' => array(
				'ceop' => $ceop,
				'data' => $data,
			    'count' => $count,
			)
		);
	}
}