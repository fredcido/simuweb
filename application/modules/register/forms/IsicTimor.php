<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_IsicTimor extends App_Form_Default
{

    const ID = 88;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_isicclasstimor' )
			   ->setDecorators( array( 'ViewHelper' ) );
	
	$mapperDivision =  new Register_Model_Mapper_IsicDivision();
	$divisions = $mapperDivision->fetchAll();
	
	$optDivisions[''] = '';
	foreach ( $divisions as $divisions )
	    $optDivisions[$divisions['id_isicdivision']] = $divisions['name_disivion'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicdivision' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Divisaun' )
			    ->addMultiOptions( $optDivisions )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicgroup' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Grupu' )
			    ->setRegisterInArrayValidator(false)
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicclass' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Klase' )
			    ->setRegisterInArrayValidator(false)
			    ->setRequired( true );

	$elements[] = $this->createElement( 'text', 'name_classtimor' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran Klase Index Timor' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'acronym' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'm-wrap span4' )
			    ->setAttrib( 'readOnly', true )
			    ->setLabel( 'Sigla' )
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
    }

}