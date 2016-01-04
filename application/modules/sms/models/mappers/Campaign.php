<?php

class Sms_Model_Mapper_Campaign extends App_Model_Abstract
{

    const STATUS_STOPPED = 'S';
    
    const STATUS_INITIED = 'I';
    
    const STATUS_SCHEDULED = 'H';
    
    const STATUS_COMPLETED = 'C';
    
    const STATUS_CANCELLED = 'X';
    
    const STATUS_ROBOT = 'R';
    
    /**
     * 
     * @var Model_DbTable_Campaign
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_Campaign();

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
	  
	    // Check if there is already a campaign with the title defined
	    $row = $this->_checkTitleCampaign();
	    if ( !empty( $row ) ) {
		
		$this->_message->addMessage( sprintf( 'Iha kampanha tiha ona ho naran %', $this->_data['campaign_title'] ) );
		return false;
	    }
	    
	    $dataForm = $this->_data;
	    
	    // Check if the campaign was scheduled to validated and format the date
	    if ( !empty( $this->_data['date_scheduled'] ) ) {
		
		$date = new Zend_Date( $this->_data['date_scheduled'] );
		if ( $date->isEarlier( Zend_Date::now() ) ) {
		    
		    $this->_message->addMessage( 'Keta rejistu data atu haruka uluk data ohin' );
		    $this->addFieldError( 'date_scheduled' ); 
		    return false;
		}
		    
		$this->_data['date_scheduled'] = $date->toString( 'yyyy-MM-dd' );
	    }
	    
	    if ( empty( $this->_data['id_campaign'] ) ) {
		
		$mapperSmsConfig = new Admin_Model_Mapper_SmsConfig();
		$config = $mapperSmsConfig->getConfig();
		
		$this->_data['fk_id_sysuser'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
		$this->_data['fk_id_sms_config'] = $config->id_sms_config;
		$this->_data['status'] = empty( $this->_data['date_scheduled'] ) ? self::STATUS_STOPPED : self::STATUS_SCHEDULED;
		
		$history = 'INSERE KAMPANHA SMS: %s';
		
	    } else {
		
		$history = 'ALTERA KAMPANHA SMS: %s';
		$this->_data['status'] = empty( $this->_data['date_scheduled'] ) ? self::STATUS_STOPPED : self::STATUS_SCHEDULED;
	    }
	   
	    // Save the campaign
	    $id = parent::_simpleSave();
	    
	    // Save the campaign log
	    if ( empty( $dataForm['id_campaign'] ) )
		$this->saveLog( 'KAMPANHA HALOT', $id );
	    else
		$this->saveLog( 'KAMPANHA ALTERADA', $id );
	    
	    $dataForm['id_campaign'] = $id;
	    
	    // Save the groups for the campaign
	    $this->_saveCampaignGroups( $dataForm );
	    
	    $history = sprintf( $history, $id );
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
     * @param type $id
     * @return type
     */
    public function listLogs( $id )
    {
	$dbLog = App_Model_DbTable_Factory::get( 'CampaignLog' );
	return $dbLog->fetchAll( array( 'fk_id_campaign = ?' => $id ), array( 'date_time DESC' ) );
    }
    
    /**
     * 
     * @param array $data
     */
    protected function _saveCampaignGroups( $data )
    {
	$dbCampaignHasGroup = App_Model_DbTable_Factory::get( 'CampaignHasSmsGroup');
	
	// Fetch the groups already registered to the campaign
	$groupsCampaign = $this->listGroupsCampaign( $data['id_campaign'] );
	
	$groups = $this->_data['group'];
	
	$toDelete = array_diff( $groupsCampaign, $groups );
	$toInsert = array_diff( $groups, $groupsCampaign );
	
	// Delete all the groups removed
	if ( !empty( $toDelete ) ) {
	    
	    $where = array(
		'fk_id_campaign = ?' => $data['id_campaign'],
		'fk_id_sms_group IN(?)' => $toDelete
	    );
	    $dbCampaignHasGroup->delete( $where );
	}
	
	$dataGroup = array( 'fk_id_campaign' => $data['id_campaign'] );
	foreach ( $toInsert as $group ) {
	    
	    $dataGroup['fk_id_sms_group'] = $group;
	    $dbCampaignHasGroup->insert( $dataGroup );
	}
    }
    
    /**
     * 
     * @param int $id
     * @return array
     */
    public function listGroupsCampaign( $id )
    {
	$dbCampaignHasGroup = App_Model_DbTable_Factory::get( 'CampaignHasSmsGroup');
	
	$rows = $dbCampaignHasGroup->fetchAll( array( 'fk_id_campaign = ?' => $id ) );
	$groupsCampaign = array();
	foreach ( $rows as $row )
	    $groupsCampaign[] = $row->fk_id_sms_group;
	
	return $groupsCampaign;
    }
    
    public function getSelectSent()
    {
	$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	$dbSent = App_Model_DbTable_Factory::get( 'CampaignSent' );
	$dbGroupContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	$dbClient = App_Model_DbTable_Factory::get( 'PerData' );
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	$dbEduInstitution = App_Model_DbTable_Factory::get( 'FefpEduInstitution' );
	$dbConfig = App_Model_DbTable_Factory::get( 'SmsConfig' );
	
	$selectContactClient = $dbClient->select()
					->from(
					    array( 'c' => $dbClient ),
					    array(
						'contact' => new Zend_Db_Expr( "CONCAT( c.first_name, ' ', IFNULL(c.medium_name, ''), ' ', c.last_name )" ),
						'type'	  => new Zend_Db_Expr( "'Kliente'" )
					    )
					)
					->setIntegrityCheck( false )
					->join(
					    array( 'sgc' => $dbGroupContact ),
					    'sgc.fk_id_perdata = c.id_perdata',
					    array( 'id_sms_group_contact' )
					);
	
	$selectContactEnterprise = $dbClient->select()
					->from(
					    array( 'e' => $dbEnterprise ),
					    array(
						'contact' => 'enterprise_name',
						'type'	  => new Zend_Db_Expr( "'Empreza'" )
					    )
					)
					->setIntegrityCheck( false )
					->join(
					    array( 'sgc' => $dbGroupContact ),
					    'sgc.fk_id_fefpenterprise = e.id_fefpenterprise',
					    array( 'id_sms_group_contact' )
					);
	
	$selectContactInstitution = $dbClient->select()
					->from(
					    array( 'ei' => $dbEduInstitution ),
					    array(
						'contact' => 'institution',
						'type'	  => new Zend_Db_Expr( "'Inst. Ensinu'" )
					    )
					)
					->setIntegrityCheck( false )
					->join(
					    array( 'sgc' => $dbGroupContact ),
					    'sgc.fk_id_fefpeduinstitution = ei.id_fefpeduinstitution',
					    array( 'id_sms_group_contact' )
					);
	
	$selectUnion = $dbSent->select()
			    ->union( array( $selectContactClient, $selectContactEnterprise, $selectContactInstitution ) )
			    ->setIntegrityCheck( false )
			    ->order( array( 'contact' ) );
	
	$select = $dbSent->select()
			 ->from(
			    array(  'cs' => $dbSent ),
			    array(
				'target',
				'date_sent' => new Zend_Db_Expr( "DATE_FORMAT( cs.date_time, '%d/%m/%Y %H:%i' )" ),
				'status',
				'attempts',
				'log',
				'source'
			    )
			  )
			  ->setIntegrityCheck( false )
			  ->join(
			    array( 'c' => $dbCampaign ),
			    'c.id_campaign = cs.fk_id_campaign',
			    array( 'campaign_title' )
			  )
			  ->join(
			    array( 'sgc' => $dbGroupContact ),
			    'sgc.id_sms_group_contact = cs.fk_id_sms_group_contact',
			    array()
			  )
			  ->join(
			    array( 'co' => new Zend_Db_Expr( '(' . $selectUnion .  ')' ) ),
			    'co.id_sms_group_contact = sgc.id_sms_group_contact',
			    array(
				'contact',
				'type'
			    )
			  )
			  ->join(
			    array( 'sc' => $dbConfig ),
			    'sc.id_sms_config = cs.fk_id_sms_config',
			    array( 'cost' => 'sms_unit_cost' )
			  )
			  ->order( 'id_campaign_sent DESC' );
	
	return $select;
    }
    
    /**
     * 
     * @param int $campaign
     * @param int $total
     * @return Zend_Db_Table_Rowset
     */
    public function listLastsSent( $campaign = false, $total = 1000 )
    {
	$selectSent = $this->getSelectSent()->limit( $total );
	
	if ( !empty( $campaign ) )
	    $selectSent->where( 'cs.fk_id_campaign = ?', $campaign );
	
	return $this->_dbTable->fetchAll( $selectSent );
    }
    
    /**
     *
     * @param array $data
     * @return App_Model_DbTable_Row_Abstract
     */
    protected function _checkTitleCampaign()
    {
	$select = $this->_dbTable->select()->where( 'campaign_title = ?', $this->_data['campaign_title'] );

	if ( !empty( $this->_data['id_campaign'] ) )
	    $select->where( 'id_campaign <> ?', $this->_data['id_campaign'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @param array $filters
     * @return array
     */
    public function getStatistics( $filters = array() )
    {
	$statistics = array(
	    'to-send'	    =>  $this->getTotalToSend( $filters ),
	    'sent'	    =>  $this->getTotalSent( $filters ),
	    'waiting'	    =>  $this->getTotalWaiting( $filters ),
	    'sent-error'    =>  $this->getTotalErrors( $filters )
	);
	
	return $statistics;
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    protected function _getSelectToSend()
    {
	$mapperGroupSms = new Sms_Model_Mapper_Group();
	$selectContacts = $mapperGroupSms->getSelectContacts();
	
	$dbCampaignGroup = App_Model_DbTable_Factory::get( 'CampaignHasSmsGroup' );
	
	$selectToSend = $selectContacts->join(
					array( 'chsg' => $dbCampaignGroup ),
					't.fk_id_sms_group = chsg.fk_id_sms_group',
					array( 'to_send' => new Zend_Db_Expr( 'COUNT(1)') )
				    );
	
	return $selectToSend;
    }
    
    /**
     * 
     * @param array $filters
     * @return int
     */
    public function getTotalToSend( $filters = array() )
    {
	$selectToSend = $this->_getSelectToSend();
	
	if ( !empty( $filters['id'] ) )
	    $selectToSend->where( 'chsg.fk_id_campaign = ?', $filters['id'] )->group( array( 'fk_id_campaign' ) );
	
	$row = $this->_dbTable->fetchRow( $selectToSend );
	return empty( $row ) ? 0 : $row->to_send;
    }
    
    /**
     * 
     * @param array $filters
     * @return int
     */
    public function getTotalSent( $filters = array() )
    {
	$dbSent = App_Model_DbTable_Factory::get( 'CampaignSent' );
	$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	
	$selectSent = $dbCampaign->select()
				    ->from( 
					array( 'c' => $dbCampaign ), 
					array( 
					    'sent' => new Zend_Db_Expr( 'COUNT(1)')
					) 
				    )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 'cs' => $dbSent ),
					'cs.fk_id_campaign = c.id_campaign',
					array()
				    )
				    ->where( 'cs.status = ?', 'S' );
	
	if ( !empty( $filters['id'] ) )
	    $selectSent->where( 'c.id_campaign = ?', $filters['id'] )->group( array( 'id_campaign' ) );
	
	$row = $dbCampaign->fetchRow( $selectSent );
	return empty( $row ) ? 0 : $row->sent;
    }
    
    /**
     * 
     * @return Zend_Db_Select
     */
    protected function _getSelectWaiting()
    {
	$dbSent = App_Model_DbTable_Factory::get( 'CampaignSent' );
	$dbCampaignGroup = App_Model_DbTable_Factory::get( 'CampaignHasSmsGroup' );
	
	$mapperGroupSms = new Sms_Model_Mapper_Group();
	$selectContacts = $mapperGroupSms->getSelectContacts();
	
	$selectNotExists = $dbSent->select()
				  ->from( 
				    array( 'cs' => $dbSent ),
				    array( new Zend_Db_Expr( 'NULL' ) )
				  )
				  ->where( 'cs.fk_id_campaign = chsg.fk_id_campaign' )
				  ->where( 't.id_sms_group_contact = cs.fk_id_sms_group_contact' );
	
	$selectNotSent = $selectContacts->join(
					array( 'chsg' => $dbCampaignGroup ),
					't.fk_id_sms_group = chsg.fk_id_sms_group',
					array( 'not_sent' => new Zend_Db_Expr( 'COUNT(1)') )
				    )
				    ->where( 'NOT EXISTS (?)', new Zend_Db_Expr( '(' . $selectNotExists . ')' ) );
	
	return $selectNotSent;
    }
    
    /**
     * 
     * @param array $filters
     * @return int
     */
    public function getTotalWaiting( $filters = array() )
    {
	$selectNotSent = $this->_getSelectWaiting();
	
	if ( !empty( $filters['id'] ) )
	    $selectNotSent->where( 'chsg.fk_id_campaign = ?', $filters['id'] )->group( array( 'fk_id_campaign' ) );
	
	$row = $this->_dbTable->fetchRow( $selectNotSent );
	return empty( $row ) ? 0 : $row->not_sent;
    }
    
    /**
     * 
     * @param array $filters
     * @return int
     */
    public function getTotalErrors( $filters = array() )
    {
	$dbSent = App_Model_DbTable_Factory::get( 'CampaignSent' );
	$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	
	$selectNotSent = $dbCampaign->select()
				    ->from( 
					array( 'c' => $dbCampaign ), 
					array( 
					    'sent' => new Zend_Db_Expr( 'COUNT(1)')
					) 
				    )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 'cs' => $dbSent ),
					'cs.fk_id_campaign = c.id_campaign',
					array()
				    )
				    ->where( 'cs.status = ?', 'E' );
	
	if ( !empty( $filters['id'] ) )
	    $selectNotSent->where( 'c.id_campaign = ?', $filters['id'] )->group( array( 'id_campaign' ) );
	
	$row = $dbCampaign->fetchRow( $selectNotSent );
	return empty( $row ) ? 0 : $row->sent;
    }
    
    /**
     *
     * @param array $filters
     * @return array 
     */
    public function chartSending( $filters = array() )
    {
	$sent = $this->getTotalSent( $filters );
	$toSend = $this->getTotalWaiting( $filters );
	
	$data = array(
	    'data'   => array()
	);
	
	$data['data'][] = array( 'Haruka tiha ona' , (int)$sent );
	$data['data'][] = array( 'Hein atu haruka', (int)$toSend );
	
	return $data;
    }
    
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function chartSentGroup( $filters = array() )
    {
	$data = array(
	    'errors' => array(),
	    'sent'   => array()
	);
	
	$filters['status'] = 'S';
	$sent = $this->sentByGroup( $filters );
	
	$filters['status'] = 'E';
	$errors = $this->sentByGroup( $filters );
	
	foreach ( $sent as $row )
	    $data['sent'][] = array( $row->sms_group_name, (int)$row->total );
	
	foreach ( $errors as $row )
	    $data['errors'][] = array( $row->sms_group_name, (int)$row->total );
	
	return $data;
    }
    
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function sentByGroup( $filters = array() )
    {
	$dbSent = App_Model_DbTable_Factory::get( 'CampaignSent' );
	$dbGroup = App_Model_DbTable_Factory::get( 'SmsGroup' );
	$dbContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	
	$select = $dbSent->select()
			 ->from( 
			    array( 's' => $dbSent ),
			    array( 'total' => new Zend_Db_Expr( 'COUNT(1)' ) )
			 )
			 ->setIntegrityCheck( false )
			 ->join(
			    array( 'c' => $dbContact ),
			    'c.id_sms_group_contact = s.fk_id_sms_group_contact',
			    array()
			  )
			 ->join(
			    array( 'g' => $dbGroup ),
			    'g.id_sms_group = c.fk_id_sms_group',
			    array( 'sms_group_name' )
			  )
			  ->group( array( 'sms_group_name' ) );
	
	if ( !empty( $filters['status'] ) )
	    $select->where( 's.status = ?', $filters['status'] );
	
	if ( !empty( $filters['id'] ) )
	    $select->where( 's.fk_id_campaign = ?', $filters['id'] );
	
	return $dbSent->fetchAll( $select );
    }
    
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function sentByDays( $filters = array() )
    {
	$dbSent = App_Model_DbTable_Factory::get( 'CampaignSent' );
	$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	
	$selectSent = $dbCampaign->select()
				    ->from( 
					array( 'c' => $dbCampaign ), 
					array( 
					    'sent' => new Zend_Db_Expr( 'COUNT(1)' )
					) 
				    )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 'cs' => $dbSent ),
					'cs.fk_id_campaign = c.id_campaign',
					array(
					    'day'   => new Zend_Db_Expr( 'DATE_FORMAT( cs.date_time, "%d/%m/%Y" )'),
					    'hour'  => new Zend_Db_Expr( 'DATE_FORMAT( cs.date_time, "%H:%i" )')
					)
				    )
				    ->group( array( 'day' ) );
	
	if ( !empty( $filters['id'] ) )
	    $selectSent->where( 'c.id_campaign = ?', $filters['id'] );
	
	if ( !empty( $filters['date_ini'] ) )
	    $selectSent->where( 'DATE( cs.date_time ) >= ?', $filters['date_ini'] );
	
	if ( !empty( $filters['date_fin'] ) )
	    $selectSent->where( 'DATE( cs.date_time ) <= ?', $filters['date_fin'] );

	if ( !empty( $filters['hour_ini'] ) )
	    $selectSent->where( 'TIME( cs.date_time ) >= ?', $filters['hour_ini'] );
	
	if ( !empty( $filters['hour_fin'] ) )
	    $selectSent->where( 'TIME( cs.date_time ) <= ?', $filters['hour_fin'] );
	
	if ( !empty( $filters['status'] ) )
	    $selectSent->where( 'cs.status = ?', $filters['status'] );
	
	return $dbCampaign->fetchAll( $selectSent );
    }
    
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function sentByHours( $filters = array() )
    {
	$dbSent = App_Model_DbTable_Factory::get( 'CampaignSent' );
	$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	
	$selectSent = $dbCampaign->select()
				    ->from( 
					array( 'c' => $dbCampaign ), 
					array( 
					    'sent' => new Zend_Db_Expr( 'COUNT(1)' )
					) 
				    )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 'cs' => $dbSent ),
					'cs.fk_id_campaign = c.id_campaign',
					array(
					    'day'   => new Zend_Db_Expr( 'DATE_FORMAT( cs.date_time, "%d/%m/%Y" )'),
					    'hour'  => new Zend_Db_Expr( 'DATE_FORMAT( cs.date_time, "%H:00" )')
					)
				    )
				    ->group( array( 'hour' ) );
	
	if ( !empty( $filters['id'] ) )
	    $selectSent->where( 'c.id_campaign = ?', $filters['id'] );
	
	if ( !empty( $filters['date_ini'] ) )
	    $selectSent->where( 'DATE( cs.date_time ) >= ?', $filters['date_ini'] );
	
	if ( !empty( $filters['date_fin'] ) )
	    $selectSent->where( 'DATE( cs.date_time ) <= ?', $filters['date_fin'] );

	if ( !empty( $filters['hour_ini'] ) )
	    $selectSent->where( 'TIME( cs.date_time ) >= ?', $filters['hour_ini'] );
	
	if ( !empty( $filters['hour_fin'] ) )
	    $selectSent->where( 'TIME( cs.date_time ) <= ?', $filters['hour_fin'] );
	
	if ( !empty( $filters['status'] ) )
	    $selectSent->where( 'cs.status = ?', $filters['status'] );
	
	return $dbCampaign->fetchAll( $selectSent );
    }
    
    /**
     * 
     * @param array $filters
     * @return array
     */
    public function chartSentDay( array $filters = array() )
    {
	$date = new Zend_Date();
	
	// Set the date to search the current month
	$filters['date_ini'] = $date->setDay( 1 )->toString( 'yyyy-MM-dd' );
	$filters['date_fin'] = $date->addMonth( 1 )->subDay( 1 )->toString( 'yyyy-MM-dd' );
	
	// Search just the sending successfully
	$filters['status'] = 'S';
	$sent = $this->sentByDays( $filters );
	
	// Search the erros with errors
	$filters['status'] = 'E';
	$errors = $this->sentByDays( $filters );
	
	$months = array();
	foreach ( $sent as $row )
	    $months[] = $row->day;
	
	foreach ( $errors as $row )
	    $months[] = $row->day;
	
	$months = array_fill_keys( array_unique( $months ), array( 'errors' => 0, 'sent' => 0 ) );
	
	foreach ( $sent as $row )
	    $months[$row->day]['sent'] = $row->sent;
	
	foreach ( $errors as $row )
	    $months[$row->day]['errors'] = $row->sent;
	
	$data = array(
	    'months' => array_keys( $months ),
	    'errors' => array(),
	    'sent' => array()
	);
	
	foreach ( $months as $row ) {
	    
	    $data['errors'][] = (int)$row['errors'];
	    $data['sent'][] = (int)$row['sent'];
	}
	
	return $data;
    }
    
    /**
     * 
     * @param array $filters
     * @return array
     */
    public function chartSentHour( array $filters = array() )
    {
	$date = new Zend_Date();
	
	// Set the date to search the current month
	$filters['date_ini'] = $date->toString( 'yyyy-MM-dd' );
	$filters['date_fin'] = $date->toString( 'yyyy-MM-dd' );
	
	// Set the hour to search
	$filters['hour_ini'] = $date->setTime( '00:00:00' )->toString( 'HH:mm:ss' );
	$filters['hour_fin'] = $date->setTime( '23:59:59' )->toString( 'HH:mm:ss' );
	
	// Search just the sending successfully
	$filters['status'] = 'S';
	$sent = $this->sentByHours( $filters );
	
	// Search the erros with errors
	$filters['status'] = 'E';
	$errors = $this->sentByHours( $filters );
	
	$hours = array();
	foreach ( $sent as $row )
	    $hours[] = $row->hour;
	
	foreach ( $errors as $row )
	    $hours[] = $row->hour;
	
	$hours = array_fill_keys( array_unique( $hours ), array( 'errors' => 0, 'sent' => 0 ) );
	
	foreach ( $sent as $row )
	    $hours[$row->hour]['sent'] = $row->sent;
	
	foreach ( $errors as $row )
	    $hours[$row->hour]['errors'] = $row->sent;
	
	$data = array(
	    'hours' => array_keys( $hours ),
	    'errors' => array(),
	    'sent' => array()
	);
	
	foreach ( $hours as $row ) {
	    
	    $data['errors'][] = (int)$row['errors'];
	    $data['sent'][] = (int)$row['sent'];
	}
	
	return $data;
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function getSelectCampaign()
    {
	$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	$dbDepartment = App_Model_DbTable_Factory::get( 'Department' );
	$dbCampaignType = App_Model_DbTable_Factory::get( 'CampaignType' );
	$dbCampaignGroup = App_Model_DbTable_Factory::get( 'CampaignHasSmsGroup' );
	$dbSent = App_Model_DbTable_Factory::get( 'CampaignSent' );
	
	// Select totalizing the sms to send
	$selectToSend = $this->_getSelectToSend();
	$selectToSend->reset( Zend_Db_Select::COLUMNS )
		    ->columns( array( new Zend_Db_Expr( 'COUNT(1)' ) ) )
		    ->where( 'chsg.fk_id_campaign = c.id_campaign' );
	
	// Select totalizing the sms sent already
	$selectSent = $dbCampaign->select()
				    ->from( 
					array( 'cs' => $dbSent ),
					array( new Zend_Db_Expr( 'COUNT(1)' ) )
				    )
				    ->setIntegrityCheck( false )
				    ->where( 'cs.status = ?', 'S' )
				    ->where( 'cs.fk_id_campaign = c.id_campaign' )
				    ->group( array( 'cs.fk_id_campaign' ) );
	
	// Get the total waiting
	$selectNotSent = $this->_getSelectWaiting();
	$selectNotSent->reset( Zend_Db_Select::COLUMNS )
		    ->columns( new Zend_Db_Expr( 'COUNT(1)' ) )
		    ->where( 'chsg.fk_id_campaign = c.id_campaign' );
	
	$select = $dbCampaign->select()
				->from(
				    array( 'c' => $dbCampaign ),
				    array(
					'*',
					'to_send'   => new Zend_Db_Expr( 'IFNULL((' . $selectToSend . '), 0)' ),
					'sent'	    => new Zend_Db_Expr( 'IFNULL((' . $selectSent . '), 0)' ),
					'not_sent'  => new Zend_Db_Expr( 'IFNULL((' . $selectNotSent . '), 0)' ),
				    )
				)
				->setIntegrityCheck( false )
				->join(
				    array( 'd' => $dbDepartment ),
				    'd.id_department = c.fk_id_department',
				    array( 
					'department' => 'name',
					'responsible' => 'fk_id_sysuser'
				    )
				)
				->join(
				    array( 'tc' => $dbCampaignType ),
				    'tc.id_campaign_type = c.fk_id_campaign_type',
				    array( 'campaign_type' )
				)
				->join(
				    array( 'chsg' => $dbCampaignGroup ),
				    'c.id_campaign = chsg.fk_id_campaign',
				    array()
				)
				->group( array( 'c.id_campaign' ) )
				->order( array( 'campaign_title' ) );
	
	return $select;
    }
    
    /**
     * 
     * @param array $filters
     * @return Zend_Db_Table_Rowset
     */
    public function listByFilters( $filters = array() )
    {
	$select = $this->getSelectCampaign();
	
	if ( !empty( $filters['campaign_title'] ) )
	    $select->where( 'c.campaign_title LIKE ?', '%' . $filters['campaign_title'] . '%' );
	
	if ( !empty( $filters['fk_id_department'] ) )
	    $select->where( 'c.fk_id_department = ?', $filters['fk_id_department'] );
	
	if ( !empty( $filters['fk_id_campaign_type'] ) )
	    $select->where( 'c.fk_id_campaign_type = ?', $filters['fk_id_campaign_type'] );
	
	if ( !empty( $filters['status'] ) )
	    $select->where( 'c.status IN (?)', (array)$filters['status'] );
	
	if ( !empty( $filters['group'] ) )
	    $select->where( 'chsg.fk_id_sms_group IN (?)', (array)$filters['group'] );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listIncoming( $id )
    {
	$dbSmsIncoming = App_Model_DbTable_Factory::get( 'SmsIncoming' );
	
	$mapperGroupSms = new Sms_Model_Mapper_Group();
	$selectContact = $mapperGroupSms->getSelectContacts();
	
	$selectContact->join(
			array( 'si' => $dbSmsIncoming ),
			'si.fk_id_sms_group_contact = t.id_sms_group_contact'
		      )
		      ->where( 'si.fk_id_campaign = ?', $id )
		      ->order( array( 'si.date_time DESC' ) );
	
	return $dbSmsIncoming->fetchAll( $selectContact );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listContactsToSend( $id, $limit = false )
    {
	$mapperGroupSms = new Sms_Model_Mapper_Group();
	$selectContacts = $mapperGroupSms->getSelectContacts();
	
	// Get the SMS Config
	$mapperConfig = new Admin_Model_Mapper_SmsConfig();
	$config = $mapperConfig->getConfig();
	
	$dbCampainGroup = App_Model_DbTable_Factory::get( 'CampaignHasSmsGroup' );
	$dbCampainSent = App_Model_DbTable_Factory::get( 'CampaignSent' );
	
	$selectContacts->join(
			    array( 'cg' => $dbCampainGroup ),
			    'cg.fk_id_sms_group = t.fk_id_sms_group',
			    array()
			)
			->joinLeft(
			    array( 'cs' => $dbCampainSent ),
			    'cs.fk_id_campaign = cg.fk_id_campaign '
			    . 'AND cs.fk_id_sms_group_contact = t.id_sms_group_contact',
			    array(
				'id_campaign_sent',
				'attempts'
			    )
			)
			->where( 'cg.fk_id_campaign = ?', $id )
			->where( '( cs.id_campaign_sent IS NULL' )
			->orWhere( 'cs.status = ?', 'E' )
			->where( 'cs.attempts < ? )', $config->error_attempts );
	
	if ( !empty( $limit ) )
	    $selectContacts->limit( $limit );
	
	return $this->_dbTable->fetchAll( $selectContacts );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detailCampaign( $id )
    {
	$select = $this->getSelectCampaign();
	$select->where( 'c.id_campaign = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::SMS,
	    'fk_id_sysform'	    => Sms_Form_Campaign::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
    
    /**
     * 
     * @param string $text
     * @param int $campaign
     * @return mixed
     */
    public function saveLog( $text, $campaign )
    {
	$dbLog = App_Model_DbTable_Factory::get( 'CampaignLog' );
	$row = $dbLog->createRow();
	
	$row->campaign_log = $text;
	$row->fk_id_campaign = $campaign;
	return $row->save();
    }
}