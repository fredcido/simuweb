<?php

class Default_Model_Mapper_Dashboard extends App_Model_Abstract
{

    /**
     *
     * @param array $filters
     * @return array
     */
    public function getDashboards($filters = array())
    {
        $dashboards = array(
        'clients'	    =>  $this->getTotalClients($filters),
        'graduated'	    =>  $this->getGraduated($filters),
        'job-vacancy'   =>  $this->getJobVacancys($filters),
        'find-job'	    =>  $this->getHireds($filters)
    );
    
        return $dashboards;
    }
    
    /**
     *
     * @param array $filters
     * @return int
     */
    public function getTotalClients($filters = array())
    {
        $dbPerData = App_Model_DbTable_Factory::get('PerData');
    
        $select = $dbPerData->select()
                ->from(
                    array( 'c' => $dbPerData ),
                    array( 'total' => new Zend_Db_Expr('COUNT(1)') )
                )
                ->setIntegrityCheck(false);
    
        $date = new Zend_Date();
    
        if (!empty($filters['data_ini'])) {
            $select->where('c.date_registration >= ?', $date->set($filters['data_ini'])->toString('yyyy-MM-dd'));
        }
    
        if (!empty($filters['data_fim'])) {
            $select->where('c.date_registration <= ?', $date->set($filters['data_fim'])->toString('yyyy-MM-dd'));
        }
    
        $row = $dbPerData->fetchRow($select);
        return $row->total;
    }
    
    /**
     *
     * @param array $filters
     * @return int
     */
    public function getGraduated($filters = array())
    {
        $dbStudentClassPerData = App_Model_DbTable_Factory::get('FEFEPStudentClass_has_PerData');
        $dbStudentClass = App_Model_DbTable_Factory::get('FEFPStudentClass');
    
        $select = $dbStudentClassPerData->select()
                ->from(
                array( 'sc' => $dbStudentClassPerData ),
                array( 'total' => new Zend_Db_Expr('COUNT(1)') )
                )
                ->join(
                array( 'c' => $dbStudentClass ),
                'c.id_fefpstudentclass = sc.fk_id_fefpstudentclass',
                array()
                )
                ->setIntegrityCheck(false)
                ->where('sc.status = ?', StudentClass_Model_Mapper_StudentClass::GRADUATED);
    
        $date = new Zend_Date();
    
        if (!empty($filters['data_ini'])) {
            $select->where('c.real_finish_date >= ?', $date->set($filters['data_ini'])->toString('yyyy-MM-dd'));
        }
    
        if (!empty($filters['data_fim'])) {
            $select->where('c.real_finish_date <= ?', $date->set($filters['data_fim'])->toString('yyyy-MM-dd'));
        }
    
        $row = $dbStudentClassPerData->fetchRow($select);
        return $row->total;
    }
    
    /**
     *
     * @param array $filters
     * @return int
     */
    public function getJobVacancys($filters = array())
    {
        $dbJobVacancy = App_Model_DbTable_Factory::get('JOBVacancy');
        $select = $dbJobVacancy->select()
                ->from(
                array( 'jv' => $dbJobVacancy ),
                array( 'total' => new Zend_Db_Expr('SUM(num_position)') )
                )
                ->setIntegrityCheck(false);
    
        $date = new Zend_Date();
    
        if (!empty($filters['data_ini'])) {
            $select->where('jv.registration_date >= ?', $date->set($filters['data_ini'])->toString('yyyy-MM-dd'));
        }
    
        if (!empty($filters['data_fim'])) {
            $select->where('jv.registration_date <= ?', $date->set($filters['data_fim'])->toString('yyyy-MM-dd'));
        }
    
        $row = $dbJobVacancy->fetchRow($select);
        return (int)$row->total;
    }
    
    /**
     *
     * @param array $filters
     * @return int
     */
    public function getHireds($filters = array())
    {
        $dbJobHired = App_Model_DbTable_Factory::get('Hired');
        $select = $dbJobHired->select()
                ->from(
                array( 'jh' => $dbJobHired ),
                array( 'total' => new Zend_Db_Expr('COUNT(1)') )
                )
                ->setIntegrityCheck(false);
    
        $date = new Zend_Date();
    
        if (!empty($filters['data_ini'])) {
            $select->where('jh.result_date >= ?', $date->set($filters['data_ini'])->toString('yyyy-MM-dd'));
        }
    
        if (!empty($filters['data_fim'])) {
            $select->where('jh.result_date <= ?', $date->set($filters['data_fim'])->toString('yyyy-MM-dd'));
        }
    
        $row = $dbJobHired->fetchRow($select);
        return $row->total;
    }
    
    /**
     *
     * @param array $filters
     * @return array
     */
    public function chartClient($filters = array())
    {
        $clients = $this->clientByCeop($filters);
    
        $rows = array();
        foreach ($clients as $client) {
            $rows[$client->name_dec][$client->gender] = $client->total;
        }
    
        $data = array(
        'categories' => array(),
        'men'	 => array(),
        'women'	 => array()
    );
    
        foreach ($rows as $ceop => $total) {
            $data['categories'][] = $ceop;
        
            $data['men'][] = (int)(empty($total['MANE']) ? 0 : $total['MANE']);
            $data['women'][] = (int)(empty($total['FETO']) ? 0 : $total['FETO']);
        }
    
        return $data;
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function clientByCeop($filters = array())
    {
        $dbPerData = App_Model_DbTable_Factory::get('PerData');
        $dbDec = App_Model_DbTable_Factory::get('Dec');
    
        $select = $dbPerData->select()
                ->from(
                array( 'c' => $dbPerData ),
                array( 'total' => new Zend_Db_Expr('COUNT(1)'), 'gender' )
                )
                ->setIntegrityCheck(false)
                ->join(
                array( 'ce' => $dbDec ),
                'ce.id_dec = c.fk_id_dec',
                array( 'name_dec' )
                )
                ->group(array( 'id_dec', 'gender' ))
                ->order(array( 'total' ));
    
        $date = new Zend_Date();
    
        if (!empty($filters['data_ini'])) {
            $select->where('c.date_registration >= ?', $date->set($filters['data_ini'])->toString('yyyy-MM-dd'));
        }
    
        if (!empty($filters['data_fim'])) {
            $select->where('c.date_registration <= ?', $date->set($filters['data_fim'])->toString('yyyy-MM-dd'));
        }
    
        return $dbPerData->fetchAll($select);
    }
    
    /**
     *
     * @param array $filters
     * @return array
     */
    public function chartVacancy($filters = array())
    {
        $vacancies = $this->vacancyByCeop($filters);
    
        $data = array(
        'categories' => array(),
        'data'	 => array()
    );
    
        foreach ($vacancies as $vacancy) {
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
    public function vacancyByCeop($filters = array())
    {
        $dbJobVacancy = App_Model_DbTable_Factory::get('JOBVacancy');
        $dbDec = App_Model_DbTable_Factory::get('Dec');
    
        $select = $dbJobVacancy->select()
                ->from(
                array( 'jv' => $dbJobVacancy ),
                array( 'total' => new Zend_Db_Expr('SUM(num_position)') )
                )
                ->setIntegrityCheck(false)
                ->join(
                array( 'ce' => $dbDec ),
                'ce.id_dec = jv.fk_id_dec',
                array( 'name_dec' )
                )
                ->group(array( 'id_dec' ))
                ->order(array( 'total' ));
    
        $date = new Zend_Date();
    
        if (!empty($filters['data_ini'])) {
            $select->where('jv.registration_date >= ?', $date->set($filters['data_ini'])->toString('yyyy-MM-dd'));
        }
    
        if (!empty($filters['data_fim'])) {
            $select->where('jv.registration_date <= ?', $date->set($filters['data_fim'])->toString('yyyy-MM-dd'));
        }
    
        return $dbJobVacancy->fetchAll($select);
    }
    
    /**
     *
     * @param array $filters
     * @return array
     */
    public function chartOccupation($filters = array())
    {
        $vacancies = $this->vacancyByOccupation($filters);
    
        $data = array(
        'data'	 => array()
    );
    
        $view = Zend_Layout::getMvcInstance()->getView();
    
        foreach ($vacancies as $vacancy) {
            $data['categories'][] = $view->truncate(ucfirst(strtolower($vacancy->ocupation_name_timor)));
            $data['data'][] = (int)$vacancy->total;
        }
    
        return $data;
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function vacancyByOccupation($filters = array())
    {
        $dbJobVacancy = App_Model_DbTable_Factory::get('JOBVacancy');
        $dbProfOccupation = App_Model_DbTable_Factory::get('PROFOcupationTimor');
    
        $select = $dbJobVacancy->select()
                ->from(
                array( 'jv' => $dbJobVacancy ),
                array( 'total' => new Zend_Db_Expr('SUM(num_position)') )
                )
                ->setIntegrityCheck(false)
                ->join(
                array( 'po' => $dbProfOccupation ),
                'po.id_profocupationtimor = jv.fk_id_profocupation',
                array( 'ocupation_name_timor' )
                )
                ->group(array( 'id_profocupationtimor' ))
                ->limit(10)
                ->order(array( 'total DESC' ));
    
        $date = new Zend_Date();
    
        if (!empty($filters['data_ini'])) {
            $select->where('jv.registration_date >= ?', $date->set($filters['data_ini'])->toString('yyyy-MM-dd'));
        }
    
        if (!empty($filters['data_fim'])) {
            $select->where('jv.registration_date <= ?', $date->set($filters['data_fim'])->toString('yyyy-MM-dd'));
        }
    
        return $dbJobVacancy->fetchAll($select);
    }
    
    /**
     *
     * @param array $filters
     * @return array
     */
    public function chartGraduated($filters = array())
    {
        $courses = $this->graduatedByCourse($filters);
    
        $data = array(
        'data'   => array()
    );
    
        $view = Zend_Layout::getMvcInstance()->getView();
    
        foreach ($courses as $course) {
            $data['data'][] = array( $view->truncate(ucfirst(strtolower($course->scholarity))) , (int)$course->total );
        }
    
        return $data;
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function graduatedByCourse($filters = array())
    {
        $dbStudentClass = App_Model_DbTable_Factory::get('FEFPStudentClass');
        $dbStudentClassScholarity = App_Model_DbTable_Factory::get('FEFPStudentClass_has_PerScholarity');
        $dbPerScholarity = App_Model_DbTable_Factory::get('PerScholarity');
        $dbStudentClassPerData = App_Model_DbTable_Factory::get('FEFEPStudentClass_has_PerData');
    
        $select = $dbStudentClass->select()
                ->from(
                array( 'sc' => $dbStudentClass ),
                array( 'total' => new Zend_Db_Expr('COUNT(1)') )
                )
                ->setIntegrityCheck(false)
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
                ->where('sc.active = ?', 0)
                ->where('scp.status = ?', StudentClass_Model_Mapper_StudentClass::GRADUATED)
                ->group(array( 'id_perscholarity' ))
                ->limit(10)
                ->order(array( 'total DESC' ));
    
        $date = new Zend_Date();
    
        if (!empty($filters['data_ini'])) {
            $select->where('sc.real_finish_date >= ?', $date->set($filters['data_ini'])->toString('yyyy-MM-dd'));
        }
    
        if (!empty($filters['data_fim'])) {
            $select->where('sc.real_finish_date <= ?', $date->set($filters['data_fim'])->toString('yyyy-MM-dd'));
        }
    
        return $dbStudentClass->fetchAll($select);
    }
    
    public function listUserActivities($user, $onlyToday = true)
    {
        $dbAppointment = App_Model_DbTable_Factory::get('Appointment');
        $AppointmentObjective = App_Model_DbTable_Factory::get('AppointmentObjective');
        $AppointmentHasObjective = App_Model_DbTable_Factory::get('AppointmentHasObjective');

        $today = new Zend_Date();
        $tomorrow = clone $today;
        $tomorrow->addDay(1);

        $firstDate = new Zend_Date();
        $firstDate->set(1, Zend_Date::MONTH);
        $firstDate->set(1, Zend_Date::DAY);

        $lastDate = clone $firstDate;
        $lastDate->set(12, Zend_Date::MONTH);
        $lastDate->set(31, Zend_Date::DAY);

        $selectAppointment = $dbAppointment->select()
            ->from(
                array('ap' => $dbAppointment),
                array(
                    'id'  => 'fk_id_action_plan',
                    'type' => new Zend_Db_Expr('"A"'),
                    'title' => 'CONCAT("Audiensia: ", GROUP_CONCAT(apo.objective_desc))',
                    'description' => 'appointment_desc',
                    'date' => 'ap.date_appointment' // new Zend_Db_Expr('DATE_FORMAT(ap.date_appointment, "%d/%m/%Y %H:%i")'),
                )
            )
            ->setIntegrityCheck(false)
            ->join(
                array('apho' => $AppointmentHasObjective),
                'apho.fk_id_appointment = ap.id_appointment',
                array()
            )
            ->join(
                array('apo' => $AppointmentObjective),
                'apo.id_appointment_objective = apho.fk_id_appointment_objective',
                array()
            )
            ->where('ap.fk_id_counselor = ?', $user)
            ->group('id_appointment');

        if ($onlyToday) {
            $selectAppointment->where('( DATE(ap.date_appointment) = ?', $today->toString('yyyy-MM-dd'))
                            ->orWhere('DATE(ap.date_appointment) = ? )', $tomorrow->toString('yyyy-MM-dd'));
        } else {
            $selectAppointment->where('DATE(ap.date_appointment) >= ?', $firstDate->toString('yyyy-MM-dd'))
                            ->where('DATE(ap.date_appointment) <= ?', $lastDate->toString('yyyy-MM-dd'));
        }

        $ActionPlanTimeline = App_Model_DbTable_Factory::get('ActionPlanTimeline');
        $ActionPlanBarrier = App_Model_DbTable_Factory::get('ActionPlanBarrier');
        $ActionPlan = App_Model_DbTable_Factory::get('ActionPlan');
        $PerData = App_Model_DbTable_Factory::get('PerData');

        $selectTimeline = $ActionPlanTimeline->select()
            ->from(
                array('tl' => $ActionPlanTimeline),
                array(
                    'id'  => 'ap.id_action_plan',
                    'type' => new Zend_Db_Expr('"T"'),
                    'title' => 'CONCAT("Liña tempu: ", c.first_name, " ", c.last_name)',
                    'description',
                    'date' => 'tl.date_end'// new Zend_Db_Expr('DATE_FORMAT(tl.date_end, "%d/%m/%Y")'),
                )
            )
            ->setIntegrityCheck(false)
            ->join(
                array('ab' => $ActionPlanBarrier),
                'ab.id_action_barrier = tl.fk_id_action_barrier',
                array()
            )
            ->join(
                array('ap' => $ActionPlan),
                'ap.id_action_plan = ab.fk_id_action_plan',
                array()
            )
            ->join(
                array('c' => $PerData),
                'c.id_perdata = ap.fk_id_perdata',
                array()
            )
            ->where('tl.fk_id_sysuser = ?', $user)
            ->group('id_action_plan_timeline');

        if ($onlyToday) {
            $selectTimeline->where('( DATE(tl.date_end) = ?', $today->toString('yyyy-MM-dd'))
                            ->orWhere('DATE(tl.date_end) = ? ', $tomorrow->toString('yyyy-MM-dd'))
                            ->orWhere('tl.date_end IS NULL )');
        } else {
            $selectTimeline->where('DATE(tl.date_start) >= ?', $firstDate->toString('yyyy-MM-dd'))
                            ->where('DATE(tl.date_start) <= ?', $lastDate->toString('yyyy-MM-dd'));
        }

        $selectUnion = $dbAppointment->select()
                            ->union(array($selectAppointment, $selectTimeline))
                            ->setIntegrityCheck(false);

        return $dbAppointment->fetchAll($selectUnion);
    }
}
