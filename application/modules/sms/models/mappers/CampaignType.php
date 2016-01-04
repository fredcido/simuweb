<?php

class Sms_Model_Mapper_CampaignType extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_SmsCampaignType
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_CampaignType();

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

	    $row = $this->_checkNameCampaignType( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Tipu Kampanha SMS iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_campaign_type'] ) )
		$history = 'INSERE TIPU KAMPANHA SMS: %s DADUS PRINCIPAL - INSERE NOVA TIPU KAMPANHA SMS';
	    else
		$history = 'ALTERA TIPU KAMPANHA SMS: %s DADUS PRINCIPAL - ALTERA TIPU KAMPANHA SMS';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['campaign_type'] );
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
    protected function _checkNameCampaignType()
    {
	$select = $this->_dbTable->select()->where( 'campaign_type = ?', $this->_data['campaign_type'] );

	if ( !empty( $this->_data['id_campaign_type'] ) )
	    $select->where( 'id_campaign_type <> ?', $this->_data['id_campaign_type'] );

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
	    'fk_id_sysform'	    => Sms_Form_CampaignType::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}