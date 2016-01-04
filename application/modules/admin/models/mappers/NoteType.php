<?php

class Admin_Model_Mapper_NoteType extends App_Model_Abstract
{

    const CLASS_EXPIRED = 1;
    
    const JOB_EXPIRED = 2;

    const JOB_TRAINING_EXPIRED = 3;
    
    const APPOINTMENT_EXPIRED = 4;
    
    const JOB_REFERED_SHORTLIST = 5;
    
    const CLASS_SHORTLIST = 6;
    
    const CLIENT_OJT = 7;
    
    const DEPARTMENT_CREDIT = 8;
    
    const SMS_RECEIVED = 9;
    
    const CAMPAIGN_FINISHED = 10;
    
    const CASE_FOLLOW_UP = 11;
    
    const JOB_FOLLOW_UP = 12;
    
    const TP_NOT_FOUND = 13;
    
    const DRH_GREATER = 14;
    
    const RI_AMOUNT_GREATER = 15;
    
    const RI_DURATION_GREATER = 16;
    
    const FE_DURATION_GREATER = 17;
    
    const FE_GRADUATION = 18;
    
    /**
     * 
     * @var Model_DbTable_NoteType
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_NoteType();

	return $this->_dbTable;
    }
    
    /**
     *
     * @param int $noteType
     * @return array
     */
    public function getUsersByNoteType( $noteType )
    {
	$dbNoteType = App_Model_DbTable_Factory::get( 'NoteType' );
	$dbGroupNoteType = App_Model_DbTable_Factory::get( 'UserGroup_has_NoteType' );
	$dbGroupUser = App_Model_DbTable_Factory::get( 'UserGroup_has_SysUser' );
	
	$select = $dbNoteType->select()
			     ->from( array( 'nt' => $dbNoteType ) )
			     ->setIntegrityCheck( false )
			     ->join(
				array( 'gnt' => $dbGroupNoteType ),
				'gnt.fk_id_notetype = nt.id_notetype',
				array()
			     )
			     ->join(
				array( 'gu' => $dbGroupUser ),
				'gu.fk_id_usergroup = gnt.fk_id_usergroup',
				array( 'fk_id_sysuser' )
			     )
			     ->where( 'nt.id_notetype = ?', $noteType );
	
	$rows = $dbNoteType->fetchAll( $select );
	
	$users = array();
	foreach ( $rows as $row )
	    $users[] = $row->fk_id_sysuser;
	
	return $users;
    }
}