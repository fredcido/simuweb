<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_FERegistrationSearch extends App_Form_Default
{   
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();
	
	$mapperFeRegistration = new Fefop_Model_Mapper_FERegistration();
	
	// Combo to search clients
	$clients = $mapperFeRegistration->listBeneficiaries();
	$optClients[''] = '';
	foreach ( $clients as $row )
	    $optClients[$row['id_perdata']] = Client_Model_Mapper_Client::buildNumRow( $row ) . ' - ' . Client_Model_Mapper_Client::buildName( $row );
	
	$elements[] = $this->createElement( 'select', 'fk_id_perdata' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optClients )
			    ->setLabel( 'Benefisiariu' );
	
	// Combo to search enterprises
	$enterprises = $mapperFeRegistration->listEnterprises();
	$optEnterprises[''] = '';
	foreach ( $enterprises as $row )
	    $optEnterprises[$row['id_fefpenterprise']] = $row['enterprise_name'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpenterprise' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optEnterprises )
			    ->setLabel( 'Empreza' );
	
	// Combo to search institutes
	$intitutes = $mapperFeRegistration->listInstitutes();
	$optInstitutes[''] = '';
	foreach ( $intitutes as $row )
	    $optInstitutes[$row['id_fefpeduinstitution']] = $row['institution'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpeduinstitution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optInstitutes )
			    ->setLabel( 'Inst. Ensinu' );
	
	$optIdentifier = array(
	    //''		    => 'Hotu-Hotu',
	    //'professional'  => 'Formação Profissional',
	    //'formation'	    => 'Formação Academica',
	    'selected'	    => "Formasaun iha servisu fatin ne'ebe hakarak"
	);
	
	$elements[] = $this->createElement( 'select', 'identifier' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen focused' )
			    ->setLabel( 'Formasaun' )
			    ->addMultiOptions( $optIdentifier );
	
	$mapperScholarityArea = new Register_Model_Mapper_ScholarityArea();
	$sections = $mapperScholarityArea->fetchAll();
	
	$optScholarityArea[''] = '';
	foreach ( $sections as $section )
	    $optScholarityArea[$section['id_scholarity_area']] = $section['scholarity_area'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_scholarity_area' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen focused' )
			    ->setLabel( 'Area Kursu' )
			    ->addMultiOptions( $optScholarityArea );
	
	$dbOccupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$occupations = $dbOccupationTimor->fetchAll();
	
	$optOccupations[''] = '';
	foreach ( $occupations as $occupation )
	    $optOccupations[$occupation['id_profocupationtimor']] = $occupation['acronym'] . ' ' . $occupation['ocupation_name_timor'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_profocupationtimor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Dezignasaun' )
			    ->addMultiOptions( $optOccupations );
	
	$dbScholarityLevel = App_Model_DbTable_Factory::get( 'PerLevelScholarity' );
	$levels = $dbScholarityLevel->fetchAll( array(), array( 'id_perlevelscholarity' ) );
	
	$optLevel[''] = '';
	foreach ( $levels as $level )
	    $optLevel[$level->id_perlevelscholarity] = $level->level_scholarity;
	
	$elements[] = $this->createElement( 'select', 'fk_id_perlevelscholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optLevel )
			    ->setLabel( 'Nivel' );
	
	$this->addElements( $elements );
    }
}