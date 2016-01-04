<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_EducationInstitutionQualification extends App_Form_Default
{

    const ID = 155;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpeduinstitution' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );

	$elements[] = $this->createElement( 'hidden', 'step' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'qualification' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_staff' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true )
			    ->setLabel( 'Treinador' );
	
	$mapperScholarity = new Register_Model_Mapper_PerScholarity();
	$optCategory = $mapperScholarity->getOptionsCategory( Register_Model_Mapper_PerTypeScholarity::NON_FORMAL );
	
	$elements[] = $this->createElement( 'select', 'category_qualification' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 200 )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRequired( true )
			    ->addMultiOptions( $optCategory )
			    ->setLabel( 'Kategoria' );
	
	$elements[] = $this->createElement( 'select', 'fk_id_perscholarity_staff' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setRegisterInArrayValidator( false )
			    ->setRequired( true )
			    ->setLabel( 'Kursu' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}