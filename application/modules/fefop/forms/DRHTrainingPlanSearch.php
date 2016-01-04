<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_DRHTrainingPlanSearch extends App_Form_Default
{   
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'minimum_amount' )->setValue( 1000 )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'maximum_amount' )->setValue( 200000 )->setDecorators( array( 'ViewHelper' ) );
	
	$mapperDrhTrainingPlan = new Fefop_Model_Mapper_DRHTrainingPlan();
	$institutes = $mapperDrhTrainingPlan->listIntitutes();
	
	$optInstitute[''] = '';
	foreach ( $institutes as $institute )
	    $optInstitute[$institute['fk_id_fefpeduinstitution']] = $institute['entity'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpeduinstitution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen focused' )
			    ->setLabel( 'Entidade responsável ba formasaun' )
			    ->addMultiOptions( $optInstitute );
	
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
	
	$dbCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$countries = $dbCountry->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $countries as $country )
	    $optCountry[$country['id_addcountry']] = $country['country'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_addcountry' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Nasaun' )
			    ->addMultiOptions( $optCountry );
	
	$optModality[''] = '';
	$optModality['L'] = 'Em território e entidades nacionais';
	$optModality['A'] = 'No estrangeiro';
	$optModality['T'] = 'Em território nacional com entidade formadora estrangeira';
	
	$elements[] = $this->createElement( 'select', 'modality' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Modalidade' )
			    ->addMultiOptions( $optModality );
	
	$elements[] = $this->createElement( 'text', 'date_start' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setLabel( 'Data hahú' );
	
	$elements[] = $this->createElement( 'text', 'date_finish' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setLabel( 'Data finalizasaun' );
	
	$this->addElements( $elements );
    }
}