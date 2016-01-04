<?php

/**
 *
 * @author Frederico Estrela
 */
class StudentClass_Form_RegisterCancel extends App_Form_Default
{

    const ID = 157;
    
    /**
     * 
     */
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' )->setName( 'cancel' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpstudentclass' )->setAttrib( 'class', 'no-clear' )->setDecorators( array( 'ViewHelper' ) );
	$elements[] = $this->createElement( 'hidden', 'step' )->setDecorators( array( 'ViewHelper' ) )->setAttrib( 'class', 'no-clear' )->setValue( 'cancel' );
	
	$elements[] = $this->createElement( 'textarea', 'cancel_justification' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'rows', 4 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Justifikasaun' );
	
	$this->addElements( $elements );
    }
}