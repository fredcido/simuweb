<?php

class Fefop_Model_Mapper_TypeTransaction extends App_Model_Abstract
{
    /**
     * 
     * @var Model_DbTable_FEFOPTypeTransaction
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEFOPTypeTransaction();

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

	    $row = $this->_checkTypeTransaction( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Tipu Transasaun iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_budget_category_type'] ) )
		$history = 'INSERE TIPU TRANSASAUN: %s - INSERIDO NOVO TIPU TRANSASAUN';
	    else
		$history = 'ALTERA TIPU TRANSASAUN: %s - ALTERADO TIPU TRANSASAUN';
	   
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
    protected function _checkTypeTransaction()
    {
	$select = $this->_dbTable->select()->where( 'description = ?', $this->_data['description'] );

	if ( !empty( $this->_data['id_fefop_type_transaction'] ) )
	    $select->where( 'id_fefop_type_transaction <> ?', $this->_data['id_fefop_type_transaction'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listAll()
    {
	$dbTypeTransaction = $this->_dbTable;
	$dbTransaction = App_Model_DbTable_Factory::get( 'FEFOPTransaction' );
	$dbBankStatement = App_Model_DbTable_Factory::get( 'FEFOPBankStatements' );
	
	$select = $dbTypeTransaction->select()
				->from( 
				    array( 'tt' => $dbTypeTransaction ) 
				)
				->joinLeft(
				    array( 't' => $dbTransaction ),
				    't.fk_id_fefop_type_transaction = tt.id_fefop_type_transaction',
				    array(
					'can_delete' => new Zend_Db_Expr(
						'COALESCE('
						    . 't.id_fefop_transaction,'
						    . 'bs.id_fefop_bank_statements'
						. ')'
					)
				    )
				)
				->joinLeft(
				    array( 'bs' => $dbBankStatement ),
				    'bs.fk_id_fefop_type_transaction = tt.id_fefop_type_transaction',
				    array()
				)
				->setIntegrityCheck( false )
				->group( array( 'id_fefop_type_transaction' ) )
				->order( array( 'description' ) );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function removeTransactionType( $data )
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $expense = $this->fetchRow( $data['id'] );
	    $expense->delete();
	    
	    $history = sprintf( 'REMOVE TIPU TRANSASAUM: %s - REMOVE TIPU TRANSASAUM', $data['id'] );
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
	    'fk_id_sysform'	    => Fefop_Form_TypeTransaction::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}