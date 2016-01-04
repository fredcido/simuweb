<?php

class Admin_Model_Mapper_Group extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_UserGroup
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_UserGroup();

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

	    $row = $this->_checkNameGroup( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Grupu iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_usergroup'] ) )
		$history = 'INSERE GRUPU: %s DADUS PRINCIPAL - INSERE NOVO GRUPU';
	    else
		$history = 'ALTERA GRUPU: %s DADUS PRINCIPAL - ALTERA GRUPU';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['name'] );
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
    protected function _checkNameGroup()
    {
	$select = $this->_dbTable->select()->where( 'name = ?', $this->_data['name'] );

	if ( !empty( $this->_data['id_usergroup'] ) )
	    $select->where( 'id_usergroup <> ?', $this->_data['id_usergroup'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return mixed 
     */
    public function saveItens()
    {
	$method = 'save' . App_General_String::toCamelCase( $this->_data['action'] );
	return call_user_func( array( $this, $method ) );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveUser()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $users = $this->listUserGroup( $this->_data['group'] );
	    
	    $usersGroup = array();
	    foreach ( $users as $user )
		$usersGroup[] = $user->id_sysuser;
	    
	    $newUsers = array_diff( $this->_data['users'], $usersGroup );
	    if ( empty( $newUsers ) ) {
		
		$this->_message->addMessage( 'Uzuariu hotu iha grupu tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    $dbUserGroup = App_Model_DbTable_Factory::get( 'UserGroup_has_SysUser' );
	    foreach ( $newUsers as $user ) {
		
		$row = $dbUserGroup->createRow();
		$row->fk_id_usergroup = $this->_data['group'];
		$row->fk_id_sysuser = $user;
		$row->save();
	    }
	    
	    $history = 'ATUALIZA GRUPU: %s - INSERE UZUARIU FOUN';
	    
	    // Save the audit
	    $history = sprintf( $history, $this->_data['group'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    return $this->_data['group'];
	    
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
    public function saveDeleteUser()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbUserGroup = App_Model_DbTable_Factory::get( 'UserGroup_has_SysUser' );
	    $where = array(
		'fk_id_usergroup = ?' => $this->_data['group'],
		'fk_id_sysuser IN (?)' => (array)$this->_data['users']
	    );
	    $dbUserGroup->delete( $where );
	    
	    $history = 'ATUALIZA GRUPU: %s - REMOVE UZUARIU';
	    
	    // Save the audit
	    $history = sprintf( $history, $this->_data['group'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    return $this->_data['group'];
	    
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
    public function saveTypeNote()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $types = $this->listTypesGroup( $this->_data['group'] );
	    
	    $typeGroup = array();
	    foreach ( $types as $type )
		$typeGroup[] = $type->id_notetype;
	    
	    $newTypes = array_diff( $this->_data['types'], $typeGroup );
	    if ( empty( $newTypes ) ) {
		
		$this->_message->addMessage( 'Tipu nota hotu iha grupu tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    $dbUserGroupNoteType = App_Model_DbTable_Factory::get( 'UserGroup_has_NoteType' );
	    foreach ( $newTypes as $type ) {
		
		$row = $dbUserGroupNoteType->createRow();
		$row->fk_id_usergroup = $this->_data['group'];
		$row->fk_id_notetype = $type;
		$row->save();
	    }
	    
	    $history = 'ATUALIZA GRUPU: %s - INSERE TIPU NOTA FOUN';
	    
	    // Save the audit
	    $history = sprintf( $history, $this->_data['group'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    return $this->_data['group'];
	    
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
    public function saveDeleteTypeNote()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbUserGroupNoteType = App_Model_DbTable_Factory::get( 'UserGroup_has_NoteType' );
	    $where = array(
		'fk_id_usergroup = ?' => $this->_data['group'],
		'fk_id_notetype IN (?)' => (array)$this->_data['types']
	    );
	    $dbUserGroupNoteType->delete( $where );
	    
	    $history = 'ATUALIZA GRUPU: %s - REMOVE TIPU NOTA';
	    
	    // Save the audit
	    $history = sprintf( $history, $this->_data['group'] );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    return $this->_data['group'];
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
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
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::ADMIN,
	    'fk_id_sysform'	    => Admin_Form_Group::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listUsers( $id )
    {   
        $dbSysUser = App_Model_DbTable_Factory::get( 'SysUser' );
        $dbUserGroup = App_Model_DbTable_Factory::get( 'UserGroup_has_SysUser' );
        
        $subSelect = $dbUserGroup->select()
                                ->from( array( 'ug' => $dbUserGroup ) )
                                ->setIntegrityCheck( false )
                                ->where( 'ug.fk_id_sysuser = u.id_sysuser' )
                                ->where( 'ug.fk_id_usergroup = ?', $id );
        
        $select = $dbSysUser->select()
                            ->from( array( 'u' => $dbSysUser ) )
                            ->setIntegrityCheck( false )
                            ->where( 'NOT EXISTS(?)', $subSelect )
                            ->order( array( 'name' ) );
        
        return $dbSysUser->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listUserGroup( $id )
    {
        $dbSysUser = App_Model_DbTable_Factory::get( 'SysUser' );
        $dbUserGroup = App_Model_DbTable_Factory::get( 'UserGroup_has_SysUser' );
        
        $subSelect = $dbUserGroup->select()
                                ->from( array( 'ug' => $dbUserGroup ) )
                                ->setIntegrityCheck( false )
                                ->where( 'ug.fk_id_sysuser = u.id_sysuser' )
                                ->where( 'ug.fk_id_usergroup = ?', $id );
        
        $select = $dbSysUser->select()
                            ->from( array( 'u' => $dbSysUser ) )
                            ->setIntegrityCheck( false )
                            ->where( 'EXISTS(?)', $subSelect )
                            ->order( array( 'name' ) );
        
        return $dbSysUser->fetchAll( $select );
    }
    
     /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listTypes( $id )
    {
        $dbNoteType = App_Model_DbTable_Factory::get( 'NoteType' );
        $dbUserGroupNote = App_Model_DbTable_Factory::get( 'UserGroup_has_NoteType' );
        
        $subSelect = $dbUserGroupNote->select()
                                ->from( array( 'ugn' => $dbUserGroupNote ) )
                                ->setIntegrityCheck( false )
                                ->where( 'ugn.fk_id_notetype = nt.id_notetype' )
                                ->where( 'ugn.fk_id_usergroup = ?', $id );
        
        $select = $dbNoteType->select()
                            ->from( array( 'nt' => $dbNoteType ) )
                            ->setIntegrityCheck( false )
                            ->where( 'NOT EXISTS(?)', $subSelect )
                            ->order( array( 'name' ) );
        
        return $dbNoteType->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listTypesGroup( $id )
    {
        $dbNoteType = App_Model_DbTable_Factory::get( 'NoteType' );
        $dbUserGroupNote = App_Model_DbTable_Factory::get( 'UserGroup_has_NoteType' );
        
        $subSelect = $dbUserGroupNote->select()
                                ->from( array( 'ugn' => $dbUserGroupNote ) )
                                ->setIntegrityCheck( false )
                                ->where( 'ugn.fk_id_notetype = nt.id_notetype' )
                                ->where( 'ugn.fk_id_usergroup = ?', $id );
        
        $select = $dbNoteType->select()
                            ->from( array( 'nt' => $dbNoteType ) )
                            ->setIntegrityCheck( false )
                            ->where( 'EXISTS(?)', $subSelect )
                            ->order( array( 'name' ) );
        
        return $dbNoteType->fetchAll( $select );
    }
}