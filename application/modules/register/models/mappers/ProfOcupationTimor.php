<?php

class Register_Model_Mapper_ProfOcupationTimor extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_PROFOcupationTimor
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_PROFOcupationTimor();

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

//	    $row = $this->_checkOcupationTimor( $this->_data );
//	    
//	    if ( !empty( $row ) ) {
//		$this->_message->addMessage( 'Okupasaun Timor-Leste iha tiha ona.', App_Message::ERROR );
//		return false;
//	    }
	    
	    if ( empty( $this->_data['id_profocupationtimor'] ) )
		$history = 'INSERE OCUPASAUN TIMOR: %s DADUS PRINCIPAL - INSERE NOVO OCUPASAUN TIMOR';
	    else
		$history = 'ALTERA Ocupasaun Timor: %s DADUS PRINCIPAL - ALTERA Ocupasaun Timor';
	    
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['ocupation_name_timor'] );
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
    protected function _checkOcupationTimor()
    {
	$select = $this->_dbTable->select()
				 ->where( 'acronym = ?', $this->_data['acronym'] )
				 ->where( 'fk_id_profocupation = ?', $this->_data['fk_id_profocupation'] );

	if ( !empty( $this->_data['id_profocupationtimor'] ) )
	    $select->where( 'id_profocupationtimor <> ?', $this->_data['id_profocupationtimor'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll()
    {
	$dbOcupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$dbOcupation = App_Model_DbTable_Factory::get( 'PROFOcupation' );
	
	$select = $dbOcupationTimor->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 'ot' => $dbOcupationTimor ) )
			  ->join(
				array( 'oi' => $dbOcupation ),
				'ot.fk_id_profocupation = oi.id_profocupation',
				array(
				    'ocupation_acronym' => 'acronym',
				    'ocupation_name'
				)
			   )
			   ->order( array( 'ocupation_name', 'ocupation_name_timor' ) );
	
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
	    'fk_id_sysform'	    => Register_Form_OccupationTimor::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}