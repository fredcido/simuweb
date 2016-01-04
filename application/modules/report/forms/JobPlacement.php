<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_JobPlacement extends Report_Form_StandardSearch
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
			    ->setValue( 'job/placement-report' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'title' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'Relatoriu: Job Placements' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$this->addElements( $elements );
    }
}