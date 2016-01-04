<?php

class Admin_Model_Mapper_SmsConfig extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_SmsConfig
     */
    protected $_dbTable;
    
    /**
     *
     * @var string
     */
    protected $_idCache = 'sms_config';

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_SmsConfig();

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
	    
	    $history = 'INSERE SMS CONFIG';
	    
	    $this->_data['fk_id_sysuser'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $this->_data['sms_unit_cost'] = App_General_String::toFloat( $this->_data['sms_unit_cost'] );
	   
	    $id = parent::_simpleSave();
	    
	    $this->_sysAudit( $history );
	    
	    $this->_reloadSmsConfig();
	    
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
     */
    protected function _reloadSmsConfig()
    {
	App_Cache::remove( $this->_idCache );
	$config = $this->getCurrentConfig();
	App_Cache::save( $config, $this->_idCache );
    }
    
    /**
     *
     * @return Zend_Db_Table_Row
     */
    public function getCurrentConfig()
    {
	$dbSmsConfig = App_Model_DbTable_Factory::get( 'SmsConfig' );
	return $dbSmsConfig->fetchAll( array(), array( 'id_sms_config DESC' ) )->current();
    }
    
    /**
     *
     * @return type 
     */
    public function getConfig()
    {
	if ( FALSE == ( $config = App_Cache::load( $this->_idCache ) ) ) {
	    
	    $config = $this->getCurrentConfig();
	    App_Cache::save( $config, $this->_idCache );
	}
	
	return $config;
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
	    'fk_id_sysform'	    => Admin_Form_SmsConfig::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}