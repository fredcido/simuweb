<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_EducationInstitutionContact extends App_Form_Default
{

    const ID = 75;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpeduinstitution' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'id_percontact' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'contact' );
	
	$elements[] = $this->createElement( 'text', 'contact_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 250 )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span8 focused' )
			    ->setLabel( 'Naran Contatu' );
	
	$elements[] = $this->createElement( 'text', 'cell_fone' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 15 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span8 mobile-phone' )
			    ->setLabel( 'Tele-móvel' );
	
	$elements[] = $this->createElement( 'text', 'house_fone' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 15 )
			    ->setAttrib( 'class', 'm-wrap span8 house-phone' )
			    ->setLabel( 'Telefone Uma' );
	
	$elements[] = $this->createElement( 'text', 'job_fone' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 15 )
			    ->setAttrib( 'class', 'm-wrap span8' )
			    ->setLabel( 'Telefone Serbisu' );
	
	$elements[] = $this->createElement( 'text', 'email' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 250 )
			    ->addValidator( 'EmailAddress' )
			    ->setAttrib( 'class', 'm-wrap span8' )
			    ->setLabel( 'E-mail' );
	
	$elements[] = $this->createElement( 'text', 'website' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 250 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Website' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}