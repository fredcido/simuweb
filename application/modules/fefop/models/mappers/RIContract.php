<?php

class Fefop_Model_Mapper_RIContract extends App_Model_Abstract
{

    const LIMIT_AMOUNT = 80000;
    
    const MOUNTH_LIMIT = 24;
    
    /**
     * 
     * @var Model_DbTable_RIContract
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_RIContract();

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
	    
	    $dateStart = new Zend_Date( $this->_data['date_start'] );
	    $dateFinish = new Zend_Date( $this->_data['date_finish'] );
	    
	    $mapperRule = new Fefop_Model_Mapper_Rule();
	    $mapperRule->validate( $this->_message, $this->_data, Fefop_Model_Mapper_Expense::CONFIG_PFPCI_RI );
	    
	    // Check if the initial date is later than finish date
	    if ( $dateStart->isLater( $dateFinish ) ) {
		
		$this->_message->addMessage( 'Data loron keta liu data remata.' );
		$this->addFieldError( 'date_start' )->addFieldError( 'date_finish' );
		return false;
	    }
	    
	    // If there is no contract yet
	    if ( empty( $this->_data['fk_id_fefop_contract'] ) ) {
		
		$dataContract = array(
		    'module'	=> Fefop_Model_Mapper_Module::RI,
		    'district'	=> $this->_data['fk_id_adddistrict']
		);
		
		$mapperFefopContract = new Fefop_Model_Mapper_Contract();
		$this->_data['fk_id_fefop_contract'] = $mapperFefopContract->save( $dataContract );
	    }
	    
	    $this->_data['amount'] = App_General_String::toFloat( $this->_data['amount'] );
	    
	    $this->_data['date_start'] = $dateStart->toString( 'yyyy-MM-dd' );
	    $this->_data['date_finish'] = $dateFinish->toString( 'yyyy-MM-dd' );
	   
	    $dataForm = $this->_data;
	    
	    // Save the contract
	    $dataForm['id_ri_contract'] = parent::_simpleSave();
	    
	    // Save budget category
	    $this->_saveExpenses( $dataForm );
	    
	    if ( empty( $this->_data['id_ri_contract'] ) )
		$history = 'REJISTU KONTRAKTU RI: %s';
	    else
		$history = 'ATUALIZA KONTRAKTU RI: %s';
	    
	    $history = sprintf( $history, $dataForm['id_ri_contract'] );
	    $this->_sysAudit( $history );
	    
	    if ( $this->_data['amount'] > self::LIMIT_AMOUNT )
		$this->_sendWarningAmount( $dataForm['id_ri_contract'] );
	    
	    $diff = $dateFinish->sub( $dateStart );
	    
	    $measure = new Zend_Measure_Time( $diff->toValue(), Zend_Measure_Time::SECOND );
	    $diffMonth = preg_replace( '/[^0-9.]/i', '', $measure->convertTo( Zend_Measure_Time::MONTH, 0 ) );
	    
	    if ( (float)$diffMonth > self::MOUNTH_LIMIT )
		$this->_sendWarningDuration( $dataForm['id_ri_contract'] );
	    
	    $dbAdapter->commit();
	    
	    return $dataForm['id_ri_contract'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @param int $idRiContract
     */
    protected function _sendWarningAmount( $idRiContract )
    {
	// Search the user who must receive notes when the amount is ultrapassed
	$noteTypeMapper = new Admin_Model_Mapper_NoteType();
	$users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::RI_AMOUNT_GREATER );

	$noteModelMapper = new Default_Model_Mapper_NoteModel();
	$noteMapper = new Default_Model_Mapper_Note();
	
	$dataNote = array(
	    'title'   => 'RI KONTRATU HO FOLIN HIRA LIU $' . self::LIMIT_AMOUNT,
	    'level'   => 0,
	    'message' => $noteModelMapper->getRIGreaterAmount( $this->detail( $idRiContract ) ),
	    'users'   => $users
	);
	

	$noteMapper->setData( $dataNote )->saveNote();
    }
    
    /**
     * 
     * @param int $idRiContract
     */
    protected function _sendWarningDuration( $idRiContract )
    {
	// Search the user who must receive notes when the duration is ultrapassed
	$noteTypeMapper = new Admin_Model_Mapper_NoteType();
	$users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::RI_DURATION_GREATER );

	$noteModelMapper = new Default_Model_Mapper_NoteModel();
	$noteMapper = new Default_Model_Mapper_Note();
	
	$dataNote = array(
	    'title'   => 'RI KONTRATU HO DURASAUN KLEUR',
	    'level'   => 0,
	    'message' => $noteModelMapper->getRIGreaterDuration( $this->detail( $idRiContract ) ),
	    'users'   => $users
	);
	

	$noteMapper->setData( $dataNote )->saveNote();
    }
    
    /**
     * 
     * @param array $data
     */
    protected function _saveExpenses( $data )
    {
	$dbRIBudgetCategory = App_Model_DbTable_Factory::get( 'RIBudgetCategory' );
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	
	// Delete all the detailed items
	$where = array( 'fk_id_ri_contract = ?' => $data['id_ri_contract'] );
	$dbRIBudgetCategory->delete( $where );
	
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
	    $idBudgetContract = $row->save();
	    
	    // If it wasn't defined detailed items
	    if ( empty( $data['item_expense'][$id] ) ) continue;
	    
	    // For each budget category, save its detailed items
	    foreach ( $data['item_expense'][$id] as $count => $itemExpense ) {
		
		$rowItemExpense = $dbRIBudgetCategory->createRow();
		$rowItemExpense->fk_id_ri_contract = $data['id_ri_contract'];
		$rowItemExpense->fk_id_budgetcategory_contract = $idBudgetContract;
		$rowItemExpense->description = $itemExpense;
		$rowItemExpense->quantity = $data['quantity'][$id][$count];
		$rowItemExpense->amount_unit = App_General_String::toFloat( $data['amount_unit'][$id][$count] );
		$rowItemExpense->amount_total = App_General_String::toFloat( $data['amount_total'][$id][$count] );
		$rowItemExpense->comments = $data['comments'][$id][$count];
		$rowItemExpense->save();
	    }
	}
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
	$mapperContract = new Fefop_Model_Mapper_Contract();
	$select = $mapperContract->getSelect();
	
	$dbRiContract = App_Model_DbTable_Factory::get( 'RIContract' );
	$dbInstitute = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbSubDistrict = App_Model_DbTable_Factory::get( 'AddSubDistrict' );
	$dbBudgetContract = App_Model_DbTable_Factory::get( 'BudgetCategoryHasFEFOPContract' );
	
	$select->join(
		    array( 'ric' => $dbRiContract ),
		    'ric.fk_id_fefop_contract = c.id_fefop_contract'
		)
		->join(
		    array( 'ei' => $dbInstitute ),
		    'ei.id_fefpeduinstitution = ric.fk_id_fefpeduinstitution',
		    array( 'institute' => 'institution' )
		)
		->join(
		    array( 'sd' => $dbSubDistrict ),
		    'ric.fk_id_addsubdistrict = sd.id_addsubdistrict',
		    array( 'sub_district' )
		)
		->joinLeft(
		    array( 'bcc' => $dbBudgetContract ),
		    'bcc.fk_id_fefop_contract = c.id_fefop_contract',
		    array()
		)
		->group( array( 'ric.id_ri_contract' ) );
	
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
	$dbRIContract = App_Model_DbTable_Factory::get( 'RIContract' );
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
					array( 'ric' => $dbRIContract ),
					'ric.fk_id_fefop_contract = bco.fk_id_fefop_contract',
					array()
				    )
				    ->join(
					array( 'fc' => $dbFEFOPContract ),
					'ric.fk_id_fefop_contract = fc.id_fefop_contract',
					array()
				    )
				    ->where( 'ric.id_ri_contract = ?', $id )
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
				     ->where( 'ribc.fk_id_ri_contract = ?', $id )
				     ->order( array( 'id_ri_budget_category' ) );
	
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
	$select->where( 'ric.id_ri_contract = ?', $id );
	
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
	
	if ( !empty( $filters['fk_id_fefpeduinstitution'] ) )
	    $select->where( 'ric.fk_id_fefpeduinstitution = ?', $filters['fk_id_fefpeduinstitution'] );
	
	if ( !empty( $filters['minimum_amount'] ) )
	    $select->where( 'ric.amount >= ?', (float)$filters['minimum_amount'] );
	
	if ( !empty( $filters['maximum_amount'] ) )
	    $select->where( 'ric.amount <= ?', (float)$filters['maximum_amount'] );
	
	if ( !empty( $filters['fk_id_adddistrict'] ) )
	    $select->where( 'ric.fk_id_adddistrict = ?', $filters['fk_id_adddistrict'] );
	
	if ( !empty( $filters['fk_id_addsubdistrict'] ) )
	    $select->where( 'ric.fk_id_addsubdistrict = ?', $filters['fk_id_addsubdistrict'] );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'ric.date_start >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'ric.date_finish <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
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
	    'fk_id_sysform'	    => Fefop_Form_RIContract::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}