<?php

class Admin_Model_Mapper_SmsCredit extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_SmsCredit
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_SmsCredit();

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
	    
	    $this->_data['fk_id_sysuser'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $this->_data['value'] = App_General_String::toFloat( $this->_data['value'] );
	    
	    $id = parent::_simpleSave();
	    
	    $history = 'INSERE PULSA, FOLIN HIRA %s - HIRA: %s  - DEPARTAMENTU %s';
	    $history = sprintf( $history, $this->_data['value'], $this->_data['amount'], $this->_data['fk_id_department'] );
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
     * @return Zend_Db_Table_Rowset 
     */
    public  function fetchAll()
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
	
	return $dbSmsCredit->fetchAll( $select );
    }
    
    /**
     *
     * @return Zend_Db_Select
     */
    public function selectBalance()
    {
	$dbDepartment = App_Model_DbTable_Factory::get( 'Department' );
	$dbCredit = App_Model_DbTable_Factory::get( 'SmsCredit' );
	$dbConfig = App_Model_DbTable_Factory::get( 'SmsConfig' );
	$dbSent = App_Model_DbTable_Factory::get( 'CampaignSent' );
	$dbCampaign = App_Model_DbTable_Factory::get( 'Campaign' );
	$dbCampaignGroup = App_Model_DbTable_Factory::get( 'CampaignHasSmsGroup' );
	$dbSmsGroup = App_Model_DbTable_Factory::get( 'SmsGroup' );
	$dbSmsGroupContact = App_Model_DbTable_Factory::get( 'SmsGroupContact' );
	
	$selectSent = $dbSent->select()
			    ->from( 
				array( 'cs' => $dbSent ), 
				array( 
				    'sents'	=> new Zend_Db_Expr( 'COUNT(1)' ),
				    'attempts'	=> new Zend_Db_Expr( 'SUM(attempts)' ),
				) 
			    )
			    ->setIntegrityCheck( false )
			    ->join(
				array( 'sc' => $dbConfig ),
				'sc.id_sms_config = cs.fk_id_sms_config',
				array( 'sms_unit_cost' )
			    )
			    ->join(
				array( 'c' => $dbCampaign ),
				'c.id_campaign = cs.fk_id_campaign',
				array( 'fk_id_department' )
			    )
			    ->group( array( 'sms_unit_cost' ) );
	
	$selectSentCost = $dbSent->select()
				 ->setIntegrityCheck( false )
				 ->from(
				    array( 't' => new Zend_Db_Expr( '(' . $selectSent . ')' ) ),
				    array(
					'total' => new Zend_Db_Expr( 'SUM( t.attempts * t.sms_unit_cost )' )
				    )
				 )
				 ->where( 't.fk_id_department = scd.fk_id_department' );

	
	$selectNotExists = $dbSent->select()
				  ->from( 
				    array( 'cs' => $dbSent ),
				    array( new Zend_Db_Expr( 'NULL' ) )
				  )
				  ->where( 'cs.fk_id_campaign = c.id_campaign' )
				  ->where( 'cs.fk_id_sms_group_contact = sgc.id_sms_group_contact' );
	
	$selectNotSent = $dbCampaign->select()
				    ->from( 
					array( 'c' => $dbCampaign ), 
					array( 
					    'not_sents' => new Zend_Db_Expr( 'COUNT(1)'),
					    'fk_id_department' => new Zend_Db_Expr( 'IFNULL( fk_id_department, 0 )')
					) 
				    )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 'chsg' => $dbCampaignGroup ),
					'chsg.fk_id_campaign = c.id_campaign',
					array()
				    )
				    ->join(
					array( 'sg' => $dbSmsGroup ),
					'sg.id_sms_group = chsg.fk_id_sms_group',
					array()
				    )
				    ->join(
					array( 'sgc' => $dbSmsGroupContact ),
					'sgc.fk_id_sms_group = sg.id_sms_group',
					array()
				    )
				    ->join(
					array( 'sc' => $dbConfig ),
					'sc.id_sms_config = c.fk_id_sms_config',
					array( 'sms_unit_cost' => new Zend_Db_Expr( 'IFNULL( sms_unit_cost, 0 )') )
				    )
				    ->where( 'c.status not IN(?)', array( Sms_Model_Mapper_Campaign::STATUS_CANCELLED, Sms_Model_Mapper_Campaign::STATUS_COMPLETED ) )
				    ->where( 'NOT EXISTS (?)', new Zend_Db_Expr( '(' . $selectNotExists . ')' ) );
	
	$selectNotSentCost = $dbSent->select()
				 ->setIntegrityCheck( false )
				 ->from(
				    array( 't' => new Zend_Db_Expr( '(' . $selectNotSent . ')' ) ),
				    array(
					'total' => new Zend_Db_Expr( 'SUM( t.not_sents * t.sms_unit_cost )' )
				    )
				 )
				 ->where( 't.fk_id_department = scd.fk_id_department' );
	
	$selectValues = $dbCredit->select()
				 ->from(
				    array( 'scd' => $dbCredit ),
				    array(
				      'fk_id_department',
				      'spent'	    => new Zend_Db_Expr( '(' . $selectSentCost . ')' ),
				      'not_sent'    => new Zend_Db_Expr( '(' . $selectNotSentCost . ')' ),
				      'credit'	    => new Zend_Db_Expr( '(IFNULL( SUM(scd.value), 0 ))' ),
				    )
				 )
				 ->setIntegrityCheck( false )
				 ->group( array( 'fk_id_department' ) );
	
	$selectBalance = $dbCredit->select()
				  ->from(
					array( 'b' => new Zend_Db_Expr( '(' . $selectValues . ')' ) ),
					array(
					    'balance' => new Zend_Db_Expr( '( (IFNULL(b.credit, 0) - IFNULL(b.spent, 0)) - IFNULL(b.not_sent, 0))' )
					)
				   )
				   ->setIntegrityCheck( false )
				   ->join(
					array( 'd' => $dbDepartment ),
					'd.id_department = b.fk_id_department',
					array( 'department' => 'name' )
				   )
				   ->order( array( 'name' ) );
	
	return $selectBalance;
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function getBalanceDepartment( $id )
    {
	$dbDepartment = App_Model_DbTable_Factory::get( 'Department' );
	$selectBalance = $this->selectBalance();
	
	$selectBalance->where( 'd.id_department = ?', $id );
	
	$balance = $dbDepartment->fetchRow( $selectBalance );
	return $balance;
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function getBalance()
    {
	$dbDepartment = App_Model_DbTable_Factory::get( 'Department' );
	$selectBalance = $this->selectBalance();
	
	return $dbDepartment->fetchAll( $selectBalance );
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
	    'fk_id_sysform'	    => Admin_Form_SmsCredit::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}