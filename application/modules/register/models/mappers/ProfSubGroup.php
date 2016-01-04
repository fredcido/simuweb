<?php

class Register_Model_Mapper_ProfSubGroup extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_PROFSubGroup
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_PROFSubGroup();

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

	    $row = $this->_checkSubGroup( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Sub-Grupu Okupasaun iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    $mapperGroup = new Register_Model_Mapper_ProfGroup();
	    $group = $mapperGroup->fetchRow( $this->_data['fk_id_profgroup'] );
	    $this->_data['acronym'] = $group->acronym . $this->_data['acronym'];
	    
	    if ( empty( $this->_data['id_profsubgroup'] ) )
		$history = 'INSERE SUB-GROUP: %s - INSERIDO NOVO SUB-GRUPO';
	    else
		$history = 'ALTERA SUB-GROUP: %s - ALTERADO SUB-GROUP';
	    
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['sub_group'] );
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
    protected function _checkSubGroup()
    {
	$select = $this->_dbTable->select()
				 ->where( 'acronym = ?', $this->_data['acronym'] )
				 ->where( 'fk_id_profgroup = ?', $this->_data['fk_id_profgroup'] );

	if ( !empty( $this->_data['id_profsubgroup'] ) )
	    $select->where( 'id_profsubgroup <> ?', $this->_data['id_profsubgroup'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll( $group = false )
    {
	$dbGroup = App_Model_DbTable_Factory::get( 'PROFGroup' );
	$dbSubGroup = App_Model_DbTable_Factory::get( 'PROFSubGroup' );
	
	$select = $dbGroup->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 'sg' => $dbSubGroup ) )
			  ->join(
				array( 'g' => $dbGroup ),
				'g.id_profgroup = sg.fk_id_profgroup',
				array(
				    'group_acronym' => 'acronym',
				    'group_name'
				)
			   )
			   ->order( array( 'group_name', 'sub_group' ) );
	
	if ( !empty( $group ) )
	    $select->where( 'sg.fk_id_profgroup = ?', $group );
	
	return $dbSubGroup->fetchAll( $select );
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
	    'fk_id_sysform'	    => Register_Form_SubGroup::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}