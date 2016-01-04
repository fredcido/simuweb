<?php

class Register_Model_Mapper_Enterprise extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_FEFPEnterprise
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEFPEnterprise();

	return $this->_dbTable;
    }

    /**
     * 
     * @return int|bool
     */
    public function saveInformation()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	   
	    $row = $this->_checkEnterprise( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Empreza iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_fefpenterprise'] ) )
		$history = 'REJISTRU EMPRESA: %s';
	    else {
		
		unset( $this->_data['fk_id_dec'] );
		$history = 'ALTERA EMPRESA: %s';
	    }
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['enterprise_name'] );
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
    protected function _checkEnterprise()
    {
	$select = $this->_dbTable->select()->where( 'enterprise_name = ?', $this->_data['enterprise_name'] );

	if ( !empty( $this->_data['id_fefpenterprise'] ) )
	    $select->where( 'id_fefpenterprise <> ?', $this->_data['id_fefpenterprise'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @return int|bool
     */
    public function saveContact()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	    $dbEnteprriseContact = App_Model_DbTable_Factory::get( 'FefpEnterprise_has_PerContact' );
	    
	    $dataForm = $this->_data;
	    
	    $id = parent::_simpleSave( $dbContact, false );
	
	    if ( empty( $dataForm['id_percontact'] ) ) {
		
		$row = $dbEnteprriseContact->createRow();
		$row->fk_id_fefpenterprise = $dataForm['fk_id_fefpenterprise'];
		$row->fk_id_id_percontact = $id;
		$row->save();
		
		$history = 'REJISTRU CONTATU EMPREZA: %s';
		
	    } else
		$history = 'ALTERA CONTATU EMPREZA: %s';
	    
	    $history = sprintf( $history, $id );
	    $this->_sysAudit( $history, Register_Form_EnterpriseContact::ID );
	    
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
     * @return int|bool
     */
    public function saveAddress()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbAddAddress = App_Model_DbTable_Factory::get( 'AddAddress' );
	    
	    $dataForm = $this->_data;
	    
            if ( !empty( $this->_data['start_date'] ) ) {
                $startDate = new Zend_Date( $this->_data['start_date'] );
                $this->_data['start_date'] = $startDate->toString( 'yyyy-MM-dd' );
            } else
                $this->_data['start_date'] = null;
	    
            if ( !empty( $this->_data['finish_date'] ) ) {
                $finishDate = new Zend_Date( $this->_data['finish_date'] );
                $this->_data['finish_date'] = $finishDate->toString( 'yyyy-MM-dd' );
            } else
                $this->_data['finish_date'] = null;
	    
	    if ( empty( $dataForm['id_addaddress'] ) )
		$history = 'INSERE HELA FATIN EMPREZA: %s';
	    else
		$history = 'ALTERA HELA FATIN EMPREZA: %s';
	    
	    $id = parent::_simpleSave( $dbAddAddress, false );
	    
	    $history = sprintf( $history, $id );
	    $this->_sysAudit( $history, Register_Form_EnterpriseAddress::ID );
	    
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
     * @return int|bool
     */
    public function saveStaff()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	    
	    $dataForm = $this->_data;
	    
	    $dateBirth = new Zend_Date( $this->_data['birth_date'] );
	    $this->_data['birth_date'] = $dateBirth->toString( 'yyyy-MM-dd' );
	    
	    if ( empty( $dataForm['id_staff'] ) )
		$history = 'REJISTRU FUNCIONARIU BA EMPREZA: %s';
	    else
		$history = 'ALTERA FUNCIONARIU BA EMPREZA: %s';
	    
	    $id = parent::_simpleSave( $dbStaff, false );
	    
	    $history = sprintf( $history, $id );
	    $this->_sysAudit( $history, Register_Form_EnterpriseStaff::ID );
	    
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
     * @return int|bool
     */
    public function deleteContact()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	    $dbEnterpriseContact = App_Model_DbTable_Factory::get( 'FefpEnterprise_has_PerContact' );
	    
	    $where = array(
		$dbAdapter->quoteInto( 'fk_id_fefpenterprise = ?', $this->_data['id'] ),
		$dbAdapter->quoteInto( 'fk_id_id_percontact = ?', $this->_data['id_contact'] )
	    );
	    
	    $dbEnterpriseContact->delete( $where );
	    
	    $where = $dbAdapter->quoteInto( 'id_percontact = ?', $this->_data['id_contact'] );
	    $dbContact->delete( $where );
	    
	    $history = 'DELETA CONTATU EMPREZA: %s';
	    
	    $history = sprintf( $history, $this->_data['id_contact'] );
	    $this->_sysAudit( $history, Register_Form_EnterpriseContact::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
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
     * @return int|bool
     */
    public function deleteAddress()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbAddAddress = App_Model_DbTable_Factory::get( 'AddAddress' );
	    
	    $where = array( $dbAdapter->quoteInto( 'id_addaddress = ?', $this->_data['id'] ) );
	    
	    $dbAddAddress->delete( $where );
	    
	    $history = 'DELETA HELA FATIN EMPREZA: %s';
	    
	    $history = sprintf( $history, $this->_data['id'] );
	    $this->_sysAudit( $history, Register_Form_EnterpriseAddress::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
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
     * @return int|bool
     */
    public function deleteStaff()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	    
	    $where = array( $dbAdapter->quoteInto( 'id_staff = ?', $this->_data['id'] ) );
	    
	    $dbStaff->delete( $where );
	    
	    $history = 'DELETA FUNCIONARIU BA EMPREZA: %s';
	    
	    $history = sprintf( $history, $this->_data['id'] );
	    $this->_sysAudit( $history, Register_Form_EnterpriseStaff::ID, Admin_Model_Mapper_SysUserHasForm::HAMOS );
	    
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
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $form = Register_Form_EnterpriseInformation::ID, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::REGISTER,
	    'fk_id_sysform'	    => $form,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
    
     /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listContacts( $id )
    {
	$dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	$dbEnterpriseContact = App_Model_DbTable_Factory::get( 'FefpEnterprise_has_PerContact' );
	
	$select = $dbContact->select()
			    ->from( array( 'c' => $dbContact ) )
			    ->setIntegrityCheck( false )
			    ->join(
				array( 'ec' => $dbEnterpriseContact ),
				'ec.fk_id_id_percontact = c.id_percontact',
				array()
			    )
			    ->where( 'ec.fk_id_fefpenterprise = ?', $id )
			    ->order( array( 'c.contact_name' ) );
	
	return $dbContact->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function fetchContact( $id )
    {
	$dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	return $dbContact->fetchRow( array( 'id_percontact = ?' => $id ) );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function fetchAddress( $id )
    {
	$dbAddAddress = App_Model_DbTable_Factory::get( 'AddAddress' );
	return $dbAddAddress->fetchRow( array( 'id_addaddress = ?' => $id ) );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function fetchStaff( $id )
    {
	$dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	return $dbStaff->fetchRow( array( 'id_staff = ?' => $id ) );
    }
    
    /**
     *
     * @return Zend_Db_Select 
     */
    public function getSelectEnterprise()
    {
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	$dbTypeEnterprise = App_Model_DbTable_Factory::get( 'FEFPTypeEnterprise' );
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$dbEnterpriseContact = App_Model_DbTable_Factory::get( 'FefpEnterprise_has_PerContact' );
	$dbContact = App_Model_DbTable_Factory::get( 'PerContact' );
	$dbIsicTimor = App_Model_DbTable_Factory::get( 'ISICClassTimor' );
	
	$select = $dbEnterprise->select()
				->from( array( 'e' => $dbEnterprise ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'te' => $dbTypeEnterprise ),
				    'te.id_fefptypeenterprise = e.fk_fefptypeenterprite',
				    array( 'type_enterprise' )
				)
				->join(
				    array( 'ce' => $dbDec ),
				    'ce.id_dec = e.fk_id_dec',
				    array( 'name_dec' )
				)
				->join(
				    array( 'is' => $dbIsicTimor ),
				    'is.id_isicclasstimor = e.fk_id_sectorindustry',
				    array( 'name_classtimor' )
				)
				->joinLeft(
				    array( 'ecc' => $dbEnterpriseContact ),
				    'ecc.fk_id_fefpenterprise = e.id_fefpenterprise',
				    array()
				)
				->joinLeft(
				    array( 'c' => $dbContact ),
				    'ecc.fk_id_id_percontact = c.id_percontact',
				    array(
					'contact_name',
					'cell_fone',
					'email'
				    )
				)
				->order( array( 'enterprise_name' ) )
				->group( array( 'id_fefpenterprise' ) );
	
	return $select;
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detail( $id )
    {
	$select = $this->getSelectEnterprise();
	$select->where( 'e.id_fefpenterprise = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @param array $filters
     * @return Zend_Db_Table_Rowset 
     */
    public function listByFilters( $filters = array() )
    {
	$dbEnterprise = App_Model_DbTable_Factory::get( 'FEFPEnterprise' );
	$select = $this->getSelectEnterprise();
	
	if ( !empty( $filters['enterprise_name'] ) )
	    $select->where( 'e.enterprise_name LIKE ?', '%' . $filters['enterprise_name'] . '%' );
	
	if ( !empty( $filters['fk_id_sectorindustry'] ) )
	    $select->where( 'e.fk_id_sectorindustry = ?', $filters['fk_id_sectorindustry'] );
	
	if ( !empty( $filters['fk_fefptypeenterprite'] ) )
	    $select->where( 'e.fk_fefptypeenterprite = ?', $filters['fk_fefptypeenterprite'] );
	
	if ( !empty( $filters['fk_id_dec'] ) )
	    $select->where( 'e.fk_id_dec = ?', $filters['fk_id_dec'] );
	
	if ( !empty( $filters['fk_id_adddistrict'] ) || !empty( $filters['fk_nationality'] ) ) {
	    
	    $dbAddress = App_Model_DbTable_Factory::get( 'AddAddress' );
	    
	    $select->joinLeft(
		array( 'ad' => $dbAddress ),
		'ad.fk_id_fefpenterprise = e.id_fefpenterprise'
	    );
            
            if ( !empty( $filters['fk_id_adddistrict'] ) )
                $select->where( 'ad.fk_id_adddistrict = ?', $filters['fk_id_adddistrict'] );
            
            if ( !empty( $filters['fk_nationality'] ) )
                $select->where( 'ad.fk_id_addcountry = ?', $filters['fk_nationality'] );
	}
	
	return $dbEnterprise->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listAddress( $id )
    {
	$dbAddAddress = App_Model_DbTable_Factory::get( 'AddAddress' );
	$dbAddCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$dbAddDistrict = App_Model_DbTable_Factory:: get( 'AddDistrict' );
	$dbAddSubDistrict = App_Model_DbTable_Factory::get( 'AddSubDistrict' );
	$dbAddSucu = App_Model_DbTable_Factory::get( 'AddSucu' );
	
	$select = $dbAddAddress->select()
				->from( array( 'a' => $dbAddAddress ) )
				->setIntegrityCheck( false )
				->join(
				    array( 'c' => $dbAddCountry ),
				    'c.id_addcountry = a.fk_id_addcountry',
				    array( 'country' )
				)
				->joinLeft(
				    array( 'd' => $dbAddDistrict ),
				    'd.id_adddistrict = a.fk_id_adddistrict',
				    array( 'District' )
				)
				->joinLeft(
				    array( 's' => $dbAddSubDistrict ),
				    's.id_addsubdistrict = a.fk_id_addsubdistrict',
				    array( 'sub_district' )
				)
				->joinLeft(
				    array( 'k' => $dbAddSucu ),
				    'k.id_addsucu = a.fk_id_addsucu',
				    array( 'sucu' )
				)
				->where( 'a.fk_id_fefpenterprise = ?', $id )
				->order( array( 'id_addaddress' ) );
	
	return $dbAddAddress->fetchAll( $select );
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listStaff( $id )
    {
	$dbStaff = App_Model_DbTable_Factory::get( 'Staff' );
	$dbOccupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	
	$select = $dbStaff->select()
			  ->from( array( 's' => $dbStaff ) )
			  ->setIntegrityCheck( false )
			  ->join(
			    array( 'ot' => $dbOccupationTimor ),
			    's.position = ot.id_profocupationtimor',
			    array( 'ocupation_name_timor' )
			  )
			  ->where( 's.id_fefpenterprise = ?', $id )
			  ->order( array( 'staff_name' ) );
	
	return $dbStaff->fetchAll( $select );
    }
}