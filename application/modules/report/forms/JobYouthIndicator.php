<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_JobYouthIndicator extends Report_Form_StandardSearch
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
			    ->setValue( 'job/youth-indicator-report' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'title' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'Relatoriu: Indikador juventude nian / 15 - 29' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$this->addElements( $elements );
    }
}