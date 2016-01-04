<?php

/**
 *
 * @author Frederico Estrela
 */
class Report_Form_JobPlacementConsolidated extends Report_Form_StandardSearch
{
    /**
     * 
     */
    public function init()
    {
	parent::init();
	
	$elements = array();
	
	$elements[] = $this->createElement( 'hidden', 'path' )->setValue( 'job/placement-report' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	$elements[] = $this->createElement( 'hidden', 'consolidated' )->setValue( 1 )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	$elements[] = $this->createElement( 'hidden', 'title' )->setValue( 'Relatoriu: Job Placements Consolidated' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' );
	
	$this->addElements( $elements );
    }
}