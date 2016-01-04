<?php

class Fefop_Model_Mapper_UnitCost extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_UnitCost
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_UnitCost();

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

	    $this->disableLastUnitCost( $this->_data );
	    
	    $this->_data['fk_id_sysuser'] = Zend_Auth::getInstance()->getIdentity()->id_sysuser;
	    $this->_data['status'] = 1;
	    $this->_data['cost'] = App_General_String::toFloat( $this->_data['cost'] );
	    
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( 'REJISTU KUSTU UNITARIU: %s', $id );
	    $this->_sysAudit( $history );
	    
	    $dbAdapter->commit();
	    
	    return $id;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    Zend_Debug::dump( $e );
	    exit;
	    $this->_message->addMessage( $this->_config->messages->error, App_Message::ERROR );
	    return false;
	}
    }
    
    /**
     * 
     * @param array $data
     * @return boolean
     */
    public function disableLastUnitCost( $data )
    {
	$where = array(
	    'status = ?' => 1,
	    'fk_id_perscholarity = ?' => $data['fk_id_perscholarity']
	);
	
	$data = array(
	    'date_inative'	    => Zend_Date::now()->toString( 'yyyy-MM-dd HH:mm:ss' ),
	    'status'		    => 0
	);
	
	$this->_dbTable->update( $data, $where );
    }
    
    /**
     * 
     * @param int $scholarity
     * @param int $district
     * @return Zend_Db_Table_Row
     */
    public function getUnitCost( $scholarity )
    {
	$dbUnitCost = App_Model_DbTable_Factory::get( 'UnitCost' );
	
	$where = array(
	    'fk_id_perscholarity = ?'	=> $scholarity,
	    'status = ?'		=> 1
	);
	
	return $dbUnitCost->fetchRow( $where );
    }
    
    /**
     * 
     * @return Zend_Db_Table_Rowset
     */
    public function listAll()
    {
	$dbUniCost = App_Model_DbTable_Factory::get( 'UnitCost' );
	$dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbUser = App_Model_DbTable_Factory::get( 'SysUser' );
	
	$select = $dbUniCost->select()
				    ->from( array( 'uc' => $dbUniCost ) )
				    ->setIntegrityCheck( false )
				    ->join(
					array( 's' => $dbScholarity ),
					's.id_perscholarity = uc.fk_id_perscholarity',
					array( 'scholarity', 'external_code', 'category' )
				    )
				    ->join(
					array( 'u' => $dbUser ),
					'u.id_sysuser = uc.fk_id_sysuser',
					array( 'user' => 'name' )
				    )
				    ->order( array( 'id_unit_cost DESC' ) );
	
	return $dbUniCost->fetchAll( $select );
    }
    
    /**
     * 
     * @param string $description
     * @param int $operation
     */
    protected function _sysAudit( $description, $operation = Admin_Model_Mapper_SysUserHasForm::SAVE )
    {
	$data = array(
	    'fk_id_sysmodule'	    => Admin_Model_Mapper_SysModule::FEFOP,
	    'fk_id_sysform'	    => Fefop_Form_UnitCost::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
}