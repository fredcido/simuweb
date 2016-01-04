<?php

class Register_Model_Mapper_AddSuku extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_AddSucu
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_AddSucu();

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

	    $row = $this->_checkSuku( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Suku iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_addsucu'] ) )
		$history = 'INSERE SUKU: %s DADUS PRINCIPAL - INSERE NOVO SUKU';
	    else
		$history = 'ALTERA SUKU: %s DADUS PRINCIPAL - ALTERA SUKU';
	    
	    $id = parent::_simpleSave();
	    
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
     * @param array $data
     * @return App_Model_DbTable_Row_Abstract
     */
    protected function _checkSuku()
    {
	$select = $this->_dbTable->select()
				 ->where( 'sucu = ?', $this->_data['sucu'] )
				 ->where( 'fk_id_adddistrict = ?', $this->_data['fk_id_adddistrict'] )
				 ->where( 'fk_id_addsubdistrict = ?', $this->_data['fk_id_addsubdistrict'] );

	if ( !empty( $this->_data['id_addsucu'] ) )
	    $select->where( 'id_addsucu <> ?', $this->_data['id_addsucu'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll( $subDistrict = false )
    {
	$dbCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$dbSubDistrict = App_Model_DbTable_Factory::get( 'AddSubDistrict' );
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$dbSuku = App_Model_DbTable_Factory::get( 'AddSucu' );
	
	$select = $dbSubDistrict->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 's' => $dbSuku ) )
			  ->join(
				array( 'sd' => $dbSubDistrict ),
				's.fk_id_addsubdistrict = sd.id_addsubdistrict',
				array( 'sub_district' )
			   )
			  ->join(
				array( 'd' => $dbDistrict ),
				'd.id_adddistrict = sd.fk_id_adddistrict',
				array( 'District' )
			   )
			  ->join(
				array( 'c' => $dbCountry ),
				'c.id_addcountry = d.fk_id_addcountry',
				array( 'country' )
			   )
			   ->order( array( 'country', 'District', 'sub_district', 'sucu' ) );
	
	if ( !empty( $subDistrict ) )
	    $select->where( 's.fk_id_addsubdistrict = ?', $subDistrict );
	
	return $dbSubDistrict->fetchAll( $select );
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
	    'fk_id_sysform'	    => Register_Form_Suku::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function fetchRow( $id )
    {
	$dbCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$dbSubDistrict = App_Model_DbTable_Factory::get( 'AddSubDistrict' );
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$dbSuku = App_Model_DbTable_Factory::get( 'AddSucu' );
	
	$select = $dbSubDistrict->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 's' => $dbSuku ) )
			  ->join(
				array( 'sd' => $dbSubDistrict ),
				's.fk_id_addsubdistrict = sd.id_addsubdistrict',
				array( 'sub_district' )
			   )
			  ->join(
				array( 'd' => $dbDistrict ),
				'd.id_adddistrict = sd.fk_id_adddistrict',
				array( 'District', 'fk_id_addcountry' )
			   )
			  ->join(
				array( 'c' => $dbCountry ),
				'c.id_addcountry = d.fk_id_addcountry',
				array( 'country' )
			   )
			   ->where( 's.id_addsucu = ?', $id );
	
	return $dbSubDistrict->fetchRow( $select );
    }
}