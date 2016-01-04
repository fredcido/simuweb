<?php

class Register_Model_Mapper_IsicClass extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_ISICClass
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_ISICClass();

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

	    $row = $this->_checkClass( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Klase Setor Industria iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    $mapperGroup = new Register_Model_Mapper_IsicGroup();
	    $miniGroup = $mapperGroup->fetchRow( $this->_data['fk_id_isicgroup'] );
	    $this->_data['acronym'] = $miniGroup->acronym . $this->_data['acronym'];
	   
	    if ( empty( $this->_data['id_isicclass'] ) )
		$history = 'INSERE CLASSE: %s';
	    else
		$history = 'ALTERA ALTERA CLASSE: %s';
	    
	    $id = parent::_simpleSave();
	    
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
    protected function _checkClass()
    {
	$select = $this->_dbTable->select()
				 ->where( 'acronym = ?', $this->_data['acronym'] )
				 ->where( 'fk_id_isicgroup = ?', $this->_data['fk_id_isicgroup'] )
				 ->where( 'fk_id_isicdivision = ?', $this->_data['fk_id_isicdivision'] )
				 ->where( 'fk_id_isicsection = ?', $this->_data['fk_id_isicsection'] );

	if ( !empty( $this->_data['id_isicclass'] ) )
	    $select->where( 'id_isicclass <> ?', $this->_data['id_isicclass'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll( $group = false )
    {
	$dbClass = App_Model_DbTable_Factory::get( 'ISICClass' );
	$dbGroup = App_Model_DbTable_Factory::get( 'ISICGroup' );
	$dbDivision = App_Model_DbTable_Factory::get( 'ISICDivision' );
	$dbSection = App_Model_DbTable_Factory::get( 'ISICSection' );
	
	$select = $dbClass->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 'c' => $dbClass ) )
			  ->join(
				array( 'g' => $dbGroup ),
				'g.id_isicgroup = c.fk_id_isicgroup',
				array(
				    'group_acronym' => 'acronym',
				    'name_group'
				)
			   )
			  ->join(
				array( 'd' => $dbDivision ),
				'd.id_isicdivision = c.fk_id_isicdivision',
				array(
				    'division_acronym' => 'acronym',
				    'name_disivion'
				)
			   )
			  ->join(
				array( 's' => $dbSection ),
				's.id_isicsection = c.fk_id_isicsection',
				array(
				    'section_acronym' => 'acronym',
				    'name_section'
				)
			   )
			   ->order( array( 'name_section', 'name_disivion', 'name_group', 'name_class' ) );
	
	if ( !empty( $group ) )
	    $select->where( 'c.fk_id_isicgroup = ?', $group );
	
	return $dbClass->fetchAll( $select );
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
	    'fk_id_sysform'	    => Register_Form_IsicClass::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}