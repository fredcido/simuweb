<?php

class Register_Model_Mapper_IsicTimor extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_ISICClassTimor
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_ISICClassTimor();

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

	    $row = $this->_checkClassTimor( $this->_data );
	    
	    if ( !empty( $row ) ) {
		$this->_message->addMessage( 'Klase Timor-Leste iha tiha ona.', App_Message::ERROR );
		return false;
	    }
	    
	    if ( empty( $this->_data['id_isicclasstimor'] ) )
		$history = 'REJISTRU CLASSE TIMOR: %s-%s';
	    else
		$history = 'ALTERA CLASSE TIMOR: %s-%s';
	    
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['acronym'], $this->_data['name_classtimor'] );
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
    protected function _checkClassTimor()
    {
	$select = $this->_dbTable->select()
				 ->where( 'acronym = ?', $this->_data['acronym'] )
				 ->where( 'fk_id_isicclass = ?', $this->_data['fk_id_isicclass'] );

	if ( !empty( $this->_data['id_isicclasstimor'] ) )
	    $select->where( 'id_isicclasstimor <> ?', $this->_data['id_isicclasstimor'] );

	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll()
    {
	$dbIsicTimor = App_Model_DbTable_Factory::get( 'ISICClassTimor' );
	$dbClass = App_Model_DbTable_Factory::get( 'ISICClass' );
	$dbGroup = App_Model_DbTable_Factory::get( 'ISICGroup' );
	$dbDivision = App_Model_DbTable_Factory::get( 'ISICDivision' );
	$dbSection = App_Model_DbTable_Factory::get( 'ISICSection' );
	
	$select = $dbIsicTimor->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 'ct' => $dbIsicTimor ) )
			  ->join(
				array( 'c' => $dbClass ),
				'c.id_isicclass = ct.fk_id_isicclass',
				array(
				    'class_acronym' => 'acronym',
				    'name_class'
				)
			   )
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
				'd.id_isicdivision = g.fk_id_isicdivision',
				array(
				    'division_acronym' => 'acronym',
				    'name_disivion'
				)
			   )
			  ->join(
				array( 's' => $dbSection ),
				's.id_isicsection = d.fk_id_isicsection',
				array(
				    'section_acronym' => 'acronym',
				    'name_section'
				)
			   )
			   ->order( array( 'name_section', 'name_disivion', 'name_class', 'name_classtimor' ) );
	
	return $dbIsicTimor->fetchAll( $select );
    }
    
    /**
     *
     * @param int $division
     * @return array
     */
    public function listClassByDisivion( $division = false )
    {
	$dbIsicTimor = App_Model_DbTable_Factory::get( 'ISICClassTimor' );
	$dbClass = App_Model_DbTable_Factory::get( 'ISICClass' );
	$dbGroup = App_Model_DbTable_Factory::get( 'ISICGroup' );
	$dbDivision = App_Model_DbTable_Factory::get( 'ISICDivision' );
	
	$select = $dbIsicTimor->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 'ct' => $dbIsicTimor ) )
			  ->join(
				array( 'c' => $dbClass ),
				'c.id_isicclass = ct.fk_id_isicclass',
				array()
			   )
			  ->join(
				array( 'g' => $dbGroup ),
				'g.id_isicgroup = c.fk_id_isicgroup',
				array()
			   )
			  ->join(
				array( 'd' => $dbDivision ),
				'd.id_isicdivision = g.fk_id_isicdivision',
				array(
				    'id_isicdivision',
				    'division_acronym' => 'acronym',
				    'name_disivion'
				)
			   )
			   ->order( array( 'name_disivion', 'name_classtimor' ) );
	
	if ( !empty( $division ) )
	    $select->where( 'd.id_isicdivision = ?', $division );
	
	$rows = $dbIsicTimor->fetchAll( $select );
	
	$data = array();
	foreach ( $rows as $row ) {
	    
	    if ( !array_key_exists( $row->id_isicdivision, $data ) ) {
		
		$data[$row->id_isicdivision] = array(
		    'division' => $row,
		    'classes' => array()
		);
	    }
	    
	    $data[$row->id_isicdivision]['classes'][] = $row;
	}
	
	return $data;
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
	    'fk_id_sysform'	    => Register_Form_IsicTimor::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
    
    /**
     *
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function fetchRow( $id )
    {
	$dbIsicTimor = App_Model_DbTable_Factory::get( 'ISICClassTimor' );
	$dbIsicClass = App_Model_DbTable_Factory::get( 'ISICClass' );
	$dbIsicGroup = App_Model_DbTable_Factory::get( 'ISICGroup' );
	
	$select = $dbIsicTimor->select()
			      ->from( 
				array( 'ct' => $dbIsicTimor ),
				array(
				    'id_isicclasstimor',
				    'fk_id_isicclass',
				    'acronym',
				    'name_classtimor',
				    'description'
				)
			      )
			      ->setIntegrityCheck( false )
			      ->join(
				array( 'c' => $dbIsicClass ),
				'ct.fk_id_isicclass = c.id_isicclass',
				array(  'fk_id_isicgroup'  )
			      )
			      ->join(
				array( 'g' => $dbIsicGroup ),
				'c.fk_id_isicgroup = g.id_isicgroup',
				array( 'fk_id_isicdivision' )
			      )
			      ->where( 'ct.id_isicclasstimor = ?', $id );
	
	return $dbIsicTimor->fetchRow( $select );
    }
}