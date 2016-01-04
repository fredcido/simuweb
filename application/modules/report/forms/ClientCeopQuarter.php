<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_ClientCeopQuarter extends Report_Form_StandardSearch
{
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$elements = array();
	
	$this->removeElement( 'date_start' );
	$this->removeElement( 'date_finish' );
	$this->removeElement( 'fk_id_dec' );
	
	$elements[] = $this->createElement( 'hidden', 'path' )
			    ->setValue( 'client/ceop-quarter-report' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'title' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'Relatoriu: Kliente CEOP/Quarter' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$optYear[''] = '';
	for ( $i = 2005; $i <= date( 'Y' ) + 3; $i++ )
	    $optYear[$i] = $i;
	
	$elements[] = $this->createElement( 'select', 'year' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Tinan' )
			    ->addMultiOptions( $optYear )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' );
	
	$this->addElements( $elements );
    }
}