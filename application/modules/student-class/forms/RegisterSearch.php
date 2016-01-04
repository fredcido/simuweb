<?php

/**
 *
 * @author Frederico Estrela
 */
class StudentClass_Form_RegisterSearch extends App_Form_Default
{

    const ID = 60;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'text', 'class_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Klase' );
	
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
	
	$mapperEducationInsitute = new Register_Model_Mapper_EducationInstitute();
	$rows = $mapperEducationInsitute->listByFilters();
	
	$optEducationInstitute[''] = '';
	foreach ( $rows as $row )
	    $optEducationInstitute[$row->id_fefpeduinstitution] = $row->institution;
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpeduinstitution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Instituisaun Ensinu' )
			    ->addMultiOptions( $optEducationInstitute )
			    ->setRegisterInArrayValidator( false )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' );
	
	$optTransport['1'] = 'Loke';
	$optTransport['0'] = 'Taka';
	$optTransport['2'] = 'Kansela';
	
	$elements[] = $this->createElement( 'select', 'active' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Status' )
			    ->addMultiOptions( $optTransport )
			    ->setValue( 1 )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'start_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Loron Inisiu' );
	
	$elements[] = $this->createElement( 'text', 'schedule_finish_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Loron Planu Remata' );
	
	$filters = array(
	    'type'	=> Register_Model_Mapper_PerTypeScholarity::NON_FORMAL
	);
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$optScholarity = $mapperScholarity->getOptionsScholarity( $filters );
	
	$elements[] = $this->createElement( 'select', 'fk_id_perscholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRegisterInArrayValidator( false )
			    ->addMultiOptions( $optScholarity )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Kursu' );
	
	$this->addElements( $elements );
    }
}