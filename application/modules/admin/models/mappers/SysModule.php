<?php

class Admin_Model_Mapper_SysModule extends App_Model_Abstract
{
    const SIMU_WEB = 1;
    
    const REGISTER = 3;
    
    const CLIENT = 4;
    
    const JOB = 5;
    
    const FEFOP = 6;
    
    const FORMATION = 7;
    
    const ADMIN = 8;
    
    const SMS = 9;
    
    /**
     * 
     * @var Model_DbTable_SysModule
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_SysModule();

	return $this->_dbTable;
    }

    /**
     * 
     * @return int|bool
     */
    public function save()
    {
	try {

	    $row = $this->_checkModule( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Modulu iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	   
	    $id = parent::_simpleSave();
	    
	    return $id;
	    
	} catch ( Exception $e ) {

	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
   
    /**
     *
     * @param array $data
     * @return App_Model_DbTable_Row_Abstract
     */
    protected function _checkModule()
    {
	$select = $this->_dbTable->select()->where( 'module = ?', $this->_data['module'] );

	if ( !empty( $this->_data['id_sysmodule'] ) )
	    $select->where( 'id_sysmodule <> ?', $this->_data['id_sysmodule'] );

	return $this->_dbTable->fetchRow( $select );
    }
}