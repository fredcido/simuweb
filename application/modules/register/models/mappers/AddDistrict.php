<?php

class Register_Model_Mapper_AddDistrict extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_AddDistrict
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_AddDistrict();

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

	    $row = $this->_checkDistrict( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Distritu iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	  
	    if ( empty( $this->_data['id_adddistrict'] ) )
		$history = 'INSERE DISTRITU: %s - INSERIDO NOVO DISTRITU';
	    else
		$history = 'ALTERA DISTRITU: %s - ALTUALIZADO DISTRITO COM SUCESSO';
	    
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['District'] );
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
    protected function _checkDistrict()
    {
	$select = $this->_dbTable->select()
				 ->where( 'acronym = ?', $this->_data['acronym'] )
				 ->where( 'fk_id_addcountry = ?', $this->_data['fk_id_addcountry'] );

	if ( !empty( $this->_data['id_adddistrict'] ) )
	    $select->where( 'id_adddistrict <> ?', $this->_data['id_adddistrict'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll( $country = false )
    {
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$dbCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	
	$select = $dbDistrict->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 'd' => $dbDistrict ) )
			  ->join(
				array( 'c' => $dbCountry ),
				'c.id_addcountry = d.fk_id_addcountry',
				array( 'country' )
			   )
			   ->order( array( 'country', 'District' ) );
	
	if ( !empty( $country ) )
	    $select->where( 'd.fk_id_addcountry = ?', $country );
	
	return $dbDistrict->fetchAll( $select );
    }
    
    /**
     *
     * @param string $acronym
     * @return Zend_Db_Table_Row
     */
    public function fetchByAcronym( $acronym )
    {
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	return $dbDistrict->fetchRow( array( 'acronym = ?' => $acronym ) );
    }
    
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::REGISTER,
	    'fk_id_sysform'	    => Register_Form_District::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}