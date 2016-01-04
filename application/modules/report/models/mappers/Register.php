<?php

class Report_Model_Mapper_Register extends App_Model_Mapper_Abstract
{
 
    /**
     *
     * @return \ArrayObject 
     */
    public function courseReport()
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
			   ->order( array( 'type_scholarity', 'scholarity_area', 'scholarity' ) );
	
	$filters = $this->_data;
	
	if ( !empty( $filters['fk_id_pertypescholarity'] ) )
	    $select->where( 's.fk_id_pertypescholarity = ?', $filters['fk_id_pertypescholarity'] );
	
	if ( !empty( $filters['fk_id_scholarity_area'] ) )
	    $select->where( 's.fk_id_scholarity_area = ?', $filters['fk_id_scholarity_area'] );
	
	if ( !empty( $filters['fk_id_perlevelscholarity'] ) )
	    $select->where( 's.fk_id_perlevelscholarity = ?', $filters['fk_id_perlevelscholarity'] );
	
	if ( !empty( $filters['category_school'] ) )
	    $select->where( 's.category = ?', $filters['category_school'] );
	
	$rows = $dbScholarity->fetchAll( $select );
	
	$data = array(
	    'rows' => $rows
	);
	
	return $data;
    }
    
    
    /**
     *
     * @return array 
     */
    public function institutionReport()
    {
	$mapperInstitution = new Register_Model_Mapper_EducationInstitute();
	$rows = $mapperInstitution->listByFilters( $this->_data );
	
	$data = array(
	    'rows' => $rows
	);
	
	return $data;
    }
    
    /**
     *
     * @return array 
     */
    public function enterpriseReport()
    {
	$filters = $this->_data;
	$filters['fk_nationality'] = $this->_data['fk_id_addcountry'];
	
	$mapperEnterprise = new Register_Model_Mapper_Enterprise();
	$rows = $mapperEnterprise->listByFilters( $filters );
	
	$data = array(
	    'rows' => $rows
	);
	
	return $data;
    }
}