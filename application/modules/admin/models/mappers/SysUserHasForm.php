<?php

class Admin_Model_Mapper_SysUserHasForm extends App_Model_Abstract
{
    const VIEW = 4;
    
    const SAVE = 1;
    
    const HAMOS = 4;
    
    /**
     * 
     * @var Model_DbTable_SysUserHasForm
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_SysUserHasSysForm();

	return $this->_dbTable;
    }

    /**
     * 
     * @return int|bool
     */
    public function save()
    {
	try {
	    
	    if ( !empty( $this->_data['insert'] ) ) {

		$this->_data['fk_id_sysuser'] = $this->_data['user'];
		$this->_data['fk_id_sysform'] = $this->_data['id_sysform'];
		$this->_data['fk_id_sysoperation'] = $this->_data['id_sysoperation'];
		
		parent::_simpleSave();
		
	    } else {

		$where = array(
		    'fk_id_sysform = ?'		=>  $this->_data['id_sysform'],
		    'fk_id_sysoperation = ?'    =>  $this->_data['id_sysoperation'],
		    'fk_id_sysuser = ?'		=>  $this->_data['user']
		);
		
		$this->_getDbTable()->delete( $where );
	    }
	    
	    return true;
	    
	} catch ( Exception $e ) {

	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    
	    return false;
	}
    }
    
    /**
     *
     * @return boolean 
     */
    public function copyPermissions()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    // Fetch the user permissions
	    $dbSysUserHasForm = App_Model_DbTable_Factory::get( 'SysUserHasSysForm' );
	    $permissions = $dbSysUserHasForm->fetchAll( array( 'fk_id_sysuser = ?' => $this->_data['source'] ) );
            
	    $dbSysUserHasForm->delete( array( 'fk_id_sysuser = ?' => $this->_data['id'] ) );
	    
	    foreach ( $permissions as $permission ) {
		
		$row = $dbSysUserHasForm->createRow( $permission->toArray() );
		$row->fk_id_sysuser = $this->_data['id'];
		$row->id_relationship = null;
		$row->save();
	    }
	    
	    $dbAdapter->commit();
	    return true;
	    
	} catch ( Exception $e ) {
	    
	    $dbAdapter->rollBack();
	    return false;
	}
    }
    
    /**
     *
     * @param array $filter
     * @return Zend_Db_Table_Rowset
     */
    public function listGroupedOperations()
    {
	$dbSysModule = App_Model_DbTable_Factory::get( 'SysModule' );
	$dbSysForm = App_Model_DbTable_Factory::get( 'SysForm' );
	$dbSysOperation = App_Model_DbTable_Factory::get( 'SysOperations' );
	$dbSysFormHasOperation = App_Model_DbTable_Factory::get( 'SysFormHasSysOperations' );
	
	$select = $dbSysModule->select()
			      ->from( array( 'm' => $dbSysModule ) )
			      ->setIntegrityCheck( false )
			      ->join(
				    array( 'f' => $dbSysForm ),
				    'f.fk_id_sysmodule = m.id_sysmodule',
				    array( 'form', 'id_sysform' )
			      )
			      ->join(
				    array( 'fo' => $dbSysFormHasOperation ),
				    'f.id_sysform = fo.fk_id_sysform',
				    array()
			      )
			      ->join(
				    array( 'o' => $dbSysOperation ),
				    'o.id_sysoperation = fo.fk_id_sysoperation',
				    array( 'operation', 'id_sysoperation' )
			      )
			      ->where( 'f.active = ?', 1 )
			      ->order( array( 'module', 'form' ) );
	
	$rows = $dbSysModule->fetchAll( $select );
	
	$data = array();
	foreach ( $rows as $row ) {
	    
	    if ( !array_key_exists( $row->id_sysmodule, $data ) ) {
		
		$data[$row->id_sysmodule] = array(
		    'module' => $row->module,
		    'forms'  => array()
		);
	    }
	    
	    if ( !array_key_exists( $row->id_sysform, $data[$row->id_sysmodule]['forms'] ) ) {
		
		$data[$row->id_sysmodule]['forms'][$row->id_sysform] = array(
		    'form'		=> $row->form,
		    'operations'	=> array()
		);
	    }
	    
	    $data[$row->id_sysmodule]['forms'][$row->id_sysform]['operations'][] = $row;
	}
	
	return $data;
    }
}