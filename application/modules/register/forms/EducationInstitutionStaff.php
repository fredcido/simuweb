<?php

/**
 *
 * @author Frederico Estrela
 */
class Register_Form_EducationInstitutionStaff extends App_Form_Default
{

    const ID = 77;
    
    public function init()
    {
	$this->setAttrib( 'class', 'horizontal-form' );
	
	$elements = array();	
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_fefpeduinstitution' )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'id_staff' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'fk_id_perdata' )
			    ->setDecorators( array( 'ViewHelper' ) );
	
	$elements[] = $this->createElement( 'hidden', 'step' )
			    ->setDecorators( array( 'ViewHelper' ) )
			    ->setAttrib( 'class', 'no-clear' )
			    ->setValue( 'staff' );
	
	$elements[] = $this->createElement( 'text', 'staff_name' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'readOnly', true )
			    ->setAttrib( 'maxlength', 250 )
			    ->setRequired( true )
			    ->setAttrib( 'class', 'm-wrap span12 focused' )
			    ->setLabel( 'Naran Funcionariu' );
	
	$elements[] = $this->createElement( 'text', 'birth_date' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'maxlength', 10 )
			    //->setRequired( true )
			    ->setAttrib( 'disabled', true )
			    ->setAttrib( 'class', 'm-wrap span12 date-mask date' )
			    ->setLabel( 'Data Moris' );
	
	$optGender[''] = '';
	$optGender['M'] = 'MANE';
	$optGender['F'] = 'FETO';
	
	$elements[] = $this->createElement( 'select', 'gender' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Seksu' )
			    ->setAttrib( 'disabled', true )
			    ->addMultiOptions( $optGender );
	
	$optPost[''] = '';
	$optPost['Formandu'] = 'Formandu';
	$optPost['Trabalhador'] = 'Traballador';
	$optPost['Assistente'] = 'Assistente';
	$optPost['Treinador'] = 'Treinador';
	$optPost['Chefe'] = 'Xefe';
	$optPost['Diretor'] = 'Diretor';
	
	$elements[] = $this->createElement( 'select', 'post' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12' )
			    ->setLabel( 'Kargo' )
			    ->setRequired( true )
			    ->addMultiOptions( $optPost );
	
	$dbOccupationTimor = App_Model_DbTable_Factory::get( 'PROFOcupationTimor' );
	$occupations = $dbOccupationTimor->fetchAll();
	
	$optOccupations[''] = '';
	foreach ( $occupations as $occupation )
	    $optOccupations[$occupation['id_profocupationtimor']] = $occupation['ocupation_name_timor'];
	
	$elements[] = $this->createElement( 'select', 'position' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->setAttrib( 'class', 'm-wrap span12 chosen' )
			    ->setLabel( 'Okupasaun' )
			    ->addMultiOptions( $optOccupations )
			    ->setRequired( true );
	
	$elements[] = $this->createElement( 'textarea', 'description' )
			    ->setDecorators( $this->getDefaultElementDecorators() )
			    ->addFilter( 'StringTrim' )
			    ->addFilter( 'StringToUpper' )
			    ->setAttrib( 'class', 'span12' )
			    ->setAttrib( 'cols', 100 )
			    ->setAttrib( 'rows', 2 )
			    ->setLabel( 'Deskrisaun Funcionariu' );
	
	App_Form_Toolbar::build( $this, self::ID );
	$this->addElements( $elements );
    }
}