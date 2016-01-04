<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_ClientDistrict extends Report_Form_StandardSearch
{
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'path' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'client/district-report' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'title' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'Relatoriu: Kliente husi Distritu' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$this->addElements( $elements );
    }
}