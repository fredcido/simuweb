<?php

class Default_Model_Mapper_Note extends App_Model_Abstract
{
    
    /**
     * 
     * @var Model_DbTable_Note
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_Note();

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
	    
	    $id = $this->saveNote();
	    
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
     * @return int
     */
    public function saveNote()
    {
	$users = $this->getUsers( $this->_data );
	    
	if ( !empty( $this->_data['date_scheduled'] ) ) {

	    $dateSheduled = new Zend_Date( $this->_data['date_scheduled'] );
	    $this->_data['date_scheduled'] = $dateSheduled->toString( 'yyyy-MM-dd' );
	}

	// Save the note
	$id = parent::_simpleSave();

	$dbNoteTarget = App_Model_DbTable_Factory::get( 'NoteTarget' );

	foreach ( $users as $user ) {

	    $row = $dbNoteTarget->createRow();
	    $row->fk_id_note = $id;
	    $row->fk_id_sysuser = $user;
	    $row->save();
	}
	
	return $id;
    }
    
    /**
     *
     * @param array $data
     * @return array 
     */
    public function getUsers( $data )
    {
	$users = $data['users'];
	
	$mapperGroup = new Admin_Model_Mapper_Group();
	
	if ( !empty( $data['groups'] ) ) {
	    
	    foreach ( $data['groups'] as $group ) {

		$usersGroup = $mapperGroup->listUserGroup( $group );
		foreach ( $usersGroup as $userGroup )
		    $users[] = $userGroup->id_sysuser;
	    }
	}
	
	return array_unique( $users );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function finishNote()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbNoteTarget = App_Model_DbTable_Factory::get( 'NoteTarget' );
	    
	    $data = array(
		'read'	    => 1,
		'finished'  => Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm' )
	    );
	    
	    $where = array(
		'fk_id_note = ?'    => $this->_data['note'],
		'fk_id_sysuser = ?' => Zend_Auth::getInstance()->getIdentity()->id_sysuser
	    );
	    
	    $dbNoteTarget->update( $data, $where );
	    
	    $dbAdapter->commit();
	    return true;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset 
     */
    public function listNotesToUser( $id )
    {
	$dbNote = App_Model_DbTable_Factory::get( 'Note' );
	$dbNoteTarget = App_Model_DbTable_Factory::get( 'NoteTarget' );
	
	$select = $dbNote->select()
			 ->from( array( 'n' => $dbNote ) )
			 ->setIntegrityCheck( false )
			 ->join(
			    array( 'nt' => $dbNoteTarget ),
			    'nt.fk_id_note = n.id_note',
			    array(
				'date' => new Zend_Db_Expr( 'DATE_FORMAT( n.date_registration, "%d/%m/%Y" )' )
			    )
			 )
			 ->where( 'nt.fk_id_sysuser = ?', $id )
			 ->where( 'nt.read = ?', 0 )
			 ->where( 'n.status = ?', 1 )
			 ->where( '( n.date_scheduled IS NULL' )
			 ->orWhere( 'n.date_scheduled <= ? )', Zend_Date::now()->toString( 'yyyy-MM-dd' ) )
			 ->order( array( 'level', 'id_note' ) );
	
	return $dbNote->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listAllNotesToUser( $id )
    {
	$dbNote = App_Model_DbTable_Factory::get( 'Note' );
	$dbNoteTarget = App_Model_DbTable_Factory::get( 'NoteTarget' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$select = $dbNote->select()
			 ->from( array( 'n' => $dbNote ) )
			 ->setIntegrityCheck( false )
			 ->join(
			    array( 'nt' => $dbNoteTarget ),
			    'nt.fk_id_note = n.id_note',
			    array(
				'read',
				'date' => new Zend_Db_Expr( 'DATE_FORMAT( n.date_registration, "%d/%m/%Y" )' )
			    )
			 )
			 ->joinLeft(
			    array( 'u' => $dbUser ),
			    'u.id_sysuser = n.fk_id_sysuser',
			    array( 'name' )
			 )
			 ->where( 'nt.fk_id_sysuser = ?', $id )
			 ->order( array( 'read', 'id_note DESC' ) );
	
	return $dbNote->fetchAll( $select );
    }
    
     /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listAllNotesByUser( $id )
    {
	$dbNote = App_Model_DbTable_Factory::get( 'Note' );
	$dbNoteTarget = App_Model_DbTable_Factory::get( 'NoteTarget' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$select = $dbNote->select()
			 ->from( array( 'n' => $dbNote ) )
			 ->setIntegrityCheck( false )
			 ->join(
			    array( 'nt' => $dbNoteTarget ),
			    'nt.fk_id_note = n.id_note',
			    array(
				'read',
				'date' => new Zend_Db_Expr( 'DATE_FORMAT( n.date_registration, "%d/%m/%Y" )' )
			    )
			 )
			 ->join(
			    array( 'u' => $dbUser ),
			    'u.id_sysuser = nt.fk_id_sysuser',
			    array( 'name' )
			 )
			 ->where( 'n.fk_id_sysuser = ?', $id )
			 ->order( 'id_note DESC' );
	
	return $dbNote->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row 
     */
    public function detail( $id )
    {
	$dbNote = App_Model_DbTable_Factory::get( 'Note' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$select = $dbNote->select()
			 ->from( array( 'n' => $dbNote ) )
			 ->setIntegrityCheck( false )
			 ->joinLeft(
			    array( 'u' => $dbUser ),
			    'u.id_sysuser = n.fk_id_sysuser',
			    array(
				'name',
				'date' => new Zend_Db_Expr( 'DATE_FORMAT( n.date_registration, "%d/%m/%Y" )' ),
				'scheduled' => new Zend_Db_Expr( 'DATE_FORMAT( n.date_scheduled, "%d/%m/%Y" )' ),
			    )
			 )
			 ->where( 'n.id_note = ?', $id );
	
	return $dbNote->fetchRow( $select );
    }
}