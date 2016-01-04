<?php

class Admin_Model_Mapper_UserBusiness extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_UserBusinessPlan
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_UserBusinessPlan();

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
	    
	    $dbUserBusinessPlan = App_Model_DbTable_Factory::get( 'UserBusinessPlan' );
	    $row = $dbUserBusinessPlan->fetchRow( array( 'fk_id_dec = ?' => $this->_data['fk_id_dec'] ) );
	    
	    if ( empty( $row ) )
		$row = $dbUserBusinessPlan->createRow();
	    
	    $row->setFromArray( $this->_data );
	    $row->save();
	    
	    $history = 'CONFIGURA USUARIU: %s BA CEOP %s';
	    
	    $history = sprintf( $history, $this->_data['fk_id_sysuser'], $this->_data['fk_id_dec'] );
	    $this->_sysAudit( $history );
	    
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
     * @return Zend_Db_Table_Rowset
     */
    public function listAll()
    {
	$dbUserBusinessPlan = App_Model_DbTable_Factory::get( 'UserBusinessPlan' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	
	$select = $dbUser->select()
			 ->setIntegrityCheck( false )
			 ->from( array( 'ub' => $dbUserBusinessPlan ) )
			 ->join(
			    array( 'd' => $dbDec ),
			    'd.id_dec = ub.fk_id_dec',
			    array( 'name_dec' )
			 )
			 ->join(
			    array( 'u' => $dbUser ),
			    'u.id_sysuser = ub.fk_id_sysuser',
			    array( 'name' )
			 )
			 ->order( array( 'name_dec' ) );
	
	return $dbUser->fetchAll( $select );
    }
    
    /**
     *
     * @param int $ceop
     * @return Zend_Db_Table_Row
     */
    public function searchUserCeop( $ceop )
    {
	$dbUserBusinessPlan = App_Model_DbTable_Factory::get( 'UserBusinessPlan' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$select = $dbUser->select()
			 ->from( array( 'u' => $dbUser ) )
			 ->setIntegrityCheck( false )
			 ->join(
			    array( 'ub' => $dbUserBusinessPlan ),
			    'ub.fk_id_sysuser = u.id_sysuser',
			    array()
			 )
			 ->where( 'ub.fk_id_dec = ?', $ceop );
	
	return $dbUser->fetchRow( $select );
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
	    'fk_id_sysform'	    => Admin_Form_UserBusiness::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}