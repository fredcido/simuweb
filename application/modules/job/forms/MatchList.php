<?php

/**
 *
 * @author Frederico Estrela
 */
class Job_Form_MatchList extends Job_Form_Match
{
    /**
     * 
     */
    public function init()
    {
	$elements = array();
	$elements[] = $this->createElement( 'hidden', 'fk_id_jobvacancy' )->setDecorators( array( 'ViewHelper' ) );
	
	$this->addElements( $elements );
    }
}