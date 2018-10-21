<?php

class Job_Model_Mapper_JobVacancy extends App_Model_Abstract
{

    /**
     *
     * @var Model_DbTable_JOBVacancy
     */
    protected $_dbTable;

    /**
     *
     */
    protected function _getDbTable()
    {
        if (is_null($this->_dbTable)) {
            $this->_dbTable = new Model_DbTable_JOBVacancy();
        }

        return $this->_dbTable;
    }

    /**
     *
     * @return int|bool
     */
    public function saveInformation()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            if (!$this->_validateJobVacancy()) {
                return false;
            }
        
            $date = new Zend_Date();
        
            $this->_data['registration_date'] = $date->set($this->_data['registration_date'])->toString('yyyy-MM-dd');
            $this->_data['open_date'] = $date->set($this->_data['open_date'])->toString('yyyy-MM-dd');
            $this->_data['close_date'] = $date->set($this->_data['close_date'])->toString('yyyy-MM-dd');
        
            if (!empty($this->_data['start_job_date'])) {
                $this->_data['start_job_date'] = $date->set($this->_data['start_job_date'])->toString('yyyy-MM-dd');
            }
        
            if (!empty($this->_data['finish_job_date'])) {
                $this->_data['finish_job_date'] = $date->set($this->_data['finish_job_date'])->toString('yyyy-MM-dd');
            }
        
            $this->_data['start_salary'] = Zend_Locale_Format::getFloat((empty($this->_data['start_salary']) ? 0 : $this->_data['start_salary']), array( 'locale' => 'en_US' ));
            $this->_data['finish_salary'] = Zend_Locale_Format::getFloat((empty($this->_data['finish_salary']) ? 0 : $this->_data['finish_salary']), array( 'locale' => 'en_US' ));
            $this->_data['additional_salary'] = Zend_Locale_Format::getFloat((empty($this->_data['additional_salary']) ? 0 : $this->_data['additional_salary']), array( 'locale' => 'en_US' ));
        
            $this->_data['post'] = '-';
        
            if (empty($this->_data['id_jobvacancy'])) {
                $history = 'REJISTRU VAGA EMPREGU NUMERU: %s - DADOS PRICIPAIS VAGA DE EMPREGO REGISTRADO';
                $this->_data['active'] = 1;
            } else {
                $history = 'ATUALIZADO VAGA EMPREGU NUMERU: %s - DADOS PRICIPAIS VAGA DE EMPREGO ATUALIZADO';
            }
       
            $id = parent::_simpleSave();
        
            $history = sprintf($history, $id);
            $this->_sysAudit($history);
        
            $dbAdapter->commit();
        
            return $id;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @return boolean
     */
    protected function _validateJobVacancy()
    {
        if (!empty($this->_data['id_jobvacancy'])) {
            return true;
        }
    
        $select = $this->_dbTable->select()
                 ->setIntegrityCheck(false)
                 ->where('fk_id_profocupation = ?', $this->_data['fk_id_profocupation'])
                 ->where('fk_id_fefpenterprise = ?', $this->_data['fk_id_fefpenterprise'])
                 ->where('active = ?', 1);
    
        $row = $this->_dbTable->fetchRow($select);
        if (empty($row)) {
            return true;
        }
    
        $this->_message->addMessage('Vaga empregu ho Okupasaun ba Empreza ida ne\'e iha rejistu tiha ona.', App_Message::ERROR);
        $this->addFieldError('fk_id_profocupation')->addFieldError('fk_id_fefpenterprise');
        return false;
    }
    
    /**
     *
     * @return int|bool
     */
    public function saveAddress()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $dbJobLocation = App_Model_DbTable_Factory::get('JOBVacancy_has_Location');
        
            $history = 'ATUALIZADO HELA FATIN VAGA EMPREGU NUMERU: %s - lOCALIZACAO DA VAGA DE EMPREGO ATUALIZADO';
       
            $id = parent::_simpleSave($dbJobLocation, false);
        
            $history = sprintf($history, $this->_data['fk_id_jobvacancy']);
            $this->_sysAudit($history, Job_Form_VacancyAddress::ID);
        
            $dbAdapter->commit();
        
            return $id;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @return int|bool
     */
    public function saveScholarity()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $dbJobScholarity = App_Model_DbTable_Factory::get('JOBVacancy_has_PerScholarity');
        
            $history = 'ATUALIZADO ESCOLARIDADE BA VAGA EMPREGU NUMERU: %s - ESCOLARIDADE DA VAGA DE EMPREGO ATUALIZADO';
       
            $id = parent::_simpleSave($dbJobScholarity, false);
        
            $history = sprintf($history, $this->_data['fk_id_jobvacancy']);
            $this->_sysAudit($history, Job_Form_VacancyScholarity::ID);
        
            $dbAdapter->commit();
        
            return $id;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @return int|bool
     */
    public function saveTraining()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $dbJobTraining = App_Model_DbTable_Factory::get('JOBVacancy_has_Training');
        
            $history = 'ATUALIZA FORMASAUN PROFISIONAL VAGA EMPREGU NUMERU: %s - FORMACAO PROFISSIONAL VAGA DE EMPREGO REGISTRADO';
       
            $id = parent::_simpleSave($dbJobTraining, false);
        
            $history = sprintf($history, $this->_data['fk_id_jobvacancy']);
            $this->_sysAudit($history, Job_Form_VacancyTraining::ID);
        
            $dbAdapter->commit();
        
            return $id;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @return int|bool
     */
    public function saveLanguage()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $dbJobLanguage = App_Model_DbTable_Factory::get('JOBVacancy_has_PerLanguage');
        
            $history = 'ATUALIZADO LIAN FUAN BA VAGA EMPREGU NUMERU: %s - LINGUA REQUERIDA PARA VAGA DE EMPREGO ATUALIZADO';
       
            $id = parent::_simpleSave($dbJobLanguage, false);
        
            $history = sprintf($history, $this->_data['fk_id_jobvacancy']);
            $this->_sysAudit($history, Job_Form_VacancyLanguage::ID);
        
            $dbAdapter->commit();
        
            return $id;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @return int|bool
     */
    public function saveHandicapped()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $dbHandicapped = App_Model_DbTable_Factory::get('Handicapped');
        
            $history = 'ATUALIZA DEFICIENCIA BA VAGA EMPREGU: %s - ATUALIZA DEFICIENCIA HUSI VAGA EMPREGU';
        
            $id = parent::_simpleSave($dbHandicapped, false);
        
            $history = sprintf($history, $this->_data['fk_id_jobvacancy']);
            $this->_sysAudit($history, Job_Form_VacancyHandicapped::ID);
        
            $dbAdapter->commit();
            return $id;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @return int|bool
     */
    public function saveClose()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $dbShortlist = App_Model_DbTable_Factory::get('ShortlistVacancy');
            $dbHired = App_Model_DbTable_Factory::get('Hired');
            $dbVacancy = App_Model_DbTable_Factory::get('JOBVacancy');
            $dbPerData = App_Model_DbTable_Factory::get('PerData');
            $dbPerExperience = App_Model_DbTable_Factory::get('PerExperience');
            $dbPersonHistory = App_Model_DbTable_Factory::get('Person_History');
            $dbActionPlanReferences = App_Model_DbTable_Factory::get('Action_Plan_References');
            $dbActionPlanBarrier = App_Model_DbTable_Factory::get('Action_Plan_Barrier');
            $dbJobCandidates = App_Model_DbTable_Factory::get('JOBVacancy_Candidates');
        
            $dataForm = $this->_data;
        
            // Check if the clients selected are the same quantity defined in the vacancy
            $vacancy = $this->detailVacancy($this->_data['fk_id_jobvacancy']);
            if (count($this->_data['clients']) != $vacancy->num_position) {
                $this->_message->addMessage(sprintf('Tenki seleciona Kliente %s deit ba Kontratasaun!', $vacancy->num_position), App_Message::ERROR);
                return false;
            }
        
            // Close the vacancy
            $this->_data = array(
        'id_jobvacancy'	=> $this->_data['fk_id_jobvacancy'],
        'close_date'    => Zend_Date::now()->toString('yyyy-MM-dd'),
        'active'	=> 0
        );
            parent::_simpleSave($dbVacancy, false);
        
            // Insert the Auditing
            $history = 'TAKA VAGA EMPREGU NUMERU: %s ';
            $history = sprintf($history, $dataForm['fk_id_jobvacancy']);
            $this->_sysAudit($history, Job_Form_VacancyClose::ID);
        
            $now = Zend_Date::now()->toString('yyyy-MM-dd');
        
            $noteMapper = new Default_Model_Mapper_Note();
            $noteModelMapper = new Default_Model_Mapper_NoteModel();
        
            // Insert clients as hired
            foreach ($dataForm['clients'] as $client) {
        
        // Update short list setting client selected
                $whereShortlist = array(
            'fk_id_jobvacancy = ?'  => $dataForm['fk_id_jobvacancy'],
            'fk_id_perdata = ?'	    => $client
        );
                $dbShortlist->update(array( 'selected' => 1 ), $whereShortlist);
        
                // Insert client as hired
                $this->_data = array(
            'fk_id_jobvacancy'  => $dataForm['fk_id_jobvacancy'],
            'fk_id_perdata'	=> $client,
            'result_date'	=> $now
        );
                parent::_simpleSave($dbHired, false);
                $dbPerData->update(array( 'hired' => 1 ), array( 'id_perdata = ?' => $client ));
        
                // Insert experience to client
                $this->_data = array(
            'fk_id_profocupation'   => $vacancy->fk_id_profocupation,
            'fk_id_perdata'	    => $client,
            'enterprise_name'	    => $vacancy->enterprise_name,
            'post'		    => ' ',
            'start_date'	    => empty($vacancy->start_job_date) ? $now : $vacancy->start_job_date,
            'experience_year'	    => 0,
        );
                parent::_simpleSave($dbPerExperience, false);
        
                // Save history to client
                $rowHistory = $dbPersonHistory->createRow();
                $rowHistory->fk_id_perdata = $client;
                $rowHistory->fk_id_jobvacancy = $dataForm['fk_id_jobvacancy'];
                $rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
                $rowHistory->fk_id_dec = $vacancy->fk_id_dec;
                $rowHistory->date_time = Zend_Date::now()->toString('yyyy-MM-dd HH:mm');
                $rowHistory->action = sprintf('KLIENTE IDA NEE HETAN SERBISU TIHA ONA BA VAGA EMPREGO: %s, DATA: %s', $dataForm['fk_id_jobvacancy'], $now);
                $rowHistory->description = '';
                $rowHistory->save();
        
                // Search if the vacancy was referencied by some barrier
                $whereReference = array(
            'fk_id_jobvacancy = ?'  => $dataForm['fk_id_jobvacancy'],
            'fk_id_perdata = ?'	    => $client
        );
        
                $reference = $dbActionPlanReferences->fetchRow($whereReference);
                if (!empty($reference)) {
                    $barrier = $dbActionPlanBarrier->fetchRow(array( 'id_action_barrier = ?' => $reference->fk_id_action_barrier ));
                    $barrier->status = Client_Model_Mapper_Case::BARRIER_COMPLETED;
                    $barrier->date_finish = Zend_Date::now()->toString('yyyy-MM-dd');
                    $barrier->save();
                }
        
                $whereCandidates = array(
            'fk_id_perdata = ?'	   => $client,
            'fk_id_jobvacancy = ?' => $dataForm['fk_id_jobvacancy']
        );
        
                $referer = $dbJobCandidates->fetchRow($whereCandidates);
        
                if (empty($referer->fk_id_sysuser)) {
                    continue;
                }
        
                $dataNote = array(
            'title'   => 'KLIENTE HETAN SERVISU',
            'level'   => 1,
            'message' => $noteModelMapper->geJobService($client, $dataForm['fk_id_jobvacancy']),
            'users'   => array( $referer->fk_id_sysuser )
        );
        
                $noteMapper->setData($dataNote)->saveNote();
            }
       
            $dbAdapter->commit();
            return $dataForm['fk_id_jobvacancy'];
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @return int|bool
     */
    public function saveCancel()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $dbVacancy = App_Model_DbTable_Factory::get('JOBVacancy');
        
            // Cancel vacancy
            $where = $dbAdapter->quoteInto('id_jobvacancy = ?', $this->_data['fk_id_jobvacancy']);
            $dataUpdate = array(
        'active'		=>  2,
        'close_date'		=> Zend_Date::now()->toString('yyyy-MM-dd'),
        'cancel_justification'	=>  $this->_data['cancel_justification']
        );
        
            $dbVacancy->update($dataUpdate, $where);
        
            // Save auditing
            $history = 'KANSELA VAGA EMPREGU: %s - JUSTIFIKASAUN: %s';
        
            $history = sprintf($history, $this->_data['fk_id_jobvacancy'], $this->_data['cancel_justification']);
            $this->_sysAudit($history, Job_Form_VacancyCancel::ID);
        
            $dbAdapter->commit();
            return $this->_data['fk_id_jobvacancy'];
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @return int|bool
     */
    public function deleteAddress()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $dbJobLocation = App_Model_DbTable_Factory::get('JOBVacancy_has_Location');
        
            $where = array( $dbAdapter->quoteInto('id_relationship = ?', $this->_data['id']) );
        
            $dbJobLocation->delete($where);
        
            $history = 'DELETADO HELA FATIN VAGA EMPREGU NUMERU: %s - lOCALIZACAO DA VAGA DE EMPREGO DELETADO';
        
            $history = sprintf($history, $this->_data['id']);
            $this->_sysAudit($history, Job_Form_VacancyAddress::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS);
        
            $dbAdapter->commit();
        
            return true;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @return int|bool
     */
    public function deleteScholarity()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $dbJobScholarity = App_Model_DbTable_Factory::get('JOBVacancy_has_PerScholarity');
        
            $where = array( $dbAdapter->quoteInto('id_relationship = ?', $this->_data['id']) );
        
            $dbJobScholarity->delete($where);
        
            $history = 'DELETADO ESCOLARIDADE BA VAGA EMPREGU NUMERU: %s - ESCOLARIDADE DA VAGA DE EMPREGO DELETADO';
        
            $history = sprintf($history, $this->_data['id']);
            $this->_sysAudit($history, Job_Form_VacancyScholarity::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS);
        
            $dbAdapter->commit();
        
            return true;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @return int|bool
     */
    public function deleteTraining()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $dbJobTraining = App_Model_DbTable_Factory::get('JOBVacancy_has_Training');
        
            $where = array( $dbAdapter->quoteInto('id_relationship = ?', $this->_data['id']) );
        
            $dbJobTraining->delete($where);
        
            $history = 'DELETA FORM PROFISIONAL BA VAGA: %s  - DELETA FORMASAUN PROFISIONAL HUSI VAGA EMPREGU';
        
            $history = sprintf($history, $this->_data['id']);
            $this->_sysAudit($history, Job_Form_VacancyTraining::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS);
        
            $dbAdapter->commit();
        
            return true;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @return int|bool
     */
    public function deleteLanguage()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $dbJobLanguage = App_Model_DbTable_Factory::get('JOBVacancy_has_PerLanguage');
        
            $where = array( $dbAdapter->quoteInto('id_relationship = ?', $this->_data['id']) );
        
            $dbJobLanguage->delete($where);
        
            $history = 'DELETA LIAN FUAN BA VAGA: %s - DELETA LIAN FUAN HUSI VAGA EMPREGU';
        
            $history = sprintf($history, $this->_data['id']);
            $this->_sysAudit($history, Job_Form_VacancyLanguage::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS);
        
            $dbAdapter->commit();
        
            return true;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @return int|bool
     */
    public function deleteHandicapped()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $dbHandicapped = App_Model_DbTable_Factory::get('Handicapped');
        
            $where = array( $dbAdapter->quoteInto('id_handicapped = ?', $this->_data['id']) );
        
            $dbHandicapped->delete($where);
        
            $history = 'DELETA DEFICIENCIA VAGA EMPREGU: %s - DELETA DEFICIENCIA HUSI VAGA EMPREGU';
        
            $history = sprintf($history, $this->_data['id']);
            $this->_sysAudit($history, Job_Form_VacancyHandicapped::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS);
        
            $dbAdapter->commit();
        
            return true;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function fetchHandicapped($id)
    {
        $dbHandicapped = App_Model_DbTable_Factory::get('Handicapped');
        return $dbHandicapped->fetchRow(array( 'id_handicapped = ?' => $id ));
    }
    
    /**
     *
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit($description, $form = Job_Form_VacancyInformation::ID, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE)
    {
        $data = array(
        'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::JOB,
        'fk_id_sysform'	    => $form,
        'fk_id_sysoperation'    => $operation,
        'description'	    => $description
    );
    
        $mapperSysAudit = new Model_Mapper_SysAudit();
        $mapperSysAudit->setData($data)->save();
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listAddress($id)
    {
        $dbCountry = App_Model_DbTable_Factory::get('AddCountry');
        $dbDistrict = App_Model_DbTable_Factory::get('AddDistrict');
        $dbSubDistrict = App_Model_DbTable_Factory::get('AddSubDistrict');
        $dbJobLocation = App_Model_DbTable_Factory::get('JOBVacancy_has_Location');
    
        $select = $dbJobLocation->select()
                ->from(array( 'l' => $dbJobLocation ))
                ->setIntegrityCheck(false)
                ->join(
                    array( 'c' => $dbCountry ),
                    'c.id_addcountry = l.fk_id_addcountry',
                    array( 'country' )
                )
                ->joinLeft(
                    array( 'sd' => $dbSubDistrict ),
                    'sd.id_addsubdistrict = l.fk_id_addsubdistrict',
                    array( 'sub_district' )
                )
                ->joinLeft(
                    array( 'd' => $dbDistrict ),
                    'd.id_adddistrict = l.fk_id_adddistrict',
                    array( 'District' )
                )
                ->where('l.fk_id_jobvacancy = ?', $id)
                ->order(array( 'country', 'District', 'sub_district' ));
    
        return $dbJobLocation->fetchAll($select);
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listScholarity($id)
    {
        $dbJobScholarity = App_Model_DbTable_Factory::get('JOBVacancy_has_PerScholarity');
        $dbScholarity = App_Model_DbTable_Factory::get('PerScholarity');
        $dbTypeScholarity = App_Model_DbTable_Factory::get('PerTypeScholarity');
    
        $select = $dbJobScholarity->select()
                   ->from(array( 'js' => $dbJobScholarity ))
                   ->setIntegrityCheck(false)
                   ->joinLeft(
                    array( 's' => $dbScholarity ),
                    's.id_perscholarity = js.fk_id_perscholarity',
                    array( 'scholarity', 'Title', 'category', 'external_code' )
                   )
                   ->joinLeft(
                    array( 'ts' => $dbTypeScholarity ),
                    'ts.id_pertypescholarity = s.fk_id_pertypescholarity',
                    array( 'type_scholarity' )
                   )
                   ->where('js.fk_id_jobvacancy = ?', $id)
                   ->order(array( 'type_scholarity', 'scholarity' ));
    
        return $dbJobScholarity->fetchAll($select);
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listTraining($id)
    {
        $dbJobTraining = App_Model_DbTable_Factory::get('JOBVacancy_has_Training');
        $dbScholarity = App_Model_DbTable_Factory::get('PerScholarity');
        $dbTypeScholarity = App_Model_DbTable_Factory::get('PerTypeScholarity');
    
        $select = $dbJobTraining->select()
                   ->from(array( 'js' => $dbJobTraining ))
                   ->setIntegrityCheck(false)
                   ->join(
                    array( 's' => $dbScholarity ),
                    's.id_perscholarity = js.fk_id_perscholarity',
                    array( 'scholarity', 'Title', 'category', 'external_code' )
                   )
                   ->join(
                    array( 'ts' => $dbTypeScholarity ),
                    'ts.id_pertypescholarity = s.fk_id_pertypescholarity',
                    array( 'type_scholarity' )
                   )
                   ->where('js.fk_id_jobvacancy = ?', $id)
                   ->order(array( 'type_scholarity', 'scholarity' ));
    
        return $dbJobTraining->fetchAll($select);
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listLanguage($id)
    {
        $dbJobLanguage = App_Model_DbTable_Factory::get('JOBVacancy_has_PerLanguage');
        $dbLanguage = App_Model_DbTable_Factory::get('PerLanguage');
        $dbLevelKnowledge = App_Model_DbTable_Factory::get('PerLevelKnowledge');
    
        $select = $dbJobLanguage->select()
                ->from(array( 'jl' => $dbJobLanguage ))
                ->setIntegrityCheck(false)
                ->join(
                    array( 'l' => $dbLanguage ),
                    'l.id_perlanguage = jl.fk_id_perlanguage',
                    array( 'language' )
                )
                ->join(
                    array( 'lk' => $dbLevelKnowledge ),
                    'lk.id_levelknowledge = jl.fk_id_levelknowledge',
                    array( 'name_level', 'level' => 'description' )
                )
                ->where('jl.fk_id_jobvacancy = ?', $id)
                ->order('language');
    
        return $dbJobLanguage->fetchAll($select);
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listHandicapped($id)
    {
        $dbTypeHandicapped = App_Model_DbTable_Factory::get('TypeHandicapped');
        $dbHandicapped = App_Model_DbTable_Factory::get('Handicapped');
    
        $select = $dbHandicapped->select()
                ->from(array( 'h' => $dbHandicapped ))
                ->setIntegrityCheck(false)
                ->join(
                    array( 'th' => $dbTypeHandicapped ),
                    'th.id_typehandicapped = h.fk_id_typehandicapped',
                    array( 'type_handicapped' )
                )
                ->where('h.fk_id_jobvacancy = ?', $id)
                ->order('handicapped');
    
        return $dbHandicapped->fetchAll($select);
    }
    
    /**
     *
     * @return Zend_Db_Select
     */
    public function getSelectVacancy()
    {
        $dbJobVacancy = App_Model_DbTable_Factory::get('JOBVacancy');
        $dbEnterprise = App_Model_DbTable_Factory::get('FEFPEnterprise');
        $dbOcupationTimor = App_Model_DbTable_Factory::get('PROFOcupationTimor');
    
        $select = $dbJobVacancy->select()
                ->from(array( 'jv' => $dbJobVacancy ))
                ->setIntegrityCheck(false)
                ->join(
                    array( 'e' => $dbEnterprise ),
                    'e.id_fefpenterprise = jv.fk_id_fefpenterprise',
                    array(
                    'enterprise_name',
                    'open_date_formated' => new Zend_Db_Expr('DATE_FORMAT( jv.open_date, "%d/%m/%Y" )'),
                    'close_date_formated' => new Zend_Db_Expr('DATE_FORMAT( jv.close_date, "%d/%m/%Y" )'),
                    'expired'	=> new Zend_Db_Expr('( CASE WHEN jv.close_date < CURDATE() THEN 1 ELSE 0 END )')
                    )
                )
                ->join(
                    array( 'o' => $dbOcupationTimor ),
                    'o.id_profocupationtimor = jv.fk_id_profocupation',
                    array( 'ocupation_name_timor' )
                )
                ->order(array( 'open_date DESC', 'vacancy_titule' ));
    
        return $select;
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listByFilters(array $filters = array())
    {
        $dbJobVacancy = App_Model_DbTable_Factory::get('JOBVacancy');
    
        $select = $this->getSelectVacancy();
    
        $select->where('jv.active = ?', (int)$filters['active']);
    
        if (!empty($filters['vacancy_titule'])) {
            $select->where('jv.vacancy_titule LIKE ?', '%' . $filters['vacancy_titule'] . '%');
        }
    
        if (!empty($filters['fk_id_dec'])) {
            $select->where('jv.fk_id_dec = ?', $filters['fk_id_dec']);
        }
    
        if (!empty($filters['fk_id_profocupation'])) {
            $select->where('jv.fk_id_profocupation = ?', $filters['fk_id_profocupation']);
        }
    
        if (!empty($filters['fk_id_fefpenterprise'])) {
            $select->where('jv.fk_id_fefpenterprise = ?', $filters['fk_id_fefpenterprise']);
        }
    
        $date = new Zend_Date();
    
        if (!empty($filters['open_date'])) {
            $select->where('jv.open_date >= ?', $date->set($filters['open_date'])->toString('yyyy-MM-dd'));
        }
    
        if (!empty($filters['close_date'])) {
            $select->where('jv.close_date <= ?', $date->set($filters['close_date'])->toString('yyyy-MM-dd'));
        }
    
        return $dbJobVacancy->fetchAll($select);
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detailVacancy($id)
    {
        $dbJobVacancy = App_Model_DbTable_Factory::get('JOBVacancy');
        $dbEnterprise = App_Model_DbTable_Factory::get('FEFPEnterprise');
        $dbOcupationTimor = App_Model_DbTable_Factory::get('PROFOcupationTimor');
        $dbDec = App_Model_DbTable_Factory::get('Dec');
        $dbCountry = App_Model_DbTable_Factory::get('AddCountry');
    
        $select = $dbJobVacancy->select()
                ->from(array( 'jv' => $dbJobVacancy ))
                ->setIntegrityCheck(false)
                ->join(
                    array( 'e' => $dbEnterprise ),
                    'e.id_fefpenterprise = jv.fk_id_fefpenterprise',
                    array(
                    'enterprise_name',
                    'registration_date_formated' => new Zend_Db_Expr('DATE_FORMAT( jv.registration_date, "%d/%m/%Y" )'),
                    'open_date_formated' => new Zend_Db_Expr('DATE_FORMAT( jv.open_date, "%d/%m/%Y" )'),
                    'close_date_formated' => new Zend_Db_Expr('DATE_FORMAT( jv.close_date, "%d/%m/%Y" )'),
                    'start_job_date_formated' => new Zend_Db_Expr('DATE_FORMAT( jv.start_job_date, "%d/%m/%Y" )'),
                    'finish_job_date_formated' => new Zend_Db_Expr('DATE_FORMAT( jv.finish_job_date, "%d/%m/%Y" )')
                    )
                )
                ->join(
                    array( 'o' => $dbOcupationTimor ),
                    'o.id_profocupationtimor = jv.fk_id_profocupation',
                    array( 'ocupation_name_timor' )
                )
                ->join(
                    array( 'd' => $dbDec ),
                    'd.id_dec = jv.fk_id_dec',
                    array( 'name_dec' )
                )
                ->joinLeft(
                    array( 'c' => $dbCountry ),
                    'c.id_addcountry = jv.fk_location_overseas',
                    array( 'overseas' => 'country' )
                )
                ->where('jv.id_jobvacancy = ?', $id);
    
        return $dbJobVacancy->fetchRow($select);
    }
    
    /**
     *
     * @param int $id
     * @return array
     */
    public function listScholarityPrint($id)
    {
        $scholarities = $this->listScholarity($id);
    
        $data = array();
        foreach ($scholarities as $scholarity) {
            $data[$scholarity->type_scholarity][] = $scholarity;
        }
    
        return $data;
    }
    
    /**
     *
     * @param int $id
     * @return array
     */
    public function listTrainingPrint($id)
    {
        $trainings = $this->listTraining($id);
    
        $data = array();
        foreach ($trainings as $training) {
            $data[$training->type_scholarity][] = $training;
        }
    
        return $data;
    }
    
    /**
     *
     * @param int $id
     * @return array
     */
    public function listLanguagePrint($id)
    {
        $languages = $this->listLanguage($id);
    
        $data = array();
        foreach ($languages as $language) {
            $data[$language->language][] = $language;
        }
    
        return $data;
    }
    
    /**
     *
     * @return int|bool
     */
    public function saveClientList()
    {
        $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbAdapter->beginTransaction();
        try {
            $dbQuickShortlist = App_Model_DbTable_Factory::get('QuickShortlist');
        
            $clients = $this->_data['clients'];
            
            $row = $dbQuickShortlist->createRow();
            $row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
            $row->fk_id_fefpenterprise = $this->_data['fk_id_fefpenterprise'];
            $id = $row->save();
        
            $dbPersonHistory = App_Model_DbTable_Factory::get('Person_History');
            $dbQuickShortlistPerData = App_Model_DbTable_Factory::get('QuickShortlist_has_PerData');
        
            // Insert all the new clients in the list
            foreach ($clients as $client) {
        
        // Add the client to the list
                $row = $dbQuickShortlistPerData->createRow();
                $row->fk_id_quick_shortlist = $id;
                $row->fk_id_perdata = $client;
                $row->save();
        
                // Save history to client
                $rowHistory = $dbPersonHistory->createRow();
                $rowHistory->fk_id_perdata = $client;
                $rowHistory->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
                $rowHistory->fk_id_dec = Zend_Auth::getInstance()->getIdentity()->fk_id_dec;
                $rowHistory->date_time = Zend_Date::now()->toString('yyyy-MM-dd HH:mm');
                $rowHistory->action = sprintf('KLIENTE SELECIONADO ATU LISTA BA EMPREZA:%s ', $this->_data['fk_id_fefpenterprise']);
                $rowHistory->description = 'KLIENTE SELECIONADO ATU LISTA BA EMPREZA';
                $rowHistory->save();
        
                // Save the auditing
                $history = sprintf('KLIENTE SELECIONADO ATU LISTA KANDIDATU: %s - BA EMPREZA: %s ', $client, $this->_data['fk_id_fefpenterprise']);
                $this->_sysAudit($history);
            }
        
            $dbAdapter->commit();
        
            return $id;
        } catch (Exception $e) {
            $dbAdapter->rollBack();
            $this->_message->addMessage($this->_config->messages->error, App_Message::ERROR);
            return false;
        }
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function detailQuickList($id)
    {
        $dbQuickShortlist = App_Model_DbTable_Factory::get('QuickShortlist');
        $dbEnterprise = App_Model_DbTable_Factory::get('FEFPEnterprise');
    
        $select = $dbQuickShortlist->select()
                   ->from(array( 'ql' => $dbQuickShortlist ))
                   ->setIntegrityCheck(false)
                   ->join(
                    array( 'e' => $dbEnterprise ),
                    'e.id_fefpenterprise = ql.fk_id_fefpenterprise',
                    array(
                        'enterprise_name',
                        'date_registration_formated' => new Zend_Db_Expr('DATE_FORMAT( ql.date_registration, "%d/%m/%Y" )')
                    )
                   )
                   ->where('ql.id_quick_shortlist = ?', $id);
    
        return $dbQuickShortlist->fetchRow($select);
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listQuickList($id)
    {
        $mapperClient = new Client_Model_Mapper_Client();
        $select = $mapperClient->selectClient();
    
        $dbQuickShortlistPerData = App_Model_DbTable_Factory::get('QuickShortlist_has_PerData');
    
        $select->join(
            array( 'qlp' => $dbQuickShortlistPerData ),
            'qlp.fk_id_perdata = c.id_perdata',
            array()
        )
        ->where('qlp.fk_id_quick_shortlist = ?', $id)
        ->group('id_perdata')
        ->order(array( 'first_name', 'last_name' ));
    
        return $dbQuickShortlistPerData->fetchAll($select);
    }
}
