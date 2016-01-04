<?php

/**
 *
 * @author Frederico Estrela
 */
class Job_Form_VacancyClose extends App_Form_Default
{

    const ID = 111;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'close' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_jobvacancy' )->setAttrib( 'class', 'no-clear' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'num_position' )->setAttrib( 'class', 'no-clear' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'step' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' )->setValue( 'close' );
	$elements[] = $this->createElement( 'hidden', 'clients' )->setIsArray( true )->setDecorators( array( 'ViewHelper' ) );
	
	$this->addElements( $elements );
    }
}