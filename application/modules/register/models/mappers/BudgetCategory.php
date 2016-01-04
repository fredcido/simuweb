<?php

class Register_Model_Mapper_BudgetCategory extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_BudgetCategory
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_BudgetCategory();

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
	    
	    $row = $this->_checkBudgetCategory( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Categoria Orsamentu iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_budget_category'] ) )
		$history = 'REJISTRU CATEGORIA ORSAMENTU: %s';
	    else
		$history = 'ALTERA CATEGORIA ORSAMENTU: %s';
	    
	    $id = parent::_simpleSave();
	    
	    // Save the client history
	    $history = sprintf( $history, $id );
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
    protected function _checkBudgetCategory()
    {
	$select = $this->_dbTable->select()
			->where( 'description = ?', $this->_data['description'] )
			->where( 'type = ?', $this->_data['type'] );

	if ( !empty( $this->_data['id_budget_category'] ) )
	    $select->where( 'id_budget_category <> ?', $this->_data['id_budget_category'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset 
     */
    public function listAll()
    {
	$dbBudgetCategory = App_Model_DbTable_Factory::get( 'BudgetCategory' );
	return $dbBudgetCategory->fetchAll( array(), array( 'type' ) );
    }
    
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::REGISTER,
	    'fk_id_sysform'	    => Register_Form_BudgetCategory::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}