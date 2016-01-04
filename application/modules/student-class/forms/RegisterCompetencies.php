<?php

/**
 *
 * @author Frederico Estrela
 */
class StudentClass_Form_RegisterCompetencies extends StudentClass_Form_RegisterClient
{
    
    public function init()
    {
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpstudentclass' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'competencies' );
	
	$elements[] = $this->createElement( 'hidden', 'status' )
			    ->setIsArray( true )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'text', 'date_drop_out' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data Retira' );
	
	$this->addElements( $elements );
    }
}