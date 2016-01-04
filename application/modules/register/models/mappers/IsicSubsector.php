<?php

class Register_Model_Mapper_IsicSubsector extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_ISICSubsector
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_ISICSubsector();

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

	    $row = $this->_checkSubsector( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Subsektor iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	   
	    if ( empty( $this->_data['id_isic_subsector'] ) )
		$history = 'REJISTRU SUBSEKTOR: %s';
	    else
		$history = 'ALTERA SUBSEKTOR: %s';
	    
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
    protected function _checkSubsector()
    {
	$select = $this->_dbTable->select()
				 ->where( 'name_subsector = ?', $this->_data['name_subsector'] )
				 ->where( 'fk_id_isicclasstimor = ?', $this->_data['fk_id_isicclasstimor'] );

	if ( !empty( $this->_data['id_isic_subsector'] ) )
	    $select->where( 'id_isic_subsector <> ?', $this->_data['id_isic_subsector'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll( $sector = false )
    {
	$dbSector = App_Model_DbTable_Factory::get( 'ISICClassTimor' );
	$dbSubsector = App_Model_DbTable_Factory::get( 'ISICSubsector' );
	
	$select = $dbSector->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 'ss' => $dbSubsector ) )
			  ->join(
				array( 's' => $dbSector ),
				's.id_isicclasstimor = ss.fk_id_isicclasstimor',
				array( 'name_classtimor' )
			   )
			   ->order( array( 'name_classtimor', 'name_subsector' ) );
	
	if ( !empty( $sector ) )
	    $select->where( 'ss.fk_id_isicclasstimor = ?', $sector );
	
	return $dbSubsector->fetchAll( $select );
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
	    'fk_id_sysform'	    => Register_Form_IsicSubsector::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}