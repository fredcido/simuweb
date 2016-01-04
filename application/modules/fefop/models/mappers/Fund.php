<?php

class Fefop_Model_Mapper_Fund extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_FEFOPFund
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEFOPFund();

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

	    $row = $this->_checkFund( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Fundu iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_fefopfund'] ) )
		$history = 'FUNDU: %s - INSERIDO NOVO FUNDU';
	    else
		$history = 'FUNDU RÚBRICA: %s - ALTERADO FUNDU';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['name_fund'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return $id;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @return int|bool
     */
    public function savePlanning()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbFundPlanning = App_Model_DbTable_Factory::get( 'FundPlanning' );
	    $dbFundPlanningModule = App_Model_DbTable_Factory::get( 'FundPlanningModule' );
	    
	    $where = array(
		'fk_id_fefopfund = ?' => $this->_data['fk_id_fefopfund'],
		'year_planning = ?' => $this->_data['year_planning']
	    );
	    
	    $this->_data['amount'] = App_General_String::toFloat( $this->_data['amount'] );
	    $this->_data['additional_cost'] = App_General_String::toFloat( $this->_data['additional_cost'] );
	    
	    $planning = $dbFundPlanning->fetchRow( $where );
	    if ( empty( $planning ) )
		$planning = $dbFundPlanning->createRow();
	    
	    $planning->setFromArray( $this->_data );
	    $idPlanning = $planning->save();
	    
	    foreach ( $this->_data['modules_cost'] as $module => $cost ) {
		
		$where = array(
		    'fk_id_fund_planning = ?' => $idPlanning,
		    'fk_id_fefop_modules = ?' => $module
		);
		
		$planningModule = $dbFundPlanningModule->fetchRow( $where );
		if ( empty( $planningModule ) ) {
		    
		    $planningModule = $dbFundPlanningModule->createRow();
		    $planningModule->fk_id_fund_planning = $idPlanning;
		    $planningModule->fk_id_fefop_modules = $module;
		}
		
		$planningModule->amount = App_General_String::toFloat( $cost );
		$planningModule->save();
	    }
	    
	    $history = 'PLANEAMENTU FUNDU: %s - BA TINAN: %s';
	    $history = sprintf( $history, $this->_data['fk_id_fefopfund'], $this->_data['year_planning'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return $idPlanning;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
   
    /**
     *
     * @param array $data
     * @return App_Model_DbTable_Row_Abstract
     */
    protected function _checkFund()
    {
	$select = $this->_dbTable->select()
			->where( 'name_fund = ?', $this->_data['name_fund'] );

	if ( !empty( $this->_data['id_fefopfund'] ) )
	    $select->where( 'id_fefopfund <> ?', $this->_data['id_fefopfund'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @param int $fund
     * @param int $year
     * @return Zend_Db_Table_Rowset
     */
    public function fetchPlanning( $fund, $year )
    {
	$dbFundPlanning = App_Model_DbTable_Factory::get( 'FundPlanning' );
	$dbFundPlanningModule = App_Model_DbTable_Factory::get( 'FundPlanningModule' );
	$dbFefopModule = App_Model_DbTable_Factory::get( 'FEFOPModules' );
	
	$select = $dbFundPlanningModule->select()
					->from( array( 'pm' => $dbFundPlanningModule ) )
					->setIntegrityCheck( false )
					->join(
					    array( 'm' => $dbFefopModule ),
					    'm.id_fefop_modules = pm.fk_id_fefop_modules',
					    array(
						'num_module' => 'acronym',
						'module' => 'description'
					    )	
					)
					->join(
					    array( 'p' => $dbFundPlanning ),
					    'p.id_fund_planning = pm.fk_id_fund_planning',
					    array( 'additional_cost' )	
					)
					->where( 'p.year_planning = ?', $year )
					->where( 'p.fk_id_fefopfund = ?', $fund );
	
	return $dbFundPlanning->fetchAll( $select );
    }
    
    public function listTotals()
    {
	$dbFundPlanning = App_Model_DbTable_Factory::get( 'FundPlanning' );
	$dbFEFOPFund = App_Model_DbTable_Factory::get( 'FEFOPFund' );
	
	$select = $dbFEFOPFund->select()
					->from( array( 'f' => $dbFEFOPFund ) )
					->setIntegrityCheck( false )
					->join(
					    array( 'p' => $dbFundPlanning ),
					    'p.fk_id_fefopfund = f.id_fefopfund',
					    array( 'year_planning', 'amount', 'additional_cost' )	
					)
					->order( array( 'year_planning', 'name_fund' ) );
	
	$rows = $dbFEFOPFund->fetchAll( $select );
	
	$mapperReport = new Report_Model_Mapper_Fefop();
	$currentBalanceReport = $mapperReport->donorContractCostReport();
	
	$currentBalance = array();
	foreach ( $currentBalanceReport['item']['donor'] as $current )
	    foreach ( $current as $fund => $values )
		$currentBalance[$fund] = $values['balance'];
	
	$totals = array();
	$years = array();
	$balance = array();
	$additional = array();
	$totalBalance = 0;
	
	foreach ( $rows as $row ) {
	    
	    if ( empty( $years[$row['year_planning']] ) )
		$years[$row['year_planning']] = 0;
	    
	    if ( empty( $totals[$row['name_fund']] ) )
		$totals[$row['name_fund']] = array();
	    
	    $totals[$row['name_fund']][$row['year_planning']] = $row->amount;
	    $additional[$row['name_fund']][$row['year_planning']] = $row->additional_cost;
	    $balance[$row['name_fund']] = empty( $currentBalance[$row['id_fefopfund']] ) ? 0 : $currentBalance[$row['id_fefopfund']];
	    
	    $years[$row['year_planning']] += (float)$row->amount;
	    $totalBalance += $balance[$row['name_fund']];
	}
	
	return array(
	    'years'	    => $years,
	    'totals'	    => $totals,
	    'balance'	    => $balance,
	    'total_balance' => $totalBalance
	);
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
	    'fk_id_sysform'	    => Fefop_Form_Fund::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}