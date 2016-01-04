<?php

class Report_Model_Mapper_Report extends App_Model_Mapper_Abstract
{
    /**
     * 
     * @return mixed
     */
    public function report()
    {
	try {
	    
	    $mapperClass = $this->_getMapper();
	    $methodMapper  = $this->_getMethod();
	    
	    $mapper = new $mapperClass();
	    $data = $mapper->setData( $this->_data )->$methodMapper();
	    
	    if ( empty( $data['filters'] ) )
		$data['filters'] = array();
	    
	    $data['filters'] += $this->getFilters();
	    
	    return $data;
	    
	} catch ( Exception $e ) {
	    Zend_Debug::dump( $e );
	    exit;
	    return array();
	}
    }
    
    /**
     * 
     */
    public function getFilters()
    {
	$filters = $this->_data;
	
	if ( !empty( $this->_data['fk_id_dec'] ) ) {
	    
	    $dbDec = App_Model_DbTable_Factory::get('Dec');
	    
	    $select = $dbDec->select()
	       ->from(
	    		$dbDec,
	    		array('name_dec')
    	    )
    	    ->where('id_dec IN(?)', $this->_data['fk_id_dec']);
	     
	    $rows = $dbDec->fetchAll($select);
	     
	    $filters['ceop'] = '';
	     
	    foreach ($rows as $key => $row) {
	    	$filters['ceop'] .= $row->name_dec;
	    
	    	if ($rows->count() != ++$key) {
	    		$filters['ceop'] .= ', ';
	    	}
	    }
	    
	}
	
	if (!empty($this->_data['fk_id_counselor'])) {
	    
	    $dbSysUser = App_Model_DbTable_Factory::get('SysUser');
	    
	    $select = $dbSysUser->select()
    	    ->from(
	    		$dbSysUser,
	    		array('name')
    	    )
    	    ->where('id_sysuser IN(?)', $this->_data['fk_id_counselor']);
	    
	    $rows = $dbSysUser->fetchAll($select);
	    
	    $filters['fk_id_counselor'] = '';
	    
	    foreach ($rows as $key => $row) {
	    	$filters['fk_id_counselor'] .= $row->name;
	    	 
	    	if ($rows->count() != ++$key) {
	    		$filters['fk_id_counselor'] .= ', ';
	    	}
	    }
	    
	}
	
	if ( !empty( $this->_data['fk_id_addcountry'] ) ) {
	 
	    $mapperCountry = new Register_Model_Mapper_AddCountry();
	    $contry = $mapperCountry->fetchRow( $this->_data['fk_id_addcountry'] );
	    
	    $filters['country'] = $contry->country;
	}
	
	
	if ( !empty( $this->_data['fk_id_adddistrict'] ) ) {
	 
	    $mapperDistrict = new Register_Model_Mapper_AddDistrict();
	    $district = $mapperDistrict->fetchRow( $this->_data['fk_id_adddistrict'] );
	    
	    $filters['district'] = $district->District;
	}
	
	if ( !empty( $this->_data['fk_id_fefpenterprise'] ) ) {
	 
	    $mapperEnterprise = new Register_Model_Mapper_Enterprise();
	    $enterprise = $mapperEnterprise->fetchRow( $this->_data['fk_id_fefpenterprise'] );
	    
	    $filters['enterprise'] = $enterprise->enterprise_name;
	}
	
	if ( !empty( $this->_data['fk_id_fefpeduinstitution'] ) ) {
	 
	    $mapperEducationInstititue = new Register_Model_Mapper_EducationInstitute();
	    $educationInstititute = $mapperEducationInstititue->fetchRow( $this->_data['fk_id_fefpeduinstitution'] );
	    
	    $filters['institute'] = $educationInstititute->institution;
	}
	
	if ( !empty( $this->_data['fk_id_profocupation'] ) ) {
	 
	    $mapperOccupation = new Register_Model_Mapper_ProfOcupationTimor();
	    $occupation = $mapperOccupation->fetchRow( $this->_data['fk_id_profocupation'] );
	    
	    $filters['occupation'] = $occupation['acronym'] . ' ' . $occupation['ocupation_name_timor'];
	}
	
	if ( !empty( $this->_data['fk_id_scholarity_area'] ) ) {
	 
	    $mapperScholarityArea = new Register_Model_Mapper_ScholarityArea();
	    $area = $mapperScholarityArea->fetchRow( $this->_data['fk_id_scholarity_area'] );
	    
	    $filters['area'] = $area['scholarity_area'];
	}
	
	if ( !empty( $this->_data['fk_id_pertypescholarity'] ) ) {
	 
	    $mapperTypeScholarity = new Register_Model_Mapper_PerTypeScholarity();
	    $typeScholarity = $mapperTypeScholarity->fetchRow( $this->_data['fk_id_pertypescholarity'] );
	    
	    $filters['type_scholarity'] = $typeScholarity['type_scholarity'];
	}
	
	if ( !empty( $this->_data['fk_typeinstitution'] ) ) {
	 
	    $mapperTypeInstitution = App_Model_DbTable_Factory::get( 'TypeInstitution' );
	    $typeInstitution = $mapperTypeInstitution->fetchRow( array( 'id_typeinstitution = ?' => $this->_data['fk_typeinstitution'] ) );
	    
	    $filters['type_institution'] = $typeInstitution['type_institution'];
	}
	
	if ( !empty( $this->_data['fk_id_sectorindustry'] ) ) {
	 
	    $mapperIsicClass = App_Model_DbTable_Factory::get( 'ISICClassTimor' );
	    $sectorIndustry = $mapperIsicClass->fetchRow( array( 'id_isicclasstimor = ?' => $this->_data['fk_id_sectorindustry'] ) );
	    
	    $filters['sector_industry'] = $sectorIndustry['name_classtimor'];
	}
	
	if ( !empty( $this->_data['fk_fefptypeenterprite'] ) ) {
	 
	    $mapperTypeEnterprise = App_Model_DbTable_Factory::get( 'FEFPTypeEnterprise' );
	    $typeEnterprise = $mapperTypeEnterprise->fetchRow( array( 'id_fefptypeenterprise = ?' => $this->_data['fk_fefptypeenterprite'] ) );
	    
	    $filters['type_enterprise'] = $typeEnterprise['type_enterprise'];
	}
	
	if ( !empty( $this->_data['fk_id_perlevelscholarity'] ) ) {
	 
	    $dbLevelScholarity = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
	    $levelScholarity = $dbLevelScholarity->fetchRow( array( 'id_perlevelscholarity = ?' => $this->_data['fk_id_perlevelscholarity'] ) );
	    
	    $filters['level_scholarity'] = $levelScholarity['level_scholarity'];
	}
	
	if ( !empty( $this->_data['fk_id_perscholarity'] ) ) {
	 
	    $mapperScholarity = new Register_Model_Mapper_PerScholarity();
	    $course = $mapperScholarity->fetchRow( $this->_data['fk_id_perscholarity'] );
	    
	    $filters['course'] = ( empty( $course['external_code'] ) ? '' : $course['external_code'] . ' - ' ) . $course['scholarity'];
	}
	
	if ( !empty( $this->_data['fk_id_sysuser'] ) ) {
	 
	    $mapperSysUser = new Admin_Model_Mapper_SysUser();
	    $user = $mapperSysUser->fetchRow( $this->_data['fk_id_sysuser'] );
	    
	    $filters['user'] = $user['name'] . ' (' . $user['login'] . ')';
	}
	
	if ( !empty( $this->_data['fk_id_department'] ) ) {
	 
	    $mapperDepartment = new Admin_Model_Mapper_Department();
	    $department = $mapperDepartment->fetchRow( $this->_data['fk_id_department'] );
	    
	    $filters['department'] = $department['name'];
	}
	
	if ( !empty( $this->_data['fk_id_campaign_type'] ) ) {
	 
	    $mapperCampaignType = new Sms_Model_Mapper_CampaignType();
	    $campaignType = $mapperCampaignType->fetchRow( $this->_data['fk_id_campaign_type'] );
	    
	    $filters['campaign_type'] = $campaignType['campaign_type'];
	}
	
	if ( !empty( $this->_data['status_campaign'] ) ) {
	 
	    $view = Zend_Layout::getMvcInstance()->getView();
	    $optStatuses = $view->campaign()->getStatuses();
	    
	    $filters['status_campaign'] = $optStatuses[$this->_data['status_campaign']];
	}
	
	if ( !empty( $this->_data['fk_id_campaign'] ) ) {
	 
	    $mapperCampaign = new Sms_Model_Mapper_Campaign();
	    $campaign = $mapperCampaign->fetchRow( $this->_data['fk_id_campaign'] );
	    
	    $filters['campaign_title'] = $campaign['campaign_title'];
	}
	
	if (!empty($this->_data['id_fefop_programs']) || !empty($this->_data['fk_id_fefop_programs'])) {
	    
	    $dbFEFOPPrograms = App_Model_DbTable_Factory::get('FEFOPPrograms');
	     
	    if (!empty($this->_data['id_fefop_programs'])) {
	    	
	    	if (!is_array($this->_data['id_fefop_programs'])) {
		    	
	    		$row = $dbFEFOPPrograms->find($this->_data['id_fefop_programs'])->current();
	    		
	    		$filters['fefop_program'] = $row->acronym . ' - ' . $row->description;
		    	
	    	} else {
	    			    		
	    		$select = $dbFEFOPPrograms->select()
	    			->from(
	    				$dbFEFOPPrograms,
	    				array('description', 'acronym')
	    			)
	    			->where('id_fefop_programs IN(?)', $this->_data['id_fefop_programs']);
	    		
	    		$rows = $dbFEFOPPrograms->fetchAll($select);
	    		
	    		$filters['fefop_program'] = '';
	    		
	    		foreach ($rows as $key => $row) {
	    			$filters['fefop_program'] .= $row->acronym . ' - ' . $row->description;
	    				    			
	    			if ($rows->count() != ++$key) {
	    				$filters['fefop_program'] .= ', ';
	    			}
	    		}
	    		
	    	}
	    	
	    } else if (!empty($this->_data['fk_id_fefop_programs'])) {
	    	
	    	$row = $dbFEFOPPrograms->find($this->_data['fk_id_fefop_programs'])->current();
	    	
	    	$filters['fefop_program'] = $row->acronym . ' - ' . $row->description;
	    	
	    }
	    
	}
	
	if (!empty($this->_data['id_fefop_modules']) || !empty($this->_data['fk_id_fefop_modules'])) {
	    
	    $dbFEFOPModules = App_Model_DbTable_Factory::get('FEFOPModules');
	    
	    if (!empty($this->_data['id_fefop_modules'])) {
			
	    	if (!is_array($this->_data['id_fefop_modules'])) {
		    	
	    		$row = $dbFEFOPModules->find($this->_data['id_fefop_modules'])->current();
		    	
		    	$filters['fefop_module'] = $row->acronym . ' - ' . $row->description;
		    	
	    	} else {
	    		
	    		$select = $dbFEFOPModules->select()
	    			->from(
	    				$dbFEFOPModules,
	    				array('description', 'acronym')
		    		)
		    		->where('id_fefop_modules IN(?)', $this->_data['id_fefop_modules']);
	    		 
	    		$rows = $dbFEFOPModules->fetchAll($select);
	    		 
	    		$filters['fefop_module'] = '';
	    		 
	    		foreach ($rows as $key => $row) {
	    			$filters['fefop_module'] .= $row->acronym . ' - ' . $row->description;
	    		
	    			if ($rows->count() != ++$key) {
	    				$filters['fefop_module'] .= ', ';
	    			}
	    		}
	    		
	    	}
	    	
	    } else if (!empty($this->_data['fk_id_fefop_modules'])) {
	    	
	    	$row = $dbFEFOPModules->find($this->_data['fk_id_fefop_modules'])->current();
	    	
	    	$filters['fefop_module'] = $row->acronym . ' - ' . $row->description;
	    	
	    }
		
	}
	
	if (!empty($this->_data['id_adddistrict'])) {
		
		$dbAddDistrict = App_Model_DbTable_Factory::get('AddDistrict');
		
		if (is_array($this->_data['id_adddistrict'])) {
		
			$select = $dbAddDistrict->select()
				->from(
					$dbAddDistrict,
					array('District')
				)
				->where('id_adddistrict IN(?)', $this->_data['id_adddistrict']);
			
			$rows = $dbAddDistrict->fetchAll($select);
			
			$filters['district'] = '';
			
			foreach ($rows as $key => $row) {
				$filters['district'] .= $row->District;
				
				if ($rows->count() != ++$key) {
					$filters['district'] .= ', ';
				}
			}
			
		} else {
			
			$row = $dbAddDistrict->find($this->_data['id_adddistrict'])->current();
		
			$filters['district'] = $row->District;
			
		}
		
	}
	
	if (!empty($this->_data['num_district'])) {
		$row = App_Model_DbTable_Factory::get('AddDistrict')->fetchRow(array('acronym = ?' => $this->_data['num_district']));
		$filters['district'] = $row->District;
	}
	
	if (!empty($this->_data['id_scholarity_area'])) {
	    $row = App_Model_DbTable_Factory::get('ScholarityArea')->find($this->_data['id_scholarity_area'])->current();
	    $filters['scholarity_area'] = (empty($row->acronym) ? '' : $row->acronym . ' - ') . $row->scholarity_area;
	}
	
	if (!empty($this->_data['id_profocupationtimor'])) {
		$row = App_Model_DbTable_Factory::get('PROFOcupationTimor')->find($this->_data['id_profocupationtimor'])->current();
		$filters['ocupationtimor'] = $row->acronym . ' - ' . $row->ocupation_name_timor;
	}

	if (!empty($this->_data['id_fefpeduinstitution'])) {
		$row = App_Model_DbTable_Factory::get('FefpEduInstitution')->find($this->_data['id_fefpeduinstitution'])->current();
		$filters['institution'] = $row->institution;
	}
	
    if (!empty($this->_data['fk_id_user_inserted'])) {
        $row = App_Model_DbTable_Factory::get('SysUser')->find($this->_data['fk_id_user_inserted'])->current();
        $filters['user_inserted'] = $row->name;
    }
	
	if (!empty($this->_data['fk_id_user_removed'])) {
		$row = App_Model_DbTable_Factory::get('SysUser')->find($this->_data['fk_id_user_removed'])->current();
		$filters['user_removed'] = $row->name;
	}
	
	if (array_key_exists('status', $this->_data) && is_numeric($this->_data['status'])) {
		$filters['status_description'] = $this->_data['status'] ? 'Loos' : 'Lae';
	}
	
	if (!empty($this->_data['date_registration_ini'])) {
        $filters['date_registration_ini'] = $this->_data['date_registration_ini'];
    }
    
    if (!empty($this->_data['date_registration_fim'])) {
    	$filters['date_registration_fim'] = $this->_data['date_registration_fim'];
    }
    
    if (!empty($this->_data['num_year'])) {
    	$filters['num_year'] = $this->_data['num_year'];
    }
    
    if (!empty($this->_data['num_sequence'])) {
    	$filters['num_sequence'] = $this->_data['num_sequence'];
    }
    
    if (array_key_exists('minimum_amount', $this->_data) && array_key_exists('maximum_amount', $this->_data)) {
        
        $min = new Zend_Currency('en_US');
        $min->setValue($this->_data['minimum_amount']);
        
        $max = new Zend_Currency('en_US');
        $max->setValue($this->_data['maximum_amount']);
        
        $filters['minmaxamount'] = $min . ' - ' . $max;
    }
    
    if (!empty($this->_data['id_fefop_status']) || !empty($this->_data['fk_id_fefop_status'])) {
        
        $dbFEFOPStatus = App_Model_DbTable_Factory::get('FEFOPStatus');
        
        if (!empty($this->_data['id_fefop_status'])) {
        
        	if (!is_array($this->_data['id_fefop_status'])) {
        		 
        		$row = $dbFEFOPStatus->find($this->_data['id_fefop_status'])->current();
        		 
        		$filters['fefop_status'] = $row->status_description;
        		 
        	} else {
        
        		$select = $dbFEFOPStatus->select()
                    ->from(
        				$dbFEFOPStatus,
        				array('status_description')
                    )
                    ->where('id_fefop_status IN(?)', $this->_data['id_fefop_status']);
        		 
        		$rows = $dbFEFOPStatus->fetchAll($select);
        		 
        		$filters['fefop_status'] = '';
        		 
        		foreach ($rows as $key => $row) {
        			$filters['fefop_status'] .= $row->status_description;
        
        			if ($rows->count() != ++$key) {
        				$filters['fefop_status'] .= ', ';
        			}
        		}
        		 
        	}
        	
        } else {
         
            $row = $dbFEFOPStatus->find($this->_data['fk_id_fefop_status'])->current();
            
            $filters['fefop_status'] = $row->status_description;
        
        }
    }
    
    if (!empty($this->_data['id_budget_category_type'])) {
        $row = App_Model_DbTable_Factory::get('BudgetCategoryType')->find($this->_data['id_budget_category_type'])->current();
        $filters['budget_category_type'] = $row->description;
    }
    
    if (!empty($this->_data['type_fefopfund'])) {
        if ('G' == $this->_data['type_fefopfund']) {
            $filters['description_type_fefopfund'] = 'Governo';
        } else {
            $filters['description_type_fefopfund'] = 'Donor';
        }
    }
    
    if (!empty($this->_data['id_beneficiary'])) {
    	
    	$mapper = new Fefop_Model_Mapper_Contract();
    	$adapter = App_Model_DbTable_Abstract::getDefaultAdapter();
    	
    	$select = $adapter->select()
    		->from(
				array('b' => new Zend_Db_Expr('(' . $mapper->getSelectBeneficiary() . ')')),
    			array('name')
    		)
    		->where('b.id = ?', $this->_data['id_beneficiary']);
    	
    	$row = $adapter->fetchRow($select);
    	
    	$filters['beneficiary'] = $row['name'];
    	
    }
    
    if (!empty($this->_data['type_beneficiary'])) {
    	
    	switch ($this->_data['type_beneficiary']) {
    	
    		case 'fk_id_staff':
    			$type_beneficiary = 'Empreza Staff';
    			break;
    	
    		case 'fk_id_fefpenterprise':
    			$type_beneficiary = 'Empreza';
    			break;
    	
    		case 'fk_id_fefpeduinstitution':
    			$type_beneficiary = 'Inst Ensinu';
    			break;
    			 
    		case 'fk_id_perdata':
    			$type_beneficiary = 'Kliente';
    			break;
    			 
    		default:
    			$type_beneficiary = 'N/A';
    			 
    	}
    	
    	$filters['type_beneficiary'] = $type_beneficiary;
    	
    }
    
	return $filters;
    }
    
    /**
     * 
     * @return string
     */
    protected function _getMapper()
    {
	$prefix = 'Report_Model_Mapper_';
	
	$class = explode( '/', $this->_data['path'] );
	$class = $prefix . App_General_String::toCamelCase( $class[0] );
	
	return $class;
    }
    
    /**
     *
     * @return string
     */
    protected function _getMethod()
    {
	$method = explode( '/', $this->_data['path'] );
	$method = lcfirst( App_General_String::toCamelCase( $method[1] ) );
	
	return $method;
    }
}