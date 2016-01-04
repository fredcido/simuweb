<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_AreaScholarity extends App_Form_Default
{

    const ID = 150;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_scholarity_area' )->setDecorators( array( 'ViewHelper' ) );

	$elements[] = $this->createElement( 'text', 'scholarity_area' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Area Kursu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'acronym' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 3 )
			    ->setAttrib( 'class', 'm-wrap span4' )
			    ->setLabel( 'Sigla' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}