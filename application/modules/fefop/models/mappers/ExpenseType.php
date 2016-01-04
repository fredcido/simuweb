<?php

class Fefop_Model_Mapper_ExpenseType extends App_Model_Abstract
{
    const FORMATION = 1;
    
    const MATERIALS = 2;
    
    const ADDITIONALS = 3;
    
    const INITIAL = 4;
    
    const ANNUAL = 5;
    
    const REVENUE = 6;

    /**
     * 
     * @var Model_DbTable_BudgetCategoryType
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_BudgetCategoryType();

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

	    $row = $this->_checkExpenseType( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Komponente iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_budget_category_type'] ) )
		$history = 'INSERE KOMPONENTE: %s - INSERIDO NOVO KOMPONENTE';
	    else
		$history = 'ALTERA KOMPONENTE: %s - ALTERADO KOMPONENTE';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['description'] );
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
     * @param array $data
     * @return App_Model_DbTable_Row_Abstract
     */
    protected function _checkExpenseType()
    {
	$select = $this->_dbTable->select()->where( 'description = ?', $this->_data['description'] );

	if ( !empty( $this->_data['id_budget_category_type'] ) )
	    $select->where( 'id_budget_category_type <> ?', $this->_data['id_budget_category_type'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listAll()
    {
	$dbExpenseType = $this->_dbTable;
	
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	$dbTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbContractFund = App_Model_DbTable_Factory::get( 'FEFOPContractFund' );
	
	$select = $dbExpenseType->select()
				->from( array( 'et' => $dbExpenseType ) )
				->setIntegrityCheck( false )
				->joinLeft(
				    array( 'bc' => $dbBudgetCategory ),
				    'bc.fk_id_budget_category_type = et.id_budget_category_type',
				    array(
					'can_delete' => new Zend_Db_Expr(
						'COALESCE('
						    . 'bc.id_budget_category,'
						    . 't.id_fefop_transaction,'
						    . 'cf.id_fefop_contract_fund'
						. ')'
					)
				    )
				)
				->joinLeft(
				    array( 't' => $dbTransaction ),
				    't.fk_id_budget_category_type = et.id_budget_category_type',
				    array()
				)
				->joinLeft(
				    array( 'cf' => $dbContractFund ),
				    'cf.fk_id_budget_category_type = et.id_budget_category_type',
				    array()
				)
				->order( array( 'description' ) )
				->group( array( 'id_budget_category_type' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function removeExpenseType( $data )
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $expense = $this->fetchRow( $data['id'] );
	    $expense->delete();
	    
	    $history = sprintf( 'REMOVE KOMPONENTE: %s - REMOVE KOMPONENTE', $data['id'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return array( 'status' => true );
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    return array( 'status' => false );
	}
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
	    'fk_id_sysform'	    => Fefop_Form_ExpenseType::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}