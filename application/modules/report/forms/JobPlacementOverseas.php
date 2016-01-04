<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_JobPlacementOverseas extends Report_Form_StandardSearch
{
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'path' )->setValue( 'job/placement-report' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	$elements[] = $this->createElement( 'hidden', 'overseas' )->setValue( 1 )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	$elements[] = $this->createElement( 'hidden', 'title' )->setValue( 'Relatoriu: Job Placements Overseas' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	
	$mapperCountry =  new Register_Model_Mapper_AddCountry();
	$countries = $mapperCountry->fetchAll();
	
	$optNations[''] = '';
	foreach ( $countries as $country )
	    $optNations[$country['id_addcountry']] = $country['country'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_addcountry' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Lokalizasaun Internasional' )
			    ->addMultiOptions( $optNations );
	
	$this->addElements( $elements );
    }
}