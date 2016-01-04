<?php

class Fefop_Model_Mapper_Contract extends App_Model_Abstract
{   
    /**
     * 
     * @var Model_DbTable_FEFOPContract
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEFOPContract();

	return $this->_dbTable;
    }

    /**
     * 
     * @return int|bool
     */
    public function save( $dataExternal )
    {
	try {
	    
	    // Get the Module
	    $mapperModule = new Fefop_Model_Mapper_Module();
	    $module = $mapperModule->fetchModule( $dataExternal['module'] );
	    
	    // Get the District
	    $mapperDistrict = new Register_Model_Mapper_AddDistrict();
	    $district = $mapperDistrict->fetchRow( $dataExternal['district'] );
	    
	    $data = array(
		'fk_id_fefop_modules'	=> $module->id_fefop_modules,
		'fk_id_fefop_programs'	=> $module->id_fefop_programs,
		'fk_id_sysuser'		=> Zend_Auth::getInstance()->getIdentity()->id_sysuser,
		'num_district'		=> $district->acronym,
		'num_program'		=> $module->num_program,
		'num_module'		=> $module->num_module,
		'num_year'		=> date( 'y' )
	    );
	    
	    $data['num_sequence'] = str_pad( $this->_getNumSequence( $data ), 4, '0', STR_PAD_LEFT );
	    
	    $this->_data = $data;
	    
	    $id = parent::_simpleSave( $this->_dbTable, false );
	    
	    $dataStatus = array(
		'contract'	=> $id,
		'status'	=> !empty( $dataExternal['status'] ) ? $dataExternal['status'] : Fefop_Model_Mapper_Status::ANALYSIS,
		'description'	=> 'Kontraktu rejistu'
	    );
	    
	    $mapperStatus = new Fefop_Model_Mapper_Status();
	    $mapperStatus->setData( $dataStatus )->save();
	    
	    return $id;
	    
	} catch ( Exception $ex ) {
	    
	    throw $ex;
	}
    }
    
    /**
     * 
     * @param array $data
     * @return int
     */
    public function _getNumSequence( $data )
    {
	$dbContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	
	$select = $dbContract->select()
			   ->from ( 
				array( 'c' => $dbContract ),
				array( 'num_sequence' => new Zend_Db_Expr( 'IFNULL( MAX( num_sequence ), 0 ) + 1' ) )
			    )
			   ->where( 'c.num_district = ?', $data['num_district'] )
			   ->where( 'c.num_program = ?', $data['num_program'] )
			   ->where( 'c.num_module = ?', $data['num_module'] )
			   ->where( 'c.num_year = ?', $data['num_year'] );
	
	$row = $dbContract->fetchRow( $select );
	return $row->num_sequence;
    }
    
    /**
    *
    * @param Zend_Db_Table_Row $row
    * @return string 
    */
   public static function buildNumRow( $row )
   {
	$numContract = array(
	    $row['num_program'],
	    $row['num_module'],
	    $row['num_district'],
	    $row['num_year'],
	    $row['num_sequence']
	);
	
	return implode( '-', $numContract );
    }
    
    /**
     *
     * @param int $id
     * @return string
     */
    public static function buildNumById( $id )
    {
	$obj = new self();
	$row = $obj->detail( $id );
	return self::buildNumRow( $row );
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
	$dbContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	$dbModules = App_Model_DbTable_Factory::get( 'FEFOPModules' );
	$dbPrograms = App_Model_DbTable_Factory::get( 'FEFOPPrograms' );
	$dbContractStatus = App_Model_DbTable_Factory::get( 'FEFOPContractStatus' );
	$dbStatus = App_Model_DbTable_Factory::get( 'FEFOPStatus' );
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	
	$selectStatusOrder = $dbStatus->select()
				 ->from( array( 'cs1' => $dbContractStatus ), array( 'fk_id_fefop_contract' ) )
				 ->setIntegrityCheck( false )
				 ->join(
				    array( 's' => $dbStatus ),
				    's.id_fefop_status = cs1.fk_id_fefop_status'
				 )
				 ->where( 'cs1.status = ?', 1 )
				 ->order( 'id_fefop_contract_status DESC' );
	
	$selectStatus = $dbStatus->select()
				 ->from( new Zend_Db_Expr( '(' . $selectStatusOrder . ')' ) )
				 ->setIntegrityCheck( false )
				 ->group( array( 'fk_id_fefop_contract' ) );
	
	$selectTotal = $dbBudgetCategory->select()
					->from( 
					    array( 'bcc' => $dbBudgetCategory ),
					    array( 'total' => new Zend_Db_Expr( 'SUM(bcc.amount)' ) )
					)
					->setIntegrityCheck( false )
					->where( 'bcc.status = ?', 1 )
					->where( 'bcc.fk_id_fefop_contract = c.id_fefop_contract' );
	
	$selectBeneficiary = $this->getSelectBeneficiary();
	
	$select = $dbContract->select()
			     ->setIntegrityCheck( false )
			     ->from( array( 'c' => $dbContract ) )
			     ->join(
				array( 'm' => $dbModules ),
				'm.id_fefop_modules = c.fk_id_fefop_modules',
				array( 'module' => new Zend_Db_Expr("CONCAT(m.acronym, ' - ', m.description)") )
			     )
			     ->join(
				array( 'p' => $dbPrograms ),
				'p.id_fefop_programs = c.fk_id_fefop_programs',
				array( 'program' => new Zend_Db_Expr("CONCAT(p.acronym, ' - ', p.description)") )
			     )
			     ->join(
				array( 'u' => $dbUser ),
				'u.id_sysuser = c.fk_id_sysuser',
				array( 'user' => 'name' )
			     )
			     ->join(
				array( 'd' => $dbDistrict ),
				'd.acronym = c.num_district',
				array( 'district' => 'District', 'id_adddistrict' )
			     )
			     ->join(
				array( 'b' => new Zend_Db_Expr( '(' . $selectBeneficiary . ')' ) ),
				'b.fk_id_fefop_contract = c.id_fefop_contract AND b.target = 1',
				array(	'beneficiary' => 'name' )
			     )
			     ->join(
				array( 'cs' => new Zend_Db_Expr( '(' . $selectStatus . ')' ) ),
				'c.id_fefop_contract = cs.fk_id_fefop_contract',
				array(
				    'id_fefop_status',
				    'status_description',
				    'total' => new Zend_Db_Expr( '(' . $selectTotal . ')' )
				)
			     )
			     ->group( array( 'id_fefop_contract' ) );
	
	return $select;
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detail( $id )
    {
	$select = $this->getSelect();
	$select->where( 'c.id_fefop_contract = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listByFilters( $filters = array() )
    {
	$select = $this->getSelect();
	
	if ( !empty( $filters['fk_id_fefop_status'] ) )
	    $select->where( 'cs.id_fefop_status = ?', $filters['fk_id_fefop_status'] );
	
	if ( !empty( $filters['fk_id_fefop_programs'] ) )
	    $select->where( 'c.fk_id_fefop_programs = ?', $filters['fk_id_fefop_programs'] );
	
	if ( !empty( $filters['fk_id_fefop_modules'] ) )
	    $select->where( 'c.fk_id_fefop_modules = ?', $filters['fk_id_fefop_modules'] );
	
	if ( !empty( $filters['num_district'] ) )
	    $select->where( 'c.num_district = ?', $filters['num_district'] );
	
	if ( !empty( $filters['num_year'] ) )
	    $select->where( 'c.num_year = ?', $filters['num_year'] );
	
	if ( !empty( $filters['num_sequence'] ) )
	    $select->where( 'c.num_sequence LIKE ?', '%' . $filters['num_sequence'] . '%' );
	
	if ( !empty( $filters['minimum_amount'] ) )
	    $select->having( 'total >= ?', $filters['minimum_amount'] );

	if ( !empty( $filters['maximum_amount'] ) )
	    $select->having( 'total <= ?', $filters['maximum_amount'] );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listExpensesContract( $id, $type = false )
    {
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	
	$select = $dbBudgetCategory->select()
				   ->from( array( 'bc' => $dbBudgetCategory ) )
				   ->setIntegrityCheck( false )
				   ->join(
					array( 'bco' => $dbBudgetContract ),
					'bco.fk_id_budget_category = bc.id_budget_category',
					array( 'amount' )
				    )
				    ->join(
					array( 'fc' => $dbFEFOPContract ),
					'bco.fk_id_fefop_contract = fc.id_fefop_contract',
					array()
				    )
				    ->where( 'fc.id_fefop_contract = ?', $id )
				    ->where( 'bco.status = ?', 1 )
				    ->order( array( 'id_budgetcategory_contract' ) );
	
	if ( !empty( $type ) )
	    $select->where( 'bc.fk_id_budget_category_type = ?', $type );
	
	return $dbBudgetCategory->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listComponentsContract( $id )
    {
	$dbBudgetCategoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	
	$select = $dbBudgetCategoryType->select()
					->from( array( 'bct' => $dbBudgetCategoryType ) )
					->setIntegrityCheck( false )
					->join(
					    array( 'bc' => $dbBudgetCategory ),
					    'bc.fk_id_budget_category_type = bct.id_budget_category_type',
					    array()
					)
					->join(
					    array( 'bcc' => $dbBudgetContract ),
					    'bcc.fk_id_budget_category = bc.id_budget_category',
					    array()
					)
					->where( 'bcc.fk_id_fefop_contract = ?', $id )
					->where( 'bcc.status = ?', 1 )
					->group( array( 'description' ) )
					->order( array( 'id_budget_category_type' ) );
	
	return $dbBudgetCategoryType->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelectBeneficiary()
    {
	$dbClient = App_Model_DbTable_Factory::get( 'PerData' );
	$dbEduInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	$dbFPContract = App_Model_DbTable_Factory::get( 'FPContract' );
	$dbAnnualPlanning = App_Model_DbTable_Factory::get( 'FPAnnualPlanning' );
	$dbRiContract = App_Model_DbTable_Factory::get( 'RIContract' );
	$dbFPBeneficiary = App_Model_DbTable_Factory::get( 'FPBeneficiary' );
	$dbDRHContract = App_Model_DbTable_Factory::get( 'DRHContract' );
	$dbDRHBeneficiary = App_Model_DbTable_Factory::get( 'DRHBeneficiary' );
	$dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	$dbFEContract = App_Model_DbTable_Factory::get( 'FEContract' );
	$dbBusinessPlan = App_Model_DbTable_Factory::get( 'BusinessPlan' );
	$dbPCEContract = App_Model_DbTable_Factory::get( 'PCEContract' );
	$dbPCEGroup = App_Model_DbTable_Factory::get( 'PCEGroup' );
	$dbPerContract = App_Model_DbTable_Factory::get( 'PERContract' );
	
	$selectEduInstitution = $dbEduInstitution->select()
				    ->from( 
					array( 'ei' => $dbEduInstitution ),
					array(
					    'id'    => 'id_fefpeduinstitution',
					    'code'=> new Zend_Db_Expr('NULL'),
					    'name'  =>  'institution',
					    'type'  => new Zend_Db_Expr( "'IE'" ),
					    'label' => new Zend_Db_Expr( "'Inst Ensinu'" ),
					)
				    )
				    ->setIntegrityCheck( false );
	
	$selectClient = $dbClient->select()
				    ->from( 
					array( 'cl' => $dbClient ),
					array(
					    'id'    => 'id_perdata',
					    'code' => new Zend_Db_Expr( "CONCAT(cl.num_district, '-', cl.num_subdistrict, '-', cl.num_servicecode, '-', cl.num_year, '-', cl.num_sequence)"),
					    'name' => new Zend_Db_Expr( "CONCAT(cl.num_district, '-', cl.num_subdistrict, '-', cl.num_servicecode, '-', cl.num_year, '-', cl.num_sequence, ' - ', cl.first_name, ' ', IF(cl.medium_name, CONCAT( cl.medium_name, ' '), '' ), cl.last_name)" ),
					    'type'  => new Zend_Db_Expr( "'CL'" ),
					    'label' => new Zend_Db_Expr( "'Kliente'" )
					)
				    )
				    ->setIntegrityCheck( false );
	
	$selectEnterprise = $dbClient->select()
				    ->from( 
					array( 'et' => $dbEnterprise ),
					array(
					    'id'     => 'id_fefpenterprise',
					    'code' => new Zend_Db_Expr( 'NULL' ),
					    'name'   => 'enterprise_name',
					    'type'   => new Zend_Db_Expr( "'EM'" ),
					    'label'  => new Zend_Db_Expr( "'Empreza'" )
					)
				    )
				    ->setIntegrityCheck( false );
	
	$selectsUnion = array();
	
	/**
	 *  SELECT MODULE RI
	 */
	$selectRi = clone $selectEduInstitution;
	$selectRi->join(
			array( 'ri' => $dbRiContract ),
			'ri.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
			array(
			    'fk_id_fefop_contract',
			    'target'	=>  new Zend_Db_Expr( '1' )
			)
		   );
	
	$selectsUnion[] = $selectRi;
	
	/**
	 *  SELECT MODULE FP - TARGET BENEFICIARY
	 */
	$selectFpTarget = clone $selectEduInstitution;
	$selectFpTarget->join(
			array( 'ap' => $dbAnnualPlanning ),
			'ap.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
			array()
		 )
		 ->join(
			array( 'fp' => $dbFPContract ),
			'fp.fk_id_annual_planning = ap.id_annual_planning',
			array( 
			    'fk_id_fefop_contract',
			    'target'	=>  new Zend_Db_Expr( '1' )
			)
		 );
	
	$selectsUnion[] = $selectFpTarget;
	
	/**
	 *  SELECT MODULE FP - CLIENT BENEFICIARY
	 */
	$selectFpClient = clone $selectClient;
	$selectFpClient->join(
				array( 'fpb' => $dbFPBeneficiary ),
				'fpb.fk_id_perdata = cl.id_perdata',
				array()
			 )
			 ->join(
				array( 'fp' => $dbFPContract ),
				'fpb.fk_id_fp_contract = fp.id_fp_contract',
				array( 
				    'fk_id_fefop_contract',
				    'target'	=>  new Zend_Db_Expr( '0' )
				)
			 );
	
	$selectsUnion[] = $selectFpClient;
	
	/**
	 *  SELECT MODULE DRH
	 */
	$selectDrh = clone $selectClient;
	$selectDrh->join(
			array( 'stf' => $dbStaff ),
			'stf.fk_id_perdata = cl.id_perdata',
			array()
		 )
		 ->join(
			array( 'dhb' => $dbDRHBeneficiary ),
			'dhb.fk_id_staff = stf.id_staff',
			array()
		 )
		 ->join(
			array( 'dhc' => $dbDRHContract ),
			'dhb.id_drh_beneficiary = dhc.fk_id_drh_beneficiary',
			array( 
			    'fk_id_fefop_contract',
			    'target'	=>  new Zend_Db_Expr( '1' )
			)
		 );
	
	$selectsUnion[] = $selectDrh;
	
	/**
	 *  SELECT MODULE FE - ENTERPRISE
	 */
	$selectFeEnterprise = clone $selectEnterprise;
	$selectFeEnterprise->join(
				array( 'feet' => $dbFEContract ),
				'feet.fk_id_fefpenterprise = et.id_fefpenterprise',
				array(
				    'fk_id_fefop_contract',
				    'target'	=>  new Zend_Db_Expr( '0' )
				)
			   );
	
	$selectsUnion[] = $selectFeEnterprise;
	
	/**
	 *  SELECT MODULE FE - EDUCATIONAL INSTITUTE
	 */
	$selectFeEduInstitute = clone $selectEduInstitution;
	$selectFeEduInstitute->join(
				array( 'feei' => $dbFEContract ),
				'feei.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
				array(
				    'fk_id_fefop_contract',
				    'target'	=>  new Zend_Db_Expr( '0' )
				)
			   );
	
	$selectsUnion[] = $selectFeEduInstitute;
	
	/**
	 *  SELECT MODULE FE - CLIENT
	 */
	$selectFeClient = clone $selectClient;
	$selectFeClient->join(
				array( 'fecl' => $dbFEContract ),
				'fecl.fk_id_perdata = cl.id_perdata',
				array(
				    'fk_id_fefop_contract',
				    'target'	=>  new Zend_Db_Expr( '1' )
				)
			   );
	
	$selectsUnion[] = $selectFeClient;
	
	/**
	 *  SELECT MODULE PCE - TARGET
	 */
	$selectPceTarget = clone $selectClient;
	$selectPceTarget->join(
				array( 'pct' => $dbBusinessPlan ),
				'pct.fk_id_perdata = cl.id_perdata',
				array(
				    'fk_id_fefop_contract',
				    'target'=>  new Zend_Db_Expr( '1' )
				)
			  )
			  ->where( 'pct.fk_id_fefop_contract IS NOT NULL' );
	
	$selectsUnion[] = $selectPceTarget;
	
	/**
	 *  SELECT MODULE PCE FASE I - TARGET
	 */
	$selectPceFaseITarget = clone $selectClient;
	$selectPceFaseITarget->join(
				array( 'pci' => $dbPCEContract ),
				'pci.fk_id_perdata = cl.id_perdata',
				array(
				    'fk_id_fefop_contract',
				    'target' =>  new Zend_Db_Expr( '1' )
				)
			  );
	
	$selectsUnion[] = $selectPceFaseITarget;
	
	/**
	 *  SELECT MODULE PCE - GROUP
	 */
	$selectPceGroup = clone $selectClient;
	$selectPceGroup->join(
			    array( 'pcg' => $dbPCEGroup ),
			    'pcg.fk_id_perdata = cl.id_perdata AND pcg.status = 1',
			    array()
		       )
		       ->join(
			    array( 'pctg' => $dbBusinessPlan ),
			    'pcg.fk_id_businessplan = pctg.id_businessplan',
			    array(
				'fk_id_fefop_contract',
				'target'	=>  new Zend_Db_Expr( '0' )
			    )
		      )
		      ->where( 'pctg.fk_id_fefop_contract IS NOT NULL' );
	
	$selectsUnion[] = $selectPceGroup;
	
	/**
	 *  SELECT MODULE PER
	 */
	$selectPer = clone $selectEnterprise;
	$selectPer->join(
			array( 'perc' => $dbPerContract ),
			'perc.fk_id_fefpenterprise = et.id_fefpenterprise',
			array(
			    'fk_id_fefop_contract',
			    'target' =>  new Zend_Db_Expr( '1' )
			)
		   );
	
	$selectsUnion[] = $selectPer;
	
	$adapter = App_Model_DbTable_Abstract::getDefaultAdapter();
	$select = $adapter->select()->union( $selectsUnion, Zend_Db_Select::SQL_UNION_ALL );
	
	return $select;
    }
    
    /**
     * 
     * @param int $contract
     * @return float
     */
    public function getTotalContract( $contract )
    {
	$total = 0;
	
	$expensesContract = $this->listExpensesContract( $contract );
	foreach ( $expensesContract as $expense )
	    $total += (float)$expense->amount;
	
	return $total;
    }
    
    /**
     * 
     * @param int $contract
     * @param int $expense
     * @return float
     */
    public function getTotalExpenseContract( $contract, $expense )
    {
	$total = 0;
	
	$expensesContract = $this->listExpensesContract( $contract );
	foreach ( $expensesContract as $expenseContract )
	    if ( $expenseContract->id_budget_category == $expense )
		$total = (float)$expenseContract->amount;
	
	return $total;
    }
}