<?php

class Register_Model_Mapper_ProfMiniGroup extends App_Model_Abstract
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
	    $this->_dbTable = new Model_DbTable_PROFMiniGroup();

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

	    $row = $this->_checkMiniGroup( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Mini-Grupu Okupasaun iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    $mapperSubGroup = new Register_Model_Mapper_ProfSubGroup();
	    $subGroup = $mapperSubGroup->fetchRow( $this->_data['fk_id_profsubgroup'] );
	    $this->_data['acronym'] = $subGroup->acronym . $this->_data['acronym'];
	   
	    if ( empty( $this->_data['id_profminigroup'] ) )
		$history = 'INSERE MINI-GROUP: %s - INSERIDO NOVO MINI-GRUPO';
	    else
		$history = 'ALTERA MINI-GROUP: %s - ALTERADO MINI-GRUPO COM SUCESSO';
	    
	    $id = parent::_simpleSave();
	    
	    // Save the client history
	    $history = sprintf( $history, $this->_data['mini_group'] );
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
    protected function _checkMiniGroup()
    {
	$select = $this->_dbTable->select()
				 ->where( 'acronym = ?', $this->_data['acronym'] )
				 ->where( 'fk_id_profsubgroup = ?', $this->_data['fk_id_profsubgroup'] )
				 ->where( 'fk_id_profgroup = ?', $this->_data['fk_id_profgroup'] );

	if ( !empty( $this->_data['id_profminigroup'] ) )
	    $select->where( 'id_profminigroup <> ?', $this->_data['id_profminigroup'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll( $subGroup = false )
    {
	$dbGroup = App_Model_DbTable_Factory::get( 'PROFGroup' );
	$dbSubGroup = App_Model_DbTable_Factory::get( 'PROFSubGroup' );
	$dbMiniGroup = App_Model_DbTable_Factory::get( 'PROFMiniGroup' );
	
	$select = $dbGroup->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 'mg' => $dbMiniGroup ) )
			  ->join(
				array( 'g' => $dbGroup ),
				'g.id_profgroup = mg.fk_id_profgroup',
				array(
				    'group_acronym' => 'acronym',
				    'group_name'
				)
			   )
			  ->join(
				array( 'sg' => $dbSubGroup ),
				'mg.fk_id_profsubgroup = sg.id_profsubgroup',
				array(
				    'sub_group_acronym' => 'acronym',
				    'sub_group'
				)
			   )
			   ->order( array( 'group_name', 'sub_group', 'mini_group' ) );
	
	if ( !empty( $subGroup ) )
	    $select->where( 'mg.fk_id_profsubgroup = ?', $subGroup );
	
	return $dbMiniGroup->fetchAll( $select );
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
	    'fk_id_sysform'	    => Register_Form_MiniGroup::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}