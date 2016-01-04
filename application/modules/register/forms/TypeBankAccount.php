<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_TypeBankAccount extends App_Form_Default
{

    const ID = 163;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_typebankaccount' )
			   ->setDecorators( array( 'ViewHelper' ) );

	$elements[] = $this->createElement( 'text', 'type_bankaccount' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Tipu Konta Banku' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'textarea', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'span12' )
			    ->setAttrib( 'cols', 100 )
			    ->setAttrib( 'rows', 5 )
			    ->setLabel( 'Deskrisaun' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }
}