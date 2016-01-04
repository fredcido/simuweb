<?php

class Admin_Model_Mapper_Department extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_Department
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_Department();

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

	    $row = $this->_checkNameDepartment( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Departamentu iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_department'] ) )
		$history = 'INSERE DEPARTAMENTU: %s DADUS PRINCIPAL - INSERE NOVO DEPARTAMENTU';
	    else
		$history = 'ALTERA DEPARTAMENTU: %s DADUS PRINCIPAL - ALTERA DEPARTAMENTU';
	   
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
    protected function _checkNameDepartment()
    {
	$select = $this->_dbTable->select()->where( 'name = ?', $this->_data['name'] );

	if ( !empty( $this->_data['id_department'] ) )
	    $select->where( 'id_department <> ?', $this->_data['id_department'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset 
     */
    public  function fetchAll()
    {
	$dbDepartment = App_Model_DbTable_Factory::get( 'Department' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$select = $dbDepartment->select()
				->from( array( 'd' => $dbDepartment ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'u' => $dbUser ),
				    'u.id_sysuser = d.fk_id_sysuser',
				    array( 'user' => 'name' )
				)
				->order( array( 'd.name' ) );
	
	return $dbDepartment->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function getDepartmentByUser( $id )
    {
	$dbDepartment = App_Model_DbTable_Factory::get( 'Department' );
	$row = $dbDepartment->fetchRow( array( 'fk_id_sysuser = ?' => $id ) );
	
	if ( empty( $row ) )
	    return array();
	else {
	    
	    $data = $row->toArray();
	    
	    $mapperCredit = new Admin_Model_Mapper_SmsCredit();
	    $totalBalance = $mapperCredit->getBalanceDepartment( $row->id_department );
	    $balance = (float)( empty( $totalBalance ) ? 0 : $totalBalance->balance );
	    
	    $mapperConfig = new Admin_Model_Mapper_SmsConfig();
	    $config = $mapperConfig->getConfig();

	    $sending = 0;
	    if ( !empty( $balance ) )
		$sending = floor( $balance / $config->sms_unit_cost );
	    
	    $data['balance'] = $balance;
	    $data['sending'] = $sending;
	    
	    return $data;
	}
    }
    
    /**
     * 
     * @param int $id
     * @return array
     */
    public function detailDepartment( $id )
    {
	$dbDepartment = App_Model_DbTable_Factory::get( 'Department' );
	$row = $dbDepartment->fetchRow( array( 'id_department = ?' => $id ) );
	
	if ( empty( $row ) )
	    return array();
	else {
	    
	    $data = $row->toArray();
	    
	    $mapperCredit = new Admin_Model_Mapper_SmsCredit();
	    $totalBalance = $mapperCredit->getBalanceDepartment( $row->id_department );
	    $balance = (float)( empty( $totalBalance ) ? 0 : $totalBalance->balance );
	    
	    $mapperConfig = new Admin_Model_Mapper_SmsConfig();
	    $config = $mapperConfig->getConfig();

	    $sending = 0;
	    if ( !empty( $balance ) )
		$sending = floor( $balance / $config->sms_unit_cost );
	    
	    $data['balance'] = $balance;
	    $data['sending'] = $sending;
	    
	    return $data;
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
	    'fk_id_sysform'	    => Admin_Form_Department::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}