<?php

class Report_Model_Mapper_StudentClass extends App_Model_Mapper_Abstract
{
    
    /**
     *
     * @var array
     */
    protected $_graphs = array();
    
    /**
     *
     * @return array
     */
    public function areaReport()
    {
        $rows = $this->selectGraduated();
    
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
        foreach ($rows as $row) {
            $gender = trim($row['gender']);
            $area = trim($row['scholarity_area']);
        
            if (!array_key_exists($area, $areaScholarity)) {
                $areaScholarity[$area] = $schoolData;
            }
        
            $areaScholarity[$area][$gender]++;
            $areaScholarity[$area]['total']++;
        
            $data['total']++;
            $data[$gender]++;
        }
    
        foreach ($areaScholarity as $area => $row) {
            $areaScholarity[$area]['porcent'] = round((100 * $row['total']) / $data['total'], 2);
        }
    
        $data['rows'] = $areaScholarity;
    
        if (!empty($data['rows'])) {
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
            $view = Zend_Layout::getMvcInstance()->getView();
        
            foreach ($data['rows'] as $area => $row) {
                $area = App_General_String::addBreakLine($view->truncate(ucfirst(strtolower($area)), 20), 1);
        
                $pie['series'][] = $row['total'];
                $pie['labels'][] = $area;
        
                $man[] = $row['MANE'];
                $woman[] = $row['FETO'];
                $column['labels'][] = $area;
            }
        
            $column['series'] = array( $man, $woman );
            $column['names'] = array( 'MANE', 'FETO' );
    
            $scaleConfig = array( 'LabelRotation' => 90, 'XMargin' => 20 );
            $data['graph'][App_General_String::randomHash()] = App_Util_Chart::columnChart($column, 'Graduadu liu husi Area / Seksu', $scaleConfig);
        }
    
        return $data;
    }
    
    /**
     *
     * @return array
     */
    public function courseReport()
    {
        $rows = $this->selectGraduated();
    
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
        foreach ($rows as $row) {
            $gender = trim($row['gender']);
            $course = trim($row['scholarity']);
        
            if (!array_key_exists($course, $courses)) {
                $courses[$course] = $schoolData;
            }
        
            $courses[$course][$gender]++;
            $courses[$course]['total']++;
        
            $data['total']++;
            $data[$gender]++;
        }
    
        foreach ($courses as $course => $row) {
            $courses[$course]['porcent'] = round((100 * $row['total']) / $data['total'], 2);
        }
    
        $data['rows'] = $courses;
        $data['graph'] = array();
    
        return $data;
    }
    
    /**
     *
     * @return array
     */
    public function schoolReport()
    {
        $rows = $this->selectGraduated();
    
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
    
        $schoolLevels = array();
        foreach ($rows as $row) {
            $gender = trim($row['gender']);
            $schoolLevel = empty($row['max_level_scholarity']) ? 'LA IHA' : $row['max_level_scholarity'];
        
            if (!array_key_exists($schoolLevel, $schoolLevels)) {
                $schoolLevels[$schoolLevel] = $schoolData;
            }
        
            $schoolLevels[$schoolLevel][$gender]++;
            $schoolLevels[$schoolLevel]['total']++;
        
            $data['total']++;
            $data[$gender]++;
        }
    
        foreach ($schoolLevels as $schoolLevel => $row) {
            $schoolLevels[$schoolLevel]['porcent'] = round((100 * $row['total']) / $data['total'], 2);
        }
    
        $data['rows'] = $schoolLevels;
        $data['graph'] = array();
    
        return $data;
    }
    
    /**
    *
    * @return array
    */
    public function districtReport()
    {
        $rows = $this->selectGraduated();
    
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
    
        $districts = array();
        foreach ($rows as $row) {
            $gender = trim($row['gender']);
            $district = $row['District'];
        
            if (!array_key_exists($district, $districts)) {
                $districts[$district] = $schoolData;
            }
        
            $districts[$district][$gender]++;
            $districts[$district]['total']++;
        
            $data['total']++;
            $data[$gender]++;
        }
    
        foreach ($districts as $district => $row) {
            $districts[$district]['porcent'] = round((100 * $row['total']) / $data['total'], 2);
        }
    
        $data['rows'] = $districts;
        $data['graph'] = array();
    
        return $data;
    }
    
    /**
     *
     * @return array
     */
    public function ageGroupReport()
    {
        $rows = $this->selectGraduated();
    
        $data = array(
            'rows'		    => array(),
            'total_man'		    => 0,
            'total_man_porcent'	    => 0,
            'total_woman'	    => 0,
            'total_woman_porcent'   => 0,
            'total'		    => $rows->count()
        );
    
        $ages = array();
        foreach ($rows as $row) {
            switch ($row['age']) {
        case $row['age'] < 15:
            @$ages['< 15'][ trim($row['gender']) ]++;
            break;
        case $row['age'] >= 15 && $row['age'] <= 24:
            @$ages['15 - 24'][ trim($row['gender']) ]++;
            break;
        case $row['age'] >= 25 && $row['age'] <= 39:
            @$ages['25 - 39'][ trim($row['gender']) ]++;
            break;
        case $row['age'] >= 40 && $row['age'] <= 54:
            @$ages['40 - 54'][ trim($row['gender']) ]++;
            break;
        case $row['age'] >= 55:
            @$ages['55+'][ trim($row['gender']) ]++;
            break;
        }
        }
    
        ksort($ages);
    
        foreach ($ages as $key => $row) {
            $row['man'] = empty($row['MANE']) ? 0 : (int)$row['MANE'];
            $row['woman'] = empty($row['FETO']) ? 0 : (int)$row['FETO'];
            $total = $row['man'] + $row['woman'];
            $percentWoman = round((100 * $row['woman']) / $total, 2);
            $percentMan = round((100 * $row['man']) / $total, 2);
        
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
    
        foreach ($data['rows'] as $key => $value) {
            $percent = round((100 * $value['total']) / $data['total'], 2);
            $data['rows'][$key]['total_porcent'] = $percent;
        }
    
        if (!empty($data['rows'])) {
            $data['total_man_porcent'] = round((100 * $data['total_man']) / $data['total'], 2);
            $data['total_woman_porcent'] = round((100 * $data['total_woman']) / $data['total'], 2);
        }
    
        $data['graph'] = array();
    
        return $data;
    }
    
    /**
     *
     * @return array
     */
    public function listGraduateReport()
    {
        $mapperClient = new Client_Model_Mapper_Client();
        $select = $mapperClient->selectClient();
    
        $dbPerScholarity = App_Model_DbTable_Factory::get('PerScholarity');
        $dbPerDataPerScholarity = App_Model_DbTable_Factory::get('PerScholarityHasPerTypeScholarity');
    
        $select->join(
            array( 'sp' => $dbPerDataPerScholarity ),
            'sp.fk_id_perdata = c.id_perdata',
            array()
        )
        ->join(
            array( 's' => $dbPerScholarity ),
            's.id_perscholarity = sp.fk_id_perscholarity',
            array()
        );
    
        $date = new Zend_Date();
        $filters = $this->_data;
    
        if (!empty($filters['date_start'])) {
            $select->where('sp.finish_date >= ?', $date->set($filters['date_start'])->toString('yyyy-MM-dd'));
        }
    
        if (!empty($filters['date_finish'])) {
            $select->where('sp.finish_date <= ?', $date->set($filters['date_finish'])->toString('yyyy-MM-dd'));
        }
    
        if (!empty($filters['fk_id_dec'])) {
            $select->where('c.fk_id_dec = ?', $filters['fk_id_dec']);
        }
    
        if (!empty($filters['fk_id_fefpeduinstitution'])) {
            $select->where('sp.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution']);
        }
    
        if (!empty($filters['fk_id_scholarity_area'])) {
            $select->where('s.fk_id_scholarity_area = ?', $filters['fk_id_scholarity_area']);
        }
    
        if (!empty($filters['fk_id_perscholarity'])) {
            $select->where('sp.fk_id_perscholarity = ?', $filters['fk_id_perscholarity']);
        }
        
        if (!empty($filters['gender'])) {
            $select->where('c.gender = ?', $filters['gender']);
        }
    
        $select->group(array( 'id_perdata' ))
			->order(array( 'first_name', 'medium_name', 'last_name' ));
		
		if (!empty($filters['page'])) {
			list($initial, $final) = explode('-', $filters['page']);
			$select->limit(200, $initial);
		} else {
			$select->limit(1000);
		}
    
		$rows = $dbPerScholarity->fetchAll($select);
    
        return array( 'rows' => $rows );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function selectGraduated()
    {
        $mapperClient = new Client_Model_Mapper_Client();
        $select = $mapperClient->selectClient();
    
        $dbStudents = App_Model_DbTable_Factory::get('FEFEPStudentClass_has_PerData');
        $dbStudentClass = App_Model_DbTable_Factory::get('FEFPStudentClass');
        $dbScholarity = App_Model_DbTable_Factory::get('PerScholarity');
        $dbAreaScholarity = App_Model_DbTable_Factory::get('ScholarityArea');
        $dbDistrict = App_Model_DbTable_Factory::get('AddDistrict');
        $dbEduInstitution = App_Model_DbTable_Factory::get('FefpEduInstitution');
    
        $select->join(
            array( 'spd' => $dbStudents ),
            'spd.fk_id_perdata = c.id_perdata',
            array()
        )
        ->join(
            array( 'sc' => $dbStudentClass ),
            'spd.fk_id_fefpstudentclass = sc.id_fefpstudentclass',
            array()
        )
        ->join(
            array( 's' => $dbScholarity ),
            'sc.fk_id_perscholarity = s.id_perscholarity',
            array( 'scholarity' )
        )
        ->join(
            array( 'sa' => $dbAreaScholarity ),
            's.fk_id_scholarity_area = sa.id_scholarity_area',
            array( 'scholarity_area' )
        )
        ->join(
            array( 'ds' => $dbDistrict ),
            'ds.acronym = c.num_district',
            array( 'District' )
        )
        ->join(
            array( 'ei' => $dbEduInstitution ),
            'ei.id_fefpeduinstitution = sc.fk_id_fefpeduinstitution',
            array( 'institution' )
        )
        ->where('spd.status = ?', StudentClass_Model_Mapper_StudentClass::GRADUATED);
    
        $date = new Zend_Date();
        $filters = $this->_data;
    
        if (!empty($filters['date_start'])) {
            $select->where('sc.real_finish_date >= ?', $date->set($filters['date_start'])->toString('yyyy-MM-dd'));
        }
    
        if (!empty($filters['date_finish'])) {
            $select->where('sc.real_finish_date <= ?', $date->set($filters['date_finish'])->toString('yyyy-MM-dd'));
        }
    
        if (!empty($filters['fk_id_dec'])) {
            $select->where('sc.fk_id_dec = ?', $filters['fk_id_dec']);
        }
    
        if (!empty($filters['fk_id_fefpeduinstitution'])) {
            $select->where('sc.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution']);
        }
    
        if (!empty($filters['fk_id_scholarity_area'])) {
            $select->where('s.fk_id_scholarity_area = ?', $filters['fk_id_scholarity_area']);
        }
    
        if (!empty($filters['fk_id_perscholarity'])) {
            $select->where('sc.fk_id_perscholarity = ?', $filters['fk_id_perscholarity']);
        }
    
        return $dbStudentClass->fetchAll($select);
    }
}
