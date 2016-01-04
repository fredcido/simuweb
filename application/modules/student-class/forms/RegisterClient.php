<?php

/**
 *
 * @author Frederico Estrela
 */
class StudentClass_Form_RegisterClient extends App_Form_Default
{
    const ID = 108;
    
    public function init()
    {
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpstudentclass' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'client' );
	
	$elements[] = $this->createElement( 'hidden', 'status' )
			    ->setIsArray( true )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'date_drop' )
			    ->setIsArray( true )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$this->addElements( $elements );
    }
}