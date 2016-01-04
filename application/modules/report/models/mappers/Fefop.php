<?php

/**
 * 
 * @version $Id: Fefop.php 522 2014-12-19 02:31:07Z frederico $
 */
class Report_Model_Mapper_Fefop extends App_Model_Mapper_Abstract
{

    /**
     * 
     * @return Zend_Db_Select
     */
    protected function _selectFEFOPStatus()
    {
	$dbFEFOPStatus = App_Model_DbTable_Factory::get( 'FEFOPStatus' );
	$dbFEFOPContractStatus = App_Model_DbTable_Factory::get( 'FEFOPContractStatus' );

	$subSelect = $dbFEFOPStatus->select()
		->setIntegrityCheck( false )
		->from(
			$dbFEFOPStatus->__toString(), array('id_fefop_status', 'status_description')
		)
		->join(
			$dbFEFOPContractStatus->__toString(), 'FEFOP_Status.id_fefop_status = FEFOP_Contract_Status.fk_id_fefop_status', array('fk_id_fefop_contract')
		)
		->where( 'FEFOP_Contract_Status.status = ?', 1 )
		->order( 'FEFOP_Contract_Status.date_inserted DESC' );

	$adapter = App_Model_DbTable_Abstract::getDefaultAdapter();

	$subSelect = $adapter->select()
		->from(
			array('FEFOPStatus' => new Zend_Db_Expr( '(' . $subSelect . ')' )), array('*')
		)
		->group( 'FEFOPStatus.fk_id_fefop_contract' );

	$select = $adapter->select()
		->from(
		array('s' => new Zend_Db_Expr( '(' . $subSelect . ')' )), array('*')
	);

	return $select;
    }

    /**
     * @access protected
     * @param Zend_Db_Select $select
     * @return void
     */
    protected function _joinDefault( Zend_Db_Select $select )
    {
	$dbFEFOPPrograms = App_Model_DbTable_Factory::get( 'FEFOPPrograms' );
	$dbFEFOPModules = App_Model_DbTable_Factory::get( 'FEFOPModules' );
	$dbAddDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$dbSysUser = App_Model_DbTable_Factory::get( 'SysUser' );

	$mapper = new Fefop_Model_Mapper_Contract();

	$select->join(
		    $dbFEFOPPrograms->__toString(), 
		    'FEFOP_Programs.id_fefop_programs = FEFOP_Contract.fk_id_fefop_programs', 
		    array()
		)
		->join(
		    $dbFEFOPModules->__toString(), 
		    'FEFOP_Modules.id_fefop_modules = FEFOP_Contract.fk_id_fefop_modules', 
		    array()
		)
		->join(
		    $dbAddDistrict->__toString(), 
		    'AddDistrict.acronym = FEFOP_Contract.num_district', 
		    array()
		)
		->join(
		    $dbSysUser->__toString(), 
		    'SysUser.id_sysuser = FEFOP_Contract.fk_id_sysuser', 
		    array()
		)
		->join(
		    array( 's' => new Zend_Db_Expr( '(' . $this->_selectFEFOPStatus() . ')' )), 
		    's.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract', 
		    array()
		)
		->join(
		    array( 'b' => new Zend_Db_Expr( '(' . $mapper->getSelectBeneficiary() . ')' )), 
		    'b.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract AND b.target = 1', 
		    array()
		);
    }

    /**
     * Padrão de filtros para os relatórios FEFOP  
     * 
     * @access protected
     * @param Zend_Db_Select $select
     * @return void
     */
    protected function _whereDefault( Zend_Db_Select $select )
    {
	if ( !empty( $this->_data['fk_id_dec'] ) ) {
	    $select->where( 'SysUser.fk_id_dec IN(?)', $this->_data['fk_id_dec'] );
	}

	if ( !empty( $this->_data['id_fefop_programs'] ) ) {
	    $select->where( 'FEFOP_Contract.fk_id_fefop_programs IN(?)', $this->_data['id_fefop_programs'] );
	}

	if ( !empty( $this->_data['id_fefop_modules'] ) ) {
	    $select->where( 'FEFOP_Contract.fk_id_fefop_modules IN(?)', $this->_data['id_fefop_modules'] );
	}

	if ( !empty( $this->_data['id_adddistrict'] ) ) {
	    $select->where( 'AddDistrict.id_adddistrict IN(?)', $this->_data['id_adddistrict'] );
	}

	if ( !empty( $this->_data['id_scholarity_area'] ) ) {
	    $select->where( 'DRH_TrainingPlan.fk_id_scholarity_area = ?', $this->_data['id_scholarity_area'] );
	}

	if ( !empty( $this->_data['id_profocupationtimor'] ) ) {
	    $select->where( 'DRH_TrainingPlan.fk_id_profocupationtimor = ?', $this->_data['id_profocupationtimor'] );
	}

	if ( !empty( $this->_data['id_fefpeduinstitution'] ) ) {
	    $select->where( 'DRH_TrainingPlan.fk_id_fefpeduinstitution = ?', $this->_data['id_fefpeduinstitution'] );
	}

	if ( !empty( $this->_data['id_fefop_status'] ) ) {
	    $select->where( 's.id_fefop_status IN(?)', $this->_data['id_fefop_status'] );
	}

	if ( !empty( $this->_data['id_beneficiary'] ) ) {
	    $select->where( 'b.id = ?', $this->_data['id_beneficiary'] );
	}
    }

    /**
     * 
     * @return Zend_Db_Select
     */
    protected function _columnGender()
    {
	$dbPerData = App_Model_DbTable_Factory::get( 'PerData' );

	$select = $dbPerData->select()
		->from(
			$dbPerData->__toString(), array('gender')
		)
		->where( 'PerData.id_perdata = b.id' )
		->where( 'b.type = ?', array('CL') );

	return $select;
    }

    /**
     * Indica que o Beneficiário é portador de necessidades especiais
     *
     * @access protected
     * @return Zend_Db_Select
     */
    protected function _columnDisability()
    {
	$dbHandicapped = App_Model_DbTable_Factory::get( 'Handicapped' );

	$select = $dbHandicapped->select()
		->from(
			$dbHandicapped->__toString(), array(
		    'disability' => new Zend_Db_Expr( "CASE WHEN COUNT(1) > 0 AND b.type = 'CL' THEN 'Sin' WHEN b.type = 'CL' THEN 'Lae' ELSE '' END" )
			)
		)
		->where( 'handicapped.fk_id_perdata = b.id' )
		->where( 'b.type = ?', 'CL' );

	return $select;
    }

    /**
     * Valor contratado
     *
     * @access protected
     * @return Zend_Db_Select
     */
    protected function _columnAmountContracted()
    {
	$dbBudgetCategoryHasFEFOPContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );

	$select = $dbBudgetCategoryHasFEFOPContract->select()
		->from(
			$dbBudgetCategoryHasFEFOPContract->__toString(), array(new Zend_Db_Expr( 'IFNULL(SUM(amount), 0)' ))
		)
		->where( 'BudgetCategory_has_FEFOP_Contract.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract' );

	return $select;
    }

    /**
     * Valor pago
     * 
     * @access protected
     * @return Zend_Db_Select
     */
    protected function _columnAmouontPayment()
    {
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbBudgetCategoryHasFEFOPContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );

	$select = $dbFEFOPTransaction->select()
		->setIntegrityCheck( false )
		->from(
			$dbFEFOPTransaction->__toString(), array(new Zend_Db_Expr( "IFNULL(ABS(SUM(FEFOP_Transaction.amount * IF(FEFOP_Transaction.operation = 'D', -1, 1))), 0)" ))
		)
		->join(
			$dbBudgetCategoryHasFEFOPContract->__toString(), 'BudgetCategory_has_FEFOP_Contract.fk_id_fefop_contract = FEFOP_Transaction.fk_id_fefop_contract AND BudgetCategory_has_FEFOP_Contract.fk_id_budget_category = FEFOP_Transaction.fk_id_budget_category', array()
		)
		->where( 'FEFOP_Transaction.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract' );

	return $select;
    }

    /**
     * Valor real
     *
     * @access protected
     * @return Zend_Db_Select
     */
    protected function _columnAmountReal()
    {
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbBudgetCategoryHasFEFOPContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );

	$select = $dbFEFOPTransaction->select()
		->setIntegrityCheck( false )
		->from(
			$dbFEFOPTransaction->__toString(), array(new Zend_Db_Expr( "IFNULL(ABS(SUM(FEFOP_Transaction.amount * IF(FEFOP_Transaction.operation = 'D', -1, 1))), 0)" ))
		)
		->join(
			$dbBudgetCategoryHasFEFOPContract->__toString(), 'BudgetCategory_has_FEFOP_Contract.fk_id_budget_category = FEFOP_Transaction.fk_id_budget_category AND BudgetCategory_has_FEFOP_Contract.fk_id_fefop_contract = FEFOP_Transaction.fk_id_fefop_contract', array()
		)
		->where( 'FEFOP_Transaction.fk_id_budget_category_type <> ?', 3 )
		->where( 'FEFOP_Transaction.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract' );

	return $select;
    }

    /**
     * 
     * @access protected
     * @return Zend_Db_Select
     */
    protected function _columnAmountAddCosts()
    {
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );

	$select = $dbFEFOPTransaction->select()
		->from(
			$dbFEFOPTransaction->__toString(), array(new Zend_Db_Expr( "IFNULL(ABS(SUM(FEFOP_Transaction.amount * IF(FEFOP_Transaction.operation = 'D', -1, 1))), 0)" ))
		)
		->where( 'FEFOP_Transaction.fk_id_budget_category_type = ?', 3 )
		->where( 'FEFOP_Transaction.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract' );

	return $select;
    }

    /**
     * 
     * @return Zend_Db_Select
     */
    protected function _columnAdditional()
    {
	$dbFEFOPContractAdditional = App_Model_DbTable_Factory::get( 'FEFOPContractAdditional' );

	$select = $dbFEFOPContractAdditional->select()
		->from(
			$dbFEFOPContractAdditional->__toString(), array(new Zend_Db_Expr( 'IFNULL(SUM(amount), 0)' ))
		)
		->where( 'FEFOP_Contract_Additional.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract' );

	return $select;
    }

    /**
     * Padrão de Colunas para o relatório de Beneficiários 
     * 
     * @access protected
     * @param Zend_Db_Select $select
     * @return void
     */
    protected function _columnsDefault( Zend_Db_Select $select )
    {
	$select->reset( Zend_Db_Select::COLUMNS );

	$select->columns( array(
	    'FEFOP_Contract.id_fefop_contract',
	    'FEFOP_Contract.num_district',
	    'FEFOP_Contract.num_program',
	    'FEFOP_Contract.num_module',
	    'AddDistrict.id_adddistrict',
	    'AddDistrict.District',
	    's.status_description',
	    'id_perdata' => 'b.id',
	    'b.code',
	    'b.name',
	    'target' => new Zend_Db_Expr( "CASE WHEN b.target = 1 THEN 'Sin' ELSE 'Lae' END" ),
	    'module' => new Zend_Db_Expr( "CONCAT(FEFOP_Modules.acronym, ' - ', FEFOP_Modules.description)" ),
	    'program' => new Zend_Db_Expr( "CONCAT(FEFOP_Programs.acronym, ' - ', FEFOP_Programs.description)" ),
	    'cod_contract' => new Zend_Db_Expr( "CONCAT(FEFOP_Contract.num_program, '-', FEFOP_Contract.num_module, '-', FEFOP_Contract.num_district, '-', FEFOP_Contract.num_year, '-', FEFOP_Contract.num_sequence)" ),
	    'disability' => new Zend_Db_Expr( '(' . $this->_columnDisability() . ')' ),
	    'gender' => new Zend_Db_Expr( '(' . $this->_columnGender() . ')' ),
	    //Valor contrato
	    'amount_contracted' => new Zend_Db_Expr( '(' . $this->_columnAmountContracted() . ')' ),
	    //Valor pago
	    'amount_payment' => new Zend_Db_Expr( '(' . $this->_columnAmouontPayment() . ')' ),
	    //Valor financiado
	    'amount_real' => new Zend_Db_Expr( '(' . $this->_columnAmountReal() . ')' ),
	    //Custos acrescidos
	    //'amount_addcosts'   => new Zend_Db_Expr('(' . $this->_columnAmountAddCosts() . ')'),
	    'amount_addcosts' => new Zend_Db_Expr( '(' . $this->_columnAdditional() . ')' ),
	) );

	$select->group( array(
	    'FEFOP_Contract.id_fefop_contract',
	) );
    }

    /**
     * @access protected
     * @param Zend_Db_Select $sql
     * @return array
     */
    protected function getArrayFund( Zend_Db_Select $sql )
    {
	$adapter = App_Model_DbTable_Abstract::getDefaultAdapter();

	$subSelect = $adapter->select()
		->distinct( true )
		->from(
			array('t' => new Zend_Db_Expr( '(' . $sql . ')' )), array('id_fefopfund')
		)
		->where( 'id_fefopfund IS NOT NULL' );

	$dbFEFOPFund = App_Model_DbTable_Factory::get( 'FEFOPFund' );

	$select = $dbFEFOPFund->select()
		->setIntegrityCheck( false )
		->from(
			$dbFEFOPFund, array('id_fefopfund', 'name_fund', 'type')
		)
		->where( 'id_fefopfund IN(?)', new Zend_Db_Expr( '(' . $subSelect . ')' ) )
		->order( 'id_fefopfund' );


	$rows = $dbFEFOPFund->fetchAll( $select );

	$fund = array();

	foreach ( $rows as $row ) {
	    $fund[$row['type']][$row['id_fefopfund']] = $row['name_fund'];
	}

	return $fund;
    }

    /**
     * 
     * @param Zend_Db_Select $sql
     * @return array
     */
    protected function getArrayCategory( Zend_Db_Select $sql )
    {
	$adapter = App_Model_DbTable_Abstract::getDefaultAdapter();

	$subSelect = $adapter->select()
		->distinct( true )
		->from(
			array('t' => new Zend_Db_Expr( '(' . $sql . ')' )), array('fk_id_budget_category')
		)
		->where( 'fk_id_budget_category IS NOT NULL' );

	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );

	$select = $dbBudgetCategory->select()
		->from(
			$dbBudgetCategory, array('id_budget_category', 'description')
		)
		->where( 'id_budget_category IN(?)', new Zend_Db_Expr( '(' . $subSelect . ')' ) )
		->order( 'id_budget_category ASC' );

	$rows = $dbBudgetCategory->fetchAll( $select );

	$category = array();

	foreach ( $rows as $row ) {
	    $category[$row['id_budget_category']] = $row['description'];
	}

	return $category;
    }

    /**
     * 
     * @param Zend_Db_Select $sql
     * @return array
     */
    protected function getArrayCategoryType( Zend_Db_Select $sql )
    {
	$adapter = App_Model_DbTable_Abstract::getDefaultAdapter();

	$subSelect = $adapter->select()
		->distinct( true )
		->from(
			array('t' => new Zend_Db_Expr( '(' . $sql . ')' )), array('id_budget_category_type')
		)
		->where( 'id_budget_category_type IS NOT NULL' );

	$dbBudgetCategoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );

	$select = $dbBudgetCategoryType->select()
		->from(
			$dbBudgetCategoryType, array('id_budget_category_type', 'description')
		)
		->where( 'id_budget_category_type IN(?)', new Zend_Db_Expr( '(' . $subSelect . ')' ) )
		->order( 'id_budget_category_type ASC' );

	$rows = $dbBudgetCategoryType->fetchAll( $select );

	$category = array();

	foreach ( $rows as $row ) {
	    $category[$row['id_budget_category_type']] = $row['description'];
	}

	return $category;
    }

    /**
     * @access protected
     * @return void
     */
    protected function getDataReport()
    {
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );

	$select = $dbFEFOPContract->select()
		->setIntegrityCheck( false )
		->from(
		$dbFEFOPContract->__toString(), array()
	);

	$this->_joinDefault( $select );

	//Período
	$select->where( "DATE(FEFOP_Contract.date_inserted) >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'] );
	$select->where( "DATE(FEFOP_Contract.date_inserted) <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish'] );

	$this->_whereDefault( $select );

	$this->_columnsDefault( $select );

	$adapter = App_Model_DbTable_Abstract::getDefaultAdapter();

	return $adapter->fetchAll( $select );
    }

    /**
     * 
     * @access public
     * @return array
     */
    public function beneficiaryAnalyticReport()
    {
	return array('rows' => $this->getDataReport());
    }

    /**
     * 
     * @access public
     * @return void
     */
    public function beneficiarySyntheticReport()
    {
	$rows = $this->getDataReport();

	if ( empty( $rows ) ) {
	    return array('item' => null);
	}

	$idPerData = array();

	$idFEFOPContract = array();

	$data = array(
	    'gender' => array(
		'mane' => 0,
		'feto' => 0
	    ),
	    'disability' => array(
		'yes' => 0,
		'no' => 0
	    ),
	    'district' => array(),
	    'modules' => array(),
	    'certificate' => array(),
	    'amount' => array(
		'contracted' => 0,
		'funded' => 0,
		'addcosts' => 0,
	    ),
	);

	foreach ( $rows as $row ) {

	    if ( !in_array( $row['id_fefop_contract'], $idFEFOPContract ) ) {
		$data['amount']['contracted'] += App_General_String::toFloat( $row['amount_contracted'] );
		$data['amount']['funded'] += App_General_String::toFloat( $row['amount_real'] );
		$data['amount']['addcosts'] += App_General_String::toFloat( $row['amount_addcosts'] );
	    }

	    array_push( $idPerData, $row['id_perdata'] );

	    array_push( $idFEFOPContract, $row['id_fefop_contract'] );

	    //Gênero do beneficiário
	    $data['gender']['mane'] += ('MANE' === $row['gender']) ? 1 : 0;
	    $data['gender']['feto'] += ('FETO' === $row['gender']) ? 1 : 0;

	    //Distrito
	    if ( in_array( $row['id_adddistrict'], array_keys( $data['district'] ) ) ) {

		$data['district'][$row['id_adddistrict']]['count'] += 1;
	    } else {

		$data['district'][$row['id_adddistrict']] = array(
		    'name' => $row['District'],
		    'count' => 1
		);
	    }

	    //Necessidades especiais
	    $data['disability']['yes'] += ('Sin' == $row['disability']) ? 1 : 0;
	    $data['disability']['no'] += ('Lae' == $row['disability']) ? 1 : 0;

	    //Modulos
	    $key = serialize( array('num_module' => $row['num_module'], 'num_program' => $row['num_program']) );

	    if ( in_array( $key, array_keys( $data['modules'] ) ) ) {
		$data['modules'][$key] += 1;
	    } else {
		$data['modules'][$key] = 1;
	    }
	}

	//Certificação Nacional
	$dbPerScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbPerScholarityHasPerTypeScholarity = App_Model_DbTable_Factory::get( 'PerScholarityHasPerTypeScholarity' );

	$select = $dbPerScholarity->select()
		->setIntegrityCheck( false )
		->from(
			$dbPerScholarity->__toString(), array(
		    'external_code',
		    'scholarity',
		    'total' => new Zend_Db_Expr( 'COUNT(1)' )
			)
		)
		->join(
			$dbPerScholarityHasPerTypeScholarity->__toString(), 'PerScholarity_has_PerTypeScholarity.fk_id_perscholarity = PerScholarity.id_perscholarity', array()
		)
		->where( 'PerScholarity.category = ?', 'N' )
		->where( 'PerScholarity_has_PerTypeScholarity.fk_id_perdata IN(?)', $idPerData )
		->group( 'PerScholarity.id_perscholarity' );

	$rows = $dbPerScholarity->fetchAll( $select );

	$data['certificate'] = $rows->toArray();

	return array('item' => $data);
    }

    /**
     * @access public
     * @return array
     */
    public function contractReport()
    {
	return array('rows' => $this->getDataReport());
    }

    /**
     * Formações por país
     * 
     * @access public
     * @return array
     */
    public function trainingCountryReport()
    {
	$dbAddCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$dbDRHTrainingPlan = App_Model_DbTable_Factory::get( 'DRHTrainingPlan' );
	$dbDRHContract = App_Model_DbTable_Factory::get( 'DRHContract' );
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	$dbSysUser = App_Model_DbTable_Factory::get( 'SysUser' );

	$select = $dbAddCountry->select()
		->setIntegrityCheck( false )
		->from(
			$dbAddCountry->__toString(), array(
		    'country',
		    'total' => new Zend_Db_Expr( 'COUNT(1)' ),
			)
		)
		->join(
			$dbDRHTrainingPlan->__toString(), 'DRH_TrainingPlan.fk_id_addcountry = AddCountry.id_addcountry', array()
		)
		->join(
			$dbDRHContract->__toString(), 'DRH_Contract.fk_id_drh_trainingplan = DRH_TrainingPlan.id_drh_trainingplan', array()
		)
		->join(
			$dbFEFOPContract->__toString(), 'FEFOP_Contract.id_fefop_contract = DRH_Contract.fk_id_fefop_contract', array()
		)
		->join(
			$dbSysUser->__toString(), 'SysUser.id_sysuser = FEFOP_Contract.fk_id_sysuser', array()
		)
		->where( "DATE(FEFOP_Contract.date_inserted) >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'] )
		->where( "DATE(FEFOP_Contract.date_inserted) <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish'] )
		->group( 'AddCountry.id_addcountry' );

	$dbBudgetCategoryHasFEFOPContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );

	//Valor Contratado
	$subSelect = $dbBudgetCategoryHasFEFOPContract->select()
		->from(
			$dbBudgetCategoryHasFEFOPContract->__toString(), array(new Zend_Db_Expr( 'SUM(amount)' ))
		)
		->where( 'BudgetCategory_has_FEFOP_Contract.fk_id_fefop_contract = DRH_Contract.fk_id_fefop_contract' );

	$select->columns( array('amount_contracted' => new Zend_Db_Expr( '(' . $subSelect . ')' )) );

	//Valor Pago
	$subSelect = $dbFEFOPTransaction->select()
		->from(
			$dbFEFOPTransaction->__toString(), array(new Zend_Db_Expr( "IFNULL(ABS(SUM(FEFOP_Transaction.amount * IF(FEFOP_Transaction.operation = 'D', -1, 1))), 0)" ))
		)
		->join(
			$dbBudgetCategoryHasFEFOPContract->__toString(), 'BudgetCategory_has_FEFOP_Contract.fk_id_fefop_contract = FEFOP_Transaction.fk_id_fefop_contract AND BudgetCategory_has_FEFOP_Contract.fk_id_budget_category = FEFOP_Transaction.fk_id_budget_category', array()
		)
		->where( 'FEFOP_Transaction.fk_id_fefop_contract = DRH_Contract.fk_id_fefop_contract' );

	$select->columns( array('amount' => new Zend_Db_Expr( '(' . $subSelect . ')' )) );

	$this->_whereDefault( $select );

	$rows = $dbDRHContract->fetchAll( $select );

	return array('rows' => $rows->toArray());
    }

    /**
     * @access public
     * @return array
     */
    public function blackListReport()
    {
	$mapper = new Fefop_Model_Mapper_BeneficiaryBlacklist();

	$rows = $mapper->listByFilters( $this->_data );

	return array('rows' => $rows);
    }

    /**
     * @access public
     * @return array
     */
    public function financialContractReport()
    {
	$mapper = new Fefop_Model_Mapper_Financial();

	$rows = $mapper->listByFilters( $this->_data );

	return array('rows' => $rows->toArray());
    }

    /**
     * @access public
     * @return array
     */
    public function costReport()
    {
	$arrFEFOPContract = array();

	foreach ( $this->getDataReport() as $data ) {
	    if ( !in_array( $data['id_fefop_contract'], $arrFEFOPContract ) ) {
		array_push( $arrFEFOPContract, $data['id_fefop_contract'] );
	    }
	}

	if ( empty( $arrFEFOPContract ) ) {
	    return array('rows' => null);
	}

	$dbBudgetCategoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbBudgetCategoryHasFEFOPContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );

	$select = $dbBudgetCategoryType->select()
		->setIntegrityCheck( false )
		->from(
			$dbBudgetCategoryType->__toString(), array('category_type' => 'description')
		)
		->join(
			$dbBudgetCategory->__toString(), 'BudgetCategory.fk_id_budget_category_type = BudgetCategoryType.id_budget_category_type', array('category' => 'description')
		)
		->join(
			$dbBudgetCategoryHasFEFOPContract->__toString(), 'BudgetCategory_has_FEFOP_Contract.fk_id_budget_category = BudgetCategory.id_budget_category', array('amount' => new Zend_Db_Expr( 'SUM(amount)' ))
		)
		->where( 'BudgetCategory_has_FEFOP_Contract.status = ?', 1 )
		->where( 'BudgetCategory_has_FEFOP_Contract.fk_id_fefop_contract IN(?)', $arrFEFOPContract );

	if ( !empty( $this->_data['id_budget_category_type'] ) ) {
	    $select->where( 'BudgetCategoryType.id_budget_category_type = ?', $this->_data['id_budget_category_type'] );
	}

	$select->group( array(
	    'BudgetCategoryType.id_budget_category_type',
	    'BudgetCategory.id_budget_category'
	) );

	$rows = $dbBudgetCategoryType->fetchAll( $select );

	return array('rows' => $rows->toArray());
    }

    /**
     * @access public
     * @return array
     */
    public function balanceSourceReport()
    {
	$dbFEFOPFund = App_Model_DbTable_Factory::get( 'FEFOPFund' );
	$dbFEFOPContractAdditional = App_Model_DbTable_Factory::get( 'FEFOPContractAdditional' );
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbFundPlanning = App_Model_DbTable_Factory::get( 'FundPlanning' );
	$dbFundPlanningModule = App_Model_DbTable_Factory::get( 'FundPlanningModule' );
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	$dbFEFOPContractFund = App_Model_DbTable_Factory::get( 'FEFOPContractFund' );
	$dbBudgetCategoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	$dbFEFOPBankStatements = App_Model_DbTable_Factory::get( 'FEFOPBankStatements' );

	//Fundos
	$subSelect = $dbFEFOPBankStatements->select()
		->from(
			$dbFEFOPBankStatements->__toString(), array(new Zend_Db_Expr( 'IFNULL(SUM(IFNULL(FEFOP_Bank_Statements.amount, 0)), 0)' ))
		)
		->where( 'FEFOP_Bank_Statements.fk_id_fefop_type_transaction = ?', 3 )
		->where( 'FEFOP_Bank_Statements.fk_id_fefopfund = FEFOPFund.id_fefopfund' );

	$select = $dbFEFOPFund->select()
		->setIntegrityCheck( false )
		->from(
			$dbFEFOPFund->__toString(), array(
		    'id_fefopfund',
		    'name_fund',
		    'transaction' => new Zend_Db_Expr( '(' . $subSelect . ')' )
			)
		)
		->join(
			$dbFundPlanning->__toString(), 'FundPlanning.fk_id_fefopfund = FEFOPFund.id_fefopfund', array('budget' => new Zend_Db_Expr( 'IFNULL(FundPlanning.amount, 0)' ))
		)
		->join(
			$dbFundPlanningModule->__toString(), 'FundPlanningModule.fk_id_fund_planning = FundPlanning.id_fund_planning', array()
		)
		->join(
			$dbFEFOPContract->__toString(), 'FEFOP_Contract.fk_id_fefop_modules = FundPlanningModule.fk_id_fefop_modules', array()
		)
		->join(
			$dbFEFOPContractFund->__toString(), 'FEFOP_Contract_Fund.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract', array()
		)
		->joinLeft(
			$dbBudgetCategoryType->__toString(), 'BudgetCategoryType.id_budget_category_type = FEFOP_Contract_Fund.fk_id_budget_category_type', array('id_budget_category_type')
		)
		->join(
		array('s' => new Zend_Db_Expr( '(' . $this->_selectFEFOPStatus() . ')' )), 's.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract', array()
	);

	if ( !empty( $this->_data['year_start'] ) ) {
	    $select->where( 'FundPlanning.year_planning >= ?', $this->_data['year_start'] );
	}

	if ( !empty( $this->_data['year_finish'] ) ) {
	    $select->where( 'FundPlanning.year_planning <= ?', $this->_data['year_finish'] );
	}

	if ( !empty( $this->_data['id_fefop_programs'] ) ) {
	    $select->where( 'FEFOP_Contract.fk_id_fefop_programs IN(?)', $this->_data['id_fefop_programs'] );
	}

	if ( !empty( $this->_data['id_fefop_modules'] ) ) {
	    $select->where( 'FEFOP_Contract.fk_id_fefop_modules IN(?)', $this->_data['id_fefop_modules'] );
	}

	if ( !empty( $this->_data['id_fefop_status'] ) ) {
	    $select->where( 's.id_fefop_status IN(?)', $this->_data['id_fefop_status'] );
	}

	if ( !empty( $this->_data['type_fefopfund'] ) ) {
	    $select->where( 'FEFOPFund.type = ?', $this->_data['type_fefopfund'] );
	}

	if ( !empty( $this->_data['id_budget_category_type'] ) ) {
	    $select->where( 'BudgetCategoryType.id_budget_category_type = ?', $this->_data['id_budget_category_type'] );
	}

	$select->group( array(
	    'FEFOPFund.id_fefopfund',
	    'BudgetCategoryType.id_budget_category_type',
	) );

	$rows = $dbFEFOPFund->fetchAll( $select );

	if ( $rows->count() == 0 ) {
	    return array();
	}

	$fund = array();
	$category = array();

	foreach ( $rows as $row ) {

	    if ( !in_array( $row->id_budget_category_type, $category ) ) {
		array_push( $category, $row->id_budget_category_type );
	    }

	    if ( empty( $fund[$row->id_fefopfund] ) ) {
		$fund[$row->id_fefopfund] = array(
		    'name' => $row->name_fund,
		    'transaction' => $row->transaction,
		    'budget' => $row->budget,
		    'category' => array(),
		);
	    }
	}

	//Categorias
	$subSelect = $dbFEFOPContractAdditional->select()
		->setIntegrityCheck( false )
		->from(
			$dbFEFOPContractAdditional->__toString(), array(new Zend_Db_Expr( 'IFNULL(SUM(FEFOP_Contract_Additional.amount), 0)' ))
		)
		->join(
			$dbFEFOPTransaction->__toString(), 'FEFOP_Contract_Additional.fk_id_budget_category = FEFOP_Transaction.fk_id_budget_category', array()
		)
		->where( 'FEFOP_Transaction.fk_id_budget_category_type = FEFOP_Contract_Fund.fk_id_budget_category_type' )
		->where( 'FEFOP_Contract_Additional.fk_id_fefopfund = FEFOP_Contract_Fund.fk_id_fefopfund' );

	$select = $dbFEFOPContractFund->select()
		->from(
			$dbFEFOPContractFund->__toString(), array(
		    'id_fefopfund' => 'FEFOP_Contract_Fund.fk_id_fefopfund',
		    'id_budget_category_type' => 'FEFOP_Contract_Fund.fk_id_budget_category_type',
		    'real' => new Zend_Db_Expr( 'SUM(FEFOP_Contract_Fund.real_amount)' ),
		    'contract' => new Zend_Db_Expr( 'SUM(FEFOP_Contract_Fund.contract_amount)' ),
		    'additional' => new Zend_Db_Expr( '(' . $subSelect . ')' ),
			)
		)
		->where( 'FEFOP_Contract_Fund.fk_id_budget_category_type IN(?)', $category )
		->where( 'FEFOP_Contract_Fund.fk_id_fefopfund IN(?)', array_keys( $fund ) )
		->group( array(
		    'FEFOP_Contract_Fund.fk_id_fefopfund',
		    'FEFOP_Contract_Fund.fk_id_budget_category_type'
		) )
		->order( array(
	    'FEFOP_Contract_Fund.fk_id_budget_category_type'
	) );

	$rows = $dbFEFOPContractFund->fetchAll( $select );

	$total = array();

	foreach ( $rows as $row ) {

	    $fund[$row->id_fefopfund]['category'][$row->id_budget_category_type] = array(
		'real' => $row->real,
		'contract' => $row->contract,
		'additional' => $row->additional,
	    );

	    //Totalizador
	    if ( empty( $total[$row->id_budget_category_type]['real'] ) ) {
		$total[$row->id_budget_category_type]['real'] = 0;
	    }

	    if ( empty( $total[$row->id_budget_category_type]['contract'] ) ) {
		$total[$row->id_budget_category_type]['contract'] = 0;
	    }

	    if ( empty( $total[$row->id_budget_category_type]['additional'] ) ) {
		$total[$row->id_budget_category_type]['additional'] = 0;
	    }

	    $total[$row->id_budget_category_type]['real'] += App_General_String::toFloat( $row->real );
	    $total[$row->id_budget_category_type]['contract'] += App_General_String::toFloat( $row->contract );
	    $total[$row->id_budget_category_type]['additional'] += App_General_String::toFloat( $row->additional );
	}

	return array(
	    'item' => array(
		'fund' => $fund,
		'category' => $this->getArrayCategoryType( $select ),
		'total' => $total,
	    ),
	);
    }

    /**
     * Financiamento por Contrato x Componente
     * 
     * @access public
     * @return Array
     */
    public function contractComponentReport()
    {
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbBudgetCategoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	$dbBudgetCategoryHasFEFOPContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );

	$select = $dbFEFOPContract->select()
		->setIntegrityCheck( false )
		->from(
			$dbFEFOPContract->__toString(), array(
		    'id_fefop_contract',
		    'cod_contract' => new Zend_Db_Expr( "CONCAT(FEFOP_Contract.num_program, '-', FEFOP_Contract.num_module, '-', FEFOP_Contract.num_district, '-', FEFOP_Contract.num_year, '-', FEFOP_Contract.num_sequence)" ),
			)
		)
		->join(
			$dbFEFOPTransaction->__toString(), 'FEFOP_Transaction.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract', array()
		)
		->join(
		$dbBudgetCategoryType->__toString(), 'BudgetCategoryType.id_budget_category_type = FEFOP_Transaction.fk_id_budget_category_type', array('id_budget_category_type')
	);

	$this->_joinDefault( $select );

	$select->columns( array(
	    's.status_description',
	    'b.name',
	    'module' => new Zend_Db_Expr( "CONCAT(FEFOP_Modules.acronym, ' - ', FEFOP_Modules.description)" ),
	    'program' => new Zend_Db_Expr( "CONCAT(FEFOP_Programs.acronym, ' - ', FEFOP_Programs.description)" ),
	) );

	//Pagamentos
	$subSelect = $dbFEFOPTransaction->select()
		->from(
			array('p' => $dbFEFOPTransaction), array(new Zend_Db_Expr( "IFNULL(ABS(SUM(p.amount * IF(p.operation = 'D', -1, 1))), 0)" ))
		)
		->join(
			array('bcc' => $dbBudgetCategoryHasFEFOPContract), 'bcc.fk_id_budget_category = p.fk_id_budget_category AND bcc.fk_id_fefop_contract = p.fk_id_fefop_contract', array()
		)
		->where( 'p.fk_id_fefop_type_transaction = ?', 1 )
		->where( 'p.fk_id_budget_category_type = BudgetCategoryType.id_budget_category_type' )
		->where( 'p.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract' );

	$select->columns( array('payments' => new Zend_Db_Expr( '(' . $subSelect . ')' )) );

	//Devoluções
	$subSelect = $dbFEFOPTransaction->select()
		->from(
			array('d' => $dbFEFOPTransaction), array(new Zend_Db_Expr( "IFNULL(ABS(SUM(d.amount * IF(d.operation = 'D', -1, 1))), 0)" ))
		)
		->join(
			array('bcc' => $dbBudgetCategoryHasFEFOPContract), 'bcc.fk_id_budget_category = d.fk_id_budget_category AND bcc.fk_id_fefop_contract = d.fk_id_fefop_contract', array()
		)
		->where( 'd.fk_id_fefop_type_transaction = ?', 2 )
		->where( 'd.fk_id_budget_category_type = BudgetCategoryType.id_budget_category_type' )
		->where( 'd.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract' );

	$select->columns( array('repayments' => new Zend_Db_Expr( '(' . $subSelect . ')' )) );

	$this->_whereDefault( $select );

	//Período
	$select->where( "DATE(FEFOP_Transaction.date_reference) >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'] );
	$select->where( "DATE(FEFOP_Transaction.date_reference) <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish'] );

	if ( !empty( $this->_data['id_budget_category_type'] ) ) {
	    $select->where( 'BudgetCategoryType.id_budget_category_type = ?', $this->_data['id_budget_category_type'] );
	}

	$select->group( array(
	    'FEFOP_Contract.id_fefop_contract',
	    'BudgetCategoryType.id_budget_category_type'
	) );

	$rows = $dbFEFOPContract->fetchAll( $select );

	if ( empty( $rows ) ) {
	    return array();
	}

	$contract = array();
	$total = array();

	foreach ( $rows as $row ) {

	    if ( empty( $contract[$row['id_fefop_contract']] ) ) {
		$contract[$row['id_fefop_contract']] = array(
		    'contract' => $row['cod_contract'],
		    'beneficiary' => $row['name'],
		    'status' => $row['status_description'],
		    'program' => $row['program'],
		    'module' => $row['module'],
		    'category' => array(),
		    'total' => 0,
		);
	    }

	    if ( !array_key_exists( $row['id_budget_category_type'], $total ) ) {
		$total[$row['id_budget_category_type']] = array(
		    'payments' => 0,
		    'repayments' => 0,
		    'total' => 0,
		);
	    }

	    $total[$row['id_budget_category_type']]['payments'] += App_General_String::toFloat( $row['payments'] );
	    $total[$row['id_budget_category_type']]['repayments'] += App_General_String::toFloat( $row['repayments'] );
	    $total[$row['id_budget_category_type']]['total'] += (App_General_String::toFloat( $row['payments'] ) - App_General_String::toFloat( $row['repayments'] ));

	    if ( !empty( $row['id_budget_category_type'] ) ) {
		$contract[$row['id_fefop_contract']]['category'][$row['id_budget_category_type']]['payments'] = App_General_String::toFloat( $row['payments'] );
		$contract[$row['id_fefop_contract']]['category'][$row['id_budget_category_type']]['repayments'] = App_General_String::toFloat( $row['repayments'] );
		$contract[$row['id_fefop_contract']]['category'][$row['id_budget_category_type']]['total'] = App_General_String::toFloat( $row['payments'] ) - App_General_String::toFloat( $row['repayments'] );
	    }

	    if ( !empty( $row['id_budget_category_type'] ) ) {
		$contract[$row['id_fefop_contract']]['total'] += App_General_String::toFloat( $row['payments'] ) - App_General_String::toFloat( $row['repayments'] );
	    }
	}

	ksort( $total );

	return array(
	    'item' => array(
		'contract' => $contract,
		'category' => $this->getArrayCategoryType( $select ),
		'total' => $total,
	    ),
	);
    }

    /**
     * Financiamento por Contrato x Componente
     *
     * @access public
     * @return Array
     */
    public function fundReport()
    {
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbFEFOPContractFund = App_Model_DbTable_Factory::get( 'FEFOPContractFund' );
	$dbBudgetCategoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbBudgetCategoryHasFEFOPContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	$dbFEFOPFund = App_Model_DbTable_Factory::get( 'FEFOPFund' );

	$subSelect = $dbFEFOPContractFund->select()
		->setIntegrityCheck( false )
		->from(
			$dbFEFOPContractFund->__toString(), array(
		    'amount' => new Zend_Db_Expr( 'SUM(DISTINCT FEFOP_Contract_Fund.real_amount)' ),
		    'fk_id_fefopfund',
		    'fk_id_fefop_contract'
			)
		)
		->join(
			$dbBudgetCategoryType->__toString(), 'BudgetCategoryType.id_budget_category_type = FEFOP_Contract_Fund.fk_id_budget_category_type', array()
		)
		->join(
			$dbBudgetCategory->__toString(), 'BudgetCategory.fk_id_budget_category_type = BudgetCategoryType.id_budget_category_type', array()
		)
		->join(
			$dbBudgetCategoryHasFEFOPContract->__toString(), 'BudgetCategory_has_FEFOP_Contract.fk_id_budget_category = BudgetCategory.id_budget_category', array()
		)
		->group( array(
	    'FEFOP_Contract_Fund.fk_id_fefopfund',
	    'FEFOP_Contract_Fund.fk_id_fefop_contract'
	) );

	$select = $dbFEFOPContract->select()
		->setIntegrityCheck( false )
		->from(
			$dbFEFOPContract, array(
		    'id_fefop_contract',
		    'cod_contract' => new Zend_Db_Expr( "CONCAT(FEFOP_Contract.num_program, '-', FEFOP_Contract.num_module, '-', FEFOP_Contract.num_district, '-', FEFOP_Contract.num_year, '-', FEFOP_Contract.num_sequence)" ),
			)
		)
		->join(
			array('fcf' => new Zend_Db_Expr( '(' . $subSelect . ')' )), 'fcf.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract', array('amount')
		)
		->join(
		$dbFEFOPFund->__toString(), 'FEFOPFund.id_fefopfund = fcf.fk_id_fefopfund', array('id_fefopfund')
	);

	$this->_joinDefault( $select );

	$select->columns( array(
	    's.status_description',
	    'b.name',
	    'b.code',
	    'module' => new Zend_Db_Expr( "CONCAT(FEFOP_Modules.acronym, ' - ', FEFOP_Modules.description)" ),
	    'program' => new Zend_Db_Expr( "CONCAT(FEFOP_Programs.acronym, ' - ', FEFOP_Programs.description)" ),
	) );

	$this->_whereDefault( $select );

	//Período
	$select->where( "DATE(FEFOP_Contract.date_inserted) >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'] );
	$select->where( "DATE(FEFOP_Contract.date_inserted) <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish'] );

	if ( !empty( $this->_data['type_fefopfund'] ) ) {
	    $select->where( 'FEFOPFund.type = ?', $this->_data['type_fefopfund'] );
	}

	$select->group( array(
		    'FEFOP_Contract.id_fefop_contract',
		    'FEFOPFund.id_fefopfund',
		) )
		->order( 'FEFOPFund.id_fefopfund' );

	$rows = $dbFEFOPContract->fetchAll( $select );

	if ( empty( $rows ) ) {
	    return array();
	}

	$contract = array();
	$total = array();

	foreach ( $rows as $row ) {

	    if ( empty( $contract[$row['id_fefop_contract']] ) ) {

		$contract[$row['id_fefop_contract']] = array(
		    'contract' => $row['cod_contract'],
		    'beneficiary' => $row['name'],
		    'status' => $row['status_description'],
		    'program' => $row['program'],
		    'module' => $row['module'],
		    'fund' => array(),
		    'total' => 0,
		);
	    }

	    if ( !array_key_exists( $row['id_fefopfund'], $total ) ) {
		$total[$row['id_fefopfund']] = 0;
	    }

	    $total[$row['id_fefopfund']] += App_General_String::toFloat( $row['amount'] );

	    if ( !empty( $row['id_fefopfund'] ) ) {
		$contract[$row['id_fefop_contract']]['fund'][$row['id_fefopfund']] = App_General_String::toFloat( $row['amount'] );
		$contract[$row['id_fefop_contract']]['total'] += App_General_String::toFloat( $row['amount'] );
	    }
	}

	return array(
	    'item' => array(
		'contract' => $contract,
		'fund' => $this->getArrayFund( $select ),
		'total' => $total,
	    ),
	);
    }

    /**
     * @access public
     * @return array
     */
    public function repaymentsReport()
    {
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbFEFOPContractFund = App_Model_DbTable_Factory::get( 'FEFOPContractFund' );
	$dbFEFOPFund = App_Model_DbTable_Factory::get( 'FEFOPFund' );
	$dbBudgetCategoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbBudgetCategoryHasFEFOPContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );

	$select = $dbFEFOPContract->select()
		->setIntegrityCheck( false )
		->from(
		$dbFEFOPContract, array(
	    'id_fefop_contract',
	    'cod_contract' => new Zend_Db_Expr( "CONCAT(FEFOP_Contract.num_program, '-', FEFOP_Contract.num_module, '-', FEFOP_Contract.num_district, '-', FEFOP_Contract.num_year, '-', FEFOP_Contract.num_sequence)" )
		)
	);

	$this->_joinDefault( $select );

	$subSelect = $dbFEFOPContractFund->select()
		->from(
			$dbFEFOPContractFund, array(
		    'amount' => new Zend_Db_Expr( 'SUM(DISTINCT FEFOP_Contract_Fund.real_amount)' ),
		    'fk_id_fefopfund',
		    'fk_id_fefop_contract'
			)
		)
		->join(
			$dbBudgetCategoryType->__toString(), 'BudgetCategoryType.id_budget_category_type = FEFOP_Contract_Fund.fk_id_budget_category_type', array()
		)
		->join(
			$dbBudgetCategory->__toString(), 'BudgetCategory.fk_id_budget_category_type = BudgetCategoryType.id_budget_category_type', array()
		)
		->join(
			$dbBudgetCategoryHasFEFOPContract->__toString(), 'BudgetCategory_has_FEFOP_Contract.fk_id_budget_category = BudgetCategory.id_budget_category', array()
		)
		->group( array(
	    'FEFOP_Contract_Fund.fk_id_fefopfund',
	    'FEFOP_Contract_Fund.fk_id_fefop_contract'
	) );

	$select->join(
			array('fcf' => new Zend_Db_Expr( '(' . $subSelect . ')' )), 'fcf.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract', array('amount')
		)
		->join(
			$dbFEFOPFund->__toString(), 'FEFOPFund.id_fefopfund = fcf.fk_id_fefopfund', array('id_fefopfund')
	);

	$select->columns( array(
	    's.status_description',
	    'b.name',
	    'module' => new Zend_Db_Expr( "CONCAT(FEFOP_Modules.acronym, ' - ', FEFOP_Modules.description)" ),
	    'program' => new Zend_Db_Expr( "CONCAT(FEFOP_Programs.acronym, ' - ', FEFOP_Programs.description)" ),
	) );

	//Pagamentos
	$subSelect = $dbFEFOPTransaction->select()
		->from(
			array('p' => $dbFEFOPTransaction), array(new Zend_Db_Expr( "IFNULL(ABS(SUM(p.amount * IF(p.operation = 'D', -1, 1))), 0)" ))
		)
		->join(
			array('bcc' => $dbBudgetCategoryHasFEFOPContract), 'p.fk_id_fefop_contract = bcc.fk_id_fefop_contract AND bcc.fk_id_budget_category = p.fk_id_budget_category', array()
		)
		->where( 'p.fk_id_fefop_type_transaction = ?', 1 )
		->where( "DATE(p.date_reference) >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'] )
		->where( "DATE(p.date_reference) <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish'] )
		->where( 'p.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract' );

	$select->columns( array('payments' => new Zend_Db_Expr( '(' . $subSelect . ')' )) );

	//Devoluções
	$subSelect = $dbFEFOPTransaction->select()
		->from(
			array('d' => $dbFEFOPTransaction), array(new Zend_Db_Expr( "IFNULL(ABS(SUM(d.amount * IF(d.operation = 'D', -1, 1))), 0)" ))
		)
		->where( 'd.fk_id_fefop_type_transaction = ?', 2 )
		->where( "DATE(d.date_reference) >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'] )
		->where( "DATE(d.date_reference) <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish'] )
		->where( 'd.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract' );

	$select->columns( array('repayments' => new Zend_Db_Expr( '(' . $subSelect . ')' )) );

	$this->_whereDefault( $select );

	if ( !empty( $this->_data['type_fefopfund'] ) ) {
	    $select->where( 'FEFOPFund.type = ?', $this->_data['type_fefopfund'] );
	}

	$select->group( array(
		    'FEFOP_Contract.id_fefop_contract',
		    'FEFOPFund.id_fefopfund',
		) )
		->order( 'FEFOPFund.id_fefopfund' );

	$rows = $dbFEFOPContract->fetchAll( $select );

	if ( empty( $rows ) ) {
	    return array();
	}

	$contract = array();
	$total = array();

	foreach ( $rows as $row ) {

	    if ( empty( $contract[$row['id_fefop_contract']] ) ) {
		$contract[$row['id_fefop_contract']] = array(
		    'contract' => $row['cod_contract'],
		    'beneficiary' => $row['name'],
		    'status' => $row['status_description'],
		    'program' => $row['program'],
		    'module' => $row['module'],
		    'repayments' => App_General_String::toFloat( $row['repayments'] ),
		    'fund' => array(),
		);
	    }

	    if ( !array_key_exists( $row['id_fefopfund'], $total ) ) {
		$total[$row['id_fefopfund']] = 0;
	    }

	    if ( App_General_String::toFloat( $row['payments'] ) >= 1 ) {
		//$calculate = ((($row['amount'] * 100) / $row['payments']) * $row['repayments']) / 100;
		$calculate = (App_General_String::toFloat( $row['amount'] ) * 100 / App_General_String::toFloat( $row['payments'] ) * App_General_String::toFloat( $row['repayments'] )) / 100;
	    } else {
		$calculate = 0;
	    }

	    $total[$row['id_fefopfund']] += $calculate;

	    if ( !empty( $row['id_fefopfund'] ) ) {
		$contract[$row['id_fefop_contract']]['fund'][$row['id_fefopfund']] = $calculate;
	    }
	}

	return array(
	    'item' => array(
		'contract' => $contract,
		'fund' => $this->getArrayFund( $select ),
		'total' => $total,
	    ),
	);
    }

    /**
     * 
     * @return array
     */
    public function increasedReport()
    {
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );

	$select = $dbFEFOPContract->select()
		->setIntegrityCheck( false )
		->from(
			$dbFEFOPContract, array(
		    'id_fefop_contract',
		    'contract' => new Zend_Db_Expr( "CONCAT(FEFOP_Contract.num_program, '-', FEFOP_Contract.num_module, '-', FEFOP_Contract.num_district, '-', FEFOP_Contract.num_year, '-', FEFOP_Contract.num_sequence)" ),
			)
		)
		->join(
		$dbFEFOPTransaction->__toString(), 'FEFOP_Transaction.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract', array(
	    'fk_id_budget_category',
	    'amount' => new Zend_Db_Expr( 'IFNULL(SUM(FEFOP_Transaction.amount), 0)' )
		)
	);

	$this->_joinDefault( $select );

	$select->columns( array(
	    'status' => 's.status_description',
	    'beneficiary' => 'b.name',
	    'module' => new Zend_Db_Expr( "CONCAT(FEFOP_Modules.acronym, ' - ', FEFOP_Modules.description)" ),
	    'program' => new Zend_Db_Expr( "CONCAT(FEFOP_Programs.acronym, ' - ', FEFOP_Programs.description)" ),
	) );

	$this->_whereDefault( $select );

	$select->where( "DATE(FEFOP_Transaction.date_reference) >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'] );
	$select->where( "DATE(FEFOP_Transaction.date_reference) <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish'] );
	$select->where( 'FEFOP_Transaction.fk_id_budget_category_type = ?', 3 );
	$select->where( 'FEFOP_Transaction.fk_id_fefop_type_transaction = ?', 1 );

	$select->group( array(
	    'FEFOP_Contract.id_fefop_contract',
	    'FEFOP_Transaction.fk_id_budget_category'
	) );

	$select->order( 'FEFOP_Transaction.fk_id_budget_category ASC' );

	$rows = $dbFEFOPContract->fetchAll( $select );

	if ( $rows->count() == 0 ) {
	    return array('item' => null);
	}

	$contract = array();
	$total = array();

	foreach ( $rows as $row ) {

	    if ( empty( $contract[$row['id_fefop_contract']] ) ) {
		$contract[$row['id_fefop_contract']] = array(
		    'contract' => $row['contract'],
		    'status' => $row['status'],
		    'beneficiary' => $row['beneficiary'],
		    'module' => $row['module'],
		    'program' => $row['program'],
		    'amount' => array()
		);
	    }

	    $contract[$row['id_fefop_contract']]['amount'][$row['fk_id_budget_category']] = App_General_String::toFloat( $row['amount'] );

	    if ( empty( $total[$row['fk_id_budget_category']] ) ) {
		$total[$row['fk_id_budget_category']] = 0;
	    }

	    $total[$row['fk_id_budget_category']] += App_General_String::toFloat( $row['amount'] );
	}

	ksort( $total );

	return array(
	    'item' => array(
		'contract' => $contract,
		'category' => $this->getArrayCategory( $select ),
		'total' => $total,
	    )
	);
    }

    /**
     * 
     * @return array
     */
    public function financialIncreasedReport()
    {
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbFEFOPContractAdditional = App_Model_DbTable_Factory::get( 'FEFOPContractAdditional' );
	$dbFEFOPContractFund = App_Model_DbTable_Factory::get( 'FEFOPContractFund' );
	$dbFEFOPFund = App_Model_DbTable_Factory::get( 'FEFOPFund' );
	$dbBudgetCategoryHasFEFOPContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );

	$select = $dbFEFOPContract->select()
		->setIntegrityCheck( false )
		->from(
		    $dbFEFOPContract, 
		    array(
			'id_fefop_contract',
			'contract' => new Zend_Db_Expr( "CONCAT(FEFOP_Contract.num_program, '-', FEFOP_Contract.num_module, '-', FEFOP_Contract.num_district, '-', FEFOP_Contract.num_year, '-', FEFOP_Contract.num_sequence)" )
		    )
		);

	$this->_joinDefault( $select );

	$subSelect = $dbFEFOPContractFund->select()
					->from(
						$dbFEFOPContractFund, 
						array(
						    'amount' => new Zend_Db_Expr( 'SUM(FEFOP_Contract_Fund.real_amount)' ),
						    'fk_id_fefopfund',
						    'fk_id_fefop_contract'
						)
					)
					->group( 
					    array(
						'FEFOP_Contract_Fund.fk_id_fefopfund',
						'FEFOP_Contract_Fund.fk_id_fefop_contract'
					    ) 			
					);

	$select->join(
		    array( 'fcf' => new Zend_Db_Expr( '(' . $subSelect . ')' )), 
		    'fcf.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract', 
		    array( 'amount' )
		)
		->join(
		   $dbFEFOPFund->__toString(), 
		   'FEFOPFund.id_fefopfund = fcf.fk_id_fefopfund', 
		   array( 'id_fefopfund' )
		);

	//Pagamentos 
	$subSelect = $dbFEFOPTransaction->select()
		->from(
		    array( 'p' => $dbFEFOPTransaction ), 
		    array(
			'payments' => new Zend_Db_Expr( "IFNULL(ABS(SUM(p.amount * IF(p.operation = 'D', -1, 1))), 0)" ),
			'fk_id_fefop_contract',
		    )
		)
		->join(
		    array( 'bcc' => $dbBudgetCategoryHasFEFOPContract ), 
		    'p.fk_id_fefop_contract = bcc.fk_id_fefop_contract AND bcc.fk_id_budget_category = p.fk_id_budget_category', 
		    array()
		)
		->where( 'p.fk_id_fefop_type_transaction = ?', 1 )
		->where( "DATE(p.date_reference) >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'] )
		->where( "DATE(p.date_reference) <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish'] )
		->group( 'p.fk_id_fefop_contract' );

	$select->join(
		    array( 'pc' => new Zend_Db_Expr( '(' . $subSelect . ')' ) ), 
		    'pc.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract', 
		    array( 'payments' )
		);

	//Soma dos lançamentos de custos acrescidos
	$subSelect = $dbFEFOPContractAdditional->select()
		->setIntegrityCheck( false )
		->from(
		    $dbFEFOPContractAdditional->__toString(), 
		    array(
			'increased' => new Zend_Db_Expr( 'IFNULL(SUM(FEFOP_Contract_Additional.amount), 0)' ),
			'fk_id_fefop_contract'
		    )
		)
		->join(
		    $dbFEFOPTransaction->__toString(), 
		    'FEFOP_Contract_Additional.fk_id_fefop_contract = FEFOP_Transaction.fk_id_fefop_contract', 
		    array()
		)
		->where( 'FEFOP_Transaction.fk_id_budget_category_type = ?', 3 )
		->where( "DATE(FEFOP_Transaction.date_reference) >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'] )
		->where( "DATE(FEFOP_Transaction.date_reference) <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish'] )
		->group( 'FEFOP_Transaction.fk_id_fefop_contract' );

	$select->join(
		    array( 'ca' => new Zend_Db_Expr( '(' . $subSelect . ')' ) ), 
		    'ca.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract', 
		    array( 'increased' )
		);

	$select->columns( 
		array(
		    'status' => 's.status_description',
		    'beneficiary' => 'b.name',
		    'module' => new Zend_Db_Expr( "CONCAT(FEFOP_Modules.acronym, ' - ', FEFOP_Modules.description)" ),
		    'program' => new Zend_Db_Expr( "CONCAT(FEFOP_Programs.acronym, ' - ', FEFOP_Programs.description)" ),
		) 
	);

	$this->_whereDefault( $select );

	$select->order( 'FEFOPFund.id_fefopfund' );

	$rows = $dbFEFOPContract->fetchAll( $select );

	if ( $rows->count() == 0 ) {
	    return array( 'item' => null );
	}

	$contract = array();
	$total = array();

	foreach ( $rows as $row ) {

	    if ( empty( $contract[$row['id_fefop_contract']] ) ) {
		$contract[$row['id_fefop_contract']] = array(
		    'contract'	    => $row['contract'],
		    'status'	    => $row['status'],
		    'beneficiary'   => $row['beneficiary'],
		    'module'	    => $row['module'],
		    'program'	    => $row['program'],
		    'fund'	    => array(),
		);
	    }

	    if ( !array_key_exists( $row['id_fefopfund'], $total ) ) {
		$total[$row['id_fefopfund']] = 0;
	    }

	    if ( App_General_String::toFloat( $row['payments'] ) >= 1 ) {
		$calculate = (App_General_String::toFloat( $row['amount'] ) * 100 / App_General_String::toFloat( $row['payments'] ) * App_General_String::toFloat( $row['increased'] )) / 100;
	    } else {
		$calculate = 0;
	    }

	    $total[$row['id_fefopfund']] += $calculate;

	    if ( !empty( $row['id_fefopfund'] ) ) {
		$contract[$row['id_fefop_contract']]['fund'][$row['id_fefopfund']] = $calculate;
	    }
	}

	ksort( $total );

	return array(
	    'item' => array(
		'contract'  => $contract,
		'fund'	    => $this->getArrayFund( $select ),
		'total'	    => $total,
	    )
	);
    }

    /**
     * 
     * @return array
     */
    public function totalizerReport()
    {
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbFEFOPContractAdditional = App_Model_DbTable_Factory::get( 'FEFOPContractAdditional' );
	$dbBudgetCategoryHasFEFOPContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	$dbFEFOPBankContract = App_Model_DbTable_Factory::get( 'FEFOPBankContract' );

	$select = $dbFEFOPContract->select()
		->setIntegrityCheck( false )
		->from(
		$dbFEFOPContract, array(
	    'id_fefop_contract',
	    'contract' => new Zend_Db_Expr( "CONCAT(FEFOP_Contract.num_program, '-', FEFOP_Contract.num_module, '-', FEFOP_Contract.num_district, '-', FEFOP_Contract.num_year, '-', FEFOP_Contract.num_sequence)" ),
		)
	);

	$this->_joinDefault( $select );

	$select->columns( array(
	    'status' => 's.status_description',
	    'beneficiary' => 'b.name',
	    'module' => new Zend_Db_Expr( "CONCAT(FEFOP_Modules.acronym, ' - ', FEFOP_Modules.description)" ),
	    'program' => new Zend_Db_Expr( "CONCAT(FEFOP_Programs.acronym, ' - ', FEFOP_Programs.description)" ),
	    'amount_contract' => new Zend_Db_Expr( '(' . $this->_columnAmountContracted() . ')' ),
	) );

	//Financeiro
	$subSelect = $dbFEFOPTransaction->select()
		->setIntegrityCheck( false )
		->from(
			array('t' => $dbFEFOPTransaction), array(new Zend_Db_Expr( 'IFNULL(SUM(t.amount), 0)' ))
		)
		->join(
			array('bcc' => $dbBudgetCategoryHasFEFOPContract), 'bcc.fk_id_budget_category = t.fk_id_budget_category AND bcc.fk_id_fefop_contract = t.fk_id_fefop_contract', array()
		)
		->where( 't.operation = ?', 'D' )
		->where( 't.fk_id_fefop_type_transaction = ?', 1 )
		->where( 't.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract' );

	$select->columns( array(
	    'amount_financial' => new Zend_Db_Expr( '(' . $subSelect . ')' )
	) );

	//Devoluções
	$subSelect = $dbFEFOPTransaction->select()
		->setIntegrityCheck( false )
		->from(
			array('t' => $dbFEFOPTransaction), array(new Zend_Db_Expr( 'IFNULL(SUM(t.amount), 0)' ))
		)
		->join(
			array('bcc' => $dbBudgetCategoryHasFEFOPContract), 'bcc.fk_id_budget_category = t.fk_id_budget_category AND bcc.fk_id_fefop_contract = t.fk_id_fefop_contract', array()
		)
		->where( 't.operation = ?', 'C' )
		->where( 't.fk_id_fefop_type_transaction = ?', 2 )
		->where( 't.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract' );

	$select->columns( array(
	    'amount_repayment' => new Zend_Db_Expr( '(' . $subSelect . ')' )
	) );

	//Acrescidos
	$subSelect = $dbFEFOPContractAdditional->select()
		->from(
			array('a' => $dbFEFOPContractAdditional), array(new Zend_Db_Expr( 'IFNULL(SUM(a.amount), 0)' ))
		)
		->where( 'a.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract' );

	$select->columns( array(
	    'amount_addcosts' => new Zend_Db_Expr( '(' . $subSelect . ')' )
	) );

	//Bancário
	$subSelect = $dbFEFOPBankContract->select()
		->from(
			array('b' => $dbFEFOPBankContract), array(new Zend_Db_Expr( "IFNULL(ABS(SUM(b.amount * IF(b.operation = 'D', -1, 1))), 0)" ))
		)
		->where( 'b.fk_id_fefop_contract = FEFOP_Contract.id_fefop_contract' );

	$select->columns( array(
	    'amount_banking' => new Zend_Db_Expr( '(' . $subSelect . ')' )
	) );

	$this->_whereDefault( $select );

	$select->group( array(
	    'FEFOP_Contract.id_fefop_contract',
	) );

	$rows = $dbFEFOPContract->fetchAll( $select );

	if ( $rows->count() == 0 ) {
	    return array();
	}

	$total = array();

	foreach ( $rows as $row ) {
	    $total[$row['id_fefop_contract']] = (App_General_String::toFloat( $row['amount_financial'] ) + App_General_String::toFloat( $row['amount_addcosts'] )) - (App_General_String::toFloat( $row['amount_repayment'] ) + App_General_String::toFloat( $row['amount_banking'] ));
	}

	return array(
	    'rows' => $rows->toArray(),
	    'total' => $total
	);
    }

    /**
     * 
     * @return Zend_Db_Select
     */
    protected function _columnPlanningByProgramModule()
    {
	$dbFundPlanningModule = App_Model_DbTable_Factory::get( 'FundPlanningModule' );
	$dbFundPlanning = App_Model_DbTable_Factory::get( 'FundPlanning' );
	$dbFEFOPFund = App_Model_DbTable_Factory::get( 'FEFOPFund' );
	$dbFEFOPContractFund = App_Model_DbTable_Factory::get( 'FEFOPContractFund' );
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );

	$select = $dbFundPlanningModule->select()
		->setIntegrityCheck( false )
		->from(
			array('fpm' => $dbFundPlanningModule), array(new Zend_Db_Expr( 'IFNULL(SUM(fpm.amount), 0)' ))
		)
		->join(
			array('fp' => $dbFundPlanning), 'fp.id_fund_planning = fpm.fk_id_fund_planning', array()
		)
		->join(
			array('ff' => $dbFEFOPFund), 'ff.id_fefopfund = fp.fk_id_fefopfund', array()
		)
		->join(
			array('fcf' => $dbFEFOPContractFund), 'fcf.fk_id_fefopfund = ff.id_fefopfund', array()
		)
		->join(
			array('fc' => $dbFEFOPContract), 'fc.id_fefop_contract = fcf.fk_id_fefop_contract', array()
		)
		->where( 'fc.fk_id_fefop_programs = FEFOP_Programs.id_fefop_programs' )
		->where( 'fc.fk_id_fefop_modules = FEFOP_Modules.id_fefop_modules' );

	if ( !empty( $this->_data['year'] ) )
	    $select->where( 'fp.year_planning = ?', $this->_data['year'] );

	return $select;
    }

    /**
     * 
     * @return Zend_Db_Select
     */
    protected function _columnTransferByProgramModule()
    {
	$dbFEFOPBankContract = App_Model_DbTable_Factory::get( 'FEFOPBankContract' );
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );

	$select = $dbFEFOPBankContract->select()
		->setIntegrityCheck( false )
		->from(
			array('fbc' => $dbFEFOPBankContract), array(new Zend_Db_Expr( 'IFNULL(SUM(amount), 0)' ))
		)
		->join(
			array('fc' => $dbFEFOPContract), 'fc.id_fefop_contract = fbc.fk_id_fefop_contract', array()
		)
		->where( 'fc.fk_id_fefop_programs = FEFOP_Programs.id_fefop_programs' )
		->where( 'fc.fk_id_fefop_modules = FEFOP_Modules.id_fefop_modules' );

	return $select;
    }

    /**
     * 
     * @return string
     */
    protected function _columnTotalByProgramModule()
    {
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );

	$select = $dbFEFOPTransaction->select()
		->setIntegrityCheck( false )
		->from(
			array('ft' => $dbFEFOPTransaction), array(new Zend_Db_Expr( "IFNULL(ABS(SUM(ft.amount * IF(ft.operation = 'D', -1, 1))), 0)" ))
		)
		->join(
			array('fc' => $dbFEFOPContract), 'fc.id_fefop_contract = ft.fk_id_fefop_contract', array()
		)
		->where( 'fc.fk_id_fefop_programs = FEFOP_Programs.id_fefop_programs' )
		->where( 'fc.fk_id_fefop_modules = FEFOP_Modules.id_fefop_modules' );

	return '(' . $select . ') - (' . $this->_columnTransferByProgramModule() . ')';
    }

    /**
     * Soma total planejado para o fundo
     * 
     * @return Zend_Db_Select
     */
    protected function _columnPlanningByFund()
    {
	$dbFundPlanning = App_Model_DbTable_Factory::get( 'FundPlanning' );
	$dbFundPlanningModule = App_Model_DbTable_Factory::get( 'FundPlanningModule' );
	$dbFEFOPModule = App_Model_DbTable_Factory::get( 'FEFOPModules' );

	$select = $dbFundPlanning->select()
				->from(
				    array( 'fp' => $dbFundPlanning ), 
				    array()
				)
				->setIntegrityCheck( false )
				->join(
				    $dbFundPlanningModule->__toString(),
				    'FundPlanningModule.fk_id_fund_planning = fp.id_fund_planning',
				    array(new Zend_Db_Expr( 'IFNULL(SUM(FundPlanningModule.amount), 0)' ))
				)
				->join(
				    $dbFEFOPModule->__toString(),
				    'FundPlanningModule.fk_id_fefop_modules = FEFOP_Modules.id_fefop_modules	',
				    array()
				)
				->where( 'fp.fk_id_fefopfund = FEFOPFund.id_fefopfund' );
	
	if ( !empty( $this->_data['id_fefop_programs'] ) ) {
	    $select->where( 'FEFOP_Modules.fk_id_fefop_programs IN(?)', $this->_data['id_fefop_programs'] );
	}

	if ( !empty( $this->_data['id_fefop_modules'] ) ) {
	    $select->where( 'FundPlanningModule.fk_id_fefop_modules IN(?)', $this->_data['id_fefop_modules'] );
	}
	
	if ( !empty( $this->_data['year_start'] ) ) {
	    $select->where( 'fp.year_planning >= ?', $this->_data['year_start'] );
	}

	if ( !empty( $this->_data['year_finish'] ) ) {
	    $select->where( 'fp.year_planning <= ?', $this->_data['year_finish'] );
	}

	return $select;
    }

    /**
     * Soma total das proporcionalidades de contratos para o fundo
     * 
     * @access protected
     * @return Zend_Db_Select
     */
    protected function _columnContractByFund()
    {
	$dbFEFOPContractFund = App_Model_DbTable_Factory::get( 'FEFOPContractFund' );
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );

	$select = $dbFEFOPContractFund->select()
				    ->from(
					array('fcf' => $dbFEFOPContractFund), 
					array(new Zend_Db_Expr( 'IFNULL(SUM(fcf.contract_amount), 0)' ))
				    )
				    ->join(
					$dbFEFOPContract->__toString(),
					'FEFOP_Contract.id_fefop_contract = fcf.fk_id_fefop_contract',
					array()
				    )
				    ->where( 'fcf.fk_id_fefopfund = FEFOPFund.id_fefopfund' );
	
	$this->_joinDefault( $select );
	$this->_whereDefault( $select );
	
	if ( !empty( $this->_data['year_start'] ) ) {
	    $select->where( 'YEAR(FEFOP_Contract.date_inserted) >= ?', $this->_data['year_start'] );
	}

	if ( !empty( $this->_data['year_finish'] ) ) {
	    $select->where( 'YEAR(FEFOP_Contract.date_inserted) <= ?', $this->_data['year_finish'] );
	}
	
	return $select;
    }

    /**
     * Soma total das proporcionalidades de valores pagos em contratos para o fundo
     * 
     * @access protected
     * @return Zend_Db_Select
     */
    protected function _columnFinancialByFund()
    {
	$dbFEFOPContractFund = App_Model_DbTable_Factory::get( 'FEFOPContractFund' );
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	
	$selecTotalExpenseType = $dbFEFOPTransaction->select()
						    ->setIntegrityCheck( false )
						    ->from( 
							array( 't' => $dbFEFOPTransaction ),
							array(
							    'fk_id_fefop_contract',
							    'fk_id_budget_category_type',
							    'reimbursement' => new Zend_Db_Expr( 'SUM(t.amount)' )
							)
						    )
						    ->where( 't.fk_id_fefop_type_transaction = ?', Fefop_Model_Mapper_Financial::TYPE_REIMBURSEMENT )
						    ->where( 't.fk_id_budget_category_type <> ?', Fefop_Model_Mapper_ExpenseType::ADDITIONALS )
						    ->group( array( 'fk_id_fefop_contract', 'fk_id_budget_category_type' ) );
	
	$selectTotalFund = $dbFEFOPContractFund->select()
						->from(
						    array( 'cf' => $dbFEFOPContractFund ),
						    array(
							'real_amount',
							'percent',
							'fk_id_fefopfund',
							'fk_id_fefop_contract',
							'fk_id_budget_category_type'
						    )
						)
						->setIntegrityCheck( false )
						->joinLeft(
						    array( 'd' => new Zend_Db_Expr( '(' . $selecTotalExpenseType . ')' ) ),
						    'd.fk_id_fefop_contract = cf.fk_id_fefop_contract AND d.fk_id_budget_category_type = cf.fk_id_budget_category_type',
						    array( 
							'reimbursement' => new Zend_Db_Expr( '( IFNULL(d.reimbursement, 0) * cf.percent ) / 100' )
						    )
						)
						->group( array( 'cf.fk_id_fefopfund', 'cf.fk_id_fefop_contract', 'cf.fk_id_budget_category_type' ) );
	
	$selectFinal = $dbFEFOPContractFund->select()
					   ->from(
						array( 't' => new Zend_Db_Expr( '(' . $selectTotalFund . ')' ) ),
						array( new Zend_Db_Expr( 'SUM(t.real_amount - IFNULL(t.reimbursement, 0 ))' ) )
					   )
					   ->setIntegrityCheck( false )
					   ->join(
						$dbFEFOPContract->__toString(),
						'FEFOP_Contract.id_fefop_contract = t.fk_id_fefop_contract',
						array()
					    )
					   ->where( 't.fk_id_fefopfund = FEFOPFund.id_fefopfund' )
					   ->group( array( 't.fk_id_fefopfund' ) );
	
	$this->_joinDefault( $selectFinal );
	$this->_whereDefault( $selectFinal );
	
	if ( !empty( $this->_data['year_start'] ) ) {
	    $selectFinal->where( 'YEAR(FEFOP_Contract.date_inserted) >= ?', $this->_data['year_start'] );
	}

	if ( !empty( $this->_data['year_finish'] ) ) {
	    $selectFinal->where( 'YEAR(FEFOP_Contract.date_inserted) <= ?', $this->_data['year_finish'] );
	}

	return $selectFinal;
    }

    /**
     * Soma total de custos acrescidos pagos pelo fundo
     * 
     * @access protected
     * @return Zend_Db_Select
     */
    protected function _columnAdditionalCostsByFund()
    {
	$dbFEFOPContractAdditional = App_Model_DbTable_Factory::get( 'FEFOPContractAdditional' );
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	
	$select =  $dbFEFOPContractAdditional->select()
					     ->setIntegrityCheck( false )
					     ->from(
						    array( 'fca' => $dbFEFOPContractAdditional ),
						    array( new Zend_Db_Expr( 'SUM(amount)' ) )
					       )
					       ->join(
						    $dbFEFOPContract->__toString(),
						    'FEFOP_Contract.id_fefop_contract = fca.fk_id_fefop_contract',
						    array()
						)
					       ->where( 'fca.fk_id_fefopfund = FEFOPFund.id_fefopfund');
	
	$this->_joinDefault( $select );
	$this->_whereDefault( $select );
	
	if ( !empty( $this->_data['year_start'] ) ) {
	    $select->where( 'YEAR(FEFOP_Contract.date_inserted) >= ?', $this->_data['year_start'] );
	}

	if ( !empty( $this->_data['year_finish'] ) ) {
	    $select->where( 'YEAR(FEFOP_Contract.date_inserted) <= ?', $this->_data['year_finish'] );
	}
	
	return $select;
    }

    /**
     * Valor dos Custos Acrescidos planejados para cada Fundo
     *  
     * @return Zend_Db_Select
     */
    protected function _addCostsPlanningByFund()
    {
	$dbFundPlanning = App_Model_DbTable_Factory::get( 'FundPlanning' );

	$select = $dbFundPlanning->select()
		->from(
		    array( 'acp' => $dbFundPlanning ), 
		    array( new Zend_Db_Expr( 'IFNULL(SUM(acp.additional_cost), 0)' ) )
		)
		->where( 'acp.fk_id_fefopfund = FEFOPFund.id_fefopfund' );
	
	if ( !empty( $this->_data['year_start'] ) ) {
	    $select->where( 'acp.year_planning >= ?', $this->_data['year_start'] );
	}

	if ( !empty( $this->_data['year_finish'] ) ) {
	    $select->where( 'acp.year_planning <= ?', $this->_data['year_finish'] );
	}

	return $select;
    }

    /**
     * Soma total de Transferências bancárias feitas para o fundo
     * 
     * @access protected
     * @return Zend_Db_Select
     */
    protected function _columnBankStatementsByFund()
    {
	$dbFEFOPBankStatements = App_Model_DbTable_Factory::get( 'FEFOPBankStatements' );

	$select = $dbFEFOPBankStatements->select()
		->from(
		    array( 'fbs' => $dbFEFOPBankStatements ), 
		    array( new Zend_Db_Expr( "IFNULL(ABS(SUM(fbs.amount * IF(fbs.operation = 'D', -1, 1))), 0)" ) )
		)
		->where( 'fbs.fk_id_fefopfund = FEFOPFund.id_fefopfund' );
	
	if ( !empty( $this->_data['year_start'] ) ) {
	    $select->where( 'YEAR(fbs.date_statement) >= ?', $this->_data['year_start'] );
	}

	if ( !empty( $this->_data['year_finish'] ) ) {
	    $select->where( 'YEAR(fbs.date_statement) <= ?', $this->_data['year_finish'] );
	}

	return $select;
    }

    /**
     * 
     * @return Zend_Db_Select
     */
    protected function _columnTotalByFund()
    {
	//[T] – ( [P] + [A] ) + [D];
	$query = 'IFNULL((%T), 0) - (IFNULL((%P), 0) + IFNULL((%A), 0))';

	$query = str_replace( '%T', $this->_columnBankStatementsByFund(), $query );
	$query = str_replace( '%P', $this->_columnFinancialByFund(), $query );
	$query = str_replace( '%A',  $this->_columnAdditionalCostsByFund(), $query );

	return $query;
    }

    /**
     * 
     * @return array
     */
    public function donorContractCostReport()
    {
	$dbFEFOPFund = App_Model_DbTable_Factory::get( 'FEFOPFund' );
	$dbFEFOPContractFund = App_Model_DbTable_Factory::get( 'FEFOPContractFund' );
	$dbFEFOPContract = App_Model_DbTable_Factory::get( 'FEFOPContract' );
	$dbBudgetCategoryType = App_Model_DbTable_Factory::get( 'BudgetCategoryType' );
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );

	//Donors
	$subSelect = $dbFEFOPFund->select()
		->setIntegrityCheck( false )
		->distinct()
		->from(
			$dbFEFOPFund, 
			array(
			    'id_fefopfund',
			    'name_fund',
			    'type',
			    'planning'		=> new Zend_Db_Expr( 'IFNULL((' . $this->_columnPlanningByFund() . '), 0)' ),
			    'contract'		=> new Zend_Db_Expr( 'IFNULL((' . $this->_columnContractByFund() . '), 0)' ),
			    'financial'		=> new Zend_Db_Expr( 'IFNULL((' . $this->_columnFinancialByFund() . '), 0)' ),
			    'addcosts'		=> new Zend_Db_Expr( 'IFNULL((' . $this->_columnAdditionalCostsByFund() . '), 0)' ),
			    'addcostsplanning'	=> new Zend_Db_Expr( 'IFNULL((' . $this->_addCostsPlanningByFund() . '), 0)' ),
			    'bankstmt'		=> new Zend_Db_Expr( 'IFNULL((' . $this->_columnBankStatementsByFund() . '), 0)' ),
			    'balance'		=> new Zend_Db_Expr( 'IFNULL((' . $this->_columnTotalByFund() . '), 0)' ),
			)
		);
//		->joinLeft(
//		    $dbFEFOPContractFund->__toString(), 
//		    'FEFOP_Contract_Fund.fk_id_fefopfund = FEFOPFund.id_fefopfund', 
//		    array()
//		)
//		->joinLeft(
//		    $dbFEFOPContract->__toString(), 
//		    'FEFOP_Contract.id_fefop_contract = FEFOP_Contract_Fund.fk_id_fefop_contract', 
//		    array()
//		);
//
	//$this->_whereDefault( $subSelect );
//
//	if ( !empty( $this->_data['year_start'] ) ) {
//	    $subSelect->where( '(FEFOP_Contract.id_fefop_contract IS NULL' );
//	    $subSelect->orWhere( 'YEAR(FEFOP_Contract.date_inserted) >= ?)', $this->_data['year_start'] );
//	}
//
//	if ( !empty( $this->_data['year_finish'] ) ) {
//	    $subSelect->where( '(FEFOP_Contract.id_fefop_contract IS NULL' );
//	    $subSelect->orWhere( 'YEAR(FEFOP_Contract.date_inserted) <= ?)', $this->_data['year_finish'] );
//	}

	$adapter = App_Model_DbTable_Abstract::getDefaultAdapter();
	
	$rows = $adapter->fetchAll( $subSelect );

	$donor = array();

	$totalDonor = array(
	    'planning' => 0,
	    'contract' => 0,
	    'financial' => 0,
	    'addcosts' => 0,
	    'addcostsplanning' => 0,
	    'bankstmt' => 0,
	    'balance' => 0
	);

	foreach ( $rows as $row ) {

	    $donor[$row['type']][$row['id_fefopfund']]['name'] = $row['name_fund'];
	    $donor[$row['type']][$row['id_fefopfund']]['planning'] = $row['planning'];
	    $donor[$row['type']][$row['id_fefopfund']]['contract'] = $row['contract'];
	    $donor[$row['type']][$row['id_fefopfund']]['financial'] = $row['financial'];
	    $donor[$row['type']][$row['id_fefopfund']]['addcosts'] = $row['addcosts'];
	    $donor[$row['type']][$row['id_fefopfund']]['addcostsplanning'] = $row['addcostsplanning'];
	    $donor[$row['type']][$row['id_fefopfund']]['bankstmt'] = $row['bankstmt'];
	    $donor[$row['type']][$row['id_fefopfund']]['balance'] = $row['balance'];

	    $totalDonor['planning'] += App_General_String::toFloat( $row['planning'] );
	    $totalDonor['contract'] += App_General_String::toFloat( $row['contract'] );
	    $totalDonor['financial'] += App_General_String::toFloat( $row['financial'] );
	    $totalDonor['addcosts'] += App_General_String::toFloat( $row['addcosts'] );
	    $totalDonor['addcostsplanning'] += App_General_String::toFloat( $row['addcostsplanning'] );
	    $totalDonor['bankstmt'] += App_General_String::toFloat( $row['bankstmt'] );
	    $totalDonor['balance'] += App_General_String::toFloat( $row['balance'] );
	}

	//Dados do Contrato
	$select = $dbFEFOPContract->select()
		->setIntegrityCheck( false )
		->distinct()
		->from(
		$dbFEFOPContract->__toString(), array()
	);

	$this->_joinDefault( $select );
	$this->_whereDefault( $select );

	//Período
	if ( !empty( $this->_data['year_start'] ) ) {
	    $select->where( 'YEAR(FEFOP_Contract.date_inserted) >= ?', $this->_data['year_start'] );
	}

	if ( !empty( $this->_data['year_finish'] ) ) {
	    $select->where( 'YEAR(FEFOP_Contract.date_inserted) <= ?', $this->_data['year_finish'] );
	}

	$select->columns( array(
	    'FEFOP_Modules.id_fefop_modules',
	    'FEFOP_Programs.id_fefop_programs',
	    'acronym_module' => 'FEFOP_Modules.acronym',
	    'acronym_program' => 'FEFOP_Programs.acronym',
	    'planning' => new Zend_Db_Expr( '(' . $this->_columnPlanningByProgramModule() . ')' ),
	    'transfer' => new Zend_Db_Expr( '(' . $this->_columnTransferByProgramModule() . ')' ),
	    'total' => new Zend_Db_Expr( '(' . $this->_columnTotalByProgramModule() . ')' ),
	) );

	$rows = $dbFEFOPContract->fetchAll( $select );

	$contract = array();

	foreach ( $rows as $row ) {

	    if ( !in_array( $row['id_fefop_programs'], array_keys( $contract ) ) ) {
		$contract[$row['id_fefop_programs']]['acronym'] = $row['acronym_program'];
		$contract[$row['id_fefop_programs']]['module'] = array();

		$contract[$row['id_fefop_programs']]['planning'] = 0;
		$contract[$row['id_fefop_programs']]['transfer'] = 0;
		$contract[$row['id_fefop_programs']]['total'] = 0;
	    }

	    $contract[$row['id_fefop_programs']]['module'][$row['id_fefop_modules']]['acronym'] = $row['acronym_module'];

	    if ( !isset( $contract[$row['id_fefop_programs']]['module'][$row['id_fefop_modules']]['planning'] ) ) {
		$contract[$row['id_fefop_programs']]['module'][$row['id_fefop_modules']]['planning'] = 0;
	    }

	    if ( !isset( $contract[$row['id_fefop_programs']]['module'][$row['id_fefop_modules']]['transfer'] ) ) {
		$contract[$row['id_fefop_programs']]['module'][$row['id_fefop_modules']]['transfer'] = 0;
	    }

	    if ( !isset( $contract[$row['id_fefop_programs']]['module'][$row['id_fefop_modules']]['total'] ) ) {
		$contract[$row['id_fefop_programs']]['module'][$row['id_fefop_modules']]['total'] = 0;
	    }

	    $contract[$row['id_fefop_programs']]['module'][$row['id_fefop_modules']]['planning'] += App_General_String::toFloat( $row['planning'] );
	    $contract[$row['id_fefop_programs']]['module'][$row['id_fefop_modules']]['transfer'] += App_General_String::toFloat( $row['transfer'] );
	    $contract[$row['id_fefop_programs']]['module'][$row['id_fefop_modules']]['total'] += App_General_String::toFloat( $row['total'] );

	    $contract[$row['id_fefop_programs']]['planning'] += App_General_String::toFloat( $row['planning'] );
	    $contract[$row['id_fefop_programs']]['transfer'] += App_General_String::toFloat( $row['transfer'] );
	    $contract[$row['id_fefop_programs']]['total'] += App_General_String::toFloat( $row['total'] );
	}

	//Dados dos Custos
	$select = $dbBudgetCategoryType->select()
		->setIntegrityCheck( false )
		->from(
		    $dbBudgetCategoryType->__toString(), 
		    array('id_budget_category_type', 'description')
		)
		->join(
		    $dbFEFOPTransaction->__toString(), 
		    'FEFOP_Transaction.fk_id_budget_category_type = BudgetCategoryType.id_budget_category_type', 
		    array( 'amount' => new Zend_Db_Expr( 'IFNULL(SUM(FEFOP_Transaction.amount), 0)' ) )
		)
		->join(
		    $dbFEFOPContract->__toString(), 
		    'FEFOP_Contract.id_fefop_contract = FEFOP_Transaction.fk_id_fefop_contract', 
		    array()
		);

	$this->_whereDefault( $select );

	//Período
	if ( !empty( $this->_data['year_start'] ) ) {
	    $select->where( 'YEAR(FEFOP_Contract.date_inserted) >= ?', $this->_data['year_start'] );
	}

	if ( !empty( $this->_data['year_finish'] ) ) {
	    $select->where( 'YEAR(FEFOP_Contract.date_inserted) <= ?', $this->_data['year_finish'] );
	}

	$select->group( array('BudgetCategoryType.id_budget_category_type') );

	$rows = $dbBudgetCategoryType->fetchAll( $select );

	$cost = array();

	foreach ( $rows as $row ) {
	    $cost[$row['id_budget_category_type']]['description'] = $row['description'];
	    $cost[$row['id_budget_category_type']]['amount'] = $row['amount'];
	}

	return array(
	    'item' => array(
		'donor'	    => $donor,
		'total'	    => $totalDonor,
		'contract'  => $contract,
		'cost'	    => $cost,
	    ),
	);
    }

    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function feFormationReport()
    {
	$mapperFeContract = new Fefop_Model_Mapper_FEContract();
	$select = $mapperFeContract->getSelect();

	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbFEFOPTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );

	$select->join(
			array('bc' => $dbBudgetCategory), 'bc.id_budget_category = bcc.fk_id_budget_category', array('expense' => 'description')
		)
		->joinLeft(
			array('t' => $dbFEFOPTransaction), 't.fk_id_fefop_contract = fec.fk_id_fefop_contract AND t.fk_id_budget_category = bcc.fk_id_budget_category', array('total_spent' => new Zend_Db_Expr( "IFNULL(ABS(SUM(t.amount * IF(t.operation = 'D', -1, 1))), 0)" ))
		)
		->where( 'UPPER(bc.description) LIKE ?', '%Crédito%' )
		->group( array('id_fe_contract') )
		->order( array('date_formation DESC') );

	if ( !empty( $this->_data['fk_id_dec'] ) ) {
	    $select->where( 'u.fk_id_dec IN(?)', $this->_data['fk_id_dec'] );
	}

	if ( !empty( $this->_data['id_adddistrict'] ) ) {
	    $select->where( 'd.id_adddistrict IN(?)', $this->_data['id_adddistrict'] );
	}

	if ( !empty( $this->_data['id_fefop_status'] ) ) {
	    $select->where( 'cs.id_fefop_status IN(?)', $this->_data['id_fefop_status'] );
	}

	//Período
	$select->where( "DATE(c.date_inserted) >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'] );
	$select->where( "DATE(c.date_inserted) <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish'] );

	return array('rows' => $dbBudgetCategory->fetchAll( $select ));
    }

    /**
     * 
     * @return array
     */
    public function bankTransactionReport()
    {
	$mapperBankStatement = new Fefop_Model_Mapper_BankStatement();
	$select = $mapperBankStatement->getSelect();

	$dbBankStatement = App_Model_DbTable_Factory::get( 'FEFOPBankStatements' );

	if ( !empty( $this->_data['fk_id_fefop_type_transaction'] ) ) {
	    $select->where( 'bs.fk_id_fefop_type_transaction IN(?)', $this->_data['fk_id_fefop_type_transaction'] );
	}

	if ( !empty( $this->_data['fk_id_fefopfund'] ) ) {
	    $select->where( 'bs.fk_id_fefopfund IN(?)', $this->_data['fk_id_fefopfund'] );
	}

	if ( !empty( $this->_data['status'] ) ) {
	    $select->where( 'bs.status IN(?)', $this->_data['status'] );
	}

	if ( !empty( $this->_data['bank_payment'] ) ) {

	    if ( 'C' == $this->_data['bank_payment'] ) {
		$select->where( 'bsc.id_fefop_bank_contract IS NOT NULL' );
	    } else {
		$select->where( 'bs.fk_id_fefopfund IS NOT NULL' );
	    }
	}

	//Período
	$select->where( "bs.date_statement >= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_start'] );
	$select->where( "bs.date_statement <= STR_TO_DATE(?, '%d/%m/%Y')", $this->_data['date_finish'] );

	return array('rows' => $dbBankStatement->fetchAll( $select ));
    }

}
