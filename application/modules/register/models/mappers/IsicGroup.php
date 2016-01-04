<?php

class Register_Model_Mapper_IsicGroup extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_PROFMiniGroup
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_ISICGroup();

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

	    $row = $this->_checkIsicGroup( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Grupu Setor Industria iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    $mapperDivision = new Register_Model_Mapper_IsicDivision();
	    $division = $mapperDivision->fetchRow( $this->_data['fk_id_isicdivision'] );
	    $this->_data['acronym'] = $division->acronym . $this->_data['acronym'];
	   
	    if ( empty( $this->_data['id_isicgroup'] ) )
		$history = 'REJISTRU GRUPU: %s';
	    else
		$history = 'ALTERA GRUPU: %s';
	    
	    $id = parent::_simpleSave();
	    
	    // Save the client history
	    $history = sprintf( $history, $this->_data['acronym'] );
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
    protected function _checkIsicGroup()
    {
	$select = $this->_dbTable->select()
				 ->where( 'acronym = ?', $this->_data['acronym'] )
				 ->where( 'fk_id_isicdivision = ?', $this->_data['fk_id_isicdivision'] )
				 ->where( 'fk_id_isicsection = ?', $this->_data['fk_id_isicsection'] );

	if ( !empty( $this->_data['id_isicgroup'] ) )
	    $select->where( 'id_isicgroup <> ?', $this->_data['id_isicgroup'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll( $division = false )
    {
	$dbGroup = App_Model_DbTable_Factory::get( 'ISICGroup' );
	$dbDivision = App_Model_DbTable_Factory::get( 'ISICDivision' );
	$dbSection = App_Model_DbTable_Factory::get( 'ISICSection' );
	
	$select = $dbGroup->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 'g' => $dbGroup ) )
			  ->join(
				array( 'd' => $dbDivision ),
				'd.id_isicdivision = g.fk_id_isicdivision',
				array(
				    'division_acronym' => 'acronym',
				    'name_disivion'
				)
			   )
			  ->join(
				array( 's' => $dbSection ),
				's.id_isicsection = g.fk_id_isicsection',
				array(
				    'section_acronym' => 'acronym',
				    'name_section'
				)
			   )
			   ->order( array( 'name_section', 'name_disivion', 'name_group' ) );
	
	if ( !empty( $division ) )
	    $select->where( 'g.fk_id_isicdivision = ?', $division );
	
	return $dbSection->fetchAll( $select );
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
	    'fk_id_sysform'	    => Register_Form_IsicGroup::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}