<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_RIContractSearch extends App_Form_Default
{   
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'minimum_amount' )->setValue( 0 )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'maximum_amount' )->setValue( 100000 )->setDecorators( array( 'ViewHelper' ) );
	
	$mapperInstitute = new Register_Model_Mapper_EducationInstitute();
	$rows = $mapperInstitute->listByFilters();
	
	$optInstitutes[''] = '';
	foreach ( $rows as $row )
	    $optInstitutes[$row['id_fefpeduinstitution']] = $row['institution'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpeduinstitution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optInstitutes )
			    ->setLabel( 'Sentru Formasaun' );
	
	
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