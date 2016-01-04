<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_EducationInstitutionCourse extends App_Form_Default
{

    const ID = 76;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpeduinstitution' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'no-clear' );
	
	$elements[] = $this->createElement( 'hidden', 'id_relationship' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'course' );
	
	$elements[] = $this->createElement( 'hidden', 'scholarity_classification' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setValue( 'course' );
	
	$mapperTypeScholarity =  new Register_Model_Mapper_PerTypeScholarity();
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
	
	$elements[] = $this->createElement( 'select', 'category' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRequired( true )
			    ->setRegisterInArrayValidator( false )
			    ->setLabel( 'Kategoria' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_scholarity' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Kursu' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true );
	
	/*
	$optClassification[''] = '';
	$optClassification['R'] = 'Rejistrasaun';
	//$optClassification['A'] = 'Acreditasaun';
	$optClassification['C'] = 'Formasaun Comunitaria';
	
	$elements[] = $this->createElement( 'select', 'classification' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Klasifikasaun' )
			    ->addMultiOptions( $optClassification );
	 */
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}