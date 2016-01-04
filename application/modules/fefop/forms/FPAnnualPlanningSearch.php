<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_FPAnnualPlanningSearch extends App_Form_Default
{   
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'search' );
	
	$elements = array();	
	
	$mapperFPAnnualPlanning = new Fefop_Model_Mapper_FPAnnualPlanning();
	$rows = $mapperFPAnnualPlanning->listInstitutes();
	
	$optInstitutes[''] = '';
	foreach ( $rows as $row )
	    $optInstitutes[$row['id_fefpeduinstitution']] = $row['institution'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_fefpeduinstitution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optInstitutes )
			    ->setLabel( 'Sentru ba formasaun ne\'ebé akreditadu' );
	
	$optYear[''] = '';
	$finalYear = 2014 + 10;
	for ( $yearIni = 2014 - 2; $yearIni <= $finalYear; $yearIni++  )
	    $optYear[$yearIni] = $yearIni;
	
	$elements[] = $this->createElement( 'select', 'year_planning' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addMultiOptions( $optYear )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Tinan' );
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$categories = $mapperScholarity->getOptionsCategory( Register_Model_Mapper_PerTypeScholarity::NON_FORMAL );
	
	$optCategory[''] = '';
	foreach ( $categories as $id => $category )
	    $optCategory[$id] = $category;
	
	$elements[] = $this->createElement( 'select', 'category' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->addMultiOptions( $optCategory )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Kategoria' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_perscholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Kursu' )
			    ->setRegisterInArrayValidator( false );
	
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