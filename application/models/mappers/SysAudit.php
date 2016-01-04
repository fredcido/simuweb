<?php

class Model_Mapper_SysAudit extends App_Model_Abstract
{
   
    /**
     * 
     * @var Model_DbTable_SysAudit
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_SysAudit();

	return $this->_dbTable;
    }

    /**
     * 
     * @return int|bool
     */
    public function save()
    {
	try {

	   $this->_data['date_time'] = Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm:ss' );
	   $this->_data['ip'] = $_SERVER['REMOTE_ADDR'];
	   $this->_data['fk_id_sysuser'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	   
	   $this->_data = array_map( 'strtoupper', $this->_data );

	   $id = parent::_simpleSave();
	    
	   return $id;
	    
	} catch ( Exception $e ) {

	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    
	    throw $e;
	}
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listByFilters( $filters = array() )
    {
	$dbAudit = App_Model_DbTable_Factory::get( 'SysAudit' );
	$dbForm = App_Model_DbTable_Factory::get( 'SysForm' );
	$dbModule = App_Model_DbTable_Factory::get( 'SysModule' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$select = $dbAudit->select()
			  ->from( array( 'a' => $dbAudit ) )
			  ->setIntegrityCheck( false )
			  ->join(
			    array( 'f' => $dbForm ),
			    'f.id_sysform = a.fk_id_sysform',
			    array( 
				'form',
				'date_audit' => new Zend_Db_Expr( 'DATE_FORMAT( a.date_time, "%d/%m/%Y" )' )
			    )
			  )
			  ->join(
			    array( 'm' => $dbModule ),
			    'm.id_sysmodule = a.fk_id_sysmodule',
			    array( 'module' )
			  )
			  ->join(
			    array( 'u' => $dbUser ),
			    'u.id_sysuser = a.fk_id_sysuser',
			    array( 'name' )
			  )
			  ->order( array( 'date_time DESC' ) );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['start_date'] ) && Zend_Date::isDate( $filters['start_date'] ) )
	    $select->where( 'DATE( a.date_time ) >= ?', $date->set( $filters['start_date'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['finish_date'] ) && Zend_Date::isDate( $filters['finish_date'] ) )
	    $select->where( 'DATE( a.date_time ) <= ?', $date->set( $filters['finish_date'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['fk_id_sysuser'] ) )
	    $select->where( 'a.fk_id_sysuser = ?', $filters['fk_id_sysuser'] );
	
	if ( !empty( $filters['fk_id_sysmodule'] ) )
	    $select->where( 'a.fk_id_sysmodule = ?', $filters['fk_id_sysmodule'] );
	
	if ( !empty( $filters['fk_id_sysform'] ) )
	    $select->where( 'a.fk_id_sysform = ?', $filters['fk_id_sysform'] );
	
	if ( !empty( $filters['description'] ) )
	    $select->where( 'a.description LIKE ?', '%' . $filters['description'] . '%' );
	
	return $dbAudit->fetchAll( $select );
    }
}