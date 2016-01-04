<?php

class Register_Model_Mapper_ProfOcupation extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_PROFOcupation
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_PROFOcupation();

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

	    $row = $this->_checkOcupation( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Okupasaun Internasional iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    $mapperMiniGroup = new Register_Model_Mapper_ProfMiniGroup();
	    $miniGroup = $mapperMiniGroup->fetchRow( $this->_data['fk_id_profminigroup'] );
	    $this->_data['acronym'] = $miniGroup->acronym . $this->_data['acronym'];
	   
	    if ( empty( $this->_data['id_profocupation'] ) )
		$history = 'INSERE OCUPASAUN INTERNASIONAU: %s DADUS PRINCIPAL - INSERE NOVO OCUPASAUN INTERNASIONAU';
	    else
		$history = 'ALTERA OCUPASAUN INTERNASIONAU: %s DADUS PRINCIPAL - ALTERA OCUPASAUN INTERNASIONAU';
	    
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['ocupation_name'] );
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
    protected function _checkOcupation()
    {
	$select = $this->_dbTable->select()
				 ->where( 'acronym = ?', $this->_data['acronym'] )
				 ->where( 'fk_id_profsubgroup = ?', $this->_data['fk_id_profsubgroup'] )
				 ->where( 'fk_id_profgroup = ?', $this->_data['fk_id_profgroup'] )
				 ->where( 'fk_id_profminigroup = ?', $this->_data['fk_id_profminigroup'] );

	if ( !empty( $this->_data['id_profocupation'] ) )
	    $select->where( 'id_profocupation <> ?', $this->_data['id_profocupation'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll()
    {
	$dbGroup = App_Model_DbTable_Factory::get( 'PROFGroup' );
	$dbSubGroup = App_Model_DbTable_Factory::get( 'PROFSubGroup' );
	$dbMiniGroup = App_Model_DbTable_Factory::get( 'PROFMiniGroup' );
	$dbOcupation = App_Model_DbTable_Factory::get( 'PROFOcupation' );
	
	$select = $dbGroup->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 'oi' => $dbOcupation ) )
			  ->join(
				array( 'g' => $dbGroup ),
				'g.id_profgroup = oi.fk_id_profgroup',
				array(
				    'group_acronym' => 'acronym',
				    'group_name'
				)
			   )
			  ->join(
				array( 'sg' => $dbSubGroup ),
				'oi.fk_id_profsubgroup = sg.id_profsubgroup',
				array(
				    'sub_group_acronym' => 'acronym',
				    'sub_group'
				)
			   )
			  ->join(
				array( 'mg' => $dbMiniGroup ),
				'mg.id_profminigroup = oi.fk_id_profminigroup',
				array(
				    'mini_group_acronym' => 'acronym',
				    'mini_group'
				)
			   )
			   ->order( array( 'group_name', 'sub_group', 'mini_group', 'ocupation_name' ) );
	
	return $dbOcupation->fetchAll( $select );
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
	    'fk_id_sysform'	    => Register_Form_InternationalOccupation::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}