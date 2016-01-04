<?php

class Admin_Model_Mapper_SysForm extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_SysForm
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_SysForm();

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
	    
	    $dataForm = $this->_data;
	    
	    $row = $this->_checkNameForm( $this->_data );

	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Formulariu iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_sysform'] ) )
		$this->_data['active'] = 1;

	    $id = parent::_simpleSave();
	    
	    $dbFormOperations = App_Model_DbTable_Factory::get( 'SysFormHasSysOperations' );
	    $rows = $dbFormOperations->fetchAll( array( 'fk_id_sysform = ?' => $id ) );

	    $operations = array();
	    foreach ( $rows as $row )
		$operations[] = $row->fk_id_sysoperation;
	    
	    if ( empty( $dataForm['operations'] ) )
		$dataForm['operations'] = array();
	    
	    $delete = array_diff( $operations, $dataForm['operations'] );
	    $new = array_diff( $dataForm['operations'], $operations );
	    
	    foreach ( $new as $newOne ) {
		
		$row = $dbFormOperations->createRow();
		$row->fk_id_sysform = $id;
		$row->fk_id_sysoperation = $newOne;
		$row->fk_id_sysmodule = $this->_data['fk_id_sysmodule'];
		$row->save();
	    }
	    
	    if ( !empty( $delete ) ) {
		
		$dbUserForm = App_Model_DbTable_Factory::get('SysUserHasSysForm' );
		$where = array(
		    $dbAdapter->quoteInto( 'fk_id_sysform = ?', $id  ),
		    $dbAdapter->quoteInto( 'fk_id_sysoperation IN (?)', $delete  )
		);
		
		$dbUserForm->delete( $where );
		$dbFormOperations->delete( $where );
	    }
	    
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
    protected function _checkNameForm()
    {
	$select = $this->_dbTable->select()->where( 'form = ?', $this->_data['form'] );

	if ( !empty( $this->_data['id_sysform'] ) )
	    $select->where( 'id_sysform <> ?', $this->_data['id_sysform'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
     /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll( $filters = array() )
    {
	$dbSysForm = App_Model_DbTable_Factory::get( 'SysForm' );
	$dbSysModule = App_Model_DbTable_Factory::get( 'SysModule' );
	
	$select = $dbSysForm->select()
			     ->setIntegrityCheck( false )
			     ->from( array( 'f' => $dbSysForm ) )
			     ->join(
				array( 'm' => $dbSysModule ),
				'f.fk_id_sysmodule = m.id_sysmodule',
				array( 'module' )
			     )
			     ->order( array( 'module', 'form' ) );
	
	if ( !empty( $filters['module'] ) )
	    $select->where( 'f.fk_id_sysmodule = ?', $filters['module'] );
	
	return $dbSysForm->fetchAll( $select );
    }
}