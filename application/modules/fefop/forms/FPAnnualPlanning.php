<?php

/**
 *
 * @author Frederico Estrela
 */
class Fefop_Form_FPAnnualPlanning extends App_Form_Default
{
    const ID = 182;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_annual_planning' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpeduinstitution' )->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'text', 'institution' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Sentru ba formasaun ne\'ebé akreditadu' );
	
	$optYear[''] = '';
	$finalYear = 2014 + 10;
	for ( $yearIni = 2014 - 2; $yearIni <= $finalYear; $yearIni++  )
	    $optYear[$yearIni] = $yearIni;
	
	$elements[] = $this->createElement( 'select', 'year_planning' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setRequired( true )
			    ->addMultiOptions( $optYear )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Tinan' );
	
	$elements[] = $this->createElement( 'text', 'total_students' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Alunu Nain Hira' );
	
	$elements[] = $this->createElement( 'text', 'total_cost' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'readOnly', true )
			    ->setRequired( true )
			    ->setValue( 0 )
			    ->setAttrib( 'class', 'm-wrap span12 money-mask' )
			    ->setLabel( 'Kustu Total' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}