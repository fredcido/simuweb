<?php

class Register_Model_Mapper_AddSubDistrict extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_AddSubDistrict
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_AddSubDistrict();

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

	    $row = $this->_checkSubDistrict( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Sub-Distritu iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	  
	    if ( empty( $this->_data['id_addsubdistrict'] ) )
		$history = 'INSERE SUB DISTRITU: %s - INSERIDO NOVO SUB DISTRITO';
	    else
		$history = 'ALTERA SUB-DISTRITU: %s - ALTERADO SUB-DISTRITO COM SUCESSO';
	    
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['sub_district'] );
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
    protected function _checkSubDistrict()
    {
	$select = $this->_dbTable->select()
				 ->where( 'acronym = ?', $this->_data['acronym'] )
				 ->where( 'fk_id_adddistrict = ?', $this->_data['fk_id_adddistrict'] );

	if ( !empty( $this->_data['id_addsubdistrict'] ) )
	    $select->where( 'id_addsubdistrict <> ?', $this->_data['id_addsubdistrict'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll( $district = false )
    {
	$dbCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$dbSubDistrict = App_Model_DbTable_Factory::get( 'AddSubDistrict' );
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	
	$select = $dbSubDistrict->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 'sd' => $dbSubDistrict ) )
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
			   ->order( array( 'country', 'District', 'sub_district' ) );
	
	if ( !empty( $district ) )
	    $select->where( 'sd.fk_id_adddistrict = ?', $district );
	
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
	    'fk_id_sysform'	    => Register_Form_SubDistrict::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
    
    /**
     * 
     * @access 	public
     * @param 	int $id
     * @return 	Zend_Db_Table_Row
     */
    public function fetchRow( $id )
    {
	$dbCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$dbSubDistrict = App_Model_DbTable_Factory::get( 'AddSubDistrict' );
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	
	$select = $dbSubDistrict->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 'sd' => $dbSubDistrict ) )
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
			   ->where( 'sd.id_addsubdistrict = ?' , $id );

	return $this->_getDbTable()->fetchRow( $select );
    }
    
    /**
     *
     * @return array
     */
    public function getMultiCombo()
    {
	$subDistricts = $this->listAll();
	
	$multiGroup = array( '' => '' );
	foreach ( $subDistricts as $subDistrict ) {
	    
	    if ( !array_key_exists( $subDistrict->District, $multiGroup ) )
		$multiGroup[$subDistrict->District] = array();
	    
	    $multiGroup[$subDistrict->District][$subDistrict->id_addsubdistrict] = $subDistrict->sub_district;
	}
	
	return $multiGroup;
    }
}