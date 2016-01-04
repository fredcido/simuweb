<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_Ceop extends App_Form_Default
{

    const ID = 45;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_dec' )
			   ->setDecorators( array( 'ViewHelper' ) );

	$elements[] = $this->createElement( 'text', 'name_dec' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran CEOP' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'coordenator_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran Kordenador' );
	
	$elements[] = $this->createElement( 'text', 'email' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'E-mail' );
	
	$elements[] = $this->createElement( 'text', 'phone1' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 20 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Telefone 1' );
	
	$elements[] = $this->createElement( 'text', 'phone2' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 20 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Telefone 2' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}