<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ClientContact extends App_Form_Default
{

    const ID = 39;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'contact' );
	
	$elements[] = $this->createElement( 'text', 'contact_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setRequired( true )
			    ->setLabel( 'Naran Kontatu' );
	
	$elements[] = $this->createElement( 'text', 'cell_fone' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 15 )
			    ->setAttrib( 'class', 'm-wrap span12 mobile-phone' )
			    ->setRequired( true )
			    ->setLabel( 'Telemovel' );
	
	$elements[] = $this->createElement( 'text', 'house_fone' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 15 )
			    ->setAttrib( 'class', 'm-wrap span12 house-phone' )
			    ->setLabel( 'Telefone husi Uma' );
	
	$elements[] = $this->createElement( 'text', 'job_fone' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 15 )
			    ->setAttrib( 'class', 'm-wrap span12 house-phone' )
			    ->setLabel( 'Telefone husi Serbisu' );
	
	$elements[] = $this->createElement( 'text', 'email' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 250 )
			    ->addFilter( 'StringTrim' )
			    ->addValidator( 'EmailAddress' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'E-mail' );
	
	$elements[] = $this->createElement( 'text', 'website' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 250 )
			    ->addFilter( 'StringTrim' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Website' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}