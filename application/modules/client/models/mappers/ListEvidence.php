<?php

class Client_Model_Mapper_ListEvidence extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_FEFPListEvidence
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_JOBListPrint();

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
	   
	    if ( empty( $this->_session->client_list ) ) {
		
		$this->_message->addMessage( 'Tenki tau Kliente sira', App_Message::ERROR );
		return false;
	    }
	    
	    $history = 'KRIA LISTA IMPRESAUN CARTAUN EVIDENSIA: %s';
	    
	    $mapperDec = new Register_Model_Mapper_Dec();
	    $ceop = $mapperDec->fetchRow( $this->_data['fk_id_dec'] );
	    
	    $this->_data['create_date'] = Zend_Date::now()->toString( 'yyyy-MM-dd' );
	    $this->_data['list_name'] = $ceop->name_dec . ' - ' . Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm:ss' );
	    $this->_data['printed'] = 0;
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $id );
	    $this->_sysAudit( $history );
	    
	    $historyClient = 'KLIENTE %s SELECIONADO BA LISTA IMPRESAUN CARTAUN EVIDENSIA: %s';
	    
	    $dbJobListPrintPerData = App_Model_DbTable_Factory::get( 'JOBListPrintHasPerdata' );
	    foreach ( $this->_session->client_list as $client ) {
		
		$row = $dbJobListPrintPerData->createRow();
		$row->fk_id_joblistprint = $id;
		$row->fk_id_perdata = $client;
		$row->printed = 0;
		$row->save();
		
		$history = sprintf( $historyClient, $client, $id );
		$this->_sysAudit( $history );
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
     * @return boolean 
     */
    public function saveClient()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    if ( !empty( $this->_data['list'] ) ) {
		
		$dbJobListPrintPerData = App_Model_DbTable_Factory::get( 'JOBListPrintHasPerdata' );
		
		$where = array(
		   'fk_id_joblistprint = ?' => $this->_data['list'],
		    'fk_id_perdata = ?'	=> $this->_data['client']
		);
		
		$row = $dbJobListPrintPerData->fetchRow( $where );
		if ( !empty( $row ) ) {
		    
		    $this->_message->addMessage( 'Kliente iha lista tiha ona', App_Message::ERROR );
		    return false;
		}

		$historyClient = 'KLIENTE %s SELECIONADO BA LISTA IMPRESAUN CARTAUN EVIDENSIA: %s';
		
		$row = $dbJobListPrintPerData->createRow();
		$row->fk_id_joblistprint = $this->_data['list'];
		$row->fk_id_perdata = $this->_data['client'];
		$row->printed = 0;
		$row->save();
		
		$history = sprintf( $historyClient, $this->_data['client'], $this->_data['list'] );
		$this->_sysAudit( $history );
	    
	    } else {
		
		if ( empty( $this->_session->client_list ) )
		    $this->_session->client_list = array();
		
		$clients = $this->_session->client_list;
		
		if ( in_array( $this->_data['client'], $clients ) ) {
		    
		     $this->_message->addMessage( 'Kliente iha lista tiha ona', App_Message::ERROR );
		     return false;
		}
		
		$clients[] = $this->_data['client'];
		$this->_session->client_list = $clients;
	    }
	    
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
     * @return boolean 
     */
    public function removeClient()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    if ( !empty( $this->_data['list'] ) ) {

		$dbJobListPerdata = App_Model_DbTable_Factory::get( 'JOBListPrintHasPerdata' );
		$where = array(
		   'fk_id_joblistprint = ?' => $this->_data['list'],
		    'fk_id_perdata = ?'	=> $this->_data['id']
		);
		
		$dbJobListPerdata->delete( $where );

		$history = 'REMOVE KLIENTE: %s HUSI LISTA KARTAUN EVIDENSIA: %s';

		$history = sprintf( $history, $this->_data['id'], $this->_data['list'] );
		$this->_sysAudit( $history );
	    
	    } else {
		
		if ( empty( $this->_session->client_list ) )
		    $this->_session->client_list = array();
		
		$pos = array_search( $this->_data['id'], $this->_session->client_list );
		if ( FALSE !== $pos )
		    unset( $this->_session->client_list[$pos] );
	    }
	    
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
     * @param int|null $id
     * @return array|Zend_Db_Table_Rowset
     */
    public function listClient( $id )
    {
	$mapperClient = new Client_Model_Mapper_Client();
	$selectClient = $mapperClient->selectClient();
	
	if ( empty( $id ) ) {
	    
	    if ( empty( $this->_session->client_list ) )
		return array();
	    
	    $selectClient->where( 'c.id_perdata IN(?)', $this->_session->client_list );
	    
	} else {
	    
	    $dbJobListPerdata = App_Model_DbTable_Factory::get( 'JOBListPrintHasPerdata' );
	    
	    $selectClient->join(
			    array( 'jlp' => $dbJobListPerdata ),
			    'jlp.fk_id_perdata = c.id_perdata',
			    array( 'printed' )
			)
			->where( 'jlp.fk_id_joblistprint = ?', $id );
	}
	
	return $this->_dbTable->fetchAll( $selectClient );
    }
    
    
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::CLIENT,
	    'fk_id_sysform'	    => Client_Form_ListEvidence::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
    
    /**
     * 
     * @param type $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listByFilters( $filters = array() )
    {
	$dbJobListPrint = App_Model_DbTable_Factory::get( 'JOBListPrint' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	
	$select = $dbJobListPrint->select()
				 ->from( array( 'jl' => $dbJobListPrint ) )
				 ->setIntegrityCheck( false )
				 ->join(
				    array( 'u' => $dbUser ),
				    'u.id_sysuser = jl.fk_id_user',
				    array( 'user' => 'name' )
				)
				->join(
				    array( 'c' => $dbDec ),
				    'c.id_dec = jl.fk_id_dec',
				    array( 'name_dec' )
				)
				->order( array( 'id_job_list DESC' ) );
	
	if ( !empty( $filters['list_name'] ) )
	    $select->where( 'jl.list_name LIKE ?', '%' . $filters['list_name'] . '%' );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'jl.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	if ( !empty( $filters['fk_id_user'] ) )
	    $select->where( 'jl.fk_id_user = ?', $filters['fk_id_user'] );
	
	if ( array_key_exists( 'printed', $filters ) && is_int( $filters['printed'] ) )
	    $select->where( 'jl.printed = ?', (int)$filters['printed'] );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_registration_ini'] ) )
	    $select->where( 'jl.create_date >= ?', $date->set( $filters['date_registration_ini'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_registration_fim'] ) )
	    $select->where( 'jl.create_date <= ?', $date->set( $filters['date_registration_fim'] )->toString( 'yyyy-MM-dd' ) );
	
	return $dbJobListPrint->fetchAll( $select );
    }
    
    /**
     *
     * @return boolean 
     */
    public function savePrint( $id, $clients )
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbJobListPrint = App_Model_DbTable_Factory::get( 'JOBListPrint' );
	    $dbJobListPrintPerData = App_Model_DbTable_Factory::get( 'JOBListPrintHasPerdata' );
	    
	    $where = array( 'id_job_list = ?' => $id );
	    $update = array( 'printed' => 1 );
	    
	    // Set list printed
	    $dbJobListPrint->update( $update, $where );
	    
	    $where = array(
		'fk_id_joblistprint = ?' => $id,
		'fk_id_perdata IN (?)' => $clients
	    );
	    
	    // Set Clients printed
	    $dbJobListPrintPerData->update( $update, $where );
	    
	    $history = 'IMPRIME LISTA KARTAUN EVIDENSIA: %s BA KLIENTE: %s';

	    $history = sprintf( $history, $id, implode( ',', $clients ) );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    return true;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    Zend_Debug::dump( $e );
	    exit;
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
}