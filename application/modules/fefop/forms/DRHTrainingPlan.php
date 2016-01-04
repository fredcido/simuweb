<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_DRHTrainingPlan extends App_Form_Default
{
    const ID = 188;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_drh_trainingplan' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpeduinstitution' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_addcountry' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'need_insurance' )->setDecorators( array( 'ViewHelper' ) )->setValue( 0 );
	
	$elements[] = $this->createElement( 'hidden', 'expense' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'amount_expense' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'staff' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'handicapped' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'gender' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'unit_cost' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'final_cost' )->setIsArray( true );
	$elements[] = $this->createElement( 'hidden', 'training_fund' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'text', 'entity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Entidade responsável ba formasaun' );
	
	$mapperScholarityArea = new Register_Model_Mapper_ScholarityArea();
	$sections = $mapperScholarityArea->fetchAll();
	
	$optScholarityArea[''] = '';
	foreach ( $sections as $section )
	    $optScholarityArea[$section['id_scholarity_area']] = $section['scholarity_area'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_scholarity_area' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen focused' )
			    ->setLabel( 'Area Kursu' )
			    ->addMultiOptions( $optScholarityArea )
			    ->setRequired( true );
	
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
	
	$elements[] = $this->createElement( 'text', 'country_timor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setValue( 'Timor-Leste' )
			    ->setLabel( 'Nasaun' );
	
	$dbCountry = App_Model_DbTable_Factory::get( 'AddCountry' );
	$countries = $dbCountry->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $countries as $country )
	    if ( $country['id_addcountry'] != 1 )
		$optCountry[$country['id_addcountry']] = $country['country'];
	
	$elements[] = $this->createElement( 'select', 'country' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Nasaun' )
			    ->addMultiOptions( $optCountry );
	
	$optModality[''] = '';
	$optModality['L'] = 'Iha territóriu no ho entidade nasionál';
	$optModality['A'] = 'Iha rai liur';
	$optModality['T'] = 'Iha territóriu nasionál ho entidade formadora husi rai liur';
	
	$elements[] = $this->createElement( 'select', 'modality' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Modalidade' )
			    ->addMultiOptions( $optModality )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'date_start' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setLabel( 'Data hahú' );
	
	$elements[] = $this->createElement( 'text', 'date_finish' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask' )
			    ->setRequired( true )
			    ->setLabel( 'Data finalizasaun' );
	
	$elements[] = $this->createElement( 'text', 'duration_days' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'readonly', true )
			    ->setLabel( 'Durasaun' );
	
	$elements[] = $this->createElement( 'text', 'city' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Sidade' );
	
	$elements[] = $this->createElement( 'text', 'amount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'readonly', true )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->setLabel( 'KUSTU TOTÁL BA FORMASAUN' );
	
	$elements[] = $this->createElement( 'text', 'amount_expenses' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->removeDecorator( 'Label' )
			    ->setAttrib( 'readonly', true )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->setLabel( 'Totál' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}