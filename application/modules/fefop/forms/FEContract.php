<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_FEContract extends App_Form_Default
{
    const ID = 187;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_fe_contract' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefop_contract' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpeduinstitution' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpenterprise' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_trainee' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fe_registration' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'cost_expense' )->setIsArray( true );
	
	$elements[] = $this->createElement( 'text', 'entity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Entidade promotora' );
	
	$elements[] = $this->createElement( 'text', 'beneficiary' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Benefisiariu' );
	
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
			    ->addMultiOptions( $optOccupations )
			    ->setRequired( true );
	
	$dbDistrict = App_Model_DbTable_Factory::get( 'AddDistrict' );
	$districts = $dbDistrict->fetchAll();
	
	$optCountry[''] = '';
	foreach ( $districts as $district )
	    $optCountry[$district['id_adddistrict']] = $district['District'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_adddistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->addMultiOptions( $optCountry )
			    ->setValue( Admin_Model_Mapper_SysUser::userCeopToDistrict() )
			    ->setLabel( 'Distritu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_addsubdistrict' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Sub-Distritu' )
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
	
	/*
	$elements[] = $this->createElement( 'text', 'date_formation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'readonly', true )
			    ->setLabel( 'Data limite formasaun' );
	 */
	
	$elements[] = $this->createElement( 'text', 'duration_month' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'readonly', true )
			    ->setLabel( 'Durasaun' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}