<?php

/**
 *
 * @author Frederico Estrela
 */
class Job_Form_MatchAutomatic extends Job_Form_Match
{
    /**
     * 
     */
    public function init()
    {
	$this->setAttrib( 'onsubmit', 'return Job.Match.addList( this )' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_jobvacancy' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'clients' )->setIsArray( true )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'source' )->setValue( 'A' )->setDecorators( array( 'ViewHelper' ) );
	
	$this->addElements( $elements );
    }
}