<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_IsicClass extends App_Form_Default
{

    const ID = 87;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_isicclass' )
			   ->setDecorators( array( 'ViewHelper' ) );
	
	$mapperSection =  new Register_Model_Mapper_IsicSection();
	$section = $mapperSection->fetchAll();
	
	$optSections[''] = '';
	foreach ( $section as $section )
	    $optSections[$section['id_isicsection']] = $section['name_section'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicsection' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Sesaun' )
			    ->addMultiOptions( $optSections )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicdivision' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'disabled', true )
			    ->setLabel( 'Divisaun' )
			    ->setRegisterInArrayValidator(false)
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicgroup' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setAttrib( 'disabled', true )
			    ->setLabel( 'Grupu' )
			    ->setRegisterInArrayValidator(false)
			    ->setRequired( true );

	$elements[] = $this->createElement( 'text', 'name_class' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Klase' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'acronym' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 1 )
			    ->setAttrib( 'class', 'm-wrap span4' )
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