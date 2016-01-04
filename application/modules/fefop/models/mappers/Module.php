<?php

class Fefop_Model_Mapper_Module extends App_Model_Abstract
{   
    const CEG = 1;
    
    const CEC = 2;
    
    const CED = 3;
    
    const FP = 4;
    
    const RI = 5;
    
    const DRH = 6;
    
    const FE = 7;
    
    const EDC = 8;
    
    const ETC = 9;
    
    
    /**
     * 
     * @var Model_DbTable_FEFOPModules
     */
    protected $_dbTable;
    
    /**
     * 
     */
    protected function _getDbTable()
    {
	if ( is_null( $this->_dbTable ) )
	    $this->_dbTable = new Model_DbTable_FEFOPModules();

	return $this->_dbTable;
    }
    
    /**
     * 
     * @param int $program
     * @return Zend_Db_Table_Rowset
     */
    public function listModules( $program )
    {
	$dbProgram = App_Model_DbTable_Factory::get( 'FEFOPPrograms' );
	
	$select = $this->_dbTable->select()
				 ->from(
				    array( 'm' => $this->_dbTable ),
				    array(
					'id_fefop_modules',
					'module'	=> 'description',
					'num_module'	=> 'acronym'
				    )
				)
				->setIntegrityCheck( false )
				->join(
				    array( 'p' => $dbProgram ),
				    'p.id_fefop_programs = m.fk_id_fefop_programs',
				    array(
					'id_fefop_programs',
					'program'	=> 'description',
					'num_program'	=> 'acronym'
				    )
				)
				->where( 'p.id_fefop_programs = ?', $program );
	
	return $this->_dbTable->fetchAll( $select );
    }
    
    /**
     * 
     * @param int $id
     * @return Zend_Db_Table_Row
     */
    public function fetchModule( $id )
    {
	$dbProgram = App_Model_DbTable_Factory::get( 'FEFOPPrograms' );
	
	$select = $this->_dbTable->select()
				 ->from(
				    array( 'm' => $this->_dbTable ),
				    array(
					'id_fefop_modules',
					'module'	=> 'description',
					'num_module'	=> 'acronym'
				    )
				)
				->setIntegrityCheck( false )
				->join(
				    array( 'p' => $dbProgram ),
				    'p.id_fefop_programs = m.fk_id_fefop_programs',
				    array(
					'id_fefop_programs',
					'program'	=> 'description',
					'num_program'	=> 'acronym'
				    )
				)
				->where( 'm.id_fefop_modules = ?', $id );
	
	return $this->_dbTable->fetchRow( $select );
    }
    
    /**
     * 
     * @return array
     */
    public function listModulesGrouped()
    {
	$dbProgram = App_Model_DbTable_Factory::get( 'FEFOPPrograms' );
	
	$select = $this->_dbTable->select()
				 ->from(
				    array( 'm' => $this->_dbTable ),
				    array(
					'id_fefop_modules',
					'module'	=> 'description',
					'num_module'	=> 'acronym'
				    )
				)
				->setIntegrityCheck( false )
				->join(
				    array( 'p' => $dbProgram ),
				    'p.id_fefop_programs = m.fk_id_fefop_programs',
				    array(
					'id_fefop_programs',
					'program'	=> 'description',
					'num_program'	=> 'acronym'
				    )
				);
	
	$rows = $this->_dbTable->fetchAll( $select );
	
	$data = array();
	foreach ( $rows as $row ) {
	    
	    if ( !array_key_exists( $row->num_program, $data ) ) {
		
		$data[$row->num_program] = array(
		    'program' => $row,
		    'modules' => array()
		);
	    }
	    
	    $data[$row->num_program]['modules'][] = $row;
	}
	
	return $data;
    }
}