<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_AppointmentObjective extends App_Form_Default
{

    const ID = 151;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_appointment_objective' )
			   ->setDecorators( array( 'ViewHelper' ) );

	$elements[] = $this->createElement( 'text', 'objective_desc' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 4000 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Intensaun ba Audiencia' )
			    ->setRequired( true );
	
	$optStatus[''] = '';
	$optStatus['1'] = 'Loos';
	$optStatus['0'] = 'Lae';
	
	$elements[] = $this->createElement( 'select', 'objective_appointment_active' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span4' )
			    ->setLabel( 'Ativu' )
			    ->setRequired( true )
			    ->addMultiOptions( $optStatus );
	
	$elements[] = $this->createElement( 'select', 'objective_observation' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span4' )
			    ->setLabel( 'Presiza observasaun' )
			    ->setRequired( true )
			    ->addMultiOptions( $optStatus );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}