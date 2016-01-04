<?php

class Fefop_Model_Mapper_FEContract extends App_Model_Abstract
{

    const MOUNTH_LIMIT = 6;
    
    /**
     * 
     * @var Model_DbTable_FEContract
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEContract();

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
	    
	    $this->_data['amount'] = 0;
	    foreach ( $this->_data['cost_expense'] as $cost )
		$this->_data['amount'] += App_General_String::toFloat ( $cost );
	    
	    $mapperRule = new Fefop_Model_Mapper_Rule();
	    $mapperRule->validate( $this->_message, $this->_data, Fefop_Model_Mapper_Expense::CONFIG_PISE_FE );
	    
	    $dateStart = new Zend_Date( $this->_data['date_start'] );
	    $dateFinish = new Zend_Date( $this->_data['date_finish'] );
	    //$dateFormation = new Zend_Date( $this->_data['date_formation'] );
	    
	    // Check if the initial date is later than finish date
	    if ( $dateStart->isLater( $dateFinish ) ) {
		
		$this->_message->addMessage( 'Data loron keta liu data remata.' );
		$this->addFieldError( 'date_start' )->addFieldError( 'date_finish' );
		return false;
	    }
	    
	    // If there is no contract yet
	    if ( empty( $this->_data['fk_id_fefop_contract'] ) ) {
		
		$dataContract = array(
		    'module'	=> Fefop_Model_Mapper_Module::FE,
		    'district'	=> $this->_data['fk_id_adddistrict']
		);
		
		$mapperFefopContract = new Fefop_Model_Mapper_Contract();
		$this->_data['fk_id_fefop_contract'] = $mapperFefopContract->save( $dataContract );
	    }
	    
	    $this->_data['date_start'] = $dateStart->toString( 'yyyy-MM-dd' );
	    $this->_data['date_finish'] = $dateFinish->toString( 'yyyy-MM-dd' );
	    //$this->_data['date_formation'] = $dateFormation->toString( 'yyyy-MM-dd' );
	    
	    $dataForm = $this->_data;
	    
	    // If it has linkage with trainee
	    if ( !empty( $dataForm['fk_id_trainee'] ) ) {
		
		$dataTrainee = array(
		    'date_start'    => $this->_data['date_start'],
		    'date_finish'   => $this->_data['date_finish'],
		    'duration'	    => $this->_data['duration_month']
		);
		
		$where = array( 'id_trainee = ?' => $dataForm['fk_id_trainee'] );
		$dbTrainee = App_Model_DbTable_Factory::get( 'JOBTrainingTrainee' );
		$dbTrainee->update( $dataTrainee, $where );
	    }
	    
	    // Save the contract
	    $dataForm['id_fe_contract'] = parent::_simpleSave();
	    
	    // Check if the duration is longer than 6 months
	    $diff = $dateFinish->sub( $dateStart );
	    
	    $measure = new Zend_Measure_Time( $diff->toValue(), Zend_Measure_Time::SECOND );
	    $diffMonth = preg_replace( '/[^0-9.]/i', '', $measure->convertTo( Zend_Measure_Time::MONTH, 0 ) );
	    
	    // If it is longer, send a warning
	    //if ( (float)$diffMonth > self::MOUNTH_LIMIT )
		//$this->_sendWarningDuration( $dataForm['id_fe_contract'] );
	    
	    // Save budget category
	    $this->_saveExpenses( $dataForm );
	    
	    // Check the beneficiary graduation
	    $this->_checkBeneficiaryGraduation( $dataForm );
	    
	    if ( empty( $this->_data['id_fe_contract'] ) )
		$history = 'REJISTU KONTRAKTU RI: %s';
	    else
		$history = 'ATUALIZA KONTRAKTU RI: %s';
	    
	    $history = sprintf( $history, $dataForm['id_fe_contract'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return $dataForm['id_fe_contract'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @param int $idFeContract
     */
    protected function _sendWarningDuration( $idFeContract )
    {
	// Search the user who must receive notes when the duration is ultrapassed
	$noteTypeMapper = new Admin_Model_Mapper_NoteType();
	$users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::FE_DURATION_GREATER );

	$noteModelMapper = new Default_Model_Mapper_NoteModel();
	$noteMapper = new Default_Model_Mapper_Note();
	
	$dataNote = array(
	    'title'   => 'FE KONTRATU HO DURASAUN KLEUR',
	    'level'   => 0,
	    'message' => $noteModelMapper->getFEGreaterDuration( $this->detail( $idFeContract ) ),
	    'users'   => $users
	);
	

	$noteMapper->setData( $dataNote )->saveNote();
    }
    
    /**
     * 
     * @param array $data
     */
    protected function _checkBeneficiaryGraduation( $data )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	
	$types = array(
	    Register_Model_Mapper_PerTypeScholarity::FORMAL,
	    Register_Model_Mapper_PerTypeScholarity::NON_FORMAL,
	);
	
	$valid = false;
	foreach ( $types as $type ) {
	    
	    // Check all the client level of scholarity to check if it has National certificate level 2 or greater or is graduated
	    $scholarities = $mapperClient->listScholarity( $data['fk_id_perdata'], $type );
	    foreach ( $scholarities as $scholarity ) {
		
		if ( 
		    // if it is a national certificate with level 2 or greater
		    $scholarity->category == 'N' && $scholarity->level_scholarity >= 2
		    ||
		    // If it is formal scholarity, superior or greater
		    $type == Register_Model_Mapper_PerTypeScholarity::FORMAL && $scholarity->fk_id_perlevelscholarity >= 17 
		) {
		    $valid = true;
		    break 2;
		}
		
	    }
	}
	
	if ( !$valid ) {
	    
	    // Search the user who must receive notes when the client doens't have the necessary qualification
	    $noteTypeMapper = new Admin_Model_Mapper_NoteType();
	    $users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::FE_GRADUATION );

	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	    $noteMapper = new Default_Model_Mapper_Note();

	    $dataNote = array(
		'title'   => 'FE KONTRATU - BENEFISIARIU LA IHA FORMASAUN',
		'level'   => 0,
		'message' => $noteModelMapper->getFEGraduation( $this->detail( $data['id_fe_contract'] ) ),
		'users'   => $users
	    );


	    $noteMapper->setData( $dataNote )->saveNote();
	}
    }
    
    /**
     * 
     * @param array $data
     */
    protected function _saveExpenses( $data )
    {
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	
	// Save each budget category
	foreach ( $data['cost_expense'] as $id => $costExpense ) {
	    
	    $whereBudget = array(
		'fk_id_fefop_contract = ?' => $data['fk_id_fefop_contract'],
		'fk_id_budget_category = ?' => $id,
	    );
	    
	    $row = $dbBudgetContract->fetchRow( $whereBudget );
	    if ( empty( $row ) ) {
		
		$row = $dbBudgetContract->createRow();
		$row->fk_id_budget_category = $id;
		$row->fk_id_fefop_contract = $data['fk_id_fefop_contract'];
		$row->fk_id_sysuser = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$row->status = 1;
	    }
	    
	    $row->amount = App_General_String::toFloat( $costExpense );
	    $row->save();
	}
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listBeneficiaries()
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$select = $mapperClient->selectClient();
	
	$dbFeContract = App_Model_DbTable_Factory::get( 'FEContract' );
	
	$select->join(
		    array( 'fec' => $dbFeContract ),
		    'fec.fk_id_perdata = c.id_perdata',
		    array()
		)
		->group( array( 'id_perdata' ) );
	
	return $dbFeContract->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listEnterprises()
    {
	$mapperEnterprises = new Register_Model_Mapper_Enterprise();
	$select = $mapperEnterprises->getSelectEnterprise();
	
	$dbFeContract = App_Model_DbTable_Factory::get( 'FEContract' );
	
	$select->join(
		    array( 'fec' => $dbFeContract ),
		    'fec.fk_id_fefpenterprise = e.id_fefpenterprise',
		    array()
		);
	
	return $dbFeContract->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listInstitutes()
    {
	$mapperInstitute = new Register_Model_Mapper_EducationInstitute();
	$select = $mapperInstitute->getSelectEducationInstitute();
	
	$dbFeContract = App_Model_DbTable_Factory::get( 'FEContract' );
	
	$select->join(
		    array( 'fec' => $dbFeContract ),
		    'fec.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
		    array()
		);
	
	return $dbFeContract->fetchAll( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$select = $mapperContract->getSelect();
	
	$dbFeContract = App_Model_DbTable_Factory::get( 'FEContract' );
	$dbInstitute = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	$dbClient = App_Model_DbTable_Factory::get( 'PerData' );
	$dbSubDistrict = App_Model_DbTable_Factory::get( 'AddSubDistrict' );
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	$dbScholarityArea = App_Model_DbTable_Factory::get( 'ScholarityArea' );
	$dbOcupation = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	
	$select->join(
		    array( 'fec' => $dbFeContract ),
		    'fec.fk_id_fefop_contract = c.id_fefop_contract'
		)
		->joinLeft(
		    array( 'ei' => $dbInstitute ),
		    'ei.id_fefpeduinstitution = fec.fk_id_fefpeduinstitution',
		    array()
		)
		->joinLeft(
		    array( 'et' => $dbEnterprise ),
		    'et.id_fefpenterprise = fec.fk_id_fefpenterprise',
		    array( 'entity' => new Zend_Db_Expr( 'IFNULL(enterprise_name, institution)' ) )
		)
		->join(
		    array( 'sd' => $dbSubDistrict ),
		    'fec.fk_id_addsubdistrict = sd.id_addsubdistrict',
		    array( 'sub_district' )
		)
		->join(
		    array( 'sa' => $dbScholarityArea ),
		    'fec.fk_id_scholarity_area = sa.id_scholarity_area',
		    array( 'scholarity_area' )
		)
		->join(
		    array( 'po' => $dbOcupation ),
		    'fec.fk_id_profocupationtimor = po.id_profocupationtimor',
		    array( 'ocupation_name_timor' )
		)
		->join(
		    array( 'cl' => $dbClient ),
		    'cl.id_perdata = fec.fk_id_perdata',
		    array( 'beneficiary' => new Zend_Db_Expr( 'CONVERT(CONCAT( cl.first_name, " ", IFNULL(cl.medium_name, ""), " ", cl.last_name ) USING utf8 ) COLLATE utf8_unicode_ci' ) )
		)
		->join(
		    array( 'bcc' => $dbBudgetContract ),
		    'bcc.fk_id_fefop_contract = c.id_fefop_contract',
		    array( 'contract_amount' => 'amount' )
		)
		->group( array( 'fec.id_fe_contract' ) );
	
	return $select;
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listExpenses( $id )
    {
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	$dbFEContract = App_Model_DbTable_Factory::get( 'FEContract' );
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
					array( 'fec' => $dbFEContract ),
					'fec.fk_id_fefop_contract = bco.fk_id_fefop_contract',
					array()
				    )
				    ->join(
					array( 'fc' => $dbFEFOPContract ),
					'fec.fk_id_fefop_contract = fc.id_fefop_contract',
					array()
				    )
				    ->where( 'fec.id_fe_contract = ?', $id )
				    ->where( 'bco.status = ?', 1 )
				    ->order( array( 'id_budgetcategory_contract' ) );
	
	return $dbBudgetCategory->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listItemExpenses( $id )
    {
	$dbRiBudgetCategory = App_Model_DbTable_Factory::get( 'RIBudgetCategory' );
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	
	$select = $dbRiBudgetCategory->select()
				     ->from( array( 'ribc' => $dbRiBudgetCategory ) )
				     ->setIntegrityCheck( false )
				     ->join(
					array( 'bc' => $dbBudgetContract ),
					'bc.id_budgetcategory_contract = ribc.fk_id_budgetcategory_contract',
					array( 'fk_id_budget_category' )
				     )
				     ->where( 'ribc.fk_id_fe_contract = ?', $id )
				     ->order( array( 'description' ) );
	
	return $dbRiBudgetCategory->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detail( $id )
    {
	$select = $this->getSelect();
	$select->where( 'fec.id_fe_contract = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function getContractByTrainee( $id )
    {
	$select = $this->getSelect();
	$select->where( 'fec.fk_id_trainee = ?', $id );
	
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
	
	if ( !empty( $filters['fk_id_fefpenterprise'] ) )
	    $select->where( 'fec.fk_id_fefpenterprise = ?', $filters['fk_id_fefpenterprise'] );
	
	if ( !empty( $filters['fk_id_fefpeduinstitution'] ) )
	    $select->where( 'fec.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution'] );
	
	if ( !empty( $filters['fk_id_adddistrict'] ) )
	    $select->where( 'fec.fk_id_adddistrict = ?', $filters['fk_id_adddistrict'] );
	
	if ( !empty( $filters['fk_id_addsubdistrict'] ) )
	    $select->where( 'fec.fk_id_addsubdistrict = ?', $filters['fk_id_addsubdistrict'] );
	
	if ( !empty( $filters['fk_id_scholarity_area'] ) )
	    $select->where( 'fec.fk_id_scholarity_area = ?', $filters['fk_id_scholarity_area'] );
	
	if ( !empty( $filters['fk_id_profocupationtimor'] ) )
	    $select->where( 'fec.fk_id_profocupationtimor = ?', $filters['fk_id_profocupationtimor'] );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'fec.date_start >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'fec.date_finish <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
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
	    'fk_id_sysform'	    => Fefop_Form_FEContract::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}