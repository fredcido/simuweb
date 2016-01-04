<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_Scholarity extends App_Form_Default
{

    const ID = 98;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'id_perscholarity' )
			   ->setDecorators( array( 'ViewHelper' ) );
	
	$mapperTypeScholarity = new Register_Model_Mapper_PerTypeScholarity();
	$sections = $mapperTypeScholarity->fetchAll();
	
	$optTypeScholarity[''] = '';
	foreach ( $sections as $section )
	    $optTypeScholarity[$section['id_pertypescholarity']] = $section['type_scholarity'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_pertypescholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Tipu Kursu' )
			    ->addMultiOptions( $optTypeScholarity )
			    ->setRequired( true );
	
	$mapperScholarityArea = new Register_Model_Mapper_ScholarityArea();
	$sections = $mapperScholarityArea->fetchAll();
	
	$optScholarityArea[''] = '';
	foreach ( $sections as $section )
	    $optScholarityArea[$section['id_scholarity_area']] = $section['scholarity_area'];
	
	$elements[] = $this->createElement( 'select', 'fk_id_scholarity_area' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen focused' )
			    ->setLabel( 'Area Kursu' )
			    ->addMultiOptions( $optScholarityArea )
			    ->setRequired( true );

	$elements[] = $this->createElement( 'text', 'scholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Naran Kursu' )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'text', 'Title' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Titulu' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_perlevelscholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->setRegisterInArrayValidator( false )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Nivel' );
	
	$elements[] = $this->createElement( 'select', 'category' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true )
			    ->setLabel( 'Kategoria' );
	
	$elements[] = $this->createElement( 'text', 'external_code' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'maxlength', 50 )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Kodigu Esterno' );
	
	$this->addElements( $elements );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->setDecorators( $this->getDefaultFormDecorators() );
    }

}