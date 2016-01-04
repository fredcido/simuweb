<?php

class Report_Model_Mapper_Sms extends App_Model_Mapper_Abstract
{
 
    /**
     * 
     * @return array
     */
    public function campaignReport()
    {
	$filters = $this->_data;
	$filters['status'] = $this->_data['status_campaign'];
	
	$mapperCampaign = new Sms_Model_Mapper_Campaign();
	$rows = $mapperCampaign->listByFilters( $filters );
	
	$data = array(
	    'rows' => $rows
	);
	
	return $data;
    }
    
    /**
     * 
     * @return array
     */
    public function balanceReport()
    {
	$total = 0;
	$mapperSmsCredit = new Admin_Model_Mapper_SmsCredit();
	$balanceRows = $mapperSmsCredit->getBalance();
	
	
	foreach ( $balanceRows as $row )
	    $total += $row->balance;
	
	return array(
	    'rows'  => $balanceRows,
	    'total' => $total
	);
    }
    
    /**
     * 
     * @return array
     */
    public function creditReport()
    {
	$dbSmsCredit = App_Model_DbTable_Factory::get( 'SmsCredit' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	$dbDepartment = App_Model_DbTable_Factory::get( 'Department' );
	
	$select = $dbSmsCredit->select()
				->from( array( 'sc' => $dbSmsCredit ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'u' => $dbUser ),
				    'u.id_sysuser = sc.fk_id_sysuser',
				    array( 'user' => 'name' )
				)
				->join(
				    array( 'd' => $dbDepartment ),
				    'd.id_department = sc.fk_id_department',
				    array( 'department' => 'name' )
				)
				->order( array( 'sc.date_insert DESC' ) );
	
	$filters = $this->_data;
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'DATE(sc.date_insert) >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'DATE(sc.date_insert) <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['fk_id_department'] ) )
	    $select->where( 'sc.fk_id_department = ?', $filters['fk_id_department'] );
	
	if ( !empty( $filters['fk_id_sysuser'] ) )
	    $select->where( 'sc.fk_id_sysuser = ?', $filters['fk_id_sysuser'] );
	
	$rows = $dbDepartment->fetchAll( $select );
	
	$total = 0;
	$amount = 0;
	
	foreach ( $rows as $row ) {
	 
	    $total += $row->value;
	    $amount += $row->amount;
	}
	
	return array(
	    'rows'	=> $rows,
	    'total'	=> $total,
	    'amount'	=> $amount
	);
    }
    
    /**
     * 
     * @return array
     */
    public function sendingReport()
    {
	$dbDepartment = App_Model_DbTable_Factory::get( 'Department' );
	
	$mapperCampaign = new Sms_Model_Mapper_Campaign();
	$select = $mapperCampaign->getSelectSent();
	
	$select->join(
		    array( 'd' => $dbDepartment ),
		    'd.id_department = c.fk_id_department',
		    array( 'department' => 'name' )
		);
	
	$filters = $this->_data;
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $select->where( 'DATE(cs.date_time) >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $select->where( 'DATE(cs.date_time) <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['fk_id_department'] ) )
	    $select->where( 'c.fk_id_department = ?', $filters['fk_id_department'] );
	
	if ( !empty( $filters['fk_id_campaign'] ) )
	    $select->where( 'cs.fk_id_campaign = ?', $filters['fk_id_campaign'] );
	
	if ( !empty( $filters['status_sending'] ) )
	    $select->where( 'cs.status = ?', $filters['status_sending'] );
	
	$rows = $dbDepartment->fetchAll( $select );
	
	$total = 0;
	foreach ( $rows as $row )
	    $total += $row->cost;
	
	return array(
	    'rows'	=> $rows,
	    'total'	=> $total
	);
    }
    
    /**
     * 
     * @return array
     */
    public function incomingReport()
    {
	$filters = $this->_data;
	
	$dbSmsIncoming = App_Model_DbTable_Factory::get( 'SmsIncoming' );
	$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	
	$mapperGroupSms = new Sms_Model_Mapper_Group();
	$selectContact = $mapperGroupSms->getSelectContacts();
	
	$selectContact->reset( Zend_Db_Select::COLUMNS )
			->columns(
			    array(
				'contact',
				'type',
				'number'
			    )
			)
			->join(
			    array( 'si' => $dbSmsIncoming ),
			    'si.fk_id_sms_group_contact = t.id_sms_group_contact',
			    array(
				'date_time',
				'content',
				'target',
				'source'
			    )
			)
			->join(
			    array( 'c' => $dbCampaign ),
			    'c.id_campaign = si.fk_id_campaign',
			    array( 'campaign_title' )
			);
	
	if ( !empty( $filters['fk_id_department'] ) )
	    $selectContact->where( 'c.fk_id_department = ?', $filters['fk_id_department'] );
	
	if ( !empty( $filters['fk_id_campaign'] ) )
	    $selectContact->where( 'si.fk_id_campaign = ?', $filters['fk_id_campaign'] );
	
	$selectNonData = $dbSmsIncoming->select()
					  ->from(
					    array( 'si' => $dbSmsIncoming ),
					    array(
						'contact' => new Zend_Db_Expr( '"La identifika"' ),
						'type' => new Zend_Db_Expr( '"La identifika"' ),
						'number' => 'source',
						'date_time',
						'content',
						'target',
						'source',
						'campaign_title' => new Zend_Db_Expr( '"La identifika"' )
					    )
					  )
					  ->where( 'si.fk_id_sms_group_contact IS NULL' )
					  ->where( 'si.fk_id_campaign IS NULL' );
	
	$selectNonContact = $dbSmsIncoming->select()
					  ->setIntegrityCheck( false )
					  ->from(
					    array( 'si' => $dbSmsIncoming ),
					    array(
						'contact' => new Zend_Db_Expr( '"La identifika"' ),
						'type' => new Zend_Db_Expr( '"La identifika"' ),
						'number' => 'source',
						'date_time',
						'content',
						'target',
						'source'
					    )
					  )
					  ->join(
					    array( 'c' => $dbCampaign ),
					    'c.id_campaign = si.fk_id_campaign',
					    array( 'campaign_title' )
					  )
					  ->where( 'si.fk_id_sms_group_contact IS NULL' )
					  ->where( 'si.fk_id_campaign IS NOT NULL' );
	
	$selectNonCampaign = $dbSmsIncoming->select()
					  ->setIntegrityCheck( false )
					  ->from(
					    array( 'si' => $dbSmsIncoming ),
					    array(
						'contact' => new Zend_Db_Expr( 'source' ),
						'type' => new Zend_Db_Expr( '"La identifika"' ),
						'number' => 'source',
						'date_time',
						'content',
						'target',
						'source',
						'campaign_title' => new Zend_Db_Expr( '"La identifika"' )
					    )
					  )
					  ->where( 'si.fk_id_sms_group_contact IS NOT NULL' )
					  ->where( 'si.fk_id_campaign IS NULL' );
	
	$union = array( $selectContact, $selectNonContact, $selectNonCampaign );
	if ( empty( $filters['fk_id_campaign'] ) )
	    $union[] = $selectNonData;
	
	$selectUnion = $dbSmsIncoming->select()
				    ->union( $union )
				    ->setIntegrityCheck( false )
				    ->order( array( 'date_time DESC' ) );
	
	$selectFinal = $dbSmsIncoming->select()
				->from( array( 't' => new Zend_Db_Expr( '(' .$selectUnion . ')' ) ) )
				->setIntegrityCheck( false );
	
	$date = new Zend_Date();
	
	if ( !empty( $filters['date_start'] ) )
	    $selectFinal->where( 'DATE(t.date_time) >= ?', $date->set( $filters['date_start'] )->toString( 'yyyy-MM-dd' ) );
	
	if ( !empty( $filters['date_finish'] ) )
	    $selectFinal->where( 'DATE(t.date_time) <= ?', $date->set( $filters['date_finish'] )->toString( 'yyyy-MM-dd' ) );
	
	$rows = $dbSmsIncoming->fetchAll( $selectFinal );
	
	return array( 'rows' => $rows );
    }
}