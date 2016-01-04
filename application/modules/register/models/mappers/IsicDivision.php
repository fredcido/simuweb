<?php

class Register_Model_Mapper_IsicDivision extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_ISICDivision
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_ISICDivision();

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

	    $row = $this->_checkDivision( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Divizaun iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    $mapperSection = new Register_Model_Mapper_IsicSection();
	    $section = $mapperSection->fetchRow( $this->_data['fk_id_isicsection'] );
	    $this->_data['acronym'] = $section->acronym . $this->_data['acronym'];
	    
	    if ( empty( $this->_data['id_isicdivision'] ) )
		$history = 'REJISTRU DIVISAUN: %s';
	    else
		$history = 'ALTERA DIVISAUN: %s';
	    
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['name_disivion'] );
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
    protected function _checkDivision()
    {
	$select = $this->_dbTable->select()
				 ->where( 'acronym = ?', $this->_data['acronym'] )
				 ->where( 'fk_id_isicsection = ?', $this->_data['fk_id_isicsection'] );

	if ( !empty( $this->_data['id_isicdivision'] ) )
	    $select->where( 'id_isicdivision <> ?', $this->_data['id_isicdivision'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll( $section = false )
    {
	$dbSection = App_Model_DbTable_Factory::get( 'ISICSection' );
	$dbDivision = App_Model_DbTable_Factory::get( 'ISICDivision' );
	
	$select = $dbSection->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 'd' => $dbDivision ) )
			  ->join(
				array( 's' => $dbSection ),
				's.id_isicsection = d.fk_id_isicsection',
				array(
				    'section_acronym' => 'acronym',
				    'name_section'
				)
			   )
			   ->order( array( 'name_section', 'name_disivion' ) );
	
	if ( !empty( $section ) )
	    $select->where( 'd.fk_id_isicsection = ?', $section );
	
	return $dbDivision->fetchAll( $select );
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
	    'fk_id_sysform'	    => Register_Form_IsicDivision::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}