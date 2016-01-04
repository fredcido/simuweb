<?php

class Cron_Model_Mapper_CampaignSms extends App_Model_Abstract
{    
    
    /**
     *
     * @var Sms_Model_Mapper_Campaign 
     */
    protected $_mapperCampaign;
    
    /**
     * 
     * @return boolean
     */
    public function startSending()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    // Save log informing that the robot has initied
	    $this->saveLog( 'ROBOT HAHU BA HARUKA HAHU TIHA ONA' );
	    
	    $this->_mapperCampaign = new Sms_Model_Mapper_Campaign();
	    
	    // Fetch campaigns to send
	    $campaigns = $this->getCampaignsToSend();
	   
	    // If there is no campaign to be sent
	    if ( $campaigns->count() < 1 ) {
		
		$this->saveLog( 'LA IHA KAMPANHA ATU HARUKA' );
	    } else {
	    
		$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );

		foreach ( $campaigns as $campaign ) {

		    $checkRunning = trim( shell_exec( 'ps -ef | grep -v grep | grep idcampaign=' . $campaign->id_campaign ) );
		    if ( !empty( $checkRunning ) )
			continue;

		    // Set the campaign as be used by the robot
		    $data = array(
			'date_start' => Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm:ss'),
			'status'	 => Sms_Model_Mapper_Campaign::STATUS_ROBOT
		    );

		    $where = array( 'id_campaign = ?' => $campaign->id_campaign );
		    $dbCampaign->update( $data, $where );

		    $command = 'controller=service action=sendcampaign idcampaign=' . $campaign->id_campaign;
		    $this->_runScript( $command );
		    
		    sleep( 2 );
		}
	    }
	    
	    $dbAdapter->commit();
	    
	} catch ( Exception $ex ) {
	    
	    $dbAdapter->rollBack();
	    $this->saveLog( 'ERRO ROBO SMS: ' . $ex->getMessage() );
	}
    }
    
    /**
     * 
     * @param int $id
     * @return boolean
     */
    public function sendCampaign( $id )
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $this->_mapperCampaign = new Sms_Model_Mapper_Campaign();
	    $dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	    
	    // Save log informing that the campaign sending has initied
	    $this->saveLog( 'ROBOT HAHU BA HARUKA KAMPANHA HO ID: ' . $id );
	    $this->_mapperCampaign->saveLog( 'ROBOT HAHU BA HARUKA KAMPANHA', $id );
	    
	    // Set the campaign as running by the robot
	    $data = array( 'status' => Sms_Model_Mapper_Campaign::STATUS_ROBOT );
	    $where = array( 'id_campaign = ?' => $id );
	    $dbCampaign->update( $data, $where );
	    
	    // Fetch the campaign
	    $campaign = $this->_mapperCampaign->detailCampaign( $id );
	    
	    // Check if there is no contact to send
	    if ( empty( $campaign->to_send ) ) {
		$this->_finishCampaign( $id );
	    } else {
	    
		// Check if there is credit to the department
		$department = $this->_checkDepartment( $campaign );
		if ( !empty( $department ) )
		    $this->_sendSms( $campaign );
	    }
	    
	    // Save log informing that the campaign sending has finished
	    $this->saveLog( 'ROBOT REMATA BA HARUKA KAMPANHA HO ID: ' . $id );
	    $this->_mapperCampaign->saveLog( 'ROBOT REMATA BA HARUKA KAMPANHA', $id );
	    
	    $dbAdapter->commit();
	    
	} catch ( Exception $ex ) {
	    
	    $dbAdapter->rollBack();
	    $this->saveLog( 'ERRO ROBO SMS BA HARUKA KAMPANHA HO ID: ' . $id . ' :' . $ex->getMessage() );
	}
    }
    
    /**
     * 
     * @param int $id
     */
    protected function _finishCampaign( $id )
    {
	$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	
	$this->saveLog( 'KONTATU ATU HARUKA HOTU ONA BA KAMPANHA: ' . $id );
	$this->_mapperCampaign->saveLog( 'KONTATU ATU HARUKA HOTU ONA', $id );
	$this->_mapperCampaign->saveLog( 'KAMPANHA REMATA', $id );
	
	// Set the campaign as completed
	$data = array( 'status' => Sms_Model_Mapper_Campaign::STATUS_COMPLETED );
	$where = array( 'id_campaign = ?' => $id );
	$dbCampaign->update( $data, $where );
	
	$noteMapper = new Default_Model_Mapper_Note();
	$noteModelMapper = new Default_Model_Mapper_NoteModel();

	// Search the user who must receive notes when a campaign is finished
	$noteTypeMapper = new Admin_Model_Mapper_NoteType();
	$users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::CAMPAIGN_FINISHED );
	
	$mapperCampaign = new Sms_Model_Mapper_Campaign();
	$campaign = $mapperCampaign->detailCampaign( $id );

	$users[] = $campaign['responsible'];

	// save the warning to the user responsible of the department
	$dataNote = array(
	    'title'   => 'KAMPANHA REMATA TIHA ONA',
	    'level'   => 1,
	    'message' => $noteModelMapper->getCampaignFinished( $campaign ),
	    'users'   => $users
	);

	$noteMapper->setData( $dataNote )->saveNote();
    }
    
    /**
     * 
     * @param Zend_Db_Table_Row $campaign
     * @param Zend_Db_Table_Row $config
     * @return string
     */
    protected function _formatSmsContent( $campaign, $config )
    {
	$message = array();
	
	if ( !empty( $config->sms_prefix ) )
	    $message['prefix'] = trim( $config->sms_prefix );
	
	$message['content'] = trim( preg_replace( '/[`^~\'"]/', null, iconv( 'UTF-8', 'ASCII//TRANSLIT', trim( $campaign->content ) ) ) );
	
	if ( !empty( $campaign->wait_response ) && !empty( $config->sms_sufix ) )
	    $message['sufix'] = sprintf( 'Haruka kodigu %s%s', trim( $config->sms_sufix ), $campaign->id_campaign );
	
	$finalMessage = trim( implode( ' ', $message ) );
	
	if ( strlen( $finalMessage ) > $config->max_length ) {
	 
	    $excess =  strlen( $finalMessage ) - $config->max_length;
	    $toCut = strlen( $message['content'] ) - $excess;
	    $message['content'] = substr ( $message['content'], 0, $toCut );
	    
	    $finalMessage = trim( implode( ' ', $message ) );
	}
	
	return $finalMessage;
    }
    
    /**
     * 
     * @param Zend_Db_Table_Row $campaign
     */
    protected function _sendSms( $campaign )
    {
	// Get the current SMS Config
	$mapperConfig = new Admin_Model_Mapper_SmsConfig();
	$smsConfig = $mapperConfig->getConfig();
	$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	
	$dbContactSend = App_Model_DbTable_Factory::get( 'CampaignSent' );
	
	// Fetch All contacts to be sent
	$contactsToSend = $this->_mapperCampaign->listContactsToSend( $campaign->id_campaign, 300 );
	
	// Format Sms Content
	$contentCampaign = $this->_formatSmsContent( $campaign, $smsConfig );
	
	foreach ( $contactsToSend as $contact ) {
	    
	    // Check if the department has credit yet
	    if ( !$this->_checkDepartment( $campaign ) )
		return false;
	    
	    $number = App_General_String::validateNumber( $contact->number );
	    
	    $data = array(
		'id_campaign_sent'	    => $contact->id_campaign_sent,
		'attempts'		    => ++$contact->attempts,
		'fk_id_sms_config'	    => $smsConfig->id_sms_config,
		'fk_id_campaign'	    => $campaign->id_campaign,
		'fk_id_sms_group_contact'   => $contact->id_sms_group_contact,
		'target'		    => $number
	    );
	    
	    // Check if the number to be sent is valid
	    if ( empty( $number ) ) {
		
		$data['status'] = 'E';
		$data['log'] = 'KONTATU IDA NE\'E HO NUMERO SALA';
		
	    } else {
		
		// Prepare the parameters to be sent
		$toSend = array(
		    'to' => $number,
		    'msg'=> $contentCampaign,
		    'id' => implode( '|', array( $campaign->id_campaign, $contact->id_sms_group_contact ) )
		);
		
		// Try to send the Sms
		try {
		    
		    $result = App_Util_Sms::send( $smsConfig->gateway_url, $toSend );
		    $data['status'] = ( (bool)$result['status'] ) ? 'S' : 'E';
		    $data['log'] = ( (bool)$result['status'] ) ? '' : $result['msgerror'];
		    $data['source'] = $result['source'];
		    
		} catch ( Exception $e ) {
		    
		    $data['status'] = 'E';
		    $data['log'] = $e->getMessage();
		}
	    }
	    
	    $this->_data = $data;
	    parent::_simpleSave( $dbContactSend, false );
	}
	
	// Fetch All contacts to be sent
	$contactsToSend = $this->_mapperCampaign->listContactsToSend( $campaign->id_campaign );
	if ( $contactsToSend->count() < 1 ) {
	    $this->_finishCampaign( $campaign->id_campaign );
	} else {
	    
	    // Set the campaign as initied
	    $data = array( 'status' => Sms_Model_Mapper_Campaign::STATUS_INITIED );
	    $where = array( 'id_campaign = ?' => $campaign->id_campaign );
	    $dbCampaign->update( $data, $where );
	}
    }
    
    /**
     * 
     * @param Zend_Db_Table_Row $campaign
     * @return boolean
     */
    protected function _checkDepartment( $campaign )
    {
	$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	
	$mapperDepartment = new Admin_Model_Mapper_Department();
	
	// Fetch the department
	$department = $mapperDepartment->detailDepartment( $campaign->fk_id_department );

	// Check if the department has balance
	if ( $department['balance'] <= 0 ) {

	    $this->saveLog( 'DEPARTAMENTU: ' . $campaign->fk_id_department . ' NIA PULSA HOTU ONA ATU HARUKA KAMPANHA: ' . $campaign->id_campaign );
	    $this->_mapperCampaign->saveLog( 'DEPARTAMENTU: ' . $campaign->fk_id_department . ' NIA PULSA HOTU ONA ATU HARUKA KAMPANHA', $campaign->id_campaign );

	    // Set the campaign as initied
	    $data = array( 'status' => Sms_Model_Mapper_Campaign::STATUS_INITIED );
	    $where = array( 'id_campaign = ?' => $campaign->id_campaign );
	    $dbCampaign->update( $data, $where );
	    
	    $noteMapper = new Default_Model_Mapper_Note();
	    $noteModelMapper = new Default_Model_Mapper_NoteModel();
	    
	    // Search the user who must receive notes when a department credit is over
	    $noteTypeMapper = new Admin_Model_Mapper_NoteType();
	    $users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::DEPARTMENT_CREDIT );
	    
	    $users[] = $department['fk_id_sysuser'];
	    
	    // save the warning to the user responsible of the department
	    $dataNote = array(
		'title'   => 'DEPARTAMETNU-NIA PULSA HOTU ONA',
		'level'   => 0,
		'message' => $noteModelMapper->getDepartmentCredit( $department ),
		'users'   => $users
	    );

	    $noteMapper->setData( $dataNote )->saveNote();
	    
	    return false;
	}

	return $department;
    }
    
    /**
     * 
     * @param string $command
     */
    protected function _runScript( $command )
    {
	//$phpBin = str_replace( "\n", "", trim( shell_exec( 'which php' ) ) );
	$phpBin = '/usr/local/bin/php';
	$path = realpath( APPLICATION_PATH . '/../index.php' );
	
	$run = $phpBin . ' ' . $path . ' ' . $command . ' > /dev/null &';
	
	passthru( $run );
    }


    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function getCampaignsToSend()
    {
	$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	$selectCampaign = $this->_mapperCampaign->getSelectCampaign();
	
	$statusToSend = array(
	    Sms_Model_Mapper_Campaign::STATUS_STOPPED,
	    Sms_Model_Mapper_Campaign::STATUS_INITIED,
	);
	
	$selectCampaign->where( 'status IN (?)', $statusToSend )
		       ->orWhere( '( status = ?', Sms_Model_Mapper_Campaign::STATUS_SCHEDULED )
		       ->where( ' date_scheduled <= ? )', Zend_Date::now()->toString( 'yyyy-MM-dd' ) )
		       ->having( 'to_send > 0' );
	
	return $dbCampaign->fetchAll( $selectCampaign );
    }
    
    /**
     * 
     * @param string $content
     */
    public function saveLog( $content )
    {
	$dbLogSmsRobot = App_Model_DbTable_Factory::get( 'LogSmsRobot' );
	$row = $dbLogSmsRobot->createRow();
	$row->content = $content;
	$row->save();
	
	$logFile = APPLICATION_PATH . '/../public/log_sms.log';
	
	$fd = fopen( $logFile, 'a+' );
	$content = date( 'd/m/Y H:i:s') . " - " . $content . "\n\r" .
	fwrite( $fd, $content );
	fclose( $fd );
    }
    
    /**
     * 
     * @param array $data
     * @return int
     */
    public function testSmsSending( $data )
    {
	$this->saveLog( 'RECEIVED SMS: ' . print_r( $data, true ) );
	
	$sources = array(
	   '67077234562',  
	   '67071231232',  
	   '67078128128',  
	   '67075923423',  
	   '67072391912',  
	   '67078142212',  
	   '67078211292',  
	   '67073312122',  
	);
	
	$status = array( 0, 1 );
	$description = array( 'Error sending SMS', 'Sms Sent successfully' );
	
	$return = array(
	    'status'      => $status[array_rand($status)],
	    'description' => $description[array_rand($description)],
	    'source'	  => $sources[array_rand($sources)],
	);
	
	return $return;
    }
    
    /**
     * 
     */
    public function retrieveSms()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    // Save log informing that the robot has initied
	    $this->saveLog( 'ROBOT HAHU BA BUKA SMS MAK SIMU' );
	    
	    // Get the current SMS Config
	    $mapperConfig = new Admin_Model_Mapper_SmsConfig();
	    $smsConfig = $mapperConfig->getConfig();
	    
	    $messages = App_Util_Sms::retrieve( $smsConfig->gateway_url );
	    
	    $dbSmsIncoming = App_Model_DbTable_Factory::get( 'SmsIncoming' );
	    $campaigns = array();
	    
	    foreach ( $messages as $message ) {
		
		$message = array_map( 'trim', $message );
		$message = array_map( 'urldecode', $message );
		
		$where = array(
		    'source = ?' => $message['source'],
		    'target = ?' => $message['target'],
		    'content = ?' => $message['content'],
		);
		
		$msgReceived = $dbSmsIncoming->fetchRow( $where );
		if ( !empty( $msgReceived ) )
		    continue;
		
		$message['campaign'] = $this->_tryDiscoverCampaign( $message, $smsConfig );
		
		if ( !empty( $message['campaign'] ) && !in_array( $message['campaign'], $campaigns ) )
		    $campaigns[] = $message['campaign'];
		
		$row = $dbSmsIncoming->createRow();
		$row->source = $message['source'];
		$row->target = $message['target'];
		$row->content = $message['content'];
		$row->date_time = $message['date_time'];
		$row->fk_id_campaign = $message['campaign'];
		$row->fk_id_sms_group_contact = $this->_tryDiscoverContact( $message );
		
		$row->save();
	    }
	    
	    if ( !empty( $campaigns ) )
		$this->_sendNotificationCampaigns( $campaigns );
	    
	    $this->saveLog( 'ROBOT REMATA BA BUKA SMS MAK SIMU' );
	    
	    $dbAdapter->commit();
	    
	} catch ( Exception $ex ) {
	    
	    $dbAdapter->rollBack();
	    $this->saveLog( 'ERRO ROBO SIMU SMS: ' . $ex->getMessage() );
	}
    }
    
    /**
     * 
     * @param array $campaigns
     */
    protected function _sendNotificationCampaigns( $campaigns )
    {
	$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	
	// Retrieve the campaigns
	$mapperCampaigns = new Sms_Model_Mapper_Campaign();
	$selectCampaings = $mapperCampaigns->getSelectCampaign();
	$selectCampaings->where( 'c.id_campaign IN(?)', $campaigns );
	$rows = $dbCampaign->fetchAll( $selectCampaings );
	
	$noteMapper = new Default_Model_Mapper_Note();
	$noteModelMapper = new Default_Model_Mapper_NoteModel();

	// Search the user who must receive notes when there is sms
	$noteTypeMapper = new Admin_Model_Mapper_NoteType();
	$users = $noteTypeMapper->getUsersByNoteType( Admin_Model_Mapper_NoteType::SMS_RECEIVED );
	
	foreach ( $rows as $row ) {
	    
	    $responsibles = $users;
	    $responsibles[] = $row['responsible'];

	    // save the warning to the user responsible of the department
	    $dataNote = array(
		'title'   => 'SMS KAMPANHA FOIN TAMA',
		'level'   => 1,
		'message' => $noteModelMapper->getCampaignSmsReceived( $row ),
		'users'   => $responsibles
	    );

	    $noteMapper->setData( $dataNote )->saveNote();
	}
    }
    
    /**
     * 
     * @param array $message
     * @return null|int
     */
    protected function _tryDiscoverCampaign( $message, $smsConfig )
    {
	$pattern = '/' . preg_quote( $smsConfig->sms_sufix ) . '([0-9]+)/i';
	
	$content = strtolower( $message['content'] );
	if ( preg_match( $pattern, $content, $match ) ) {
	    
	    $dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	    $where = array( 'id_campaign = ?' => $match[1] );
	    
	    $campaign = $dbCampaign->fetchRow( $where );
	    return empty( $campaign ) ? null : (int)$campaign->id_campaign;
	}
	
	return null;
    }
    
    /**
     * 
     * @param array $message
     * @return null|int
     */
    protected function _tryDiscoverContact( $message )
    {
	if ( empty( $message['source'] ) )
	    return null;
	
	$dbCampaignSent = App_Model_DbTable_Factory::get( 'CampaignSent' );
	
	$select = $dbCampaignSent->select()
				->from(
				    array( 'cs' => $dbCampaignSent ),
				    array( 'fk_id_sms_group_contact' )
				)
				->setIntegrityCheck( false )
				->where( 'cs.target = ?', $message['source'] )
				->where( 'cs.source = ?', $message['target'] );
	
	if ( !empty( $message['campaign'] ) )
	    $select->where( 'cs.fk_id_campaign = ?', $message['campaign'] );
	
	$contact = $dbCampaignSent->fetchRow( $select );
	return empty( $contact ) ? null : (int)$contact->fk_id_sms_group_contact;
    }
}