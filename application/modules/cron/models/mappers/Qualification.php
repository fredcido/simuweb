<?php

class Cron_Model_Mapper_Qualification extends App_Model_Abstract
{    
    /**
     *
     * @return array 
     */
    public function importQualification()
    {
	$return = array(
	    'status'	=>  0,
	    'error'	=>  ''
	);
	
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	   
	    $data = unserialize( $this->_data['content'] ) ;
	    $qualifications = $data['qualifications'];
	    
	    $dbScholarity = App_Model_DbTable_Factory::get( 'PerScholarity' );
	    $dbLevelScholarity = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
	    $dbScholarityArea = App_Model_DbTable_Factory::get( 'ScholarityArea' );
	    $dbCompetency = App_Model_DbTable_Factory::get( 'Competency' );
	    $dbCompetencyScholarity = App_Model_DbTable_Factory::get( 'Competency_has_PerScholarity' );
	    
	    foreach ( $qualifications as $qualification ) {
		
		$where = array( 'remote_id = ?' => $qualification['id'] );
		$row = $dbScholarity->fetchRow( $where );
		
		// Check if there is the qualification already
		if ( empty( $row ) )
		    $row = $dbScholarity->createRow();
		
		$whereArea = array( 'acronym = ?' => $qualification['area'] );
		$rowArea = $dbScholarityArea->fetchRow( $whereArea );
		
		// Check if there is the scholarity area already
		if ( empty( $rowArea ) )
		    $rowArea = $dbScholarityArea->createRow();
		
		$rowArea->scholarity_area = $qualification['area_name'];
		$rowArea->acronym = $qualification['area'];
		$idArea = $rowArea->save();
		
		// Search ID Level Scholarity
		$whereLevel = array( 'level_scholarity = ?' => trim( $qualification['nivel'] ) );
		$level = $dbLevelScholarity->fetchRow( $whereLevel );
		
		// Data Scholarity
		$row->fk_id_pertypescholarity = Register_Model_Mapper_PerTypeScholarity::NON_FORMAL;
		$row->fk_id_perlevelscholarity = $level->id_perlevelscholarity;
		$row->fk_id_scholarity_area = $idArea;
		$row->scholarity = trim( utf8_decode( $qualification['nome'] ) );
		$row->category = 'N';
		$row->external_code = trim( $qualification['codigo'] );
		$row->remote_id = trim( $qualification['id'] );
		
		$idQualification = $row->save();
		
		if ( !empty( $qualification['competencies'] ) ) {
		    
		    // Save the qualification's competencies
		    foreach ( $qualification['competencies'] as $competency ) {

			$whereCompetency = array( 'external_code = ?' => trim( $competency['codigo'] ) );
			$rowCompetency = $dbCompetency->fetchRow( $whereCompetency );

			// Check if the competency is already registered
			if ( empty( $rowCompetency ) )
			    $rowCompetency = $dbCompetency->createRow();

			$rowCompetency->name = trim( utf8_decode( $competency['nome'] ) );
			$rowCompetency->external_code = trim( $competency['codigo'] );
			$rowCompetency->id_external = trim( $competency['id'] );
			$idCompetency = $rowCompetency->save();

			$whereCompetencyScholarity = array(
			    'fk_id_competency = ?'	=> $idCompetency,
			    'fk_id_perscholarity = ?'   => $idQualification
			);

			$competencyHasScholarity = $dbCompetencyScholarity->fetchRow( $whereCompetencyScholarity );

			// Check if the competency is already related with the Scholarity
			if ( empty( $competencyHasScholarity ) ) {

			    $dataCompetencyHasScholarity = array(
				'fk_id_competency'	=> $idCompetency,
				'fk_id_perscholarity'   => $idQualification
			    );

			    $dbCompetencyScholarity->insert( $dataCompetencyHasScholarity );
			}
		    }
		}
	    }
	    
	    $dbAdapter->commit();
	    $return = array( 'status'  =>  1 );
	    
	    return $return;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    
	    $return = array(
		'status'    =>  0,
		'error'	    =>	$e->getMessage()
	    );
	    
	    return $return;
	}
    }
    
    /**
     *
     * @return boolean 
     */
    public function sendQualification()
    {
	$dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
	$dbAdapter->beginTransaction();
	try {
	    
	    $qualifications = array(
		array(
		    'nome'	=> 'Sertifikadu II iha Administrasaun TESTE',
		    'id'	=> 31,
		    'codigo'	=> 'ADFAD113',
		    'nivel'	=> 3,
		    'competencies' => array(
			array(
			    'nome'   => 'Uza pratika serbisu neebé sustentavel ba ambiente',
			    'id'     => '10',
			    'codigo' => 'ADFAD1001A'
			),
			array(
			    'nome'   => 'Halao prosedimentu kleriku',
			    'id'     => '12',
			    'codigo' => 'ADFJT4002A'
			)
		    )
		),
		array(
		    'nome'	=> 'Sertifikadu III iha Servisus Finanseiru',
		    'id'	=> 55,
		    'codigo'	=> 'ADFSF311',
		    'nivel'	=> 4,
		    'competencies' => array(
			array(
			    'nome'   => 'Jere ita-nia dezempeñu profisionál rasik',
			    'id'     => '17',
			    'codigo' => 'ADFJT4001A'
			),
			array(
			    'nome'   => 'Maneja no ekilibra osan-kontadu',
			    'id'     => '25',
			    'codigo' => 'ADFSF3004A'
			)
		    )
		)
	    );
	    
	    $response = App_Util_Indmo::request( 'cron/api/qualification', array( 'qualifications' => $qualifications ) );
	    
	    $dbAdapter->commit();
	    return true;
	    
	} catch ( Exception $e ) {

	    $dbAdapter->rollBack();
	    echo 'Error sending qualifications: ' . $e->getMessage();
	    return false;
	}
    }
}
