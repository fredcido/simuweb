<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_FEContractSearch extends App_Form_Default
{   
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();
	
	$mapperFeContract = new Fefop_Model_Mapper_FEContract();
	
	// Combo to search clients
	$clients = $mapperFeContract->listBeneficiaries();
	$optClients[''] = '';
	foreach ( $clients as $row )
	    $optClients[$row['id_perdata']] = Client_Model_Mapper_Client::buildNumRow( $row ) . ' - ' . Client_Model_Mapper_Client::buildName( $row );
	
	$elements[] = $this->createElement( 'select', 'fk_id_perdata' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optClients )
			    ->setLabel( 'Benefisiariu' );
	
	// Combo to search enterprises
	$enterprises = $mapperFeContract->listEnterprises();
	$optEnterprises[''] = '';
	foreach ( $enterprises as $row )
	    $optEnterprises[$row['id_fefpenterprise']] = $row['enterprise_name'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpenterprise' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optEnterprises )
			    ->setLabel( 'Empreza' );
	
	// Combo to search institutes
	$intitutes = $mapperFeContract->listInstitutes();
	$optInstitutes[''] = '';
	foreach ( $intitutes as $row )
	    $optInstitutes[$row['id_fefpeduinstitution']] = $row['institution'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpeduinstitution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optInstitutes )
			    ->setLabel( 'Inst. Ensinu' );
	
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$districts = $dbDistrict->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $districts as $district )
	    $optCountry[$district['id_adddistrict']] = $district['District'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optCountry )
			    ->setLabel( 'Distritu' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_addsubdistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Sub-Distritu' );
	
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
			    ->setLabel( 'Okupasaun' )
			    ->addMultiOptions( $optOccupations );
	
	$elements[] = $this->createElement( 'text', 'date_start' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Loron Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'date_finish' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Loron Remata' );
	
	$this->addElements( $elements );
    }
}