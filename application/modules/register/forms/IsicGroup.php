<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_IsicGroup extends App_Form_Default
{

    const ID = 86;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_isicgroup' )
			   ->setDecorators( array( 'ViewHelper' ) );
	
	$mapperSection =  new Register_Model_Mapper_IsicSection();
	$sections = $mapperSection->fetchAll();
	
	$optSections[''] = '';
	foreach ( $sections as $section )
	    $optSections[$section['id_isicsection']] = $section['name_section'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicsection' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Sesaun' )
			    ->addMultiOptions( $optSections )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'select', 'fk_id_isicdivision' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    //->setAttrib( 'disabled', true )
			    ->setLabel( 'Divisaun' )
			    ->setRegisterInArrayValidator(false)
			    ->setRequired( true );

	$elements[] = $this->createElement( 'text', 'name_group' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Grupu' )
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
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}