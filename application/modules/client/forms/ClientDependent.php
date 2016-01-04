<?php

/**
 *
 * @author Frederico Estrela
 */
class Client_Form_ClientDependent extends App_Form_Default
{

    const ID = 38;
    
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
			    ->setValue( 'dependent' );
	
	$elements[] = $this->createElement( 'text', 'dependent_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setRequired( true )
			    ->setLabel( 'Naran Dependente' );
	
	$elements[] = $this->createElement( 'text', 'birth_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    //->setAttrib( 'readOnly', true )
			    ->setLabel( 'Data Moris' );
	
	$optGender['FETO'] = 'FETO';
	$optGender['MANE'] = 'MANE';
	
	$elements[] = $this->createElement( 'select', 'gender' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Seksu' )
			    ->setRequired( true )
			    ->addMultiOptions( $optGender );
	
	$optTypeDependent['AMAN'] = 'AMAN';
	$optTypeDependent['INAN'] = 'INAN';
	$optTypeDependent['OAN RASIK'] = 'OAN RASIK';
	$optTypeDependent['OAN ADOPTIVO HAKIAK'] = 'OAN ADOPTIVO HAKIAK';
	$optTypeDependent['EMA SELUK HELA HO ITA'] = 'EMA SELUK HELA HO ITA';
	
	$elements[] = $this->createElement( 'select', 'type_dependent' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setLabel( 'Tipu Dependensia' )
			    ->setRequired( true )
			    ->addMultiOptions( $optTypeDependent );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}