<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_BarrierType extends App_Form_Default
{

    const ID = 135;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_barrier_type' )
			   ->setDecorators( array( 'ViewHelper' ) );

	$elements[] = $this->createElement( 'text', 'barrier_type_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 255 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Tipu Barrerira' )
			    ->setRequired( true );
	
	$optStatus[''] = '';
	$optStatus['1'] = 'Loos';
	$optStatus['0'] = 'Lae';
	
	$elements[] = $this->createElement( 'select', 'barrier_type_active' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span4' )
			    ->setLabel( 'Ativu' )
			    ->setRequired( true )
			    ->addMultiOptions( $optStatus );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}