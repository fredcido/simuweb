<?php

class Register_Model_Mapper_PerScholarity extends App_Model_Abstract
{

    /**
     * 
     * @var Model_DbTable_PerScholarity
     */
    protected $_dbTable;

    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_PerScholarity();

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

	    $this->setValidators(
		array(
		    '_checkScholarityName',
		    '_checkScholarityExternalCode',
		)
	    );
	    
	    if ( !parent::isValid() )
		return false;
	    
	    if ( empty( $this->_data['id_perscholarity'] ) )
		$history = 'INSERE KURSU: %s DADUS PRINCIPAL - INSERE NOVO KURSU';
	    else
		$history = 'ALTERA KURSU: %s DADUS PRINCIPAL - ALTERA KURSU';
	   
	    $id = parent::_simpleSave();
	    
	    $history = sprintf( $history, $this->_data['scholarity'] );
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
     * @return boolean 
     */
    protected function _checkScholarityName()
    {
	$select = $this->_dbTable->select()->where( 'scholarity = ?', $this->_data['scholarity'] );

	if ( !empty( $this->_data['id_perscholarity'] ) )
	    $select->where( 'id_perscholarity <> ?', $this->_data['id_perscholarity'] );

	$row =  $this->_dbTable->fetchRow( $select );
	    
	if ( !empty( $row ) ) {
	    
	    $this->_message->addMessage( 'Naran kursu iha tiha ona.', App_Message::ERROR );
	    $this->addFieldError( 'scholarity' );
	    
	    return false;
	}
	
	return true;
    }
    
    /**
     *
     * @return boolean 
     */
    protected function _checkScholarityExternalCode()
    {
	if ( empty( $this->_data['external_code'] ) )
	    return true;
	
	$select = $this->_dbTable->select()->where( 'external_code = ?', $this->_data['external_code'] );

	if ( !empty( $this->_data['id_perscholarity'] ) )
	    $select->where( 'id_perscholarity <> ?', $this->_data['id_perscholarity'] );

	$row =  $this->_dbTable->fetchRow( $select );
	    
	if ( !empty( $row ) ) {
	    
	    $this->_message->addMessage( 'Kodigu esterno iha tiha ona.', App_Message::ERROR );
	    $this->addFieldError( 'external_code' );
	    
	    return false;
	}
	
	return true;
    }
    
    /**
     *
     * @return Zend_Db_Table_Rowset
     */
    public function listAll( $filters = array() )
    {
	$dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbTypeScholarity = App_Model_DbTable_Factory::get( 'PerTypeScholarity' );
	$dbScholarityArea = App_Model_DbTable_Factory::get( 'ScholarityArea' );
	$dbPerLevelScholarity = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
	$dbInstitutionScholarity = App_Model_DbTable_Factory::get( 'FefpEduInstitution_has_PerScholarity' );
	
	$select = $dbScholarity->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 's' => $dbScholarity ) )
			  ->join(
				array( 'ts' => $dbTypeScholarity ),
				'ts.id_pertypescholarity = s.fk_id_pertypescholarity'
			   )
			  ->join(
				array( 'sa' => $dbScholarityArea ),
				'sa.id_scholarity_area = s.fk_id_scholarity_area'
			   )
			  ->joinLeft(
				array( 'sl' => $dbPerLevelScholarity ),
				'sl.id_perlevelscholarity = s.fk_id_perlevelscholarity'
			   )
			   ->order( array( 'type_scholarity', 'scholarity_area', 'scholarity' ) );
	
	if ( !empty( $filters['type'] ) )
	    $select->where( 's.fk_id_pertypescholarity = ?', $filters['type'] );
	
	if ( !empty( $filters['max_level'] ) )
	    $select->where( 's.max_level = ?', $filters['max_level'] );
	
	if ( !empty( $filters['category'] ) )
	    $select->where( 's.category = ?', $filters['category'] );
	
	if ( !empty( $filters['institution'] ) ) {
	 
	    $select->join(
			array( 'is' => $dbInstitutionScholarity ),
			'is.fk_id_scholarity = s.id_perscholarity'
		    )
		    ->where( 'is.fk_id_fefpeduinstitution = ?', $filters['institution'] );
	}
	
	return $dbScholarity->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function detail( $id )
    {
	$dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	$dbTypeScholarity = App_Model_DbTable_Factory::get( 'PerTypeScholarity' );
	$dbScholarityArea = App_Model_DbTable_Factory::get( 'ScholarityArea' );
	$dbPerLevelScholarity = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
	
	$select = $dbScholarity->select()
			  ->setIntegrityCheck( false )
			  ->from( array( 's' => $dbScholarity ) )
			  ->join(
				array( 'ts' => $dbTypeScholarity ),
				'ts.id_pertypescholarity = s.fk_id_pertypescholarity'
			   )
			  ->join(
				array( 'sa' => $dbScholarityArea ),
				'sa.id_scholarity_area = s.fk_id_scholarity_area'
			   )
			  ->joinLeft(
				array( 'sl' => $dbPerLevelScholarity ),
				'sl.id_perlevelscholarity = s.fk_id_perlevelscholarity'
			   )
			   ->where( 's.id_perscholarity = ?', $id );
		
	return $dbScholarity->fetchRow( $select );
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
	    'fk_id_sysform'	    => Register_Form_Scholarity::ID,
	    'fk_id_sysoperation'    => $operation,
	    'description'	    => $description
	);
	
	$mapperSysAudit = new Model_Mapper_SysAudit();
	$mapperSysAudit->setData( $data )->save();
    }
    
    /**
     *
     * @param int $type
     * @return array
     */
    public function getOptionsCategory( $type )
    {
	$optCategory = array( '' => '' );
	
	if ( Register_Model_Mapper_PerTypeScholarity::FORMAL == $type ) {
	    
	    $optCategory['S'] = 'Eskola';
	    $optCategory['U'] = 'Superior';
	    
	} else {
	    
	    $optCategory['N'] = 'Formasaun Teknika Profisional';
	    $optCategory['C'] = 'Formasaun Comunitaria';
	    $optCategory['V'] = 'Formasaun Profisional';
	}
	
	return $optCategory;
    }
    
    /**
     *
     * @param array $filters
     * @return array
     */
    public function getOptionsScholarity( $filters = array() )
    {
	$rows = $this->listAll( $filters );
	
	$optScholarity = array( '' => '' );
	foreach ( $rows as $scholarity )
	    $optScholarity[$scholarity['id_perscholarity']] = ( empty( $scholarity['external_code'] ) ? '' : $scholarity['external_code'] . ' - ' ) . $scholarity['scholarity'];
	
	return $optScholarity;
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Rowset
     */
    public function listCompetencies( $id )
    {
	$dbCompetencies = App_Model_DbTable_Factory::get( 'Competency' );
	$dbCompetenciesScholarity = App_Model_DbTable_Factory::get( 'CompetencyHasPerScholarity' );
	
	$select = $dbCompetencies->select()
				 ->from( array( 'c' => $dbCompetencies ) )
				 ->setIntegrityCheck( false )
				 ->join(
				    array( 'sc' => $dbCompetenciesScholarity ),
				    'sc.fk_id_competency = c.id_competency',
				    array()
				 )
				 ->where( 'sc.fk_id_perscholarity = ?', $id )
				 ->order( array( 'external_code' ) );
	
	return $dbCompetencies->fetchAll( $select );
    }
}