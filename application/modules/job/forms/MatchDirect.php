<?php

/**
 *
 * @author Frederico Estrela
 */
class Job_Form_MatchDirect extends Job_Form_Match
{
    /**
     * 
     */
    public function init()
    {
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_jobvacancy' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	$elements[] = $this->createElement( 'hidden', 'clients' )->setIsArray( true )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'source' )->setValue( 'D' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	
	$dbDec = App_Model_DbTable_Factory::get( 'Dec' );
	$rows = $dbDec->fetchAll( array(), array( 'name_dec' ) );
	
	$optCeop[''] = '';
	foreach ( $rows as $row )
	    $optCeop[$row->id_dec] = $row->name_dec;
	
	$elements[] = $this->createElement( 'select', 'fk_id_dec' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'CEOP' )
			    ->addMultiOptions( $optCeop )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	// List Districts just from Timor
	$mapperDistrict = new Register_Model_Mapper_AddDistrict();
	$districts = $mapperDistrict->listAll( 1 );
	
	$optDistrict[''] = '';
	foreach ( $districts as $district )
	    $optDistrict[$district->acronym] = $district->acronym;
	
	$elements[] = $this->createElement( 'select', 'num_district' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'm-wrap span3' )
			    ->setRequired( true )
			    ->addMultiOptions( $optDistrict )
			    ->setRegisterInArrayValidator( false );
	
	// List Sub Districts
	$mapperSubDistrict = new Register_Model_Mapper_AddSubDistrict();
	$subDistricts = $mapperSubDistrict->listAll();
	
	$optSubDistrict[''] = '';
	foreach ( $subDistricts as $subDistrict )
	    $optSubDistrict[$subDistrict->acronym] = $subDistrict->acronym;
	
	$elements[] = $this->createElement( 'select', 'num_subdistrict' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'm-wrap span3' )
			    ->addMultiOptions( $optSubDistrict )
			    ->setRequired( true )
			    ->setRegisterInArrayValidator( false );
	
	$elements[] = $this->createElement( 'text', 'num_servicecode' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'maxlength', 2 )
			    ->setAttrib( 'readOnly', true )
			    ->setValue( 'BU' )
			    ->setAttrib( 'class', 'm-wrap span2' );
	
	$elements[] = $this->createElement( 'text', 'num_year' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'maxlength', 2 )
			    ->setAttrib( 'class', 'm-wrap span2 text-numeric4' );
	
	$elements[] = $this->createElement( 'text', 'num_sequence' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'maxlength', 4 )
			    ->setAttrib( 'class', 'm-wrap span2' );
	
	$elements[] = $this->createElement( 'text', 'first_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setRequired( true )
			    ->setAttrib( 'maxlength', 80 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran Primeru' );
	
	$elements[] = $this->createElement( 'text', 'last_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 80 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran Ultimu' );
	
	$this->addElements( $elements );
    }
}